<?php

namespace App\Models;

use App\Modules\Reservation\Enums\Occasion;
use App\Modules\Reservation\Enums\ReservationStatus;
use App\Modules\Reservation\Support\RoomAvailability;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reference',
    'spa_id',
    'treatment_room_id',
    'treatment_id',
    'treatment_name',
    'duration_minutes',
    'treatment_price',
    'conversation_id',
    'guest_name',
    'guest_email',
    'guest_phone',
    'contact_type',
    'reservation_date',
    'time_slot',
    'guests',
    'occasion',
    'quoted_total',
    'status',
    'notes',
])]
class Reservation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = ReservationStatus::Pending->value;

    public const STATUS_CONFIRMED = ReservationStatus::Confirmed->value;

    public const STATUS_CANCELLED = ReservationStatus::Cancelled->value;

    /**
     * Occasions a guest can mention so the team can prepare the table.
     */
    public const OCCASIONS = [
        Occasion::Birthday->value,
        Occasion::SelfCare->value,
        Occasion::Couple->value,
        Occasion::Gift->value,
    ];

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'treatment_price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'quoted_total' => 'decimal:2',
        ];
    }

    public function spa(): BelongsTo
    {
        return $this->belongsTo(Spa::class);
    }

    public function treatmentRoom(): BelongsTo
    {
        return $this->belongsTo(TreatmentRoom::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class)->withTrashed();
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * "11:00–13:00" for a reservation that starts at 11:00.
     */
    public function getTimeRangeAttribute(): string
    {
        return RoomAvailability::slotRange($this->time_slot);
    }

    /**
     * A short, unambiguous, non-sequential code guests can quote to staff.
     */
    public static function generateReference(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $reference = 'RS-'.collect(range(1, 6))
                ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
                ->implode('');
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }
}
