document.addEventListener('DOMContentLoaded', function() {
    // Gestion des filtres
    const filterHeaders = document.querySelectorAll('.filter-header');
    filterHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            const icon = header.querySelector('i');
            
            // Fermer tous les autres
            document.querySelectorAll('.filter-header').forEach(h => {
                if (h !== header) {
                    h.classList.remove('active');
                    if (h.nextElementSibling) h.nextElementSibling.style.display = 'none';
                }
            });
            
            // Ouvrir/fermer celui cliqué
            header.classList.toggle('active');
            content.classList.toggle('active');
            
            if (header.classList.contains('active')) {
                icon.style.transform = 'rotate(45deg)';
                content.style.display = 'block';
                content.style.animation = 'fadeInFilter 0.3s';
            } else {
                icon.style.transform = 'rotate(0deg)';
                content.style.display = 'none';
            }
        });
    });

    // Animation CSS pour les filtres
    const style = document.createElement('style');
    style.innerHTML = `@keyframes fadeInFilter { from { opacity: 0; transform: translateY(-10px);} to { opacity: 1; transform: none; } }`;
    document.head.appendChild(style);

    // Gestion du bouton "Effacer les filtres"
    const clearFiltersBtn = document.querySelector('.btn-clear-filters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.filter-options input[type="radio"]').forEach(input => {
                if(input.value === '') input.checked = true;
                else input.checked = false;
            });
            e.target.form.submit();
        });
    }

    // Ouvre le premier filtre par défaut
    const firstHeader = document.querySelector('.filter-header');
    if(firstHeader) {
        firstHeader.click();
    }

    // Gestion de la modal
    const modal = document.getElementById('dropModal');
    const closeModal = document.querySelector('.close-modal');

    // Vérifier si la modal existe avant d'ajouter les écouteurs
    if (modal && closeModal) {
        closeModal.addEventListener('click', closeModalHandler);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModalHandler();
            }
        });
    }

    function showDropDetails(drop) {
        // Mise à jour du contenu de la modal
        document.getElementById('modalImage').src = drop.image;
        document.getElementById('modalTitle').textContent = drop.nom;
        document.getElementById('modalDate').textContent = `Date de sortie : ${drop.date_sortie}`;
        document.getElementById('modalBrand').textContent = `Marque : ${drop.marque}`;
        document.getElementById('modalPrice').textContent = `Prix : ${drop.prix_retail}`;
        document.getElementById('modalModel').textContent = `Modèle : ${drop.modele}`;
        document.getElementById('modalColors').textContent = `Couleurs : ${drop.couleurs}`;
        document.getElementById('modalSKU').textContent = drop.sku || 'N/A';
        
        // Mise à jour des marketplaces
        const marketplacesDiv = document.getElementById('modalMarketplaces');
        marketplacesDiv.innerHTML = '';
        if (drop.marketplaces && drop.marketplaces.length > 0) {
            drop.marketplaces.forEach(marketplace => {
                const tag = document.createElement('span');
                tag.className = 'marketplace-tag';
                tag.textContent = marketplace;
                marketplacesDiv.appendChild(tag);
            });
        }

        // Afficher la modal avec animation
        modal.style.display = 'block';
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
    }

    // Fermer la modal
    function closeModalHandler() {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    // Gestion des rappels
    function setReminder(drop) {
        if (!window.isUserLoggedIn) {
            window.location.href = '/ProjetFileRouge/Backend/auth/login.php';
            return;
        }
        
        // Animation du bouton
        const button = event.currentTarget;
        button.innerHTML = '<i class="fas fa-check"></i> Rappel configuré';
        button.style.backgroundColor = '#4CAF50';
        
        // Créer la notification
        fetch('/ProjetFileRouge/Backend/notification_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=add&type=drop&message=Rappel configuré pour ${drop.nom} le ${drop.date_sortie}&link=/ProjetFileRouge/Frontend/HTML/drops.php`
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                // Stocker le rappel dans la base de données ou le localStorage
                if (window.isUserLoggedIn) {
                    fetch('/ProjetFileRouge/Backend/reminder_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=add&drop_id=${drop.sku}`
                    });
                } else {
                    let reminders = JSON.parse(localStorage.getItem('reminders') || '[]');
                    reminders.push(drop.sku);
                    localStorage.setItem('reminders', JSON.stringify(reminders));
                }
            }
        });
        
        setTimeout(() => {
            button.innerHTML = '<i class="far fa-bell"></i> Rappel';
            button.style.backgroundColor = '';
        }, 2000);
    }

    // Exposer les fonctions globalement
    window.showDropDetails = showDropDetails;
    window.setReminder = setReminder;
}); 