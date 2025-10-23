<?php

namespace App\Livewire\Coworkers;

use App\Models\Coworker;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public $search = '';

    public $selectedCoworkerId = null;

    private function loadCoworkers()
    {
        $query = Coworker::with(['client', 'user']);

        // Filtrage par client si l'utilisateur n'est pas admin
        if (! auth()->user()->isAdmin()) {
            $query->where('client_id', auth()->user()->client_id);
        }

        // Filtrage par recherche
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('firstname', 'like', '%'.$this->search.'%')
                    ->orWhere('lastname', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%');
            });
        }

        return $query->get();
    }

    /**
     * Écoute l'événement 'coworker-created' et recharge la liste des coworkers
     */
    #[On('coworker-created')]
    public function refreshCoworkers()
    {
        // Réinitialiser la recherche pour recharger la liste
        $this->search = '';
    }

    /**
     * Écoute l'événement 'coworker-updated' et recharge la liste des coworkers
     */
    #[On('coworker-updated')]
    public function refreshCoworkersAfterUpdate()
    {
        // Réinitialiser la recherche pour recharger la liste
        $this->search = '';
    }

    public function render()
    {
        $coworkers = $this->loadCoworkers();

        return view('livewire.coworkers.index', [
            'coworkers' => $coworkers,
        ]);
    }
}
