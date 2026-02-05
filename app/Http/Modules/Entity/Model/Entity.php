<?php

namespace App\Http\Modules\Entity\Model;

use Illuminate\Database\Eloquent\Model;
use App\Http\Modules\Plan\Model\Plan;

class Entity extends Model
{
    protected $table = 'entities';

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function planes()
    {
        return $this->belongsToMany(Plan::class, 'entity_plan')
            ->withPivot('start_date', 'end_date', 'status')
            ->withTimestamps();
    }
}
