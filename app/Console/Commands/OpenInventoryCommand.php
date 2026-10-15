<?php

namespace App\Console\Commands;

use App\Models\TreatmentRoom;
use App\Modules\Reservation\Support\OpenRoomInventory;
use Illuminate\Console\Command;

class OpenInventoryCommand extends Command
{
    protected $signature = 'spa:open-inventory {--spa= : Optional spa slug}';

    protected $description = 'Open the next 60 days of room capacity, preserving existing bookings';

    public function handle(OpenRoomInventory $open): int
    {
        $slug = $this->option('spa');
        $count = 0;
        TreatmentRoom::query()->where('is_active', true)
            ->whereHas('spa', fn ($query) => $query->when($slug, fn ($query) => $query->where('slug', $slug)))
            ->with('spa')->chunkById(50, function ($rooms) use ($open, &$count) {
                foreach ($rooms as $room) {
                    $count += $open->handle($room);
                }
            });
        $this->info('Opened '.$count.' new room slots. Existing capacity and reservations were preserved.');

        return self::SUCCESS;
    }
}
