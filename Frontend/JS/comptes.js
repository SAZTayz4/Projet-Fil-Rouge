document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la navigation
    const navItems = document.querySelectorAll('.settings-nav-item');
    const sections = document.querySelectorAll('.settings-section');

    function switchSection(sectionId) {
        // Animation de transition
        const content = document.querySelector('.settings-content');
        content.style.opacity = '0';
        
        setTimeout(() => {
            // Mise à jour de la navigation
            navItems.forEach(nav => {
                nav.classList.remove('active');
                if (nav.getAttribute('data-section') === sectionId) {
                    nav.classList.add('active');
                }
            });

            // Affichage de la section
            sections.forEach(section => {
                section.classList.remove('active');
                if (section.id === sectionId) {
                    section.classList.add('active');
                    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });

            content.style.opacity = '1';
        }, 300);

        // Mise à jour de l'URL
        history.pushState(null, '', `#${sectionId}`);
    }

    // Navigation par click
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const sectionId = item.getAttribute('data-section');
            switchSection(sectionId);
        });
    });

    // Gestion des formulaires
    const forms = {
        infoForm: {
            url: '../../Backend/api/update_user.php',
            success: 'Informations mises à jour avec succès'
        },
        passwordForm: {
            url: '../../Backend/api/update_password.php',
            success: 'Mot de passe modifié avec succès',
            validate: (formData) => {
                const newPass = formData.get('new_password');
                const confirmPass = formData.get('confirm_password');
                if (newPass !== confirmPass) {
                    throw new Error('Les mots de passe ne correspondent pas');
                }
                if (newPass.length < 8) {
                    throw new Error('Le mot de passe doit contenir au moins 8 caractères');
                }
            }
        }
    };

    // Gestion des boutons d'édition
    const actionButtons = document.querySelectorAll('[data-action]');
    actionButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const action = button.getAttribute('data-action');
            
            if (action === 'delete-account') {
                handleDeleteAccount();
                return;
            }

            const form = button.closest('.info-card, .security-card')?.querySelector('.settings-form');
            if (form) {
                toggleForm(form);
            }
        });
    });

    // Fonction pour gérer la suppression du compte
    function handleDeleteAccount() {
        const modal = document.createElement('div');
        modal.className = 'confirmation-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <h3>Confirmer la suppression</h3>
                <p>Cette action est irréversible. Toutes vos données seront définitivement supprimées.</p>
                <p>Pour confirmer, écrivez "SUPPRIMER" ci-dessous :</p>
                <input type="text" id="confirmDelete" placeholder="SUPPRIMER">
                <div class="modal-actions">
                    <button class="btn-cancel">Annuler</button>
                    <button class="btn-delete" disabled>Supprimer définitivement</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        const input = modal.querySelector('#confirmDelete');
        const deleteBtn = modal.querySelector('.btn-delete');
        const cancelBtn = modal.querySelector('.btn-cancel');

        input.addEventListener('input', () => {
            deleteBtn.disabled = input.value !== 'SUPPRIMER';
        });

        cancelBtn.addEventListener('click', () => {
            modal.remove();
        });

        deleteBtn.addEventListener('click', async () => {
            try {
                const response = await fetch('../../Backend/auth/delete_account.php', {
                    method: 'POST'
                });

                if (response.ok) {
                    showNotification('Compte supprimé avec succès', 'success');
                    setTimeout(() => {
                        window.location.href = '/ProjetFileRouge/Backend/auth/logout.php';
                    }, 2000);
                } else {
                    throw new Error('Erreur lors de la suppression du compte');
                }
            } catch (error) {
                showNotification(error.message, 'error');
            }
            modal.remove();
        });
    }

    // Gestion des formulaires
    Object.entries(forms).forEach(([formId, config]) => {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                // Validation spécifique si nécessaire
                if (config.validate) {
                    config.validate(formData);
                }

                const response = await fetch(config.url, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Erreur lors de la mise à jour');
                }

                showNotification(config.success, 'success');
                form.classList.remove('visible');
                
                // Recharger les informations si nécessaire
                if (formId === 'infoForm') {
                    setTimeout(() => window.location.reload(), 1500);
                }
            } catch (error) {
                showNotification(error.message, 'error');
            }
        });
    });

    // Toggle visibilité mot de passe
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            const input = button.previousElementSibling;
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Gestion des notifications
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Fonction pour basculer l'affichage des formulaires
    function toggleForm(form) {
        const allForms = document.querySelectorAll('.settings-form');
        allForms.forEach(f => {
            if (f !== form) f.classList.remove('visible');
        });
        form.classList.toggle('visible');
    }

    // Boutons d'annulation
    const cancelButtons = document.querySelectorAll('.btn-cancel');
    cancelButtons.forEach(button => {
        button.addEventListener('click', () => {
            const form = button.closest('.settings-form');
            if (form) {
                form.classList.remove('visible');
                form.reset();
            }
        });
    });

    // Gestion de l'historique
    window.addEventListener('popstate', () => {
        const hash = window.location.hash.substring(1) || 'overview';
        switchSection(hash);
    });

    // Section initiale
    const initialSection = window.location.hash.substring(1) || 'overview';
    switchSection(initialSection);
});