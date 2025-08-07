<?php

namespace Tests\Feature;

use App\Jobs\SendPersonCapturedEmail;
use App\Models\Person;
use App\Models\Interests;
use App\Models\Languages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class PersonApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_can_be_created_and_email_is_queued(): void
    {
        Bus::fake();

        $coding = Interests::create(['interest_name' => 'coding']);
        $music = Interests::create(['interest_name' => 'music']);
        $english = Languages::create(['language_name' => 'English']);

        $response = $this->postJson('/api/people', [
            'name' => 'John',
            'surname' => 'Doe',
            'south_african_id_number' => '9001011234567',
            'mobile_number' => '0123456789',
            'email' => 'john@example.com',
            'birth_date' => '1990-01-01',
            'language_id' => $english->id,
            'interests' => ['coding', 'music'],
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['email' => 'john@example.com']);

        $this->assertDatabaseHas('people', [
            'email' => 'john@example.com',
            'south_african_id_number' => '9001011234567',
        ]);

        $person = Person::where('email', 'john@example.com')->first();

        $this->assertDatabaseHas('interest_people', [
            'people_id' => $person->id,
            'interest_id' => $coding->id,
        ]);

        $this->assertDatabaseHas('interest_people', [
            'people_id' => $person->id,
            'interest_id' => $music->id,
        ]);

        Bus::assertDispatched(SendPersonCapturedEmail::class, function ($job) {
            return $job->person->email === 'john@example.com';
        });
    }

    public function test_person_cannot_be_created_with_duplicate_id_email_or_mobile(): void
    {
        $coding = Interests::create(['interest_name' => 'coding']);
        $english = Languages::create(['language_name' => 'English']);

        $this->postJson('/api/people', [
            'name' => 'Jane',
            'surname' => 'Doe',
            'south_african_id_number' => '8001011234567',
            'mobile_number' => '0123456789',
            'email' => 'jane@example.com',
            'birth_date' => '1980-01-01',
            'language_id' => $english->id,
            'interests' => ['coding'],
        ])->assertCreated();

        $response = $this->postJson('/api/people', [
            'name' => 'Jane',
            'surname' => 'Doe',
            'south_african_id_number' => '8001011234567',
            'mobile_number' => '0123456789',
            'email' => 'jane@example.com',
            'birth_date' => '1980-01-01',
            'language_id' => $english->id,
            'interests' => ['coding'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['south_african_id_number', 'email', 'mobile_number']);

        $errors = $response->json('errors');
        $this->assertEquals('A user with the same ID Number already exists', $errors['south_african_id_number'][0]);
        $this->assertEquals('A user with the same email address already exists', $errors['email'][0]);
        $this->assertEquals('A user with the same mobile number already exists', $errors['mobile_number'][0]);
    }

    public function test_id_number_must_match_birth_date(): void
    {
        Interests::create(['interest_name' => 'coding']);
        $english = Languages::create(['language_name' => 'English']);

        $response = $this->postJson('/api/people', [
            'name' => 'Mismatch',
            'surname' => 'Test',
            'south_african_id_number' => '8001011234567',
            'mobile_number' => '0123456789',
            'email' => 'mismatch@example.com',
            'birth_date' => '1990-01-01',
            'language_id' => $english->id,
            'interests' => ['coding'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('south_african_id_number');
    }

    public function test_person_cannot_be_updated_with_duplicate_id_email_or_mobile(): void
    {
        $coding = Interests::create(['interest_name' => 'coding']);
        $english = Languages::create(['language_name' => 'English']);

        $existing = Person::create([
            'name' => 'Existing',
            'surname' => 'User',
            'south_african_id_number' => '7501011234567',
            'mobile_number' => '0123456789',
            'email' => 'existing@example.com',
            'birth_date' => '1975-01-01',
            'language_id' => $english->id,
        ]);

        $person = Person::create([
            'name' => 'Edit',
            'surname' => 'Me',
            'south_african_id_number' => '7601011234567',
            'mobile_number' => '0987654321',
            'email' => 'edit@example.com',
            'birth_date' => '1976-01-01',
            'language_id' => $english->id,
        ]);

        $person->interestsRelation()->attach($coding->id);

        $response = $this->putJson("/api/people/{$person->id}", [
            'name' => 'Edit',
            'surname' => 'Me',
            'south_african_id_number' => '7501011234567',
            'mobile_number' => '0123456789',
            'email' => 'existing@example.com',
            'birth_date' => '1975-01-01',
            'language_id' => $english->id,
            'interests' => ['coding'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['south_african_id_number', 'email', 'mobile_number']);
    }

    public function test_id_number_must_match_birth_date_on_update(): void
    {
        Interests::create(['interest_name' => 'coding']);
        $english = Languages::create(['language_name' => 'English']);

        $person = Person::create([
            'name' => 'Mismatch',
            'surname' => 'Update',
            'south_african_id_number' => '8001011234567',
            'mobile_number' => '0123456789',
            'email' => 'update@example.com',
            'birth_date' => '1980-01-01',
            'language_id' => $english->id,
        ]);

        $response = $this->putJson("/api/people/{$person->id}", [
            'name' => 'Mismatch',
            'surname' => 'Update',
            'south_african_id_number' => '8001011234567',
            'mobile_number' => '0123456789',
            'email' => 'update@example.com',
            'birth_date' => '1990-01-01',
            'language_id' => $english->id,
            'interests' => ['coding'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('south_african_id_number');
    }

    public function test_person_can_be_deleted(): void
    {
        $coding = Interests::create(['interest_name' => 'coding']);
        $english = Languages::create(['language_name' => 'English']);

        $person = Person::create([
            'name' => 'Delete',
            'surname' => 'Me',
            'south_african_id_number' => '7001011234567',
            'mobile_number' => '0123456789',
            'email' => 'delete@example.com',
            'birth_date' => '1970-01-01',
            'language_id' => $english->id,
            'interests' => ['coding'],
        ]);

        $person->interestsRelation()->attach($coding->id);

        $this->deleteJson("/api/people/{$person->id}")->assertNoContent();

        $this->assertDatabaseMissing('people', ['id' => $person->id]);
    }
}
