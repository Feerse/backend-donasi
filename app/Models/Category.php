<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => url('/storage/categories/' . $value)
        );
    }
}
