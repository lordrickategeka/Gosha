<?php


namespace App\Domains\Operations\Livewire\JobCards;

use App\Domains\Operations\Models\JobCard;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Domains\Operations\Services\JobCardService;

class JobCardsComponent extends Component
{
    protected $listeners = ['deleteJobCard'];

    public function updateServiceTypeStatus($jobCardId, $serviceTypeId, $status)
    {
        DB::table('job_card_service_types')
            ->where('job_card_id', $jobCardId)
            ->where('service_type_id', $serviceTypeId)
            ->update(['status' => $status]);

        // Get all statuses for this job card's service types
        $statuses = DB::table('job_card_service_types')
            ->where('job_card_id', $jobCardId)
            ->pluck('status');

        if ($statuses->contains('pending')) {
            JobCard::where('id', $jobCardId)->update(['status' => 'pending']);
        } elseif ($statuses->contains('in_progress')) {
            JobCard::where('id', $jobCardId)->update(['status' => 'in_progress']);
        } elseif ($statuses->every(fn($s) => $s === 'completed')) {
            JobCard::where('id', $jobCardId)->update(['status' => 'completed']);
        }
    }

    public function render()
    {
        $jobCards = JobCard::with(['vehicleType', 'staff', 'customer', 'vehicle.vehicleItems', 'clientNarrations'])->latest()->paginate(10);
        return view('livewire.job-cards.job-cards-component', [
            'jobCards' => $jobCards,
        ]);
    }

    public function editJobCard($id)
    {
        return redirect()->route('job-cards.edit', $id);
    }

    public function deleteJobCard($id)
    {
        $service = new JobCardService();
        $deleted = $service->deleteJobCard((int) $id);
        if ($deleted) {
            session()->flash('message', 'Job card deleted successfully.');
        } else {
            session()->flash('error', 'Job card not found or could not be deleted.');
        }
        $this->dispatch('$refresh');
    }
}
