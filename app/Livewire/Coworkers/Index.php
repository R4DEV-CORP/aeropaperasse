<?php

namespace App\Livewire\Coworkers;

use Livewire\Component;
use App\Models\Coworker;

class Index extends Component
{
    public $coworkers;
    public $search = '';

    protected $listeners = ['coworker-created' => 'refreshCoworkers'];

    public function mount()
    {
        $this->loadCoworkers();
    }

    public function loadCoworkers()
    {
        $query = Coworker::with(['client', 'user']);

        // Filtrage par client si l'utilisateur n'est pas admin
        if (!auth()->user()->isAdmin()) {
            $query->where('client_id', auth()->user()->client_id);
        }

        // Filtrage par recherche
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('firstname', 'like', '%' . $this->search . '%')
                  ->orWhere('lastname', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        $this->coworkers = $query->get();
    }

    public function refreshCoworkers()
    {
        $this->loadCoworkers();
    }

    public function updatedSearch()
    {
        $this->loadCoworkers();
    }

    public function render()
    {
        return view('livewire.coworkers.index');
    }
}
