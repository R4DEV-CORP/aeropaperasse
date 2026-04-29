<?php

namespace Tests\Feature;

use App\Livewire\Training\AddCoworkerToTrainingForm;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AddCoworkerToTrainingFormTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_assigning_training_with_airport_when_required(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);
        $training = Training::factory()->create(['title' => 'Permis T', 'requires_airport' => true]);

        Livewire::actingAs($admin)
            ->test(AddCoworkerToTrainingForm::class)
            ->set('selected_client_id', $client->id)
            ->set('selected_coworker_id', $coworker->id)
            ->set('selected_training_id', $training->id)
            ->set('selected_airport', 'ORY')
            ->set('start_date', now()->toDateString())
            ->set('validity_years', '2')
            ->call('submit');

        $this->assertDatabaseHas('coworker_trainings', [
            'coworker_id' => $coworker->id,
            'training_id' => $training->id,
            'airport' => 'ORY',
        ]);
    }

    public function test_airport_is_optional_for_permis_t(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);
        $training = Training::factory()->create(['title' => 'Permis T', 'requires_airport' => true]);

        Livewire::actingAs($admin)
            ->test(AddCoworkerToTrainingForm::class)
            ->set('selected_client_id', $client->id)
            ->set('selected_coworker_id', $coworker->id)
            ->set('selected_training_id', $training->id)
            ->set('start_date', now()->toDateString())
            ->set('validity_years', '2')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('coworker_trainings', [
            'coworker_id' => $coworker->id,
            'training_id' => $training->id,
            'airport' => null,
        ]);
    }

    public function test_same_coworker_can_have_permis_t_for_two_airports(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);
        $training = Training::factory()->create(['title' => 'Permis T', 'requires_airport' => true]);

        DB::table('coworker_trainings')->insert([
            'coworker_id' => $coworker->id,
            'training_id' => $training->id,
            'airport' => 'ORY',
            'started_at' => now(),
            'expires_at' => now()->addYears(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(AddCoworkerToTrainingForm::class)
            ->set('selected_client_id', $client->id)
            ->set('selected_coworker_id', $coworker->id)
            ->set('selected_training_id', $training->id)
            ->set('selected_airport', 'CDG')
            ->set('start_date', now()->toDateString())
            ->set('validity_years', '2')
            ->call('submit');

        $this->assertEquals(2, DB::table('coworker_trainings')
            ->where('coworker_id', $coworker->id)
            ->where('training_id', $training->id)
            ->count());
    }

    public function test_same_coworker_cannot_have_permis_t_twice_for_same_airport(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);
        $training = Training::factory()->create(['title' => 'Permis T', 'requires_airport' => true]);

        DB::table('coworker_trainings')->insert([
            'coworker_id' => $coworker->id,
            'training_id' => $training->id,
            'airport' => 'ORY',
            'started_at' => now(),
            'expires_at' => now()->addYears(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(AddCoworkerToTrainingForm::class)
            ->set('selected_client_id', $client->id)
            ->set('selected_coworker_id', $coworker->id)
            ->set('selected_training_id', $training->id)
            ->set('selected_airport', 'ORY')
            ->set('start_date', now()->toDateString())
            ->set('validity_years', '2')
            ->call('submit');

        $this->assertEquals(1, DB::table('coworker_trainings')
            ->where('coworker_id', $coworker->id)
            ->where('training_id', $training->id)
            ->count());
    }

    public function test_non_permis_t_assignment_ignores_airport(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);
        $training = Training::factory()->create(['title' => '11.2.3.9', 'requires_airport' => false]);

        Livewire::actingAs($admin)
            ->test(AddCoworkerToTrainingForm::class)
            ->set('selected_client_id', $client->id)
            ->set('selected_coworker_id', $coworker->id)
            ->set('selected_training_id', $training->id)
            ->set('selected_airport', 'ORY')
            ->set('start_date', now()->toDateString())
            ->set('validity_years', '2')
            ->call('submit');

        $this->assertDatabaseHas('coworker_trainings', [
            'coworker_id' => $coworker->id,
            'training_id' => $training->id,
            'airport' => null,
        ]);
    }

    public function test_invalid_airport_is_rejected(): void
    {
        $admin = $this->makeAdmin();
        $client = Client::factory()->create();
        $coworker = Coworker::factory()->create(['client_id' => $client->id]);
        $training = Training::factory()->create(['title' => 'Permis T', 'requires_airport' => true]);

        Livewire::actingAs($admin)
            ->test(AddCoworkerToTrainingForm::class)
            ->set('selected_client_id', $client->id)
            ->set('selected_coworker_id', $coworker->id)
            ->set('selected_training_id', $training->id)
            ->set('selected_airport', 'XYZ')
            ->set('start_date', now()->toDateString())
            ->set('validity_years', '2')
            ->call('submit')
            ->assertHasErrors(['selected_airport']);
    }
}
