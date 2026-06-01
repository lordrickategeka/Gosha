# Project TODO

## Parts Intelligence Module - Existing Track

### Step 1: Confirm schema approach ✅ completed
- [x] Use existing `work_order_items` as the base line items for staff entry.
- [x] Create new intelligence tables keyed to `work_order_item_id`.
- [x] Fitment outcomes stored in a dedicated `part_installation_history` table (not `InventoryMovement`).

### Step 2: Generate core migrations ✅ completed
- [x] `vehicle_profiles`
- [x] `parts`
- [x] `part_oem_numbers`
- [x] `part_interchanges`
- [x] `part_fitment_rules`
- [x] `supplier_parts`
- [x] `work_order_part_sources` (option A: multiple supplier offers per line)
- [x] `part_installation_history`

### Step 3: Generate Eloquent models + relationships ✅ completed
- [x] Models for all tables above.
- [x] Relationship graph (VehicleProfile <-> Vehicle, Part <-> OEM numbers, etc.).

### Step 4: Implement core services ✅ completed
- [x] Landed cost calculator
- [x] Fitment confidence scorer
- [x] Recommendation engine
- [x] Interchange resolver

### Step 5: Livewire UI skeleton ✅ completed
- [x] Vehicle profiling editor
- [x] Work order part line “intelligence” panel
- [x] Supplier comparison table UI

### Step 6: Integrate into workflow 🚧 in progress
- [x] Add tab/section entry point from Work Order view/edit.
- [ ] Persist intelligence snapshot when staff saves.
- [ ] Add installation outcome actions in Parts Intelligence panel.
- [ ] Persist installation outcomes into `part_installation_history`.

### Step 7: Testing 🚧 in progress
- [ ] Run migrations (verification pass)
- [ ] Smoke test Livewire screens for parts intelligence flow
- [ ] Verify scoring/recommendation persistence in real workflow
- [ ] Re-run regression checks (`php artisan test`, `npm run build`)

---

## Debit Note Module (Inventory Return / Consumer Return) - Phase 1

### Step 1: Data model + migrations
- [ ] Create `debit_notes` migration:
  - [ ] `branch_id`, `work_order_id`, `customer_id`, `invoice_id` (nullable), `quotation_id` (nullable)
  - [ ] `debit_note_number` (unique), `approval_token` (unique)
  - [ ] status enum: `draft`, `sent`, `partially_approved`, `approved`, `rejected`, `applied`
  - [ ] totals + timestamps (`sent_at`, `responded_at`) + `notes`
  - [ ] indexes for status/work order/token
- [ ] Create `debit_note_items` migration:
  - [ ] `debit_note_id`, optional `work_order_item_id`, optional `inventory_item_id`
  - [ ] `item_type`, `description`, `quantity`, `unit_price`, `discount`, `total`
  - [ ] customer decision enum: `pending`, `approved`, `rejected`
  - [ ] `rejection_reason`, `sort_order`
  - [ ] index on (`debit_note_id`, `sort_order`)

### Step 2: Models + relationships
- [ ] Create `app/Models/DebitNote.php`
- [ ] Create `app/Models/DebitNoteItem.php`
- [ ] Add relationships from:
  - [ ] DebitNote -> WorkOrder/Customer/Invoice/Quotation/items
  - [ ] DebitNoteItem -> DebitNote/workOrderItem/inventoryItem

### Step 3: Public customer review flow
- [ ] Create `app/Http/Controllers/PublicDebitNoteController.php`
  - [ ] `show($token)`
  - [ ] `submit($token)` (per-item decisions + approve-all support)
- [ ] Create `resources/views/public/debit-note.blade.php`
  - [ ] prominent notice banner for additional work discovered
  - [ ] item-level approve/reject controls
  - [ ] final submit action

### Step 4: Routing
- [ ] Add public routes in `routes/web.php` for:
  - [ ] GET debit note review by token
  - [ ] POST debit note response submission by token

### Step 5: Apply approval to Work Order + Invoice
- [ ] Implement apply logic for approved items:
  - [ ] Upsert approved lines into `work_order_items`
  - [ ] Sync to `invoice_items` if invoice exists
  - [ ] Recalculate invoice totals
  - [ ] set debit note status (`rejected` / `partially_approved` / `approved` / `applied`)
- [ ] Implement inventory return handling for consumer-return context
  - [ ] create corresponding `InventoryMovement` records for returned/rejected part lines where applicable

### Step 6: Quotation review notice entry
- [ ] Update `resources/views/public/quotation.blade.php`
  - [ ] add clear notice entry for additional work / debit note review expectation

### Step 7: Verification
- [x] Run migration check
- [ ] Validate customer per-item approve/reject flow
- [ ] Validate invoice update after debit note submission
- [ ] Run regression checks (`php artisan test`, `npm run build`)

### Step 8: Work Order UI entry points (Job Card → Quotation → Work starts → Additional work discovered → Debit Note Request → Customer Approval → Invoice Updated)
- [ ] Add Debit Notes section to `resources/views/livewire/work-orders/show-work-orders-component.blade.php`
- [ ] Add "Create Debit Note Request" modal with line-item entry
- [ ] Add component logic in `app/Livewire/WorkOrders/ShowWorkOrdersComponent.php` for draft/sent debit note creation
- [ ] Add quick actions per note: Open Public Review + Copy Link + Resend
- [ ] Add invoice-side status hint in Work Order sidebar for latest debit note
