<?php

namespace App\Modules\Assistant\Tools;

use App\Models\Conversation;
use App\Models\Spa;

/**
 * What a tool may act on: one spa, one conversation, in the guest's language.
 */
final readonly class ToolContext
{
    public function __construct(
        public Spa $spa,
        public Conversation $conversation,
        public string $locale,
    ) {}
}
