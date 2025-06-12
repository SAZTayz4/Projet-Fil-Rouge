let allPairs = [];
let filteredPairs = [];
let isChecking = false;
const MAX_PAIRS = 500;

// Fonction pour mettre à jour l'affichage des paires
function updateDisplay(data) {
    const countElement = document.getElementById('spotcheck-count');
    const grid = document.getElementById('spotcheck-grid');
    
    // Limiter aux 500 annonces les plus récentes
    if (data.length > MAX_PAIRS) {
        data = data.slice(-MAX_PAIRS);
    }
    
    if (countElement) {
        countElement.textContent = data.length + ' résultats trouvés (limité aux ' + MAX_PAIRS + ' plus récentes)';
    }
    
    if (!data.length) {
        grid.innerHTML = '<div style="color:#888;text-align:center;width:100%;padding:40px 0;">Aucune paire trouvée</div>';
        return;
    }
    
    const newContent = data.map(pair => `
        <div class="spotcheck-card" style="opacity: 0; transform: translateY(20px); transition: all 0.3s ease-out;">
            <img src="${pair.article_image}" alt="${pair.name}">
            <div class="spotcheck-card-body">
                <div class="spotcheck-card-title">${pair.name || ''}</div>
                <div class="spotcheck-card-info"><b>Prix :</b> ${pair.price || ''}</div>
                <div class="spotcheck-card-info"><b>Taille :</b> ${pair.size || ''}</div>
                <a class="spotcheck-card-link" href="${pair.link || '#'}" target="_blank">Voir l'annonce</a>
            </div>
        </div>
    `).join('');

    grid.innerHTML = newContent;

    // Animation d'apparition des cartes
    setTimeout(() => {
        const cards = grid.querySelectorAll('.spotcheck-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }, 50);
}

// Fonction pour vérifier les nouvelles paires
async function checkForNewPairs() {
    if (isChecking) return;
    isChecking = true;
    
    try {
        const response = await fetch('https://8bfd-2001-861-8ce0-6c20-1b0-6ab8-1dd0-f71e.ngrok-free.app/vinted-feed', {
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        });
        const newData = await response.json();
        
        // Limiter aux 500 annonces les plus récentes
        if (newData.length > MAX_PAIRS) {
            newData = newData.slice(-MAX_PAIRS);
        }
        
        if (JSON.stringify(newData) !== JSON.stringify(allPairs)) {
            console.log('Nouvelles paires détectées ! Mise à jour de l\'affichage...');
            allPairs = newData;
            filteredPairs = newData;
            updateDisplay(newData);
        }
    } catch (error) {
        console.error('Erreur lors de la vérification des nouvelles paires:', error);
    } finally {
        isChecking = false;
    }
}

// Vérifier les nouvelles paires toutes les 5 secondes
setInterval(checkForNewPairs, 5000);

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Premier chargement des données
    checkForNewPairs();
    
    // Ajout d'un indicateur de mise à jour
    const headerTitle = document.querySelector('.spotcheck-header-title');
    if (headerTitle) {
        const updateIndicator = document.createElement('div');
        updateIndicator.style.cssText = 'font-size: 12px; color: #666; margin-top: 5px;';
        updateIndicator.innerHTML = 'Dernière mise à jour : <span id="last-update-time">à l\'instant</span>';
        headerTitle.appendChild(updateIndicator);

        // Mise à jour de l'heure
        setInterval(() => {
            const now = new Date();
            document.getElementById('last-update-time').textContent = 
                `à ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
        }, 1000);
    }
});

function applyFilters() {
    const search = document.getElementById('search-input').value.toLowerCase();
    const price = document.getElementById('filter-price').value;
    const brand = document.getElementById('filter-brand').value.toLowerCase();
    const size = document.getElementById('filter-size').value;
    const state = document.getElementById('filter-state').value.toLowerCase();
    const color = document.getElementById('filter-color').value.toLowerCase();
    filteredPairs = allPairs.filter(pair => {
        let ok = true;
        if (search && !(pair.name && pair.name.toLowerCase().includes(search))) ok = false;
        if (brand && !(pair.name && pair.name.toLowerCase().includes(brand))) ok = false;
        if (size && !(pair.size && pair.size.includes(size))) ok = false;
        if (state && !(pair.size && pair.size.toLowerCase().includes(state))) ok = false;
        if (color && !(pair.name && pair.name.toLowerCase().includes(color))) ok = false;
        if (price) {
            const match = price.match(/(\d+)[^\d]?(\d+)?/);
            if (match) {
                const min = parseInt(match[1]);
                const max = match[2] ? parseInt(match[2]) : null;
                const pairPrice = parseInt((pair.price||'').replace(/[^\d]/g, ''));
                if (isNaN(pairPrice) || pairPrice < min || (max && pairPrice > max)) ok = false;
            }
        }
        return ok;
    });
    updateDisplay(filteredPairs);
}
function resetFilters() {
    document.getElementById('filter-price').value = '';
    document.getElementById('filter-brand').value = '';
    document.getElementById('filter-size').value = '';
    document.getElementById('filter-state').value = '';
    document.getElementById('filter-color').value = '';
    document.getElementById('search-input').value = '';
    filteredPairs = allPairs;
    updateDisplay(allPairs);
}
// Filtres dynamiques sur changement
document.querySelectorAll('.spotcheck-filters input').forEach(input => {
    input.addEventListener('input', applyFilters);
});