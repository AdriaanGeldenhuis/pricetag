/**
 * AI Shopping Assistant Chat Widget
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

class ChatWidget {
    constructor(options = {}) {
        this.options = {
            position: 'right',
            primaryColor: '#4f46e5',
            title: 'Shopping Assistant',
            placeholder: 'Ask me anything...',
            welcomeMessage: "Hi there! I'm your shopping assistant. How can I help you today?",
            apiEndpoint: '/api/assistant',
            ...options
        };

        this.isOpen = false;
        this.conversationId = null;
        this.messages = [];

        this.init();
    }

    init() {
        this.createWidget();
        this.attachEventListeners();

        // Load conversation from session
        const savedConversationId = sessionStorage.getItem('chat_conversation_id');
        if (savedConversationId) {
            this.conversationId = savedConversationId;
        }
    }

    createWidget() {
        // Create container
        this.container = document.createElement('div');
        this.container.className = 'chat-widget';
        this.container.innerHTML = `
            <button class="chat-widget-trigger" aria-label="Open chat">
                <svg class="chat-icon-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <svg class="chat-icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <div class="chat-widget-window">
                <div class="chat-widget-header">
                    <div class="chat-widget-header-info">
                        <div class="chat-widget-avatar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2a10 10 0 0 1 10 10 10 10 0 0 1-10 10A10 10 0 0 1 2 12 10 10 0 0 1 12 2z"></path>
                                <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                <line x1="15" y1="9" x2="15.01" y2="9"></line>
                            </svg>
                        </div>
                        <div>
                            <div class="chat-widget-title">${this.options.title}</div>
                            <div class="chat-widget-status">Online</div>
                        </div>
                    </div>
                    <button class="chat-widget-close" aria-label="Close chat">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="chat-widget-messages" role="log" aria-live="polite"></div>
                <div class="chat-widget-suggestions"></div>
                <form class="chat-widget-input">
                    <input type="text" placeholder="${this.options.placeholder}" autocomplete="off">
                    <button type="submit" aria-label="Send message">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </form>
            </div>
        `;

        document.body.appendChild(this.container);

        // Cache elements
        this.trigger = this.container.querySelector('.chat-widget-trigger');
        this.window = this.container.querySelector('.chat-widget-window');
        this.messagesContainer = this.container.querySelector('.chat-widget-messages');
        this.suggestionsContainer = this.container.querySelector('.chat-widget-suggestions');
        this.form = this.container.querySelector('.chat-widget-input');
        this.input = this.form.querySelector('input');
        this.closeBtn = this.container.querySelector('.chat-widget-close');

        // Add styles
        this.addStyles();

        // Show welcome message
        this.addWelcomeMessage();
    }

    addStyles() {
        if (document.getElementById('chat-widget-styles')) return;

        const style = document.createElement('style');
        style.id = 'chat-widget-styles';
        style.textContent = `
            .chat-widget {
                position: fixed;
                bottom: 20px;
                ${this.options.position}: 20px;
                z-index: 9999;
                font-family: system-ui, -apple-system, sans-serif;
            }

            .chat-widget-trigger {
                width: 56px;
                height: 56px;
                border-radius: 50%;
                background: ${this.options.primaryColor};
                border: none;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .chat-widget-trigger:hover {
                transform: scale(1.05);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            }

            .chat-widget-trigger svg {
                width: 24px;
                height: 24px;
                color: white;
                transition: transform 0.2s, opacity 0.2s;
            }

            .chat-widget-trigger .chat-icon-close {
                position: absolute;
                opacity: 0;
                transform: rotate(-90deg);
            }

            .chat-widget.open .chat-widget-trigger .chat-icon-open {
                opacity: 0;
                transform: rotate(90deg);
            }

            .chat-widget.open .chat-widget-trigger .chat-icon-close {
                opacity: 1;
                transform: rotate(0);
            }

            .chat-widget-window {
                position: absolute;
                bottom: 70px;
                ${this.options.position}: 0;
                width: 360px;
                height: 500px;
                background: white;
                border-radius: 16px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                opacity: 0;
                visibility: hidden;
                transform: translateY(10px) scale(0.95);
                transition: all 0.3s ease;
            }

            .chat-widget.open .chat-widget-window {
                opacity: 1;
                visibility: visible;
                transform: translateY(0) scale(1);
            }

            .chat-widget-header {
                padding: 16px;
                background: ${this.options.primaryColor};
                color: white;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .chat-widget-header-info {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .chat-widget-avatar {
                width: 40px;
                height: 40px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .chat-widget-avatar svg {
                width: 24px;
                height: 24px;
            }

            .chat-widget-title {
                font-weight: 600;
                font-size: 15px;
            }

            .chat-widget-status {
                font-size: 12px;
                opacity: 0.8;
            }

            .chat-widget-close {
                width: 32px;
                height: 32px;
                border: none;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
            }

            .chat-widget-close:hover {
                background: rgba(255, 255, 255, 0.3);
            }

            .chat-widget-close svg {
                width: 18px;
                height: 18px;
            }

            .chat-widget-messages {
                flex: 1;
                overflow-y: auto;
                padding: 16px;
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .chat-message {
                max-width: 85%;
                padding: 10px 14px;
                border-radius: 16px;
                font-size: 14px;
                line-height: 1.4;
                animation: messageIn 0.3s ease;
            }

            @keyframes messageIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .chat-message.user {
                align-self: flex-end;
                background: ${this.options.primaryColor};
                color: white;
                border-bottom-right-radius: 4px;
            }

            .chat-message.assistant {
                align-self: flex-start;
                background: #f1f5f9;
                color: #1e293b;
                border-bottom-left-radius: 4px;
            }

            .chat-message.typing {
                display: flex;
                gap: 4px;
                padding: 14px 18px;
            }

            .chat-message.typing span {
                width: 8px;
                height: 8px;
                background: #94a3b8;
                border-radius: 50%;
                animation: typing 1.4s infinite ease-in-out;
            }

            .chat-message.typing span:nth-child(2) {
                animation-delay: 0.2s;
            }

            .chat-message.typing span:nth-child(3) {
                animation-delay: 0.4s;
            }

            @keyframes typing {
                0%, 60%, 100% { transform: translateY(0); }
                30% { transform: translateY(-6px); }
            }

            .chat-product-card {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px;
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                margin-top: 8px;
                text-decoration: none;
                color: inherit;
                transition: border-color 0.2s;
            }

            .chat-product-card:hover {
                border-color: ${this.options.primaryColor};
            }

            .chat-product-image {
                width: 50px;
                height: 50px;
                border-radius: 6px;
                object-fit: cover;
                background: #f1f5f9;
            }

            .chat-product-info {
                flex: 1;
                min-width: 0;
            }

            .chat-product-name {
                font-weight: 500;
                font-size: 13px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .chat-product-price {
                font-size: 12px;
                color: ${this.options.primaryColor};
                font-weight: 600;
            }

            .chat-widget-suggestions {
                padding: 0 16px 8px;
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
            }

            .chat-suggestion {
                padding: 6px 12px;
                background: #f1f5f9;
                border: none;
                border-radius: 16px;
                font-size: 12px;
                color: #64748b;
                cursor: pointer;
                transition: all 0.2s;
            }

            .chat-suggestion:hover {
                background: ${this.options.primaryColor};
                color: white;
            }

            .chat-widget-input {
                display: flex;
                align-items: center;
                padding: 12px 16px;
                border-top: 1px solid #e2e8f0;
                gap: 8px;
            }

            .chat-widget-input input {
                flex: 1;
                padding: 10px 14px;
                border: 1px solid #e2e8f0;
                border-radius: 24px;
                font-size: 14px;
                outline: none;
                transition: border-color 0.2s;
            }

            .chat-widget-input input:focus {
                border-color: ${this.options.primaryColor};
            }

            .chat-widget-input button {
                width: 40px;
                height: 40px;
                border: none;
                background: ${this.options.primaryColor};
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: transform 0.2s;
            }

            .chat-widget-input button:hover {
                transform: scale(1.05);
            }

            .chat-widget-input button svg {
                width: 18px;
                height: 18px;
                color: white;
            }

            @media (max-width: 480px) {
                .chat-widget-window {
                    width: calc(100vw - 32px);
                    height: calc(100vh - 100px);
                    bottom: 70px;
                    ${this.options.position}: 0;
                    left: ${this.options.position === 'right' ? 'auto' : '16px'};
                    right: ${this.options.position === 'left' ? 'auto' : '16px'};
                    border-radius: 16px;
                }
            }
        `;
        document.head.appendChild(style);
    }

    attachEventListeners() {
        // Toggle chat
        this.trigger.addEventListener('click', () => this.toggle());
        this.closeBtn.addEventListener('click', () => this.close());

        // Send message
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.sendMessage();
        });

        // Close on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.isOpen = true;
        this.container.classList.add('open');
        this.input.focus();
    }

    close() {
        this.isOpen = false;
        this.container.classList.remove('open');
    }

    addWelcomeMessage() {
        this.addMessage(this.options.welcomeMessage, 'assistant');
        this.showSuggestions([
            'What products do you have?',
            'Track my order',
            'Shipping info',
            'Return policy'
        ]);
    }

    showSuggestions(suggestions) {
        this.suggestionsContainer.innerHTML = suggestions.map(s =>
            `<button class="chat-suggestion">${s}</button>`
        ).join('');

        this.suggestionsContainer.querySelectorAll('.chat-suggestion').forEach(btn => {
            btn.addEventListener('click', () => {
                this.input.value = btn.textContent;
                this.sendMessage();
            });
        });
    }

    addMessage(text, type, products = []) {
        const message = document.createElement('div');
        message.className = `chat-message ${type}`;
        message.textContent = text;

        // Add product cards if any
        if (products.length > 0 && type === 'assistant') {
            products.slice(0, 3).forEach(product => {
                const card = document.createElement('a');
                card.className = 'chat-product-card';
                card.href = `/products/${product.slug}`;
                card.innerHTML = `
                    <img src="${product.image || '/assets/images/placeholder.jpg'}" alt="" class="chat-product-image">
                    <div class="chat-product-info">
                        <div class="chat-product-name">${product.name}</div>
                        <div class="chat-product-price">R ${parseFloat(product.price).toLocaleString('en-ZA', { minimumFractionDigits: 2 })}</div>
                    </div>
                `;
                message.appendChild(card);
            });
        }

        this.messagesContainer.appendChild(message);
        this.scrollToBottom();

        return message;
    }

    showTyping() {
        const typing = document.createElement('div');
        typing.className = 'chat-message assistant typing';
        typing.innerHTML = '<span></span><span></span><span></span>';
        typing.id = 'typing-indicator';
        this.messagesContainer.appendChild(typing);
        this.scrollToBottom();
    }

    hideTyping() {
        const typing = document.getElementById('typing-indicator');
        if (typing) typing.remove();
    }

    scrollToBottom() {
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }

    async sendMessage() {
        const text = this.input.value.trim();
        if (!text) return;

        // Add user message
        this.addMessage(text, 'user');
        this.input.value = '';

        // Hide suggestions
        this.suggestionsContainer.innerHTML = '';

        // Show typing
        this.showTyping();

        try {
            const response = await fetch(this.options.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: text,
                    conversation_id: this.conversationId,
                }),
            });

            const data = await response.json();

            this.hideTyping();

            if (data.error) {
                this.addMessage(data.error, 'assistant');
            } else {
                this.conversationId = data.conversation_id;
                sessionStorage.setItem('chat_conversation_id', this.conversationId);

                this.addMessage(data.response, 'assistant', data.products || []);
            }

        } catch (error) {
            this.hideTyping();
            this.addMessage('Sorry, something went wrong. Please try again.', 'assistant');
            console.error('Chat error:', error);
        }
    }
}

// Auto-initialize if data attribute present
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('[data-chat-widget]');
    if (container) {
        const options = {
            primaryColor: container.dataset.color || '#4f46e5',
            title: container.dataset.title || 'Shopping Assistant',
            position: container.dataset.position || 'right',
        };
        window.chatWidget = new ChatWidget(options);
    }
});

// Export for manual initialization
window.ChatWidget = ChatWidget;
