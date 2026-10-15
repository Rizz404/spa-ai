<?php

namespace App\Modules\Catalog\Queries;

use App\Models\Spa;
use App\Models\Treatment;
use App\Support\SearchText;
use Illuminate\Support\Collection;

/**
 * The active treatments, narrowed by keyword, category, wellness need or budget.
 */
class SearchTreatments
{
    /**
     * @return Collection<int, Treatment>
     */
    public function handle(Spa $spa, string $query = '', ?string $category = null, ?string $wellness = null, ?float $maxPrice = null, int $limit = 6): Collection
    {
        $query = SearchText::normalize($query);

        return $spa->treatments()
            ->where('is_active', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (Treatment $item) => ! $wellness || $item->hasTag($wellness))
            ->filter(fn (Treatment $item) => $maxPrice === null || (float) $item->price <= $maxPrice)
            ->filter(fn (Treatment $item) => SearchText::matches(implode(' ', [
                $item->name,
                $item->description,
                $item->category,
                implode(' ', $item->tags ?? []),
                collect($item->translations ?? [])->flatten()->implode(' '),
            ]), $query))
            ->take($limit);
    }
}
