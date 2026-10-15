<?php

namespace App\Policies;

use App\Models\HandoverRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HandoverRequestPolicy extends SpaScopedPolicy
{
    public function view(User $user, HandoverRequest $handover): Response
    {
        return $this->ownsSpa($user, $handover->conversation->spa_id);
    }

    public function update(User $user, HandoverRequest $handover): Response
    {
        return $this->ownsSpa($user, $handover->conversation->spa_id);
    }
}
