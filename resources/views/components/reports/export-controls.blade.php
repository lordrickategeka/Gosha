<div class="gh-card gh-card--pad">
    <div style="display:grid; grid-template-columns:1fr 1fr auto; gap:12px; align-items:end;">
        <div class="gh-field">
            <span class="gh-label">Export format</span>
            <select wire:model.live="exportFormat" class="gh-select" style="width:100%;">
                <option value="pdf">PDF</option>
                <option value="excel">Excel</option>
            </select>
        </div>
        <div class="gh-field">
            <span class="gh-label">Export type</span>
            <select wire:model.live="exportType" class="gh-select" style="width:100%;">
                <option value="summary">Summary</option>
                <option value="detailed">Detailed</option>
            </select>
        </div>
        <button wire:click="exportReport" class="gh-btn gh-btn--primary gh-btn--sm">Export report</button>
    </div>
</div>
