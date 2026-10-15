<?php

namespace App\Models;

use App\Modules\Rooms\Enums\RoomType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'spa_id',
    'name',
    'slug',
    'description',
    'translations',
    'room_type',
    'min_guests',
    'max_guests',
    'features',
    'reservation_fee',
    'minimum_spend',
    'is_active',
    'sort_order',
])]
class TreatmentRoom extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_SINGLE = RoomType::Single->value;

    public const TYPE_GARDEN = RoomType::Garden->value;

    public const TYPE_COUPLE = RoomType::Couple->value;

    /**
     * @return list<string>
     */
    public static function areaTypes(): array
    {
        return RoomType::values();
    }

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'features' => 'array',
            'reservation_fee' => 'decimal:2',
            'minimum_spend' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function spa(): BelongsTo
    {
        return $this->belongsTo(Spa::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class)->orderBy('sort_order');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(RoomInventory::class);
    }

    public function translatedName(string $locale): string
    {
        $value = $this->translations[$locale]['name'] ?? null;

        return filled($value) ? $value : $this->name;
    }

    public function translatedDescription(string $locale): ?string
    {
        $value = $this->translations[$locale]['description'] ?? null;

        return filled($value) ? $value : $this->description;
    }
}
