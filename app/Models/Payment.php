<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'or',
        'payor_name',
        'payment_date',
        'deposit_date',
        'mode_of_payment',
        'reference',
        'description',
        'nature_of_collection',
        'type',
    ];

    public $timestamps = true;

    protected $casts = [
        'payment_date' => 'datetime',
        'deposit_date' => 'datetime',
    ];
    
    public function natureOfCollection()
    {
        return $this->belongsTo(NatureOfCollection::class, 'nature_of_collection', 'type');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}