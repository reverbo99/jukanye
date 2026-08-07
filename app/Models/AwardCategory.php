<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AwardCategory extends Model
{
    protected $fillable = [
        'name_en',
        'name_sw',
        'slug',
        'description_en',
        'description_sw',
        'sort_order',
    ];

    public function nominees(): HasMany
    {
        return $this->hasMany(Nominee::class);
    }

    public static function makeSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
