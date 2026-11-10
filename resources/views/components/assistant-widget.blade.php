@auth
    @if(\App\Services\AssistantService::available() && auth()->user()->is_active)
        @once
            @vite(['resources/css/assistant.css', 'resources/js/assistant.js'])
            <div class="gh-help" data-assistant data-endpoint="{{ route('assistant.chat', [], false) }}" data-csrf="{{ csrf_token() }}">
                <button type="button" class="gh-help-launcher" data-help-toggle aria-expanded="false" aria-controls="gh-help-panel">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5Z"/><path d="M8 11h8M8 14h5"/></svg>
                    <span>Ask GarageHQ</span><span class="gh-help-ai">AI</span>
                </button>
                <section id="gh-help-panel" class="gh-help-panel" role="dialog" aria-labelledby="gh-help-title" hidden>
                    <header class="gh-help-header"><div><p class="gh-help-eyebrow">YOUR GUIDE, A CONVERSATION AWAY</p><h2 id="gh-help-title">GarageHQ assistant</h2></div><button type="button" class="gh-help-icon" data-help-close aria-label="Close assistant">&times;</button></header>
                    <div class="gh-help-scroll" data-help-scroll>
                        <p class="gh-help-intro">Ask how to use GarageHQ. I can explain the guide and point you to the right chapter.</p>
                        <div class="gh-help-prompts" aria-label="Suggested questions">
                            <button type="button" data-help-question="How do I get started with GarageHQ?">Getting started</button>
                            <button type="button" data-help-question="How does the 14-day free trial work?">Free trial</button>
                            <button type="button" data-help-question="How do supplier quotes and orders work?">Supplier orders</button>
                        </div>
                        <p class="gh-help-scope">I explain workflows. I cannot see your records or make changes. Ask each question in full; previous messages are not sent.</p>
                        <div class="gh-help-messages" data-help-messages role="log" aria-label="Assistant conversation" aria-live="polite" aria-relevant="additions"></div>
                        <p class="gh-help-status" data-help-status role="status" hidden>Looking through the GarageHQ guide…</p>
                    </div>
                    <div class="gh-help-composer">
                        <p class="gh-help-error" data-help-error role="alert" hidden></p>
                        <form data-help-form>
                            <label class="gh-help-consent"><input type="checkbox" name="consent" required><span>I agree to send my question and relevant guide excerpts to the shared assistant and Ollama Cloud. Do not include customer details, passwords or keys.</span></label>
                            <label for="gh-help-question" class="gh-help-question-label">Your question</label>
                            <textarea id="gh-help-question" name="question" rows="2" maxlength="1200" required placeholder="How do I…?"></textarea>
                            <div class="gh-help-form-actions"><button type="button" class="gh-help-clear" data-help-clear>Clear chat</button><button type="submit" class="gh-help-send">Ask assistant <span aria-hidden="true">↑</span></button></div>
                        </form>
                        <div class="gh-help-footer"><a href="{{ route('user-guide.index') }}">Browse the user guide</a><span>AI answers can be mistaken.</span></div>
                    </div>
                </section>
            </div>
        @endonce
    @endif
@endauth
