<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Reservation;
use App\Models\RoomInventory;
use App\Models\Spa;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\RunsConciergeTools;
use Tests\TestCase;

class ConciergeReservationToolTest extends TestCase
{
    use RefreshDatabase, RunsConciergeTools;

    public function test_ai_reservation_tool_creates_a_reference_and_holds_one_table(): void
    {
        $spa = Spa::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR']);
        $spa->treatments()->create(['name' => 'Botanical Calm', 'slug' => 'botanical-calm', 'category' => 'massage', 'duration_minutes' => 60, 'price' => 350000, 'is_active' => true]);
        $area = $spa->treatmentRooms()->create(['name' => 'Private Room', 'slug' => 'private-room', 'room_type' => 'single', 'min_guests' => 2, 'max_guests' => 2, 'reservation_fee' => 150000, 'is_active' => true]);
        $conversation = Conversation::create(['spa_id' => $spa->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        $date = CarbonImmutable::now($spa->timezone)->addDays(5);
        foreach (['11:00', '13:00'] as $slot) {
            RoomInventory::create(['treatment_room_id' => $area->id, 'reservation_date' => $date->toDateString(), 'time_slot' => $slot, 'total_rooms' => 1, 'reserved_rooms' => 0]);
        }

        $input = [
            'treatment_slug' => 'botanical-calm', 'treatment_room_slug' => 'private-room', 'date' => $date->toDateString(), 'time_slot' => '11:00',
            'guests' => 2, 'guest_name' => 'Ayu', 'guest_phone' => '+62 811 111 222',
        ];

        $quote = $this->runTool($spa, $conversation, 'check_room_availability', $input);
        $this->assertTrue($quote['ui']['available']);
        $this->assertSame(150000.0, $quote['ui']['quote']['reservation_fee']);

        $result = $this->runTool($spa, $conversation, 'create_reservation_request', $input);

        $reservation = Reservation::firstOrFail();
        $this->assertSame($reservation->reference, $result['ui']['reservation']['reservation_reference']);
        $this->assertSame(2, $reservation->guests);
        $this->assertSame('11:00', $reservation->time_slot);
        $this->assertSame(850000.0, (float) $reservation->quoted_total);
        $this->assertSame([1, 0], RoomInventory::orderBy('time_slot')->pluck('reserved_rooms')->all());

        $repeated = $this->runTool($spa, $conversation, 'create_reservation_request', $input);
        $this->assertSame($reservation->reference, $repeated['ui']['reservation']['reservation_reference']);
        $this->assertSame(1, Reservation::count());

        $taken = $this->runTool($spa, $conversation, 'create_reservation_request', [...$input, 'guest_name' => 'Budi']);
        $this->assertNull($taken['ui']);
        $this->assertSame(1, Reservation::count());

        $full = $this->runTool($spa, $conversation, 'check_room_availability', $input);
        $this->assertFalse($full['ui']['available']);
        $this->assertStringContainsString('13:00', $full['text']);
    }

    public function test_the_tools_refuse_a_party_that_does_not_fit_and_a_slot_that_does_not_exist(): void
    {
        $spa = Spa::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $spa->treatments()->create(['name' => 'Botanical Calm', 'slug' => 'botanical-calm', 'category' => 'massage', 'duration_minutes' => 60, 'price' => 350000, 'is_active' => true]);
        $area = $spa->treatmentRooms()->create(['name' => 'Bar', 'slug' => 'couple', 'room_type' => 'couple', 'min_guests' => 1, 'max_guests' => 2, 'is_active' => true]);
        $conversation = Conversation::create(['spa_id' => $spa->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);
        $date = CarbonImmutable::now($spa->timezone)->addDays(2);
        RoomInventory::create(['treatment_room_id' => $area->id, 'reservation_date' => $date->toDateString(), 'time_slot' => '15:00', 'total_rooms' => 2, 'reserved_rooms' => 0]);

        $base = ['treatment_slug' => 'botanical-calm', 'treatment_room_slug' => 'couple', 'date' => $date->toDateString(), 'guest_name' => 'Ayu', 'guest_phone' => '+62 811 111 222'];

        $tooMany = $this->runTool($spa, $conversation, 'create_reservation_request', [...$base, 'time_slot' => '15:00', 'guests' => 5]);
        $this->assertNull($tooMany['ui']);

        $unknownSlot = $this->runTool($spa, $conversation, 'check_room_availability', [...$base, 'time_slot' => '03:00', 'guests' => 2]);
        $this->assertStringContainsString('15:00', $unknownSlot['text']);

        $past = $this->runTool($spa, $conversation, 'check_room_availability', [...$base, 'date' => '2001-01-01', 'time_slot' => '15:00', 'guests' => 2]);
        $this->assertStringContainsString('past', $past['text']);

        $this->assertSame(0, Reservation::count());
    }

    public function test_the_reservation_tool_needs_a_usable_name_and_contact_before_it_books(): void
    {
        $spa = Spa::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $spa->treatments()->create(['name' => 'Botanical Calm', 'slug' => 'botanical-calm', 'category' => 'massage', 'duration_minutes' => 60, 'price' => 350000, 'is_active' => true]);
        $area = $spa->treatmentRooms()->create(['name' => 'Bar', 'slug' => 'couple', 'room_type' => 'couple', 'min_guests' => 1, 'max_guests' => 4, 'is_active' => true]);
        $conversation = Conversation::create(['spa_id' => $spa->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);
        $date = CarbonImmutable::now($spa->timezone)->addDays(2);
        RoomInventory::create(['treatment_room_id' => $area->id, 'reservation_date' => $date->toDateString(), 'time_slot' => '15:00', 'total_rooms' => 2, 'reserved_rooms' => 0]);

        $base = ['treatment_slug' => 'botanical-calm', 'treatment_room_slug' => 'couple', 'date' => $date->toDateString(), 'time_slot' => '15:00', 'guests' => 2];

        foreach ([
            ['guest_phone' => '+62 811 111 222'],
            ['guest_name' => 'Ayu'],
            ['guest_name' => 'Ayu', 'guest_phone' => 'call me'],
            ['guest_name' => 'Ayu', 'guest_email' => 'not-an-email'],
        ] as $guest) {
            $this->assertNull($this->runTool($spa, $conversation, 'create_reservation_request', [...$base, ...$guest])['ui']);
        }

        $this->assertSame(0, Reservation::count());

        $byEmail = $this->runTool($spa, $conversation, 'create_reservation_request', [...$base, 'guest_name' => 'Ayu', 'guest_email' => 'ayu@example.com']);

        $this->assertNotNull($byEmail['ui']);
        $this->assertSame('email', Reservation::firstOrFail()->contact_type);
    }

    public function test_treatments_search_filters_by_diet_and_budget_and_hides_unpublished_items(): void
    {
        $spa = Spa::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR']);
        $spa->treatments()->create(['name' => 'Aglio Olio', 'slug' => 'aglio-olio', 'category' => 'ritual', 'price' => 48000, 'tags' => ['botanical'], 'is_active' => true]);
        $spa->treatments()->create(['name' => 'Club Sandwich', 'slug' => 'club-sandwich', 'category' => 'ritual', 'price' => 52000, 'tags' => ['gentle'], 'is_active' => true]);
        $spa->treatments()->create(['name' => 'Secret Dish', 'slug' => 'secret', 'category' => 'ritual', 'price' => 10000, 'tags' => ['botanical'], 'is_active' => false]);
        $conversation = Conversation::create(['spa_id' => $spa->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        $vegan = $this->runTool($spa, $conversation, 'search_treatments', ['wellness' => 'botanical']);
        $this->assertSame(['aglio-olio'], collect($vegan['ui']['items'])->pluck('treatment_slug')->all());

        $cheap = $this->runTool($spa, $conversation, 'search_treatments', ['category' => 'ritual', 'max_price' => 50000]);
        $this->assertSame(['aglio-olio'], collect($cheap['ui']['items'])->pluck('treatment_slug')->all());

        $none = $this->runTool($spa, $conversation, 'search_treatments', ['query' => 'sushi']);
        $this->assertNull($none['ui']);
    }
}
