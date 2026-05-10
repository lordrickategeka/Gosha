<?php

namespace App\Livewire\Templates;

use App\Models\DocumentTemplate;
use Livewire\Component;

class CreateTemplate extends Component
{
    public $name = '';
    public $document_type = 'invoice';
    public $page_size = 'A4';
    public $orientation = 'portrait';
    public $primary_color = '#3B82F6';
    public $secondary_color = '#1E40AF';
    public $font_family = 'Inter';
    public $font_size = 10;

    protected $rules = [
        'name' => 'required|string|max:255',
        'document_type' => 'required|in:invoice,work_order,quotation,receipt,report',
        'page_size' => 'required|in:A4,Letter,A5',
        'orientation' => 'required|in:portrait,landscape',
        'primary_color' => 'required|string|max:7',
        'secondary_color' => 'required|string|max:7',
        'font_family' => 'required|string|max:50',
        'font_size' => 'required|integer|min:8|max:16',
    ];

    public function save()
    {
        $this->validate();

        $template = DocumentTemplate::create([
            'vendor_id' => auth()->user()->vendor_id,
            'name' => $this->name,
            'document_type' => $this->document_type,
            'page_size' => $this->page_size,
            'orientation' => $this->orientation,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'font_family' => $this->font_family,
            'font_size' => $this->font_size,
            'template_schema' => [
                'version' => '1.0',
                'sections' => [],
            ],
            'margins' => [
                'top' => 20,
                'right' => 20,
                'bottom' => 20,
                'left' => 20,
            ],
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', 'Template created successfully. Now design your template.');

        return redirect()->route('templates.edit', $template);
    }

    public function render()
    {
        return view('livewire.templates.create-template')
            ->layout('components.layouts.app', ['title' => 'New Template']);
    }
}
