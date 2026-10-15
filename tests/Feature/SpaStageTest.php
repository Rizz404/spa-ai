<?php

namespace Tests\Feature;

use App\Models\RoomInventory;
use App\Models\Spa;
use App\Models\Treatment;
use App\Models\TreatmentRoom;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaStageTest extends TestCase
{
    use RefreshDatabase;

    private Spa $spa;

    private Treatment $latte;

    private TreatmentRoom $terrace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->spa = Spa::create([
            'name' => 'Demo Spa', 'slug' => 'demo', 'public_status' => 'published', 'city' => 'Jakarta', 'country' => 'Indonesia', 'currency' => 'IDR',
            'default_locale' => 'id', 'opening_time' => '08:00', 'closing_time' => '22:00', 'phone' => '+62 21 1234', 'whatsapp' => '6281234567890',
            'description' => 'Sanctuary tenang di Jakarta.',
        ]);

        $this->latte = $this->spa->treatments()->create([
            'name' => 'Botanical Calm', 'slug' => 'botanical-calm', 'category' => 'massage', 'price' => 32000, 'description' => 'Pijat botanikal yang lembut.',
            'translations' => ['en' => ['name' => 'Botanical Calm', 'description' => 'A gentle botanical massage.']], 'tags' => ['botanical', 'signature'], 'contraindications' => ['sensitive_skin'], 'duration_minutes' => 60,
            'image_url' => 'images/spa/treatment.png', 'is_active' => true, 'sort_order' => 0,
        ]);
        $this->spa->treatments()->create(['name' => 'Stone Ritual', 'slug' => 'stone-ritual', 'category' => 'package', 'price' => 40000, 'is_unavailable' => true, 'is_active' => true, 'sort_order' => 1]);
        $this->spa->treatments()->create(['name' => 'Retired Ritual', 'slug' => 'retired', 'category' => 'massage', 'price' => 1000, 'is_active' => false]);

        $this->terrace = $this->spa->treatmentRooms()->create([
            'name' => 'Bamboo Pavilion', 'slug' => 'bamboo-pavilion', 'room_type' => 'garden', 'min_guests' => 1, 'max_guests' => 6, 'features' => ['garden_view'],
            'reservation_fee' => 0, 'is_active' => true,
        ]);
        $this->terrace->images()->create(['image_url' => 'images/rooms/bamboo-pavilion.svg', 'alt_text' => 'Terrace', 'sort_order' => 0]);
        RoomInventory::create([
            'treatment_room_id' => $this->terrace->id, 'reservation_date' => CarbonImmutable::now('Asia/Jakarta')->addDays(3)->toDateString(), 'time_slot' => '13:00',
            'total_rooms' => 2, 'reserved_rooms' => 0,
        ]);

        $this->spa->knowledgeItems()->create(['category' => 'facilities', 'title' => 'Wi-Fi gratis', 'body' => 'Wi-Fi cepat untuk semua tamu.', 'tags' => ['wifi'], 'is_active' => true]);
        $this->spa->knowledgeItems()->create(['category' => 'policies', 'title' => 'Kebijakan reservasi', 'body' => 'Batal gratis 2 jam sebelumnya.', 'is_active' => true]);
        $this->spa->knowledgeItems()->create(['category' => 'faq', 'title' => 'Ada musala?', 'body' => 'Ada, di sebelah toilet.', 'is_active' => true]);
    }

    public function test_the_opening_screen_lists_published_spas(): void
    {
        Spa::create(['name' => 'Draft Spa', 'slug' => 'draft']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Demo Spa')
            ->assertDontSee('Draft Spa')
            ->assertSee(route('spa.treatments', ['spaSlug' => 'demo', 'lang' => 'id']), false);
    }

    public function test_the_counter_greets_guests_in_the_requested_language(): void
    {
        $this->get('/demo')->assertOk()->assertSee('Demo Spa')->assertSee('Saatnya kembali');
        $this->get('/demo?lang=en')->assertOk()->assertSee('A moment to')->assertSee('lang="en"', false);
        $this->get('/demo?lang=ja')->assertOk()->assertSee('心ほどける');
        $this->get('/demo?lang=xx')->assertOk()->assertSee('Saatnya kembali');
    }

    public function test_every_scene_renders_and_carries_the_concierge_chat(): void
    {
        $routes = [
            'spa.show', 'spa.treatments', 'spa.rooms', 'spa.facilities', 'spa.info', 'spa.staff', 'spa.reservation',
        ];

        foreach ($routes as $route) {
            $this->get(route($route, ['spaSlug' => 'demo']))
                ->assertOk()
                ->assertSee('id="concierge-app"', false)
                ->assertSee('data-scene=', false);
        }
    }

    public function test_unpublished_and_unknown_spas_are_not_found(): void
    {
        Spa::create(['name' => 'Draft', 'slug' => 'draft']);

        $this->get('/draft')->assertNotFound();
        $this->get('/draft/treatments')->assertNotFound();
        $this->get('/nowhere')->assertNotFound();
    }

    public function test_the_treatments_scene_narrates_from_stored_data_and_lists_active_items(): void
    {
        $this->get('/demo/treatments?lang=en')
            ->assertOk()
            ->assertSee('Explore 2 treatments and rituals, from IDR 32.000')
            ->assertSee('Botanical Calm')
            ->assertSee('Stone Ritual')
            ->assertDontSee('Retired Ritual');
    }

    public function test_a_treatment_shows_its_price_contraindications_and_tags(): void
    {
        $this->get('/demo/treatments/botanical-calm?lang=en')
            ->assertOk()
            ->assertSee('Botanical Calm')
            ->assertSee('IDR 32.000')
            ->assertSee('A gentle botanical massage.')
            ->assertSee('Sensitive skin')
            ->assertSee('Signature')
            ->assertSee('Discuss these with staff before booking: Sensitive skin.')
            ->assertSee('images/spa/treatment.png', false);

        $this->get('/demo/treatments/stone-ritual?lang=en')->assertOk()->assertSee('Temporarily unavailable');

        $this->get('/demo/treatments/retired')->assertNotFound();
        $this->get('/demo/treatments/nothing-here')->assertNotFound();
    }

    public function test_the_rooms_scenes_show_capacity_and_fees(): void
    {
        $this->get('/demo/rooms?lang=en')->assertOk()->assertSee('Bamboo Pavilion')->assertSee('Welcome to Bamboo Pavilion.');

        $this->get('/demo/rooms/bamboo-pavilion?lang=en')
            ->assertOk()
            ->assertSee('Bamboo Pavilion')
            ->assertSee('Garden view')
            ->assertSee('No room supplement.')
            ->assertSee('area=bamboo-pavilion', false);

        $this->get('/demo/rooms/nowhere')->assertNotFound();
    }

    public function test_the_facility_scenes_show_stored_services(): void
    {
        $facility = $this->spa->knowledgeItems()->where('category', 'facilities')->firstOrFail();

        $this->get('/demo/facilities')->assertOk()->assertSee('Wi-Fi gratis')->assertDontSee('Kebijakan reservasi');
        $this->get('/demo/facilities/'.$facility->id)->assertOk()->assertSee('Wi-Fi cepat untuk semua tamu.');
        $this->get('/demo/facilities/99999')->assertNotFound();
    }

    public function test_the_info_scene_shows_opening_hours_policies_and_faq(): void
    {
        $this->get('/demo/info?lang=en')
            ->assertOk()
            ->assertSee('08:00')
            ->assertSee('22:00')
            ->assertSee('We are open from 08:00 to 22:00.')
            ->assertSee('Kebijakan reservasi')
            ->assertSee('Ada musala?')
            ->assertSee('Sanctuary tenang di Jakarta.');
    }

    public function test_the_staff_scene_offers_whatsapp_and_phone(): void
    {
        $this->get('/demo/staff')
            ->assertOk()
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee('tel:+62211234', false);
    }

    public function test_the_reservation_scene_offers_slots_and_treatment_rooms(): void
    {
        $this->get('/demo/reservation?area=bamboo-pavilion&lang=en')
            ->assertOk()
            ->assertSee('data-wizard', false)
            ->assertSee('data-preselect-area="bamboo-pavilion"', false)
            ->assertSee('13:00–15:00')
            ->assertSee('Bamboo Pavilion');
    }
}
