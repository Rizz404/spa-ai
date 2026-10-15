<?php

namespace App\Modules\Operations\Queries;

use App\Models\HandoverRequest;
use App\Models\Reservation;
use App\Models\Spa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * What the spa team sees first when they sign in: counts of what they manage
 * and the few things waiting for them.
 */
class DashboardMetrics
{
    /**
     * @return array{treatments: int, treatment_rooms: int, knowledge_items: int, pending_reservations: int, open_handovers: int}
     */
    public function counts(Spa $spa): array
    {
        return [
            'treatments' => $spa->treatments()->count(),
            'treatment_rooms' => $spa->treatmentRooms()->count(),
            'knowledge_items' => $spa->knowledgeItems()->count(),
            'pending_reservations' => $spa->reservations()->where('status', Reservation::STATUS_PENDING)->count(),
            'open_handovers' => $this->openHandovers($spa)->count(),
        ];
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function recentReservations(Spa $spa, int $limit = 5): Collection
    {
        return $spa->reservations()->latest()->take($limit)->with('treatmentRoom')->get();
    }

    /**
     * @return Collection<int, HandoverRequest>
     */
    public function latestOpenHandovers(Spa $spa, int $limit = 5): Collection
    {
        return $this->openHandovers($spa)->latest()->take($limit)->with('conversation')->get();
    }

    /**
     * @return Builder<HandoverRequest>
     */
    private function openHandovers(Spa $spa): Builder
    {
        return HandoverRequest::whereHas('conversation', fn ($q) => $q->where('spa_id', $spa->id))
            ->where('status', HandoverRequest::STATUS_OPEN);
    }
}
