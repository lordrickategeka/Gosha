<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-black">Parts Intelligence</h3>
            <p class="text-sm text-gray-500">
                Compare supplier sources, landed costs, and fitment confidence per part line.
            </p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success text-sm">{{ session('success') }}</div>
    @endif
    @if (session()->has('warning'))
        <div class="alert alert-warning text-sm">{{ session('warning') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saved Sources</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($this->partItems as $index => $item)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-200' }} hover:bg-base-200 transition-colors duration-150">
                            <td class="px-4 py-2 whitespace-nowrap">
                                <div class="text-xs text-gray-900">{{ $index + 1 }}</div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="text-xs font-medium text-gray-900">{{ $item->description }}</div>
                                <div class="text-xs text-gray-700">Item #{{ $item->id }}</div>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <div class="text-xs text-gray-900">{{ $item->quantity }}</div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="text-xs text-gray-900">{{ $item->partSources->count() }} source(s)</div>
                                @if($item->partSources->where('is_recommended', true)->count())
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 mt-1">
                                        Recommended source selected
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button wire:click="openSourceModal({{ $item->id }})"
                                            class="px-2 py-1.5 text-xs border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                        Add Source
                                    </button>
                                    <button wire:click="getRecommendation({{ $item->id }})"
                                            class="btn btn-primary btn-xs">
                                        Recommend
                                    </button>
                                </div>
                            </td>
                        </tr>

                        @if($item->partSources->count())
                            <tr>
                                <td colspan="5" class="px-4 py-2 bg-gray-50 border-t border-gray-200">
                                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                        <div class="overflow-x-auto">
                                            <table class="w-full">
                                                <thead class="bg-gray-50 border-b border-gray-200">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part #</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Landed</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lead Time</th>
                                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white">
                                                    @foreach($item->partSources as $sIndex => $source)
                                                        <tr class="{{ $sIndex % 2 === 0 ? 'bg-white' : 'bg-gray-200' }} hover:bg-base-200 transition-colors duration-150">
                                                            <td class="px-4 py-2">
                                                                <div class="text-xs text-gray-900">
                                                                    {{ $source->supplier?->name ?? ($source->source_name ?? 'Manual Source') }}
                                                                </div>
                                                                @if($source->is_recommended)
                                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary mt-1">
                                                                        Recommended
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-2"><div class="text-xs text-gray-900">{{ $source->source_part_number ?? '-' }}</div></td>
                                                            <td class="px-4 py-2"><div class="text-xs text-gray-900">{{ number_format((float)($source->supplier_price ?? 0), 2) }}</div></td>
                                                            <td class="px-4 py-2"><div class="text-xs text-gray-900">{{ number_format((float)($source->total_landed_cost ?? 0), 2) }}</div></td>
                                                            <td class="px-4 py-2"><div class="text-xs text-gray-900">{{ $source->lead_time_days ?? '-' }} day(s)</div></td>
                                                            <td class="px-4 py-2 text-right">
                                                                <button wire:click="markRecommended({{ $source->id }})"
                                                                        class="px-2 py-1.5 text-xs border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                                                    Mark Recommended
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    @if(isset($recommendations[$item->id]['recommended']) && $recommendations[$item->id]['recommended'])
                                        @php $r = $recommendations[$item->id]['recommended']; @endphp
                                        <div class="mt-3 p-3 rounded-lg border border-primary/30 bg-primary/5">
                                            <p class="text-xs text-gray-700">
                                                <span class="font-semibold text-gray-900">Recommendation:</span>
                                                {{ $r['is_local'] ? 'Local Source' : 'Import Source' }}
                                                •
                                                <span class="font-semibold text-gray-900">Confidence:</span> {{ $r['confidence_score'] }}%
                                                •
                                                <span class="font-semibold text-gray-900">Total Landed:</span> {{ number_format((float)$r['total_landed_cost'], 2) }}
                                            </p>
                                            @if(!empty($r['reason_codes']))
                                                <p class="text-xs text-gray-600 mt-1">
                                                    Reason: {{ implode(', ', $r['reason_codes']) }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-lg font-medium text-gray-500">No part items found</div>
                                <p class="text-sm text-gray-500 mt-1">Add part-type line items on this work order to begin sourcing.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showSourceModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeSourceModal"></div>

            <div class="flex items-end justify-center min-h-screen p-4 text-center sm:items-center sm:p-0">
                <div class="inline-block align-bottom bg-base-100 rounded-lg text-left overflow-hidden shadow-md transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-black">Add Supplier Source</h3>
                        <p class="text-sm text-gray-500">Manual link-based source entry (v1).</p>
                    </div>

                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700">Supplier</label>
                            <select wire:model="sourceSupplierId" class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Select supplier (optional)</option>
                                @foreach($this->suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('sourceSupplierId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-700">Source Name</label>
                            <input type="text" wire:model="sourceName"
                                   class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                   placeholder="PartSouq / 7zap / Local Supplier" />
                            @error('sourceName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-medium text-gray-700">Source Link</label>
                            <input type="url" wire:model="sourceLink"
                                   class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                   placeholder="https://..." />
                            @error('sourceLink') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-700">Part Number</label>
                            <input type="text" wire:model="sourcePartNumber"
                                   class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" />
                            @error('sourcePartNumber') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-700">Supplier Price</label>
                            <input type="number" step="0.01" wire:model="sourceSupplierPrice"
                                   class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" />
                            @error('sourceSupplierPrice') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-700">Shipping</label>
                            <input type="number" step="0.01" wire:model="sourceShippingCost"
                                   class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" />
                            @error('sourceShippingCost') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-700">Duty</label>
                            <input type="number" step="0.01" wire:model="sourceDutyCost"
                                   class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" />
                            @error('sourceDutyCost') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-700">Clearing</label>
                            <input type="number" step="0.01" wire:model="sourceClearingCost"
                                   class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" />
                            @error('sourceClearingCost') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-700">Margin Amount</label>
                            <input type="number" step="0.01" wire:model="sourceMarginAmount"
                                   class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" />
                            @error('sourceMarginAmount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-700">Margin %</label>
                            <input type="number" step="0.01" wire:model="sourceMarginPercent"
                                   class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" />
                            @error('sourceMarginPercent') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-700">Availability</label>
                            <input type="text" wire:model="sourceAvailability"
                                   class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                   placeholder="in_stock / preorder" />
                            @error('sourceAvailability') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-700">Lead Time (days)</label>
                            <input type="number" wire:model="sourceLeadTimeDays"
                                   class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" />
                            @error('sourceLeadTimeDays') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-700">
                                <input type="checkbox" wire:model="sourceIsLocal" class="checkbox checkbox-xs" />
                                Local Source
                            </label>
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-medium text-gray-700">Warranty</label>
                            <input type="text" wire:model="sourceWarrantyText"
                                   class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" />
                            @error('sourceWarrantyText') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-xs font-medium text-gray-700">Notes</label>
                            <textarea rows="3" wire:model="sourceNotes"
                                      class="w-full px-4 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                            @error('sourceNotes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button wire:click="saveSource" class="btn btn-primary btn-sm">Save Source</button>
                        <button wire:click="closeSourceModal" class="btn btn-ghost btn-sm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
