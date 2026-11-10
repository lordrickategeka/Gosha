<?php

namespace App\Domains\Organization\Livewire\Settings;

use App\Support\MasterData;
use Livewire\Component;

class MasterDataComponent extends Component
{
    public function render()
    {
        $items = app(MasterData::class)->items(auth()->user());

        return view('livewire.settings.master-data', compact('items'))->layout('components.layouts.app', ['title' => 'Master data']);
    }
}
