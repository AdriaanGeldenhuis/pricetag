<!-- Taggy - AI Shopping Assistant Widget -->
<div id="ai-assistant" class="taggy">
    <!-- Toggle Button with price tag icon -->
    <button type="button" class="taggy-toggle" id="ai-toggle" aria-label="Chat with Taggy">
        <svg class="taggy-icon-open" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
            <line x1="7" y1="7" x2="7.01" y2="7"/>
        </svg>
        <svg class="taggy-icon-close" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
        <span class="taggy-pulse"></span>
    </button>

    <!-- Chat Window -->
    <div class="taggy-window" id="ai-window" hidden>
        <!-- Header -->
        <div class="taggy-header">
            <div class="taggy-header-info">
                <div class="taggy-avatar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                </div>
                <div>
                    <h3 class="taggy-name">Taggy</h3>
                    <span class="taggy-status"><span class="taggy-status-dot"></span>Online &mdash; here to help</span>
                </div>
            </div>
            <button type="button" class="taggy-close-btn" id="ai-close" aria-label="Close Taggy">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <!-- Messages area -->
        <div class="taggy-messages" id="ai-messages">
            <!-- Welcome message -->
            <div class="taggy-msg taggy-msg-bot">
                <div class="taggy-msg-avatar">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                </div>
                <div class="taggy-msg-bubble">
                    Hey! I'm <strong>Taggy</strong>, your personal shopping sidekick. I know our entire store inside out &mdash; ask me about products, prices, orders, shipping, or anything else. What can I find for you?
                </div>
            </div>

            <!-- Quick suggestions -->
            <div class="taggy-suggestions" id="ai-suggestions">
                <button type="button" class="taggy-chip" data-message="Show me your best sellers">Best sellers</button>
                <button type="button" class="taggy-chip" data-message="What deals do you have on sale right now?">Deals &amp; sales</button>
                <button type="button" class="taggy-chip" data-message="How do I track my order?">Track my order</button>
                <button type="button" class="taggy-chip" data-message="What are your shipping options and costs?">Shipping info</button>
                <button type="button" class="taggy-chip" data-message="What is your return and refund policy?">Returns &amp; refunds</button>
                <button type="button" class="taggy-chip" data-message="What payment methods do you accept?">Payment methods</button>
            </div>
        </div>

        <!-- Input area -->
        <div class="taggy-input-area">
            <form id="ai-form" class="taggy-form">
                <label for="ai-input" class="sr-only">Ask Taggy anything</label>
                <input type="text" id="ai-input" class="taggy-input" placeholder="Ask Taggy anything..." autocomplete="off" />
                <button type="submit" class="taggy-send" id="ai-send" aria-label="Send message">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </form>
            <div class="taggy-powered">Powered by Taggy AI</div>
        </div>
    </div>
</div>

<style>
/* ============================================
   TAGGY - AI Shopping Assistant
   A one-of-a-kind assistant for Pricetag.co.za
   ============================================ */

/* --- Container --- */
.taggy {
    position: fixed;
    bottom: var(--space-5);
    right: var(--space-5);
    z-index: var(--z-modal);
    font-family: var(--font-sans);
}

/* --- Toggle Button --- */
.taggy-toggle {
    position: relative;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent, var(--color-primary-dark)) 100%);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4), 0 0 0 0 rgba(139, 43, 43, 0.4);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.taggy-toggle:hover {
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5), 0 0 20px rgba(139, 43, 43, 0.3);
}

.taggy-toggle.is-open .taggy-icon-open { display: none; }
.taggy-toggle.is-open .taggy-icon-close { display: block !important; }

/* Pulse ring animation on toggle */
.taggy-pulse {
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 2px solid var(--color-primary);
    opacity: 0;
    animation: taggyPulse 3s ease-out infinite;
}

.taggy-toggle.is-open .taggy-pulse,
.taggy-toggle:hover .taggy-pulse {
    animation: none;
    opacity: 0;
}

@keyframes taggyPulse {
    0% { transform: scale(1); opacity: 0.6; }
    100% { transform: scale(1.5); opacity: 0; }
}

/* --- Chat Window --- */
.taggy-window {
    position: absolute;
    bottom: 74px;
    right: 0;
    width: 380px;
    max-width: calc(100vw - 32px);
    height: 520px;
    max-height: calc(100vh - 140px);
    background: var(--color-background);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-2xl);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: taggySlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.taggy-window[hidden] {
    display: none;
}

@keyframes taggySlideUp {
    from { opacity: 0; transform: translateY(16px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* --- Header --- */
.taggy-header {
    padding: var(--space-4) var(--space-4);
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}

.taggy-header-info {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.taggy-avatar {
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(8px);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.taggy-name {
    font-size: var(--text-base);
    font-weight: var(--font-bold);
    margin: 0;
    letter-spacing: 0.02em;
}

.taggy-status {
    font-size: 11px;
    opacity: 0.85;
    display: flex;
    align-items: center;
    gap: 5px;
}

.taggy-status-dot {
    width: 7px;
    height: 7px;
    background: #34d399;
    border-radius: 50%;
    display: inline-block;
    animation: taggyStatusPulse 2s ease-in-out infinite;
}

@keyframes taggyStatusPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.taggy-close-btn {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #fff;
    cursor: pointer;
    padding: var(--space-1-5);
    border-radius: var(--radius-md);
    opacity: 0.8;
    transition: opacity 0.2s, background 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.taggy-close-btn:hover {
    opacity: 1;
    background: rgba(255, 255, 255, 0.2);
}

/* --- Messages Area --- */
.taggy-messages {
    flex: 1;
    overflow-y: auto;
    padding: var(--space-4);
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
    scroll-behavior: smooth;
    scrollbar-width: thin;
    scrollbar-color: var(--color-border) transparent;
}

.taggy-messages::-webkit-scrollbar {
    width: 4px;
}

.taggy-messages::-webkit-scrollbar-track {
    background: transparent;
}

.taggy-messages::-webkit-scrollbar-thumb {
    background: var(--color-border);
    border-radius: 4px;
}

/* --- Message Bubbles --- */
.taggy-msg {
    max-width: 88%;
    display: flex;
    gap: var(--space-2);
    animation: taggyFadeIn 0.3s ease-out;
}

@keyframes taggyFadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

.taggy-msg-bot {
    align-self: flex-start;
}

.taggy-msg-user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.taggy-msg-avatar {
    width: 26px;
    height: 26px;
    min-width: 26px;
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    margin-top: 2px;
}

.taggy-msg-user .taggy-msg-avatar {
    display: none;
}

.taggy-msg-bubble {
    padding: var(--space-3) var(--space-4);
    font-size: var(--text-sm);
    line-height: 1.6;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.taggy-msg-bot .taggy-msg-bubble {
    background: var(--color-background-elevated);
    color: var(--color-text);
    border-radius: var(--radius-xl) var(--radius-xl) var(--radius-xl) var(--radius-sm);
    border: 1px solid var(--color-border-light);
}

.taggy-msg-user .taggy-msg-bubble {
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
    color: #fff;
    border-radius: var(--radius-xl) var(--radius-xl) var(--radius-sm) var(--radius-xl);
}

/* --- Quick Suggestion Chips --- */
.taggy-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: var(--space-1) 0 0 34px;
}

.taggy-chip {
    padding: 6px 14px;
    background: transparent;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-full);
    font-size: 12px;
    color: var(--color-text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
    font-family: var(--font-sans);
}

.taggy-chip:hover {
    background: var(--color-primary);
    color: #fff;
    border-color: var(--color-primary);
    transform: translateY(-1px);
}

/* --- Input Area --- */
.taggy-input-area {
    padding: var(--space-3) var(--space-4) var(--space-3);
    border-top: 1px solid var(--color-border-light);
    flex-shrink: 0;
    background: var(--color-background);
}

.taggy-form {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    background: var(--color-background-input, var(--color-background-secondary));
    border: 1px solid var(--color-border);
    border-radius: var(--radius-full);
    padding: 4px 4px 4px var(--space-4);
    transition: border-color 0.2s;
}

.taggy-form:focus-within {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(139, 43, 43, 0.1);
}

.taggy-input {
    flex: 1;
    background: transparent;
    border: none;
    padding: var(--space-2) 0;
    font-size: var(--text-sm);
    color: var(--color-text);
    outline: none;
    font-family: var(--font-sans);
    min-width: 0;
}

.taggy-input::placeholder {
    color: var(--color-text-muted);
}

.taggy-send {
    width: 38px;
    height: 38px;
    min-width: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s, opacity 0.2s;
}

.taggy-send:hover {
    transform: scale(1.05);
}

.taggy-send:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none;
}

.taggy-powered {
    text-align: center;
    font-size: 10px;
    color: var(--color-text-muted);
    margin-top: 6px;
    letter-spacing: 0.03em;
    opacity: 0.6;
}

/* --- Typing Indicator --- */
.taggy-typing {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: var(--space-3) var(--space-4);
}

.taggy-typing span {
    width: 7px;
    height: 7px;
    background: var(--color-text-muted);
    border-radius: 50%;
    animation: taggyBounce 1.4s infinite ease-in-out;
}

.taggy-typing span:nth-child(2) { animation-delay: 0.16s; }
.taggy-typing span:nth-child(3) { animation-delay: 0.32s; }

@keyframes taggyBounce {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30% { transform: translateY(-8px); opacity: 1; }
}

/* --- Product Cards in Chat --- */
.taggy-products {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
    margin-top: var(--space-2);
}

.taggy-product-card {
    display: flex;
    gap: var(--space-3);
    padding: var(--space-2-5) var(--space-3);
    background: var(--color-background);
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border-light);
    text-decoration: none;
    color: inherit;
    transition: border-color 0.2s, transform 0.2s, box-shadow 0.2s;
}

.taggy-product-card:hover {
    border-color: var(--color-primary);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.taggy-product-card img {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    object-fit: cover;
}

.taggy-product-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 2px;
}

.taggy-product-name {
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--color-text);
}

.taggy-product-price {
    font-size: var(--text-sm);
    color: var(--color-primary);
    font-weight: var(--font-bold);
}

/* --- Responsive --- */
@media (max-width: 480px) {
    .taggy {
        bottom: var(--space-3);
        right: var(--space-3);
    }

    .taggy-window {
        width: calc(100vw - 24px);
        height: calc(100vh - 180px);
        bottom: 70px;
        right: -4px;
    }

    .taggy-suggestions {
        padding-left: 0;
    }
}
</style>

<script>
(function() {
    const taggy = {
        window: document.getElementById('ai-window'),
        toggle: document.getElementById('ai-toggle'),
        close: document.getElementById('ai-close'),
        form: document.getElementById('ai-form'),
        input: document.getElementById('ai-input'),
        messages: document.getElementById('ai-messages'),
        suggestions: document.getElementById('ai-suggestions'),
        conversationId: null,

        init() {
            this.toggle.addEventListener('click', () => this.toggleWindow());
            this.close.addEventListener('click', () => this.closeWindow());
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));

            // Quick suggestion chips
            this.suggestions.querySelectorAll('.taggy-chip').forEach(btn => {
                btn.addEventListener('click', () => {
                    this.input.value = btn.dataset.message;
                    this.handleSubmit(new Event('submit'));
                });
            });

            // Close on Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !this.window.hidden) {
                    this.closeWindow();
                }
            });
        },

        toggleWindow() {
            const isHidden = this.window.hidden;
            this.window.hidden = !isHidden;
            this.toggle.classList.toggle('is-open', isHidden);
            if (isHidden) {
                this.input.focus();
            }
        },

        closeWindow() {
            this.window.hidden = true;
            this.toggle.classList.remove('is-open');
        },

        async handleSubmit(e) {
            e.preventDefault();
            const message = this.input.value.trim();
            if (!message) return;

            // Hide suggestion chips after first interaction
            if (this.suggestions) {
                this.suggestions.style.display = 'none';
            }

            this.addMessage(message, 'user');
            this.input.value = '';
            this.input.disabled = true;
            this.showTyping();

            try {
                const response = await fetch('<?= url('/api/assistant') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        message: message,
                        conversation_id: this.conversationId
                    })
                });

                const data = await response.json();
                this.hideTyping();

                if (data.error) {
                    this.addMessage(data.error, 'bot');
                } else {
                    this.conversationId = data.conversation_id;
                    this.addMessage(data.response, 'bot', data.products);
                }
            } catch (error) {
                this.hideTyping();
                this.addMessage("Oops! Something went wrong on my end. Give it another shot, or try rephrasing your question.", 'bot');
            }

            this.input.disabled = false;
            this.input.focus();
        },

        addMessage(content, type, products = []) {
            const div = document.createElement('div');
            div.className = `taggy-msg taggy-msg-${type}`;

            let html = '';

            if (type === 'bot') {
                html += `<div class="taggy-msg-avatar">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                </div>`;
            }

            html += `<div class="taggy-msg-bubble">${this.formatMessage(content)}</div>`;

            // Product recommendation cards
            if (products && products.length > 0) {
                html += '<div class="taggy-products">';
                products.forEach(product => {
                    html += `
                        <a href="<?= url('/products/') ?>${this.escapeHtml(product.slug)}" class="taggy-product-card">
                            <div class="taggy-product-info">
                                <div class="taggy-product-name">${this.escapeHtml(product.name)}</div>
                                <div class="taggy-product-price">R${parseFloat(product.price).toFixed(2)}</div>
                            </div>
                        </a>`;
                });
                html += '</div>';
            }

            div.innerHTML = html;
            this.messages.appendChild(div);
            this.messages.scrollTop = this.messages.scrollHeight;
        },

        formatMessage(text) {
            // Escape HTML first, then apply formatting
            let safe = this.escapeHtml(text);
            // Bold: **text**
            safe = safe.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            // Bullet points
            safe = safe.replace(/^[•\-]\s?/gm, '<span style="color:var(--color-primary);margin-right:4px;">&#8226;</span>');
            // Line breaks
            safe = safe.replace(/\n/g, '<br>');
            return safe;
        },

        showTyping() {
            const div = document.createElement('div');
            div.className = 'taggy-msg taggy-msg-bot';
            div.id = 'taggy-typing';
            div.innerHTML = `
                <div class="taggy-msg-avatar">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                </div>
                <div class="taggy-msg-bubble">
                    <div class="taggy-typing"><span></span><span></span><span></span></div>
                </div>`;
            this.messages.appendChild(div);
            this.messages.scrollTop = this.messages.scrollHeight;
        },

        hideTyping() {
            const el = document.getElementById('taggy-typing');
            if (el) el.remove();
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    document.addEventListener('DOMContentLoaded', () => taggy.init());
})();
</script>
