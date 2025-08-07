<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Person;

class Languages extends Model
{
    protected $fillable = ['language_name'];

    /**
     * People that speak this language.
     */
    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }
}
