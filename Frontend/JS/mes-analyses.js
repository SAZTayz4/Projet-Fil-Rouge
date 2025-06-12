class AnalysesManager {
    constructor() {
        this.currentPage = 1;
        this.itemsPerPage = 6;
        this.totalPages = 1;
        this.analysesContent = document.getElementById('analyses-content');
        this.pagination = document.getElementById('pagination');
        this.loadAnalyses();
    }

    async loadAnalyses(page = 1) {
        try {
            this.currentPage = page;
            this.analysesContent.innerHTML = '<div class="loading">Chargement de vos analyses...</div>';

            const response = await fetch(`/ProjetFileRouge/Backend/IA-Check/get_analyses.php?page=${page}&limit=${this.itemsPerPage}`);
            const data = await response.json();

            if (data.error) {
                throw new Error(data.message);
            }

            this.totalPages = data.data.pagination.pages;
            this.displayAnalyses(data.data.analyses);
            this.updatePagination();
        } catch (error) {
            console.error('Erreur:', error);
            this.analysesContent.innerHTML = `
                <div class="no-analyses">
                    <p>Une erreur est survenue lors du chargement de vos analyses.</p>
                    <p>${error.message}</p>
                </div>
            `;
        }
    }

    displayAnalyses(analyses) {
        if (!analyses || analyses.length === 0) {
            this.analysesContent.innerHTML = `
                <div class="no-analyses">
                    <p>Vous n'avez pas encore effectué d'analyses.</p>
                    <p>Commencez par vérifier vos sneakers !</p>
                </div>
            `;
            return;
        }

        const grid = document.createElement('div');
        grid.className = 'analyses-grid';

        analyses.forEach(analyse => {
            const card = this.createAnalyseCard(analyse);
            grid.appendChild(card);
        });

        this.analysesContent.innerHTML = '';
        this.analysesContent.appendChild(grid);
    }

    createAnalyseCard(analyse) {
        const card = document.createElement('div');
        card.className = 'analyse-card';

        const date = new Date(analyse.date_analyse).toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const predictions = analyse.resultats.predictions || [];
        const statusCounts = this.countStatuses(predictions);

        card.innerHTML = `
            <div class="analyse-date">Analyse du ${date}</div>
            <div class="analyse-images">
                ${predictions.map(pred => `
                    <div class="analyse-image">
                        <img src="${pred.original_image}" alt="Image analysée">
                        ${pred.heatmap ? `<img src="${pred.heatmap}" class="heatmap-overlay" alt="Heatmap">` : ''}
                    </div>
                `).join('')}
            </div>
            <div class="analyse-status">
                ${statusCounts.legit > 0 ? `
                    <span class="status-badge status-legit">${statusCounts.legit} Authentique${statusCounts.legit > 1 ? 's' : ''}</span>
                ` : ''}
                ${statusCounts.fake > 0 ? `
                    <span class="status-badge status-fake">${statusCounts.fake} Contrefaçon${statusCounts.fake > 1 ? 's' : ''}</span>
                ` : ''}
                ${statusCounts.doubt > 0 ? `
                    <span class="status-badge status-doubt">${statusCounts.doubt} Incertain${statusCounts.doubt > 1 ? 's' : ''}</span>
                ` : ''}
            </div>
        `;

        return card;
    }

    countStatuses(predictions) {
        return predictions.reduce((acc, pred) => {
            const status = pred.statut?.toLowerCase() || 'doubt';
            acc[status] = (acc[status] || 0) + 1;
            return acc;
        }, { legit: 0, fake: 0, doubt: 0 });
    }

    updatePagination() {
        this.pagination.innerHTML = `
            <button 
                onclick="analysesManager.loadAnalyses(${this.currentPage - 1})"
                ${this.currentPage === 1 ? 'disabled' : ''}
            >
                Précédent
            </button>
            <span>Page ${this.currentPage} sur ${this.totalPages}</span>
            <button 
                onclick="analysesManager.loadAnalyses(${this.currentPage + 1})"
                ${this.currentPage === this.totalPages ? 'disabled' : ''}
            >
                Suivant
            </button>
        `;
    }
}

// Initialisation
const analysesManager = new AnalysesManager();