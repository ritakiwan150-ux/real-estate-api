<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'icon',
        'is_active',
    ];

    public function properties(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\Property::class, 'type_id');
}
}