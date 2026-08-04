<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /** Marque une notification comme lue par l'utilisateur courant, puis redirige. */
    public function marquerLue(Request $request, Notification $notification)
    {
        $notification->marquerLuePar(Auth::user());

        if ($notification->type !== 'digest_mensuel' && $notification->personnel_id) {
            return redirect()->route('personnel.show', $notification->personnel_id);
        }

        return back();
    }

    /** Marque toutes les notifications visibles comme lues par l'utilisateur courant. */
    public function marquerToutesLues()
    {
        $user = Auth::user();

        Notification::with('lectures')->get()->each(function ($n) use ($user) {
            if (!$n->estLuePar($user)) {
                $n->marquerLuePar($user);
            }
        });

        return back()->with('success', 'Notifications marquées comme lues.');
    }
}