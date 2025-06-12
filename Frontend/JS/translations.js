// Classe pour gérer les traductions côté client
class Translator {
    constructor() {
        this.translations = {};
        this.currentLang = null;
        this.config = null;
        this.initialized = false;
    }

    // Initialiser le traducteur
    async init() {
        if (this.initialized) return;

        try {
            // Charger la configuration
            const configResponse = await fetch('/ProjetFileRouge/Frontend/config/languages.php');
            this.config = await configResponse.json();

            // Charger les traductions
            await this.loadTranslations();

            // Initialiser les événements
            this.initEvents();

            this.initialized = true;
        } catch (error) {
            console.error('Erreur lors de l\'initialisation du traducteur:', error);
        }
    }

    // Charger les traductions
    async loadTranslations(lang = null) {
        try {
            const response = await fetch(`/ProjetFileRouge/Frontend/get_translations.php${lang ? '?lang=' + lang : ''}`);
            const data = await response.json();
            
            if (data.success) {
                this.translations = data.translations;
                this.currentLang = data.currentLang;
                this.updatePageTranslations();
            }
        } catch (error) {
            console.error('Erreur lors du chargement des traductions:', error);
        }
    }

    // Initialiser les événements
    initEvents() {
        // Observer les changements de langue
        document.querySelectorAll('[data-translate]').forEach(element => {
            this.translateElement(element);
        });

        // Observer les nouveaux éléments ajoutés dynamiquement
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) { // Element node
                        if (node.hasAttribute('data-translate')) {
                            this.translateElement(node);
                        }
                        node.querySelectorAll('[data-translate]').forEach(element => {
                            this.translateElement(element);
                        });
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Traduire un élément
    translateElement(element) {
        const key = element.getAttribute('data-translate');
        const params = JSON.parse(element.getAttribute('data-translate-params') || '{}');
        
        if (key && this.translations[this.currentLang]?.[key]) {
            let translation = this.translations[this.currentLang][key];
            
            // Remplacer les paramètres
            Object.entries(params).forEach(([param, value]) => {
                translation = translation.replace(`:${param}`, value);
            });
            
            // Mettre à jour le contenu
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                element.placeholder = translation;
            } else {
                element.textContent = translation;
            }
        }
    }

    // Mettre à jour toutes les traductions de la page
    updatePageTranslations() {
        document.querySelectorAll('[data-translate]').forEach(element => {
            this.translateElement(element);
        });
    }

    // Changer la langue
    async changeLanguage(lang) {
        if (!this.config.available_languages[lang]) {
            console.error('Langue non supportée:', lang);
            return false;
        }

        try {
            const response = await fetch('/ProjetFileRouge/Frontend/set_language.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `lang=${lang}`
            });

            const data = await response.json();
            
            if (data.success) {
                await this.loadTranslations(lang);
                return true;
            }
            
            return false;
        } catch (error) {
            console.error('Erreur lors du changement de langue:', error);
            return false;
        }
    }

    // Obtenir une traduction
    translate(key, params = {}) {
        if (!this.translations[this.currentLang]?.[key]) {
            return key;
        }

        let translation = this.translations[this.currentLang][key];
        
        // Remplacer les paramètres
        Object.entries(params).forEach(([param, value]) => {
            translation = translation.replace(`:${param}`, value);
        });
        
        return translation;
    }
}

// Créer une instance globale du traducteur
window.translator = new Translator();

// Initialiser le traducteur au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    window.translator.init();
}); 