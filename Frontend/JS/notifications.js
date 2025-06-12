document.addEventListener('DOMContentLoaded', function() {
    const notificationBell = document.getElementById('notificationBell');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationList = document.getElementById('notificationList');
    const notificationCount = document.getElementById('notificationCount');
    const markAllRead = document.getElementById('markAllRead');

    if (!notificationBell || !notificationDropdown || !notificationList || !notificationCount || !markAllRead) {
        return;
    }

    // Fonction pour charger les notifications
    function loadNotifications() {
        fetch('/ProjetFileRouge/Backend/notification_api.php?action=list')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notifications = data.notifications || [];
                    updateNotificationCount(notifications.filter(n => !n.lu).length);
                    renderNotifications(notifications);
                }
            })
            .catch(error => console.error('Erreur lors du chargement des notifications:', error));
    }

    // Fonction pour mettre à jour le compteur de notifications
    function updateNotificationCount(count) {
        if (count > 0) {
            notificationCount.textContent = count;
            notificationCount.style.display = 'block';
        } else {
            notificationCount.style.display = 'none';
        }
    }

    // Fonction pour afficher les notifications
    function renderNotifications(notifications) {
        notificationList.innerHTML = '';
        
        if (notifications.length === 0) {
            notificationList.innerHTML = '<div class="notification-empty">Aucune notification</div>';
            return;
        }

        notifications.forEach(notification => {
            const notificationElement = document.createElement('div');
            notificationElement.className = `notification-item ${notification.lu ? 'read' : 'unread'}`;
            notificationElement.innerHTML = `
                <div class="notification-content">
                    <p>${notification.message}</p>
                    <small>${notification.date_creation}</small>
                </div>
                ${!notification.lu ? '<div class="notification-dot"></div>' : ''}
            `;

            if (notification.link) {
                notificationElement.addEventListener('click', () => {
                    markAsRead(notification.id);
                    window.location.href = notification.link;
                });
            }

            notificationList.appendChild(notificationElement);
        });
    }

    // Fonction pour marquer une notification comme lue
    function markAsRead(notificationId) {
        fetch('/ProjetFileRouge/Backend/notification_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=mark_read&id=${notificationId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        })
        .catch(error => console.error('Erreur lors du marquage de la notification:', error));
    }

    // Fonction pour marquer toutes les notifications comme lues
    function markAllAsRead() {
        fetch('/ProjetFileRouge/Backend/notification_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=mark_all_read'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        })
        .catch(error => console.error('Erreur lors du marquage des notifications:', error));
    }

    // Événements
    notificationBell.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        notificationDropdown.style.display = notificationDropdown.style.display === 'none' ? 'block' : 'none';
        if (notificationDropdown.style.display === 'block') {
            loadNotifications();
        }
    });

    markAllRead.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        markAllAsRead();
    });

    // Fermer le dropdown si on clique en dehors
    document.addEventListener('click', (e) => {
        if (!notificationDropdown.contains(e.target) && e.target !== notificationBell) {
            notificationDropdown.style.display = 'none';
        }
    });

    // Charger les notifications au démarrage
    loadNotifications();

    // Vérifier les nouvelles notifications toutes les minutes
    setInterval(loadNotifications, 60000);
}); 