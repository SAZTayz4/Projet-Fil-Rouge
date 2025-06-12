document.addEventListener('DOMContentLoaded', function() {
    const fab = document.getElementById('reminderFab');
    const popup = document.getElementById('reminderFabPopup');
    const closeBtn = document.querySelector('.reminder-fab-close');
    const listDiv = document.getElementById('reminderFabList');
    const countSpan = document.getElementById('reminderFabCount');

    // Si les éléments n'existent pas, on ne fait rien
    if (!fab || !popup || !closeBtn || !listDiv || !countSpan) {
        return;
    }

    // Utilisateur connecté ?
    const isLoggedIn = window.isUserLoggedIn !== undefined ? window.isUserLoggedIn : false;

    // Récupérer tous les drops (pour afficher infos dans la popup)
    let allDrops = [];
    if (window.allDropsData) {
        allDrops = window.allDropsData;
    } else if (window.dropsData) {
        allDrops = window.dropsData;
    }

    // Ouvre la popup
    fab.addEventListener('click', function() {
        popup.style.display = 'flex';
        renderReminders();
    });
    // Ferme la popup
    closeBtn.addEventListener('click', function() {
        popup.style.display = 'none';
    });

    // Récupérer les rappels (API ou localStorage)
    function getReminders(callback) {
        if (isLoggedIn) {
            fetch('/ProjetFileRouge/Backend/reminder_api.php?action=list')
                .then(r => r.json())
                .then(data => callback(data.reminders || []));
        } else {
            const local = localStorage.getItem('reminders');
            callback(local ? JSON.parse(local) : []);
        }
    }

    // Supprimer un rappel
    function removeReminder(dropId, cb) {
        if (isLoggedIn) {
            fetch('/ProjetFileRouge/Backend/reminder_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=remove&drop_id=' + encodeURIComponent(dropId)
            }).then(() => {
                if(cb) cb();
            });
        } else {
            let local = localStorage.getItem('reminders');
            let arr = local ? JSON.parse(local) : [];
            arr = arr.filter(id => id !== dropId);
            localStorage.setItem('reminders', JSON.stringify(arr));
            if(cb) cb();
        }
    }

    // Afficher la liste des rappels
    function renderReminders() {
        getReminders(function(reminderIds) {
            // Met à jour le compteur
            if(reminderIds.length > 0) {
                countSpan.style.display = '';
                countSpan.textContent = reminderIds.length;
            } else {
                countSpan.style.display = 'none';
            }
            // Affichage
            listDiv.innerHTML = '';
            if(reminderIds.length === 0) {
                listDiv.innerHTML = '<div style="text-align:center;color:#888;">Aucun rappel</div>';
                return;
            }
            reminderIds.forEach(dropId => {
                // Cherche le drop dans allDrops
                let drop = null;
                if(allDrops && allDrops.length) {
                    drop = allDrops.find(d => d.sku === dropId);
                }
                if(!drop) {
                    // fallback : juste l'id
                    drop = { sku: dropId, nom: dropId, image: '', date_sortie: '' };
                }
                const item = document.createElement('div');
                item.className = 'reminder-item';
                item.innerHTML = `
                    <img src="${drop.image || ''}" class="reminder-item-img" alt="${drop.nom}">
                    <div class="reminder-item-info">
                        <div class="reminder-item-title">${drop.nom}</div>
                        <div class="reminder-item-date">${drop.date_sortie ? 'Sortie le ' + drop.date_sortie : ''}</div>
                    </div>
                    <button class="reminder-item-remove" title="Retirer le rappel">&times;</button>
                `;
                item.querySelector('.reminder-item-remove').addEventListener('click', function(e) {
                    e.stopPropagation();
                    removeReminder(drop.sku, renderReminders);
                });
                listDiv.appendChild(item);
            });
        });
    }

    // Met à jour le compteur au chargement
    renderReminders();

    // Ferme la popup si clic dehors
    window.addEventListener('click', function(e) {
        if (popup.style.display === 'flex' && !popup.contains(e.target) && e.target !== fab) {
            popup.style.display = 'none';
        }
    });
}); 