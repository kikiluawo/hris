<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'cv_path',
        'cv_original_name',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
