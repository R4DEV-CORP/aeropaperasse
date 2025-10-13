<?php

namespace App\Livewire\BadgeManagement;

use App\Models\Badge;
use Livewire\Component;

class Index extends Component
{
    public function loadBadges()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            $badges = Badge::all();
        } else {
            $badges = Badge::whereHas('badgeRequest.activityRequest', function($query) use ($user) {
                $query->where('client_id', $user->client_id);
            })->get();
        }
        
        return $badges;
    }

    public function render()
    {
        if (! empty($this->search)) {
            $badges = $this->buildScoutQuery();
        } else {
            $badges = $this->loadBadges();
        }

        return view('livewire.badge-management.index', [
            'badges' => $badges,
        ]);
    }
}
