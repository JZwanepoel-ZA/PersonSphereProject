<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Interests;
use App\Models\Languages;

class Person extends Model
{
    protected $fillable = [
        'name',
        'surname',
        'south_african_id_number',
        'mobile_number',
        'email',
        'birth_date',
        'language_id',
    ];

    protected $casts = [
        // Ensure dates are serialised in a browser-friendly format
        'birth_date' => 'date:Y-m-d',
    ];

    /**
     * Include interest names when the model is serialized.
     *
     * @var array<int, string>
     */
    protected $appends = ['interests'];

    /**
     * Hide the underlying relationship data when serializing.
     *
     * @var array<int, string>
     */
    protected $hidden = ['interestsRelation', 'pivot'];

    /**
     * Interests linked to the person via the pivot table.
     */
    public function interestsRelation(): BelongsToMany
    {
        return $this->belongsToMany(Interests::class, 'interest_people', 'people_id', 'interest_id')
            ->withTimestamps();
    }

    /**
     * Accessor to return interest names from the relationship.
     *
     * @return array<int, string>
     */
    public function getInterestsAttribute(): array
    {
        return $this->interestsRelation->pluck('interest_name')->all();
    }

    /**
     * The language associated with the person.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Languages::class);
    }
}
