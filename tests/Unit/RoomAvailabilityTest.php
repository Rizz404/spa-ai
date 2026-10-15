<?php

namespace Tests\Unit;

use App\Models\TreatmentRoom;
use App\Modules\Reservation\Support\RoomAvailability;
use Tests\TestCase;

class RoomAvailabilityTest extends TestCase
{
    public function test_a_slot_is_a_two_hour_range(): void
    {
        $this->assertSame('11:00–13:00', RoomAvailability::slotRange('11:00'));
        $this->assertSame('21:00–23:00', RoomAvailability::slotRange('21:00'));
    }

    public function test_a_party_must_fit_the_area_capacity(): void
    {
        $area = new TreatmentRoom(['min_guests' => 2, 'max_guests' => 4]);
        $availability = new RoomAvailability;

        $this->assertFalse($availability->fitsParty($area, 1));
        $this->assertTrue($availability->fitsParty($area, 2));
        $this->assertFalse($availability->fitsParty($area, 3));
        $this->assertFalse($availability->fitsParty($area, 4));
    }
}
