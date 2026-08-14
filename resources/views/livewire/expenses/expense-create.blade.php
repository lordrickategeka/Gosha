<div class="gh-page">
    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('expenses.index') }}" class="gh-btn gh-btn--sm">←</a>
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Create Expense</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Record a new expense</p>
        </div>
    </div>

    @if (session()->has('error'))
        <div class="gh-badge gh-badge--error" style="display:block; padding:10px 12px; font-size:12px;">{{ session('error') }}</div>
    @endif

    <form wire:submit="save">
        <div class="gh-split">
            <div class="gh-stack">
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:14px;">Basic Information</div>

                    <div class="gh-grid-2">
                        <div class="gh-field">
                            <span class="gh-label">Expense type *</span>
                            <select wire:model.live="expense_type" class="gh-select" style="width:100%;">
                                @foreach($expenseTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('expense_type') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div class="gh-field">
                            <span class="gh-label">Category *</span>
                            <select wire:model.live="category_id" class="gh-select" style="width:100%;">
                                <option value="">Select category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->full_name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div class="gh-field">
                            <span class="gh-label">Date *</span>
                            <input type="date" wire:model="expense_date" class="gh-input" style="width:100%;">
                            @error('expense_date') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div class="gh-field">
                            <span class="gh-label">Supplier</span>
                            <select wire:model="supplier_id" class="gh-select" style="width:100%;">
                                <option value="">None</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="gh-field" style="margin-top:14px;">
                        <span class="gh-label">Description *</span>
                        <textarea wire:model="description" rows="3" class="gh-input" style="width:100%;" placeholder="What was this expense for?"></textarea>
                        @error('description') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:14px;">Amount &amp; Currency</div>

                    <div class="gh-grid-2">
                        <div class="gh-field">
                            <span class="gh-label">Amount *</span>
                            <input type="number" wire:model.live="amount" step="0.01" class="gh-input" style="width:100%;" placeholder="0.00">
                            @error('amount') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div class="gh-field">
                            <span class="gh-label">Currency *</span>
                            <select wire:model.live="currency" class="gh-select" style="width:100%;">
                                @foreach($currencies as $curr)
                                    <option value="{{ $curr->code }}">{{ $curr->code }} - {{ $curr->name }}</option>
                                @endforeach
                            </select>
                            @error('currency') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        @if($currency !== 'UGX')
                            <div class="gh-field">
                                <span class="gh-label">Exchange rate (to UGX)</span>
                                <input type="number" wire:model="exchange_rate" step="0.0001" class="gh-input" style="width:100%;">
                                @error('exchange_rate') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                                <span class="gh-hint">1 {{ $currency }} = {{ $exchange_rate }} UGX</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:14px;">Tax Information</div>

                    <div class="gh-grid-2">
                        <div class="gh-field">
                            <span class="gh-label">Tax percentage (%)</span>
                            <input type="number" wire:model="tax_percentage" step="0.01" class="gh-input" style="width:100%;" placeholder="0">
                            @error('tax_percentage') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-top:16px;">
                                <input type="checkbox" wire:model="tax_inclusive">
                                <span style="font-weight:600; font-size:12.5px;">Tax inclusive</span>
                            </label>
                            <span class="gh-hint">Check if amount already includes tax</span>
                        </div>
                    </div>
                </div>

                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:14px;">Payment Information</div>

                    <div class="gh-grid-2">
                        <div class="gh-field">
                            <span class="gh-label">Payment method *</span>
                            <select wire:model="payment_method" class="gh-select" style="width:100%;">
                                @foreach($paymentMethods as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_method') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div class="gh-field">
                            <span class="gh-label">Reference number</span>
                            <input type="text" wire:model="payment_reference" class="gh-input" style="width:100%;" placeholder="Receipt or transaction number">
                            @error('payment_reference') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:14px;">Attachments</div>

                    <div class="gh-field">
                        <span class="gh-label">Upload files</span>
                        <input type="file" wire:model="attachments" multiple class="gh-input" style="width:100%;" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        <span class="gh-hint">Supported: JPG, PNG, PDF, DOC, DOCX (Max 10MB each)</span>
                        @error('attachments.*') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    @if($attachments)
                        <div style="margin-top:14px;">
                            <p style="font-weight:600; font-size:12.5px; margin-bottom:6px;">Selected files:</p>
                            <ul style="list-style:disc; padding-left:18px; display:flex; flex-direction:column; gap:2px;">
                                @foreach($attachments as $attachment)
                                    <li style="font-size:12px;">{{ $attachment->getClientOriginalName() }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:12px;">Additional Notes</div>
                    <textarea wire:model="notes" rows="3" class="gh-input" style="width:100%;" placeholder="Any additional information..."></textarea>
                    @error('notes') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="gh-stack">
                <div class="gh-card gh-card--pad" style="position:sticky; top:14px;">
                    <div class="gh-card__title" style="margin-bottom:14px;">Summary</div>

                    <div class="gh-stack" style="gap:9px;">
                        <div style="display:flex; justify-content:space-between; font-size:12.5px;">
                            <span class="gh-muted">Amount:</span>
                            <span style="font-weight:600;">{{ $currency }} {{ number_format($amount ?: 0, 2) }}</span>
                        </div>

                        @if($tax_percentage)
                            <div style="display:flex; justify-content:space-between; font-size:12.5px;">
                                <span class="gh-muted">Tax ({{ $tax_percentage }}%):</span>
                                <span style="font-weight:600;">
                                    {{ $currency }}
                                    @php
                                        $taxAmount = $tax_inclusive
                                            ? ($amount * $tax_percentage / (100 + $tax_percentage))
                                            : ($amount * $tax_percentage / 100);
                                    @endphp
                                    {{ number_format($taxAmount, 2) }}
                                </span>
                            </div>
                        @endif

                        <div style="border-top:1px solid var(--gh-hairline); margin:4px 0;"></div>

                        <div style="display:flex; justify-content:space-between;">
                            <span style="font-weight:700; font-size:13px;">Total:</span>
                            <span style="font-weight:800; font-size:16px;">
                                {{ $currency }}
                                @php
                                    $total = $amount ?: 0;
                                    if ($tax_percentage && !$tax_inclusive) {
                                        $total += ($amount * $tax_percentage / 100);
                                    }
                                @endphp
                                {{ number_format($total, 2) }}
                            </span>
                        </div>

                        @if($currency !== 'UGX' && $exchange_rate)
                            <div style="display:flex; justify-content:space-between; font-size:12.5px;">
                                <span class="gh-muted">In UGX:</span>
                                <span style="font-weight:600;">UGX {{ number_format($total * $exchange_rate, 2) }}</span>
                            </div>
                        @endif
                    </div>

                    <div style="border-top:1px solid var(--gh-hairline); margin:16px 0;"></div>

                    <button type="button" wire:click="previewApproval" class="gh-btn gh-btn--block gh-btn--sm" style="margin-bottom:10px;">Preview approval path</button>

                    @if($show_approval_preview && $approval_preview)
                        <div class="gh-badge gh-badge--info" style="display:block; padding:10px 12px; font-size:12px; margin-bottom:10px;">
                            @if($approval_preview['auto_approved'] ?? false)
                                Will be auto-approved: {{ $approval_preview['reason'] }}
                            @else
                                <p style="font-weight:700; margin-bottom:6px;">{{ $approval_preview['chain_name'] }}</p>
                                @foreach($approval_preview['levels'] as $level)
                                    <div style="margin-bottom:6px;">
                                        <p style="font-weight:600;">Level {{ $level['level'] }}: {{ $level['description'] }}</p>
                                        <p style="font-size:10.5px;">{{ implode(', ', $level['approvers']) }}</p>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endif

                    <div class="gh-stack" style="gap:8px;">
                        <button type="submit" wire:click="$set('save_as_draft', false)" class="gh-btn gh-btn--primary gh-btn--block">Submit for approval</button>
                        <button type="submit" wire:click="$set('save_as_draft', true)" class="gh-btn gh-btn--block">Save as draft</button>
                        <a href="{{ route('expenses.index') }}" class="gh-btn gh-btn--block gh-btn--sm">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
