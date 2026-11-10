@extends('documentation.layout')
@section('content')
    <section class="docs-hero">
        <p class="docs-eyebrow">Your guide to GarageHQ</p>
        <h1>Everyday work,<br>explained step by step.</h1>
        <p>From your first registration to daily operations. Find the right workflow for your garage, supplier business or team.</p>
        <div class="docs-hero-actions"><a href="{{ route('user-guide.download') }}">Download full guide <span aria-hidden="true">↓</span></a><button type="button" onclick="window.print()">Print this view</button><span>{{ $chapterCount }} practical chapters</span></div>
    </section>
    <main id="main-content" class="docs-container">
        <form method="GET" action="{{ route('user-guide.index') }}" class="docs-search" role="search">
            <div><label for="guide-search">What do you need help with?</label><input id="guide-search" type="search" name="q" value="{{ $search }}" maxlength="200" placeholder="Search: invoices, supplier approval, stock…" /></div>
            <div><label for="guide-audience">Show guidance for</label><select id="guide-audience" name="audience">@foreach($audiences as $key => $label)<option value="{{ $key }}" @selected($audience === $key)>{{ $label }}</option>@endforeach</select></div>
            <button type="submit">Find help</button>
            @if($search !== '' || $audience !== 'all')<a href="{{ route('user-guide.index') }}">Clear filters</a>@endif
        </form>
        <div class="docs-reading-layout">
            <aside class="docs-sidebar">
                <p class="docs-eyebrow">In this guide</p>
                <nav aria-label="Guide chapters">@foreach($chapters as $chapter)<a href="#{{ $chapter['id'] }}">{{ $chapter['title'] }}</a>@endforeach</nav>
                <a href="{{ route('api-docs.index') }}" class="docs-api-prompt"><strong>Building an integration?</strong><span>Explore the Swagger UI API reference →</span></a>
            </aside>
            <div class="docs-chapters">
                <div class="docs-result-count" role="status">{{ count($chapters) }} {{ count($chapters) === 1 ? 'chapter' : 'chapters' }}{{ $search !== '' ? ' matching your search' : ' available' }}</div>
                @forelse($chapters as $chapter)
                    <article id="{{ $chapter['id'] }}" class="docs-chapter">
                        <div class="docs-chapter-tags">@foreach($chapter['audiences'] as $group)<span>{{ $audiences[$group] ?? $group }}</span>@endforeach</div>
                        <h2><a href="#{{ $chapter['id'] }}">{{ $chapter['title'] }}</a></h2>
                        <div class="docs-prose">{!! $chapter['html'] !!}</div>
                        <a class="docs-back-top" href="#main-content">Back to contents ↑</a>
                    </article>
                @empty
                    <section class="docs-empty"><h2>No matching chapters</h2><p>Try a shorter search, choose another audience, or browse the full guide.</p><a href="{{ route('user-guide.index') }}">Show the full guide</a></section>
                @endforelse
            </div>
        </div>
    </main>
@endsection
