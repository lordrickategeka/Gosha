<?php

namespace App\Domains\ServiceConfig\Livewire\QualityCheck;

use App\Domains\ServiceConfig\Models\QualityCheck;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithFileUploads;

class QualityCheckViewComponent extends Component
{
    use WithFileUploads;

    public QualityCheck $qualityCheck;
    public $signedDocument;

    public function mount(QualityCheck $qualityCheck)
    {
        $this->authorize('quality-check.view');

        $this->qualityCheck = $qualityCheck->load([
            'workOrder',
            'vehicle',
            'customer',
            'inspector',
            'items',
        ]);
    }

    public function downloadPdf()
    {
        $this->authorize('quality-check.download-pdf');

        $pdf = Pdf::loadView('pdf.quality-check-report', [
            'qualityCheck' => $this->qualityCheck,
            'itemsBySection' => $this->qualityCheck->getItemsBySection(),
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'quality-check-' . $this->qualityCheck->workOrder->order_number . '.pdf');
    }

    public function uploadSignedDocument()
    {
        $this->authorize('quality-check.upload-signed');

        $this->validate([
            'signedDocument' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $this->signedDocument->store('quality-checks/signed', 'public');

        $this->qualityCheck->update([
            'signed_file_path' => $path,
        ]);

        session()->flash('success', 'Signed document uploaded successfully.');
        
        $this->signedDocument = null;
    }

    public function deleteSignedDocument()
    {
        $this->authorize('quality-check.upload-signed');

        if ($this->qualityCheck->signed_file_path) {
            \Storage::disk('public')->delete($this->qualityCheck->signed_file_path);
            
            $this->qualityCheck->update([
                'signed_file_path' => null,
            ]);

            session()->flash('success', 'Signed document deleted.');
        }
    }

    public function render()
    {
        return view('livewire.quality-check.quality-check-view-component')
            ->layout('components.layouts.app', ['title' => 'Quality Check - ' . $this->qualityCheck->workOrder->order_number]);
    }
}
