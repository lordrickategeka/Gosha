<?php

namespace App\Domains\ServiceConfig\Livewire\Templates;

use App\Domains\ServiceConfig\Models\DocumentTemplate;
use Livewire\Component;
use Livewire\WithPagination;

class TemplateList extends Component
{
    use WithPagination;

    public $documentType = 'all';
    public $search = '';

    protected $queryString = [
        'documentType' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDocumentType()
    {
        $this->resetPage();
    }

    public function setAsDefault($templateId)
    {
        $template = DocumentTemplate::findOrFail($templateId);

        // Verify vendor ownership
        if ($template->vendor_id !== auth()->user()->vendor_id) {
            abort(403);
        }

        $template->update(['is_default' => true]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Template set as default.',
        ]);
    }

    public function toggleActive($templateId)
    {
        $template = DocumentTemplate::findOrFail($templateId);

        if ($template->vendor_id !== auth()->user()->vendor_id) {
            abort(403);
        }

        $template->update(['is_active' => !$template->is_active]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Template ' . ($template->is_active ? 'activated' : 'deactivated') . '.',
        ]);
    }

    public function delete($templateId)
    {
        $template = DocumentTemplate::findOrFail($templateId);

        if ($template->vendor_id !== auth()->user()->vendor_id) {
            abort(403);
        }

        if ($template->is_default) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot delete default template. Set another template as default first.',
            ]);
            return;
        }

        $template->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Template deleted successfully.',
        ]);
    }

    public function getTemplatesProperty()
    {
        $query = DocumentTemplate::where('vendor_id', auth()->user()->vendor_id);

        if ($this->documentType !== 'all') {
            $query->where('document_type', $this->documentType);
        }

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return $query->orderBy('document_type')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.templates.template-list')
            ->layout('components.layouts.app', ['title' => 'Document Templates']);
    }
}
