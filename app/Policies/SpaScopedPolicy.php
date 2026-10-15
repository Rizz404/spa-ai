<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Staff manage the records of their own spa and nothing else. A record that
 * belongs to another spa is answered with "not found", so its existence is
 * never revealed.
 */
abstract class SpaScopedPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->currentSpa() !== null;
    }

    public function create(User $user): bool
    {
        return $user->currentSpa() !== null;
    }

    protected function ownsSpa(User $user, ?int $spaId): Response
    {
        $spa = $user->currentSpa();

        return $spa !== null && $spa->id === $spaId
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
