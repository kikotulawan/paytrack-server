<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NatureOfCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'account_name',
        'particular',
        'lbp_bank_account_number',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'nature_of_collection', 'type');
    }
}