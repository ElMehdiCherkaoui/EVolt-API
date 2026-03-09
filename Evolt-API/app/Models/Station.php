<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'connector_type',
        'power_kw',
        'status',
    ];
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
