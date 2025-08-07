<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Person;

class Interests extends Model
{
    /**
     * Allow mass assignment of the interest name.
     */
    protected $fillable = ['interest_name'];

    /**
     * People that are linked to this interest.
     */
    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'interest_people', 'interest_id', 'people_id')
            ->withTimestamps();
    }
}
