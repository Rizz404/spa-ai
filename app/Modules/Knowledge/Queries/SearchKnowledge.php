<?php

namespace App\Modules\Knowledge\Queries;

use App\Models\Spa;
use App\Models\SpaKnowledgeItem;
use App\Support\SearchText;
use Illuminate\Support\Collection;

/**
 * The spa's approved knowledge base, searched by keyword. The only source the
 * AI Spa Concierge may state spa facts from.
 */
class SearchKnowledge
{
    /**
     * @return Collection<int, SpaKnowledgeItem>
     */
    public function handle(Spa $spa, string $query, ?string $category = null, int $limit = 5): Collection
    {
        $query = SearchText::normalize($query);

        return $spa->knowledgeItems()
            ->where('is_active', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->get()
            ->filter(fn (SpaKnowledgeItem $item) => SearchText::matches(
                $item->title.' '.$item->body.' '.implode(' ', $item->tags ?? []),
                $query,
            ))
            ->take($limit);
    }
}
