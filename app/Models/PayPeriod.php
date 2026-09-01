<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayPeriod extends Model
{
    protected $table = 'pay_periods';

    protected $fillable = ['centre_id', 'code', 'label', 'statut', 'created_by'];

    protected $casts = [
        'statut' => 'string',
    ];

    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paySlips()
    {
        return $this->hasMany(PaySlip::class, 'pay_period_id');
    }


    public function isCloture(): bool
    {
        return $this->statut === 'cloture';
    }
}
