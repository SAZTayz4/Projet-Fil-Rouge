class Chatbot {
    constructor() {
        this.container = document.createElement('div');
        this.container.className = 'chatbot-container';
        this.isOpen = false;
        this.hasUnreadMessages = false;
        this.notificationCount = 0;
        this.typingTimeout = null;
        this.chatbotEndpoint = "https://checkmyckicks.ngrok.app//chatbot";
        
        this.init();
    }

    init() {
        // Création du bouton
        this.button = document.createElement('button');
        this.button.className = 'chatbot-button';
        this.button.innerHTML = '<i class="fas fa-comments"></i>';
        this.button.addEventListener('click', () => this.toggleChat());

        // Création de la fenêtre
        this.window = document.createElement('div');
        this.window.className = 'chatbot-window';
        this.window.innerHTML = `
            <div class="chatbot-header">
                <h3>  CheckMyKicks Assistant 🗿</h3>
                <button class="chatbot-close" aria-label="Fermer"><i class="fas fa-times"></i></button>
            </div>
            <div class="chatbot-messages"></div>
            <div class="chatbot-input-container">
                <input type="text" class="chatbot-input" placeholder="Pose une question au bot..." autocomplete="off">
                <button class="chatbot-send" aria-label="Envoyer"><i class="fas fa-paper-plane"></i></button>
            </div>
        `;

        // Ajout des éléments au DOM
        this.container.appendChild(this.button);
        this.container.appendChild(this.window);
        document.body.appendChild(this.container);

        // Gestionnaires d'événements
        this.window.querySelector('.chatbot-close').addEventListener('click', () => this.toggleChat());
        this.input = this.window.querySelector('.chatbot-input');
        this.sendButton = this.window.querySelector('.chatbot-send');
        this.messagesContainer = this.window.querySelector('.chatbot-messages');

        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.sendMessage();
            }
        });

        this.sendButton.addEventListener('click', () => this.sendMessage());

        // Message de bienvenue
        this.addBotMessage("Bonjour ! Je suis l'assistant CheckMyKicks. Comment puis-je vous aider aujourd'hui ?");
    }

    toggleChat() {
        this.isOpen = !this.isOpen;
        this.window.classList.toggle('active', this.isOpen);
        
        if (this.isOpen) {
            this.input.focus();
            this.hasUnreadMessages = false;
            this.updateNotification();
        }
    }

    createMessage(content, sender = 'bot') {
        const div = document.createElement("div");
        div.className = `chatbot-message ${sender}`;
        div.textContent = sender === 'user' ? `👤 ${content}` : `🤖 ${content}`;
        return div;
    }

    createProduct(prod) {
        const div = document.createElement("div");
        div.className = "chatbot-product";
        div.innerHTML = `
            <a href="${prod.link}" target="_blank">
                <img src="${prod.article_image || ''}" alt="Produit">
            </a><br>
            <strong>${prod.name}</strong><br>
            ${prod.size || "Taille inconnue"} – ${prod.price || "Prix ?"}
        `;
        return div;
    }

    async sendMessage() {
        const message = this.input.value.trim();
        if (!message) return;

        // Ajout du message de l'utilisateur
        const userMsg = this.createMessage(message, 'user');
        this.messagesContainer.appendChild(userMsg);
        this.scrollToBottom();

        // Réinitialisation de l'input
        this.input.value = "";

        this.showTypingIndicator();

        try {
            const res = await fetch(this.chatbotEndpoint, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ message })
            });

            const data = await res.json();
            const botMsg = this.createMessage(data.reply || "Réponse vide du bot.");
            this.messagesContainer.appendChild(botMsg);

            if (Array.isArray(data.matches)) {
                data.matches.forEach(prod => {
                    const productDiv = this.createProduct(prod);
                    this.messagesContainer.appendChild(productDiv);
                });
            }

            this.scrollToBottom();
            this.hideTypingIndicator();

        } catch (err) {
            console.error("Erreur lors de l'appel au chatbot :", err);
            const errMsg = this.createMessage("❌ Erreur de connexion avec le serveur chatbot.");
            this.messagesContainer.appendChild(errMsg);
            this.scrollToBottom();
            this.hideTypingIndicator();
        }
    }

    addUserMessage(message) {
        const messageElement = document.createElement('div');
        messageElement.className = 'chatbot-message user';
        messageElement.textContent = message;
        this.messagesContainer.appendChild(messageElement);
        this.scrollToBottom();
    }

    addBotMessage(message) {
        const messageElement = document.createElement('div');
        messageElement.className = 'chatbot-message bot';
        
        // Vérifier si le message contient du HTML
        if (/<[a-z][\s\S]*>/i.test(message)) {
            messageElement.innerHTML = message;
        } else {
            messageElement.textContent = message;
        }
        
        this.messagesContainer.appendChild(messageElement);
        this.scrollToBottom();

        // Si la fenêtre est fermée, ajouter une notification
        if (!this.isOpen) {
            this.hasUnreadMessages = true;
            this.notificationCount++;
            this.updateNotification();
        }
    }

    async displayShoesMatches(matches) {
        const container = document.createElement('div');
        container.className = 'chatbot-shoes-matches';
        
        for (const shoe of matches) {
            if (!shoe || typeof shoe !== 'object') continue;

            // Affichage brut JSON formaté
            const pre = document.createElement('pre');
            pre.style.background = '#222';
            pre.style.color = '#b5f783';
            pre.style.padding = '12px';
            pre.style.borderRadius = '8px';
            pre.style.marginBottom = '12px';
            pre.style.overflowX = 'auto';
            pre.style.fontSize = '1em';
            pre.textContent = JSON.stringify(shoe, null, 2);
            container.appendChild(pre);
        }

        if (container.children.length > 0) {
            this.messagesContainer.appendChild(container);
            this.scrollToBottom();
        }
    }

    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    showTypingIndicator() {
        const typing = document.createElement('div');
        typing.className = 'chatbot-typing';
        typing.setAttribute('aria-label', 'En train d\'écrire...');
        typing.innerHTML = '<span></span><span></span><span></span>';
        this.messagesContainer.appendChild(typing);
        this.scrollToBottom();
    }

    hideTypingIndicator() {
        const typing = this.messagesContainer.querySelector('.chatbot-typing');
        if (typing) {
            typing.remove();
        }
    }

    updateNotification() {
        let notification = this.button.querySelector('.chatbot-notification');
        
        if (this.hasUnreadMessages) {
            if (!notification) {
                notification = document.createElement('div');
                notification.className = 'chatbot-notification';
                notification.setAttribute('aria-label', `${this.notificationCount} messages non lus`);
                this.button.appendChild(notification);
            }
            notification.textContent = this.notificationCount;
        } else if (notification) {
            notification.remove();
            this.notificationCount = 0;
        }
    }

    scrollToBottom() {
        requestAnimationFrame(() => {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        });
    }
}

// Initialisation du chatbot quand le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    window.chatbot = new Chatbot();
});

const style = document.createElement('style');
style.textContent = `
.chatbot-typing {
    display: flex;
    gap: 4px;
    padding: 10px;
    margin: 5px 0;
}
.chatbot-typing span {
    width: 8px;
    height: 8px;
    background: #0059ff;
    border-radius: 50%;
    animation: typing 1s infinite ease-in-out;
}
.chatbot-typing span:nth-child(1) { animation-delay: 0.2s; }
.chatbot-typing span:nth-child(2) { animation-delay: 0.3s; }
.chatbot-typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}
`;
document.head.appendChild(style); 