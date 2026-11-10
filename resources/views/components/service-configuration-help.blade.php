@props(['topic' => 'service'])
@php
    $help = match($topic) {
        'wash' => ['title' => 'Wash packages: a clear bundle for your customer', 'purpose' => 'Save the work included in a wash or detailing offer, its total price and estimated time. Staff select the package when preparing a wash order.', 'example' => 'Standard wash — exterior body wash, wheel cleaning and interior vacuum; estimated time 45 minutes; enter your own approved package price.', 'tip' => 'The package price covers the whole bundle. List each included task in plain language so staff and customers know what is covered. Deactivate seasonal or discontinued offers.'],
        'quality' => ['title' => 'Quality checklists: consistent checks before release', 'purpose' => 'Define the inspection prompts staff complete on work orders. Group short, observable checks into sections such as Exterior, Interior and Engine Compartment.', 'example' => 'Exterior → All lights working; Engine Compartment → No visible fluid leaks; Interior → Dashboard warning lights checked.', 'tip' => 'Review system defaults before adding your own checks to avoid duplicates. Changes apply when a new, empty inspection is populated; they do not rewrite an existing inspection. A checklist item still needs an inspector’s result.'],
        default => ['title' => 'Service templates: reusable recipes for repeat jobs', 'purpose' => 'Save the labour and parts lines you repeatedly add to repair work orders. Applying a template copies those lines to the job so the team can check quantities and prices for that vehicle.', 'example' => 'Small-car oil service — oil-change labour, engine oil and one oil filter. Use separate labour and part lines with your approved quantities and prices.', 'tip' => 'Use specific names, explain what the job includes, and add at least one item. Prices are copied when the template is applied; review the job before invoicing. Deactivate old templates to keep the active list useful.'],
    };
@endphp
<section class="gh-card gh-card--pad" aria-label="How this setup works">
    <h2 class="gh-card__title">{{ $help['title'] }}</h2>
    <p class="gh-muted" style="margin-top:8px;font-size:13px;line-height:1.8;">{{ $help['purpose'] }}</p>
    <p style="margin-top:10px;font-size:13px;line-height:1.8;"><strong>Example:</strong> {{ $help['example'] }}</p>
    <details style="margin-top:12px;font-size:12.5px;"><summary style="cursor:pointer;font-weight:600;">Tips for useful records</summary><p class="gh-muted" style="margin-top:8px;line-height:1.8;">{{ $help['tip'] }}</p></details>
</section>
