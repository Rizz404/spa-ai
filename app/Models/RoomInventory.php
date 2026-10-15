<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The only source of truth for table availability. Stands in for a real
 * POS / reservation-system adapter for V1: the AI must reach this table (or
 * its future adapter) through a tool call, never answer from its own memory.
 */
#[Fillable([
    'treatment_room_id',
    'reservation_date',
    'time_slot',
    'total_rooms',
    'reserved_rooms',
])]
class RoomInventory extends Model
{
    use HasFactory;

    protected $table = 'room_inventory';

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
        ];
    }

    public function treatmentRoom(): BelongsTo
    {
        return $this->belongsTo(TreatmentRoom::class);
    }

    public function availableRooms(): int
    {
        return max(0, $this->total_rooms - $this->reserved_rooms);
    }

    public function isAvailable(): bool
    {
        return $this->availableRooms() > 0;
    }
}
