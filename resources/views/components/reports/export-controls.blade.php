<div class="card bg-base-100 shadow-sm mb-6">
    <div class="card-body p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
            <label class="form-control">
                <span class="label-text text-xs mb-1">Export Format</span>
                <select wire:model.live="exportFormat" class="select select-bordered select-sm">
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                </select>
            </label>

            <label class="form-control">
                <span class="label-text text-xs mb-1">Export Type</span>
                <select wire:model.live="exportType" class="select select-bordered select-sm">
                    <option value="summary">Summary</option>
                    <option value="detailed">Detailed</option>
                </select>
            </label>

            <button wire:click="exportReport" class="btn btn-primary btn-sm">
                Export Report
            </button>
        </div>
    </div>
</div>
