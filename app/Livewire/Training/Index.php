<?php

namespace App\Livewire\Training;

use App\Models\Client;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    public $clients;

    public $statistics;

    public function mount()
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            $this->clients = Client::all();
        } else {
            $client = Client::where('id', $user->client_id)->first();

            return redirect()->route('training.client', ['slug' => $client->slug]);
        }
        $this->statistics = $this->getStatistics();
    }

    public function getStatistics()
    {
        $clientCount = $this->clients->count();
        $coworkerCount = $this->clients->sum(function ($client) {
            return $client->coworkers->count();
        });
        $expiresSoonTrainingCount = DB::table('coworker_trainings')->where('expires_at', '<=', now()->addMonth(6))->where('expires_at', '>=', now())->count();
        return [
            'clientCount' => $clientCount,
            'coworkerCount' => $coworkerCount,
            'expiresSoonTrainingCount' => $expiresSoonTrainingCount,
        ];
    }

    public function render()
    {
        return view('livewire.training.index');
    }
}
