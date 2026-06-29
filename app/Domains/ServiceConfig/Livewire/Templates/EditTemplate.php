<?php

namespace App\Domains\ServiceConfig\Livewire\Templates;

use App\Domains\ServiceConfig\Models\DocumentTemplate;
use Livewire\Component;

class EditTemplate extends Component
{
    public DocumentTemplate $template;

    public $name;
    public $page_size;
    public $orientation;
    public $primary_color;
    public $secondary_color;
    public $font_family;
    public $font_size;

    public function mount(DocumentTemplate $template)
    {
        // Verify vendor ownership
        if ($template->vendor_id !== auth()->user()->vendor_id) {
            abort(403, 'You do not have access to this template.');
        }

        $this->template = $template;
        $this->name = $template->name;
        $this->page_size = $template->page_size;
        $this->orientation = $template->orientation;
        $this->primary_color = $template->primary_color;
        $this->secondary_color = $template->secondary_color;
        $this->font_family = $template->font_family;
        $this->font_size = $template->font_size;
    }

    public function updateSettings()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'page_size' => 'required|in:A4,Letter,A5',
            'orientation' => 'required|in:portrait,landscape',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'font_family' => 'required|string|max:50',
            'font_size' => 'required|integer|min:8|max:16',
        ]);

        $this->template->update([
            'name' => $this->name,
            'page_size' => $this->page_size,
            'orientation' => $this->orientation,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'font_family' => $this->font_family,
            'font_size' => $this->font_size,
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Template settings updated.');
    }

    public function saveSchema($schema)
    {
        $this->template->update([
            'template_schema' => $schema,
            'updated_by' => auth()->id(),
        ]);

        session()->flash('success', 'Template saved successfully.');
    }

    public function render()
    {
        return view('livewire.templates.edit-template')
            ->layout('components.layouts.app', [
                'title' => 'Edit Template: ' . $this->template->name
            ]);
    }
}
