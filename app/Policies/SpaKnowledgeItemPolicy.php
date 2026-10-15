<?php

namespace App\Policies;

use App\Models\SpaKnowledgeItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SpaKnowledgeItemPolicy extends SpaScopedPolicy
{
    public function update(User $user, SpaKnowledgeItem $knowledgeItem): Response
    {
        return $this->ownsSpa($user, $knowledgeItem->spa_id);
    }

    public function delete(User $user, SpaKnowledgeItem $knowledgeItem): Response
    {
        return $this->ownsSpa($user, $knowledgeItem->spa_id);
    }
}
