<?php

namespace App\View\Composers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        if (!$user) {
            $view->with(['notifications' => collect(), 'nbNonLues' => 0]);
            return;
        }

        $query = Notification::with(['lectures', 'personnel']);

        if (!$user->isGlobal() && $user->centre_id) {
            $userCentreId = $user->centre_id;
            $query->where(function ($q) use ($userCentreId) {
                $q->where('centre_id', $userCentreId)
                  ->orWhereHas('personnel', fn ($pq) => $pq->where('centre_id', $userCentreId));
            });
        }

        $notifications = $query->orderByDesc('date_notification')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        $nonLues = $notifications->filter(fn ($n) => !$n->estLuePar($user));

        $view->with([
            'notifications' => $notifications,
            'nbNonLues'     => $nonLues->count(),
        ]);
    }
}