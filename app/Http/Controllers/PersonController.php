<?php

namespace App\Http\Controllers;

use App\Jobs\SendPersonCapturedEmail;
use App\Models\Person;
use App\Models\Interests;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PersonController extends Controller
{
    public function index()
    {
        return Person::with(['interestsRelation', 'language'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => ['required', 'string'],
                'surname' => ['required', 'string'],
                'south_african_id_number' => ['required', 'string', Rule::unique('people', 'south_african_id_number')],
                'mobile_number' => ['required', 'string', Rule::unique('people', 'mobile_number')],
                'email' => ['required', 'email', Rule::unique('people', 'email')],
                'birth_date' => ['required', 'date'],
                'language_id' => ['required', 'integer', Rule::exists('languages', 'id')],
                'interests' => ['required', 'array'],
                'interests.*' => ['string', Rule::exists('interests', 'interest_name')],
            ],
            [
                'south_african_id_number.unique' => 'A user with the same ID Number already exists',
                'mobile_number.unique' => 'A user with the same mobile number already exists',
                'email.unique' => 'A user with the same email address already exists',
            ],
        );

        if (substr($validated['south_african_id_number'], 0, 6) !== Carbon::parse($validated['birth_date'])->format('ymd')) {
            throw ValidationException::withMessages([
                'south_african_id_number' => ['ID number does not match birth date'],
            ]);
        }

        $interestIds = Interests::whereIn('interest_name', $validated['interests'])->pluck('id');

        $person = Person::create(collect($validated)->except('interests')->toArray());

        $person->interestsRelation()->sync($interestIds);

        SendPersonCapturedEmail::dispatch($person);

        return response()->json($person->load(['language', 'interestsRelation']), 201);
    }

    public function show(Person $person)
    {
        return $person->load(['interestsRelation', 'language']);
    }

    public function update(Request $request, Person $person)
    {
        $validated = $request->validate(
            [
                'name' => ['required', 'string'],
                'surname' => ['required', 'string'],
                'south_african_id_number' => ['required', 'string', Rule::unique('people', 'south_african_id_number')->ignore($person->id)],
                'mobile_number' => ['required', 'string', Rule::unique('people', 'mobile_number')->ignore($person->id)],
                'email' => ['required', 'email', Rule::unique('people', 'email')->ignore($person->id)],
                'birth_date' => ['required', 'date'],
                'language_id' => ['required', 'integer', Rule::exists('languages', 'id')],
                'interests' => ['required', 'array'],
                'interests.*' => ['string', Rule::exists('interests', 'interest_name')],
            ],
            [
                'south_african_id_number.unique' => 'A user with the same ID Number already exists',
                'mobile_number.unique' => 'A user with the same mobile number already exists',
                'email.unique' => 'A user with the same email address already exists',
            ],
        );

        if (substr($validated['south_african_id_number'], 0, 6) !== Carbon::parse($validated['birth_date'])->format('ymd')) {
            throw ValidationException::withMessages([
                'south_african_id_number' => ['ID number does not match birth date'],
            ]);
        }

        $interestIds = Interests::whereIn('interest_name', $validated['interests'])->pluck('id');

        $person->update(collect($validated)->except('interests')->toArray());
        $person->interestsRelation()->sync($interestIds);

        return $person->load(['interestsRelation', 'language']);
    }

    public function destroy(Person $person)
    {
        $person->delete();

        return response()->noContent();
    }
}
