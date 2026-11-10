let activeWidget;

function initializeAssistant() {
    const root = document.querySelector('[data-assistant]');
    if (!root || root.dataset.initialized) return;
    root.dataset.initialized = 'true';
    const panel = root.querySelector('#gh-help-panel');
    const toggle = root.querySelector('[data-help-toggle]');
    const form = root.querySelector('[data-help-form]');
    const input = form.elements.question;
    const messages = root.querySelector('[data-help-messages]');
    const status = root.querySelector('[data-help-status]');
    const error = root.querySelector('[data-help-error]');
    const scroll = root.querySelector('[data-help-scroll]');
    const clear = root.querySelector('[data-help-clear]');
    const submit = form.querySelector('[type=submit]');
    let pending = false;
    let disposed = false;
    let controller;
    let brandingLoaded = false;

    async function loadBranding() {
        if (brandingLoaded) return;
        brandingLoaded = true;
        try {
            const response = await fetch(root.dataset.configEndpoint, { credentials: 'same-origin', headers: { Accept: 'application/json' }, signal: AbortSignal.timeout(6000) });
            if (!response.ok || disposed) { brandingLoaded = false; return; }
            const data = await response.json();
            if (disposed) return;
            if (typeof data.assistantName === 'string' && data.assistantName.trim() && data.assistantName.length <= 80) {
                root.dataset.assistantName = data.assistantName;
                root.querySelector('#gh-help-title').textContent = data.assistantName;
                root.querySelector('[data-help-launcher-label]').textContent = `Ask ${data.assistantName}`;
            }
            if (typeof data.welcomeMessage === 'string' && data.welcomeMessage.length <= 500) root.querySelector('[data-help-greeting]').textContent = data.welcomeMessage;
        } catch { brandingLoaded = false; }
    }

    function setOpen(open) {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        if (open) { input.focus(); loadBranding(); }
        else toggle.focus();
    }
    function addMessage(label, text, sources = []) {
        const article = document.createElement('article');
        article.className = `gh-help-message ${label === 'You' ? 'gh-help-message-user' : ''}`;
        const heading = document.createElement('strong');
        heading.textContent = label;
        const body = document.createElement('p');
        body.textContent = text;
        article.append(heading, body);
        if (sources.length) {
            const links = document.createElement('div');
            links.className = 'gh-help-sources';
            for (const source of sources) {
                // Defense in depth; the backend also checks the chapter allowlist.
                if (typeof source.path !== 'string' || !/^\/user-guide#[a-z0-9-]+$/.test(source.path)) continue;
                const link = document.createElement('a');
                link.href = source.path;
                link.textContent = source.title;
                links.append(link);
            }
            article.append(links);
        }
        messages.append(article);
        scroll.scrollTop = scroll.scrollHeight;
    }
    function showError(message) {
        error.textContent = message;
        error.hidden = false;
    }
    const onEscape = (event) => {
        if (event.key === 'Escape' && !panel.hidden && root.contains(document.activeElement)) setOpen(false);
    };
    document.addEventListener('keydown', onEscape);
    toggle.addEventListener('click', () => setOpen(panel.hidden));
    root.querySelector('[data-help-close]').addEventListener('click', () => setOpen(false));
    root.querySelectorAll('[data-help-question]').forEach((button) => {
        button.addEventListener('click', () => { input.value = button.dataset.helpQuestion; input.focus(); });
    });
    clear.addEventListener('click', () => {
        if (pending) return;
        messages.replaceChildren();
        error.hidden = true;
        input.value = '';
        input.focus();
    });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (pending || !form.reportValidity()) return;
        const question = input.value.trim();
        if (!question) { showError('Enter a question about GarageHQ.'); return; }
        pending = true;
        error.hidden = true;
        status.hidden = false;
        submit.disabled = clear.disabled = true;
        input.readOnly = true;
        addMessage('You', question);
        controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 60000);
        try {
            const response = await fetch(root.dataset.endpoint, {
                method: 'POST', credentials: 'same-origin', signal: controller.signal,
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': root.dataset.csrf },
                body: JSON.stringify({ question, consent: form.elements.consent.checked }),
            });
            if (disposed) return;
            if (response.status === 401 || response.status === 419) throw new Error('Your session expired. Refresh this page and sign in again.');
            if (response.status === 403) throw new Error('Your account cannot use the assistant. Contact your administrator.');
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                if (response.status === 429) throw new Error('You have reached the assistant request limit. Please wait and try again later.');
                throw new Error(data.message || 'The assistant is unavailable. Use the user guide or try again later.');
            }
            if (typeof data.answer !== 'string' || !data.answer.trim()) throw new Error('No answer was returned. Please try again.');
            addMessage(root.dataset.assistantName || 'GQ Cot', data.answer, Array.isArray(data.sources) ? data.sources : []);
            input.value = '';
        } catch (failure) {
            if (!disposed) showError(failure.name === 'AbortError' ? 'The assistant took too long. Please try again or open the user guide.' : failure.message);
        } finally {
            clearTimeout(timeout);
            if (!disposed) {
                pending = false;
                status.hidden = true;
                submit.disabled = clear.disabled = false;
                input.readOnly = false;
            }
        }
    });
    activeWidget = { dispose() { disposed = true; controller?.abort(); document.removeEventListener('keydown', onEscape); delete root.dataset.initialized; } };
}

initializeAssistant();
document.addEventListener('livewire:navigated', initializeAssistant);
document.addEventListener('livewire:navigating', () => { activeWidget?.dispose(); activeWidget = null; });
