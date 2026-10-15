<?php

namespace App\Models;

use App\Modules\Catalog\Enums\TreatmentCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One treatment or ritual package in the spa catalog. The price here is the only price the
 * AI Spa Concierge may ever quote, and only after reading it through a tool call.
 */
#[Fillable([
    'spa_id',
    'category',
    'name',
    'slug',
    'description',
    'translations',
    'price',
    'image_url',
    'tags',
    'contraindications',
    'duration_label',
    'duration_minutes',
    'is_featured',
    'is_unavailable',
    'is_active',
    'sort_order',
])]
class Treatment extends Model
{
    use HasFactory, SoftDeletes;

    public const CATEGORY_MASSAGE = TreatmentCategory::Massage->value;

    public const CATEGORY_BODY = TreatmentCategory::Body->value;

    public const CATEGORY_FACIAL = TreatmentCategory::Facial->value;

    public const CATEGORY_RITUAL = TreatmentCategory::Ritual->value;

    public const CATEGORY_PACKAGE = TreatmentCategory::Package->value;

    /**
     * Tags a guest can filter on when they have a wellness need.
     */
    public const WELLNESS_TAGS = ['relaxation', 'botanical', 'gentle', 'signature', 'bestseller'];

    /**
     * @return list<string>
     */
    public static function categories(): array
    {
        return TreatmentCategory::values();
    }

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'tags' => 'array',
            'contraindications' => 'array',
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'is_featured' => 'boolean',
            'is_unavailable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function spa(): BelongsTo
    {
        return $this->belongsTo(Spa::class);
    }

    /**
     * The picture to show: a full URL as stored, or a file under public/.
     */
    public function getImageSourceAttribute(): ?string
    {
        if (blank($this->image_url)) {
            return null;
        }

        return str_starts_with($this->image_url, 'http') ? $this->image_url : asset($this->image_url);
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? [], true);
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
