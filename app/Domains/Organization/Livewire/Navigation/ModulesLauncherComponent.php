<?php

namespace App\Domains\Organization\Livewire\Navigation;

use App\Shared\Navigation\ModuleRegistry;
use App\Shared\Navigation\ModuleStats;
use Livewire\Component;

class ModulesLauncherComponent extends Component
{
    public function getModulesProperty()
    {
        return array_map(
            fn (array $module) => $module + ['stats' => ModuleStats::forModule($module['key'])],
            ModuleRegistry::visibleModules()
        );
    }

    public function render()
    {
        return view('livewire.modules-launcher-component')
            ->layout('components.layouts.app', ['title' => 'Modules']);
    }
}
