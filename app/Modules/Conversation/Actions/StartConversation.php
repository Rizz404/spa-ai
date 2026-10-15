<?php

namespace App\Modules\Conversation\Actions;

use App\Models\Conversation;
use App\Models\Spa;
use Illuminate\Support\Str;

class StartConversation
{
    public function handle(Spa $spa, string $locale = 'id'): Conversation
    {
        return Conversation::create([
            'spa_id' => $spa->id,
            'guest_token' => (string) Str::uuid(),
            'locale' => $locale,
            'status' => Conversation::STATUS_ACTIVE,
            'last_message_at' => now(),
        ]);
    }
}
