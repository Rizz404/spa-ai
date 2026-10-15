<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;

final class GetTreatmentTool implements Tool
{
    public function name(): string
    {
        return 'get_treatment';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Get the full details of one treatment by its slug (from a previous search_treatments result): description, price, contraindications, duration in minutes and whether it is unavailable. Use this when the guest asks about a specific treatment.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'treatment_slug' => ['type' => 'string'],
                    ],
                    'required' => ['treatment_slug'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $item = CatalogSummaries::treatmentBySlug($context->spa, (string) ($arguments['treatment_slug'] ?? ''));

        if (! $item) {
            return new ToolResult('Treatment not found.');
        }

        $detail = [
            ...CatalogSummaries::treatment($item, $context->spa, $context->locale),
            'description' => $item->translatedDescription($context->locale),
            'duration_minutes' => $item->duration_minutes,
            'note' => 'price is the current treatments price; contraindications are guidance only and the spa team must confirm suitability.',
        ];

        return ToolResult::json($detail, ['type' => 'treatments_detail', 'item' => $detail]);
    }
}
