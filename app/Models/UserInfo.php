<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class UserInfo extends Model
{
    use HasFactory, LogsActivity;
    protected static $logName = 'user_info';
    protected static $logAttributes = ['full_name'];
    protected static $logOnlyDirty = true;

    protected $fillable = [
        'user_id',
        'abbreviation',
        'firstname',
        'middlename',
        'lastname',
        'suffix',
    ];

    protected $appends = ['full_name'];

     public function getFullNameAttribute()
    {
        $names = [
            $this->abbreviation,
            $this->firstname,
            $this->middlename,
            $this->lastname,
            $this->suffix,
        ];

        return trim(collect($names)->filter()->join(' '));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['full_name'])
            ->logOnlyDirty()
            ->useLogName('user_info');
    }
}