<?php

namespace App\Modules\Rooms\Actions;

use App\Models\Spa;
use App\Models\TreatmentRoom;
use App\Modules\Reservation\Support\OpenRoomInventory;
use Illuminate\Support\Str;

/**
 * Creates a rooms area or updates one, together with the photos added or
 * removed in the same form. A new area gets a slug from its name; an existing
 * one keeps its slug so guest links never break.
 */
class SaveTreatmentRoom
{
    public function __construct(private readonly OpenRoomInventory $inventory) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<int>  $imageIdsToDelete
     * @param  list<array{url: string, alt?: ?string, tags?: ?string}>  $newImages
     */
    public function handle(Spa $spa, array $attributes, array $imageIdsToDelete, array $newImages, ?TreatmentRoom $treatmentRoom = null): TreatmentRoom
    {
        if ($treatmentRoom) {
            $treatmentRoom->update($attributes);
        } else {
            $treatmentRoom = $spa->treatmentRooms()->create([...$attributes, 'slug' => $this->uniqueSlug($spa, $attributes['name'])]);
        }

        $this->syncImages($treatmentRoom, $imageIdsToDelete, $newImages);

        if ($treatmentRoom->is_active) {
            $this->inventory->handle($treatmentRoom);
        }

        return $treatmentRoom;
    }

    private function uniqueSlug(Spa $spa, string $name): string
    {
        $base = Str::slug($name) ?: 'area';
        $slug = $base;
        $suffix = 1;

        while ($spa->treatmentRooms()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  list<int>  $deleteIds
     * @param  list<array{url: string, alt?: ?string, tags?: ?string}>  $newImages
     */
    private function syncImages(TreatmentRoom $treatmentRoom, array $deleteIds, array $newImages): void
    {
        if ($deleteIds !== []) {
            $treatmentRoom->images()->whereIn('id', $deleteIds)->delete();
        }

        $sort = $treatmentRoom->images()->max('sort_order') + 1;

        foreach ($newImages as $row) {
            $treatmentRoom->images()->create([
                'image_url' => $row['url'],
                'alt_text' => $row['alt'] ?? $treatmentRoom->name,
                'tags' => collect(explode(',', $row['tags'] ?? ''))->map(fn ($t) => trim($t))->filter()->values()->all(),
                'sort_order' => $sort++,
            ]);
        }
    }
}
