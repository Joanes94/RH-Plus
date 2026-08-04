<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLecture extends Model
{
    public $timestamps = false;

    protected $fillable = ['notification_id', 'user_id', 'lu_le'];

    protected $casts = [
        'lu_le' => 'datetime',
    ];

    protected $attributes = [
        // 'lu_le' est rempli par la BDD via useCurrent(), pas besoin ici
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}