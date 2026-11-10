@extends('documentation.layout')
@section('assets')
    @vite('resources/js/api-docs.js')
@endsection
@section('content')
    <main id="main-content" class="docs-container docs-api-page">
        <div class="docs-api-intro"><p class="docs-eyebrow">Developer reference</p><h1>API reference</h1><p>Select GarageHQ, Cream Theme or OnePass in Swagger UI. Explore the endpoints, authentication and schemas, or download the OpenAPI documents below. This reference does not submit live requests.</p></div>
        <div class="docs-spec-links">@foreach(['garagehq' => 'GarageHQ', 'cream-theme' => 'Cream Theme', 'onepass' => 'OnePass'] as $service => $label)<a href="{{ route('api-docs.spec', $service) }}">{{ $label }} OpenAPI JSON ↗</a>@endforeach</div>
        <div class="docs-api-note"><strong>Before connecting:</strong> use backend-held credentials for each service. Check PHP's HTTPS certificate trust in both web and worker runtimes. <a href="{{ route('user-guide.index') }}#api-reference-for-integration-partners">Read the integration notes</a>.</div>
        <div id="swagger-ui" data-specs='@json($apiSpecs)'></div>
        <noscript><p>Enable JavaScript to display Swagger UI, or open an OpenAPI JSON document above.</p></noscript>
    </main>
@endsection
