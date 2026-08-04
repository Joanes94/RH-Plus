<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'type', 'titre', 'message', 'personnel_id', 'avancement_id', 'payload', 'date_notification',
    ];

    protected $casts = [
        'payload'           => 'array',
        'date_notification' => 'date',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function avancement()
    {
        return $this->belongsTo(Avancement::class);
    }

    public function lectures()
    {
        return $this->hasMany(NotificationLecture::class);
    }

    public function estLuePar(User $user): bool
    {
        return $this->relationLoaded('lectures')
            ? $this->lectures->contains('user_id', $user->id)
            : $this->lectures()->where('user_id', $user->id)->exists();
    }

    public function marquerLuePar(User $user): void
    {
        $this->lectures()->firstOrCreate(['user_id' => $user->id]);
    }

    public function getIconeAttribute(): string
    {
        return match ($this->type) {
            'bonification'   => '🎖️',
            'digest_mensuel' => '📅',
            default          => '📈',
        };
    }
}