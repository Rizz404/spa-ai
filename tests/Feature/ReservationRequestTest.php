<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Reservation;
use App\Models\RoomInventory;
use App\Models\Spa;
use App\Models\TreatmentRoom;
use App\Modules\Reservation\Actions\ChangeReservationStatus;
use App\Modules\Reservation\Support\RoomAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReservationRequestTest extends TestCase
{
    use RefreshDatabase;

    private Spa $spa;

    private TreatmentRoom $lounge;

    private TreatmentRoom $terrace;

    private string $date;

    protected function setUp(): void
    {
        parent::setUp();

        $this->spa = Spa::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR', 'default_locale' => 'en', 'whatsapp' => '6281234567890', 'email' => 'hello@demo.test']);
        $this->lounge = $this->spa->treatmentRooms()->create(['name' => 'Indoor Lounge', 'slug' => 'indoor-lounge', 'room_type' => 'single', 'min_guests' => 1, 'max_guests' => 4, 'reservation_fee' => 0, 'is_active' => true]);
        $this->terrace = $this->spa->treatmentRooms()->create(['name' => 'Garden Terrace', 'slug' => 'garden-terrace', 'room_type' => 'garden', 'min_guests' => 1, 'max_guests' => 6, 'reservation_fee' => 25000, 'is_active' => true]);

        $this->spa->treatments()->create(['name' => 'Botanical Calm', 'slug' => 'botanical-calm', 'category' => 'massage', 'duration_minutes' => 60, 'price' => 350000, 'is_active' => true]);

        $this->date = CarbonImmutable::now($this->spa->timezone)->addDays(5)->toDateString();

        foreach ([$this->lounge, $this->terrace] as $area) {
            foreach (['11:00', '13:00'] as $slot) {
                RoomInventory::create(['treatment_room_id' => $area->id, 'reservation_date' => $this->date, 'time_slot' => $slot, 'total_rooms' => 1, 'reserved_rooms' => 0]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'treatment_slug' => 'botanical-calm', 'treatment_room_slug' => 'indoor-lounge', 'reservation_date' => $this->date, 'time_slot' => '11:00', 'guests' => 2,
            'guest_name' => 'Ayu', 'contact_type' => 'whatsapp', 'contact_value' => '+62 811 1234 5678', 'locale' => 'en',
            ...$overrides,
        ];
    }

    public function test_a_quote_reports_the_fee_and_free_tables(): void
    {
        $this->postJson('/demo/reservation/quote', $this->payload(['treatment_room_slug' => 'garden-terrace']))
            ->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('fee', 25000)
            ->assertJsonPath('time_range', '11:00–13:00')
            ->assertJsonPath('available_rooms', 1);
    }

    public function test_submitting_creates_a_pending_reservation_and_holds_a_table(): void
    {
        $this->postJson('/demo/reservation', $this->payload(['occasion' => 'birthday', 'special_request' => 'Window seat please']))
            ->assertCreated()
            ->assertJsonPath('status', Reservation::STATUS_PENDING)
            ->assertJsonStructure(['reference', 'handover' => ['whatsapp_url', 'phone_url', 'email_url']]);

        $reservation = Reservation::firstOrFail();
        $this->assertStringStartsWith('RS-', $reservation->reference);
        $this->assertSame('birthday', $reservation->occasion);
        $this->assertSame('Window seat please', $reservation->notes);
        $this->assertSame(1, RoomInventory::where('treatment_room_id', $this->lounge->id)->where('time_slot', '11:00')->value('reserved_rooms'));
    }

    public function test_a_full_slot_is_refused_with_alternatives(): void
    {
        RoomInventory::where('treatment_room_id', $this->lounge->id)->where('time_slot', '11:00')->update(['reserved_rooms' => 1]);

        $response = $this->postJson('/demo/reservation', $this->payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('treatment_room_slug');

        $alternatives = collect($response->json('alternatives'));
        $this->assertSame('13:00', $alternatives->firstWhere('type', 'slot')['time_slot']);
        $this->assertSame('garden-terrace', $alternatives->firstWhere('type', 'area')['slug']);
        $this->assertSame(0, Reservation::count());
    }

    public function test_the_party_must_suit_the_treatment_room(): void
    {
        $this->postJson('/demo/reservation', $this->payload(['guests' => 5]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('guests');
    }

    public function test_dates_and_slots_are_validated(): void
    {
        $this->postJson('/demo/reservation', $this->payload(['reservation_date' => '2001-01-01']))->assertUnprocessable()->assertJsonValidationErrors('reservation_date');

        $far = CarbonImmutable::now($this->spa->timezone)->addDays(RoomAvailability::MAX_ADVANCE_DAYS + 5)->toDateString();
        $this->postJson('/demo/reservation', $this->payload(['reservation_date' => $far]))->assertUnprocessable()->assertJsonValidationErrors('reservation_date');

        $this->postJson('/demo/reservation', $this->payload(['time_slot' => '03:00']))->assertUnprocessable()->assertJsonValidationErrors('time_slot');
        $this->postJson('/demo/reservation', $this->payload(['treatment_room_slug' => 'nowhere']))->assertUnprocessable()->assertJsonValidationErrors('treatment_room_slug');
    }

    public function test_contact_details_are_checked_for_the_chosen_method(): void
    {
        $this->postJson('/demo/reservation', $this->payload(['contact_type' => 'email', 'contact_value' => 'not-an-email']))->assertUnprocessable()->assertJsonValidationErrors('contact_value');
        $this->postJson('/demo/reservation', $this->payload(['contact_type' => 'phone', 'contact_value' => 'abc']))->assertUnprocessable()->assertJsonValidationErrors('contact_value');
        $this->postJson('/demo/reservation', $this->payload(['contact_type' => 'email', 'contact_value' => 'ayu@example.com']))->assertCreated();

        $this->assertSame('ayu@example.com', Reservation::firstOrFail()->guest_email);
    }

    public function test_a_reservation_links_to_the_conversation_it_came_from(): void
    {
        $conversation = Conversation::create(['spa_id' => $this->spa->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        $this->postJson('/demo/reservation', $this->payload(['guest_token' => $conversation->guest_token]))->assertCreated();

        $this->assertSame($conversation->id, Reservation::firstOrFail()->conversation_id);
    }

    public function test_cancelling_gives_the_table_back(): void
    {
        $this->postJson('/demo/reservation', $this->payload())->assertCreated();
        $reservation = Reservation::firstOrFail();

        app(ChangeReservationStatus::class)->releaseRoom($reservation);

        $this->assertSame(0, RoomInventory::where('treatment_room_id', $this->lounge->id)->where('time_slot', '11:00')->value('reserved_rooms'));
    }

    public function test_unpublished_spas_take_no_reservations(): void
    {
        $this->spa->update(['public_status' => 'draft']);

        $this->postJson('/demo/reservation/quote', $this->payload())->assertNotFound();
        $this->postJson('/demo/reservation', $this->payload())->assertNotFound();
    }
}
