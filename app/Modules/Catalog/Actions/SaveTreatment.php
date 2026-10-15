<?php

namespace App\Modules\Catalog\Actions;

use App\Models\Spa;
use App\Models\Treatment;
use Illuminate\Support\Str;

/**
 * Creates a treatments item or updates one. A new item gets a slug from its name; an
 * existing one keeps its slug so guest links never break.
 */
class SaveTreatment
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Spa $spa, array $attributes, ?Treatment $treatment = null): Treatment
    {
        if ($treatment) {
            $treatment->update($attributes);

            return $treatment;
        }

        return $spa->treatments()->create([...$attributes, 'slug' => $this->uniqueSlug($spa, $attributes['name'])]);
    }

    private function uniqueSlug(Spa $spa, string $name): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $suffix = 1;

        while ($spa->treatments()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
