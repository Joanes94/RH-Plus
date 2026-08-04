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

        $notifications = Notification::with('lectures')
            ->orderByDesc('date_notification')
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