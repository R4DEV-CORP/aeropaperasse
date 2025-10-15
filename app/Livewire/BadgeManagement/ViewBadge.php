<?php

namespace App\Livewire\BadgeManagement;

use Livewire\Component;

class ViewBadge extends Component
{
    public $badge;
    
    public function render()
    {
        return view('livewire.badge-management.view-badge');
    }
}
