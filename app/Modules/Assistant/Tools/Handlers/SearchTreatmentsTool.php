<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\Treatment;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Catalog\Queries\SearchTreatments;

final class SearchTreatmentsTool implements Tool
{
    public function __construct(private readonly SearchTreatments $search) {}

    public function name(): string
    {
        return 'search_treatments';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Search the spa treatments by keyword, category, wellness need or budget. Returns matching treatments and ritual packages with their real price, contraindications and whether they are unavailable. Use this whenever a guest asks what is on the treatments, wants a recommendation, or describes a taste, diet or budget rather than naming one item.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'Keywords such as "massage", "facial", "relaxation" — optional'],
                        'category' => [
                            'type' => 'string',
                            'enum' => Treatment::categories(),
                            'description' => 'Optional category filter',
                        ],
                        'wellness' => [
                            'type' => 'string',
                            'enum' => Treatment::WELLNESS_TAGS,
                            'description' => 'Optional wellness requirement',
                        ],
                        'max_price' => ['type' => 'number', 'description' => 'Optional budget per item, in the spa currency'],
                    ],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $items = $this->search->handle(
            $context->spa,
            (string) ($arguments['query'] ?? ''),
            $arguments['category'] ?? null,
            $arguments['wellness'] ?? null,
            isset($arguments['max_price']) ? (float) $arguments['max_price'] : null,
        );

        if ($items->isEmpty()) {
            return new ToolResult('No treatments item matches that request. Tell the guest honestly and suggest a nearby alternative from another search_treatments call, or call request_human_handover for custom requests.');
        }

        $results = $items->map(fn (Treatment $item) => CatalogSummaries::treatment($item, $context->spa, $context->locale))->values()->all();

        return ToolResult::json($results, ['type' => 'treatments_results', 'items' => $results]);
    }
}
