<?php

namespace App\Console\Commands;

use App\Models\Spa;
use App\Modules\Conversation\Actions\ReplyToGuest;
use App\Modules\Conversation\Actions\StartConversation;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('concierge:chat {spa? : Spa slug} {--locale=id : id|en|ja}')]
#[Description('Talk to the AI Spa Concierge for a spa from the terminal, for testing the tool-calling loop without the web UI.')]
class ConciergeChatCommand extends Command
{
    public function handle(StartConversation $start, ReplyToGuest $reply): int
    {
        $spa = $this->argument('spa')
            ? Spa::where('slug', $this->argument('spa'))->first()
            : Spa::first();

        if (! $spa) {
            $this->error('No spa found. Seed one first: php artisan db:seed');

            return self::FAILURE;
        }

        if (blank(config('services.local_llm.base_url'))) {
            $this->error('LOCAL_LLM_BASE_URL is not set in .env — the concierge has no model endpoint to call.');

            return self::FAILURE;
        }

        $locale = $this->option('locale');
        $conversation = $start->handle($spa, $locale);

        $this->info("Chatting with the AI Spa Concierge for {$spa->name} ({$locale}). Type 'exit' to quit.");
        $this->newLine();

        while (true) {
            $guestMessage = $this->ask('You');

            if ($guestMessage === null || in_array(trim($guestMessage), ['exit', 'quit'], true)) {
                break;
            }

            $message = $reply->handle($spa, $conversation, $guestMessage);

            $this->newLine();
            $this->line('<fg=cyan>Concierge:</> '.($message->content ?? '(no text — see UI payload below)'));

            if ($message->ui_payload) {
                $this->line('<fg=gray>[ui_payload] '.json_encode($message->ui_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).'</>');
            }

            $conversation->refresh();
            if ($conversation->isHandedOver()) {
                $this->warn('Conversation handed over to the spa team. Ending session.');
                break;
            }

            $this->newLine();
        }

        return self::SUCCESS;
    }
}
