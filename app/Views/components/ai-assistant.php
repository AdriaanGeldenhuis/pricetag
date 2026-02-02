<!-- AI Shopping Assistant Widget -->
<div id="ai-assistant" class="ai-assistant">
    <!-- Toggle Button -->
    <button type="button" class="ai-assistant-toggle" id="ai-toggle" aria-label="Open AI Assistant">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ai-icon-chat">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
        </svg>
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ai-icon-close" style="display: none;">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
    </button>

    <!-- Chat Window -->
    <div class="ai-assistant-window" id="ai-window" hidden>
        <div class="ai-assistant-header">
            <div class="flex items-center gap-3">
                <div class="ai-avatar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                        <line x1="9" y1="9" x2="9.01" y2="9"/>
                        <line x1="15" y1="9" x2="15.01" y2="9"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-sm">Shopping Assistant</h3>
                    <p class="text-xs text-muted">Ask me anything</p>
                </div>
            </div>
            <button type="button" class="ai-close-btn" id="ai-close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="ai-assistant-messages" id="ai-messages">
            <!-- Welcome message -->
            <div class="ai-message ai-message-assistant">
                <div class="ai-message-content">
                    Hi there! I'm your shopping assistant. I can help you find products, answer questions about orders, or provide information about our store. How can I help you today?
                </div>
            </div>

            <!-- Quick suggestions -->
            <div class="ai-suggestions" id="ai-suggestions">
                <button type="button" class="ai-suggestion-btn" data-message="Show me your best sellers">Best sellers</button>
                <button type="button" class="ai-suggestion-btn" data-message="What's on sale?">On sale</button>
                <button type="button" class="ai-suggestion-btn" data-message="How do I track my order?">Track order</button>
                <button type="button" class="ai-suggestion-btn" data-message="What are your shipping options?">Shipping info</button>
            </div>
        </div>

        <div class="ai-assistant-input">
            <form id="ai-form">
                <div class="ai-input-wrapper">
                    <input type="text" id="ai-input" placeholder="Type your message..." autocomplete="off">
                    <button type="submit" class="ai-send-btn" id="ai-send">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"/>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.ai-assistant {
    position: fixed;
    bottom: var(--space-6);
    right: var(--space-6);
    z-index: var(--z-modal);
}

.ai-assistant-toggle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-lg);
    transition: transform var(--duration-200), box-shadow var(--duration-200);
}

.ai-assistant-toggle:hover {
    transform: scale(1.05);
    box-shadow: var(--shadow-xl);
}

.ai-assistant-toggle.is-open .ai-icon-chat {
    display: none;
}

.ai-assistant-toggle.is-open .ai-icon-close {
    display: block;
}

.ai-assistant-window {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 380px;
    max-width: calc(100vw - var(--space-8));
    height: 500px;
    max-height: calc(100vh - 150px);
    background: var(--color-background);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-2xl);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp var(--duration-300) ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.ai-assistant-window[hidden] {
    display: none;
}

.ai-assistant-header {
    padding: var(--space-4);
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.ai-avatar {
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-close-btn {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: var(--space-1);
    opacity: 0.8;
    transition: opacity var(--duration-200);
}

.ai-close-btn:hover {
    opacity: 1;
}

.ai-assistant-messages {
    flex: 1;
    overflow-y: auto;
    padding: var(--space-4);
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.ai-message {
    max-width: 85%;
    animation: fadeIn var(--duration-200) ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.ai-message-user {
    align-self: flex-end;
}

.ai-message-assistant {
    align-self: flex-start;
}

.ai-message-content {
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius-xl);
    font-size: var(--text-sm);
    line-height: 1.5;
}

.ai-message-user .ai-message-content {
    background: var(--color-primary);
    color: white;
    border-bottom-right-radius: var(--radius-sm);
}

.ai-message-assistant .ai-message-content {
    background: var(--color-neutral-100);
    color: var(--color-text);
    border-bottom-left-radius: var(--radius-sm);
}

.ai-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
    margin-top: var(--space-2);
}

.ai-suggestion-btn {
    padding: var(--space-2) var(--space-3);
    background: var(--color-background);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    cursor: pointer;
    transition: all var(--duration-200);
}

.ai-suggestion-btn:hover {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
}

.ai-assistant-input {
    padding: var(--space-4);
    border-top: 1px solid var(--color-border-light);
}

.ai-input-wrapper {
    display: flex;
    gap: var(--space-2);
}

.ai-input-wrapper input {
    flex: 1;
    padding: var(--space-3) var(--space-4);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-full);
    font-size: var(--text-sm);
    outline: none;
    transition: border-color var(--duration-200);
}

.ai-input-wrapper input:focus {
    border-color: var(--color-primary);
}

.ai-send-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--color-primary);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background var(--duration-200);
}

.ai-send-btn:hover {
    background: var(--color-primary-dark);
}

.ai-send-btn:disabled {
    background: var(--color-neutral-300);
    cursor: not-allowed;
}

.ai-typing {
    display: flex;
    gap: 4px;
    padding: var(--space-3) var(--space-4);
}

.ai-typing span {
    width: 8px;
    height: 8px;
    background: var(--color-neutral-400);
    border-radius: 50%;
    animation: typing 1.4s infinite ease-in-out;
}

.ai-typing span:nth-child(2) { animation-delay: 0.2s; }
.ai-typing span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

/* Product cards in chat */
.ai-products {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
    margin-top: var(--space-2);
}

.ai-product-card {
    display: flex;
    gap: var(--space-3);
    padding: var(--space-2);
    background: white;
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border-light);
    text-decoration: none;
    color: inherit;
    transition: border-color var(--duration-200);
}

.ai-product-card:hover {
    border-color: var(--color-primary);
}

.ai-product-card img {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    object-fit: cover;
}

.ai-product-info {
    flex: 1;
    min-width: 0;
}

.ai-product-name {
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ai-product-price {
    font-size: var(--text-sm);
    color: var(--color-primary);
    font-weight: var(--font-semibold);
}

@media (max-width: 480px) {
    .ai-assistant {
        bottom: var(--space-4);
        right: var(--space-4);
    }

    .ai-assistant-window {
        width: calc(100vw - var(--space-8));
        height: calc(100vh - 200px);
        bottom: 65px;
    }
}
</style>

<script>
(function() {
    const assistant = {
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

            // Suggestion buttons
            document.querySelectorAll('.ai-suggestion-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    this.input.value = btn.dataset.message;
                    this.handleSubmit(new Event('submit'));
                });
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

            // Hide suggestions after first message
            if (this.suggestions) {
                this.suggestions.style.display = 'none';
            }

            // Add user message
            this.addMessage(message, 'user');
            this.input.value = '';
            this.input.disabled = true;

            // Show typing indicator
            this.showTyping();

            try {
                const response = await fetch('<?= url('/api/assistant') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message: message,
                        conversation_id: this.conversationId
                    })
                });

                const data = await response.json();
                this.hideTyping();

                if (data.error) {
                    this.addMessage(data.error, 'assistant');
                } else {
                    this.conversationId = data.conversation_id;
                    this.addMessage(data.response, 'assistant', data.products);
                }
            } catch (error) {
                this.hideTyping();
                this.addMessage('Sorry, I encountered an error. Please try again.', 'assistant');
            }

            this.input.disabled = false;
            this.input.focus();
        },

        addMessage(content, type, products = []) {
            const div = document.createElement('div');
            div.className = `ai-message ai-message-${type}`;

            let html = `<div class="ai-message-content">${this.escapeHtml(content)}</div>`;

            // Add product cards if available
            if (products && products.length > 0) {
                html += '<div class="ai-products">';
                products.forEach(product => {
                    html += `
                        <a href="<?= url('/products/') ?>${product.slug}" class="ai-product-card">
                            <div class="ai-product-info">
                                <div class="ai-product-name">${this.escapeHtml(product.name)}</div>
                                <div class="ai-product-price">R${parseFloat(product.price).toFixed(2)}</div>
                            </div>
                        </a>
                    `;
                });
                html += '</div>';
            }

            div.innerHTML = html;
            this.messages.appendChild(div);
            this.messages.scrollTop = this.messages.scrollHeight;
        },

        showTyping() {
            const div = document.createElement('div');
            div.className = 'ai-message ai-message-assistant';
            div.id = 'ai-typing';
            div.innerHTML = '<div class="ai-typing"><span></span><span></span><span></span></div>';
            this.messages.appendChild(div);
            this.messages.scrollTop = this.messages.scrollHeight;
        },

        hideTyping() {
            const typing = document.getElementById('ai-typing');
            if (typing) typing.remove();
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML.replace(/\n/g, '<br>');
        }
    };

    document.addEventListener('DOMContentLoaded', () => assistant.init());
})();
</script>
