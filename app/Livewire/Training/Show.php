<?php

namespace App\Livewire\Training;

use Livewire\Component;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\Training;
use Illuminate\Support\Facades\DB;

class Show extends Component
{
    public $coworkers;
    public $slug;
    public $client;
    public $activeTrainings;
    public $soonExpiringTrainings;
    public $expiredTrainings;

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->client = Client::where('slug', $slug)->first();
        $this->coworkers = $this->client->coworkers;
        
        $this->activeTrainings = DB::table('coworker_trainings')
            ->join('coworkers', 'coworker_trainings.coworker_id', '=', 'coworkers.id')
            ->join('trainings', 'coworker_trainings.training_id', '=', 'trainings.id')
            ->select(
                'coworker_trainings.*',
                'coworkers.firstname as coworker_firstname',
                'coworkers.lastname as coworker_lastname',
                'trainings.title as training_title'
            )
            ->where('coworker_trainings.expires_at', '>=', now())
            ->whereIn('coworker_trainings.coworker_id', $this->coworkers->pluck('id'))
            ->get();
        
        $this->soonExpiringTrainings = DB::table('coworker_trainings')
            ->join('coworkers', 'coworker_trainings.coworker_id', '=', 'coworkers.id')
            ->join('trainings', 'coworker_trainings.training_id', '=', 'trainings.id')
            ->select(
                'coworker_trainings.*',
                'coworkers.firstname as coworker_firstname',
                'coworkers.lastname as coworker_lastname',
                'trainings.title as training_title'
            )
            ->where('coworker_trainings.expires_at', '<=', now()->addMonth(6))
            ->where('coworker_trainings.expires_at', '>=', now())
            ->whereIn('coworker_trainings.coworker_id', $this->coworkers->pluck('id'))
            ->get();
        
        $this->expiredTrainings = DB::table('coworker_trainings')
            ->join('coworkers', 'coworker_trainings.coworker_id', '=', 'coworkers.id')
            ->join('trainings', 'coworker_trainings.training_id', '=', 'trainings.id')
            ->select(
                'coworker_trainings.*',
                'coworkers.firstname as coworker_firstname',
                'coworkers.lastname as coworker_lastname',
                'trainings.title as training_title'
            )
            ->where('coworker_trainings.expires_at', '<', now())
            ->whereIn('coworker_trainings.coworker_id', $this->coworkers->pluck('id'))
            ->get();
    }

    public function render()
    {
        return view('livewire.training.show');
    }
}
