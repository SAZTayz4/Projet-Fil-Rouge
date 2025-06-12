class IACheck {
    constructor() {
        this.analyzeBtn = document.getElementById('analyzeBtn');
        this.resultsSection = document.getElementById('resultsSection');
        this.resultsContent = document.getElementById('resultsContent');
        this.loadingOverlay = document.getElementById('loadingOverlay');
        this.init();
    }

    init() {
        if (this.analyzeBtn) {
            this.analyzeBtn.addEventListener('click', () => this.analyzeImages());
        } else {
            console.error("Le bouton #analyzeBtn n'a pas été trouvé dans le DOM !");
        }
    }

    async analyzeImages() {
        // Récupère tous les fichiers des slots
        const fileInputs = document.querySelectorAll('.sneaker-upload-grid input[type="file"]');
        const files = [];
        const labels = [];
        
        // Collecte des fichiers et labels avec vérification
        fileInputs.forEach((input, index) => {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const slot = input.closest('.upload-slot');
                const label = slot ? slot.getAttribute('data-label') : `Image ${index + 1}`;
                
                // Vérification du type de fichier
                if (!file.type.startsWith('image/')) {
                    console.error(`Fichier invalide: ${file.name} (${file.type})`);
                    return;
                }
                
                files.push(file);
                labels.push(label);
                console.log(`Image ${index + 1} ajoutée:`, {
                    name: file.name,
                    type: file.type,
                    size: file.size,
                    label: label
                });
            }
        });

        if (files.length === 0) {
            alert("Merci de sélectionner au moins une image.");
            return;
        }

        // Vérification de la limite d'abonnement AVANT analyse
        try {
            const limitRes = await fetch('/ProjetFileRouge/Backend/IA-Check/check_limit.php', { 
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            });
            const limitData = await limitRes.json();
            if (!limitData.success) {
                console.error('Erreur abonnement:', limitData);
                alert(limitData.message);
                return;
            }
        } catch (e) {
            console.error('Erreur lors de la vérification de l\'abonnement:', e);
            alert('Erreur lors de la vérification de l\'abonnement');
            return;
        }

        this.showLoading();
        const formData = new FormData();
        
        // Ajout des images au FormData dans le format attendu par l'API
        files.forEach((file, index) => {
            // L'API attend un tableau d'images avec le nom 'images'
            formData.append('images', file);
            
            // Ajout du label correspondant si l'API le supporte
            if (labels[index]) {
                formData.append('labels', labels[index]);
            }
            
            console.log(`FormData - Image ${index}:`, {
                key: 'images',
                filename: file.name,
                type: file.type,
                size: file.size,
                label: labels[index]
            });
        });

        // Vérification du contenu du FormData
        console.log('Contenu du FormData:');
        for (let pair of formData.entries()) {
            console.log(pair[0], pair[1]);
        }

        try {
            // Appel à l'API d'analyse
            console.log('Envoi de la requête à l\'API...');
            const response = await fetch('https://checkmyckicks.ngrok.app/multi_predict', {
                method: 'POST',
                body: formData,
                // Ne pas définir de Content-Type, laissez le navigateur le faire automatiquement
                headers: {
                    'Accept': 'application/json',
                    'Origin': window.location.origin
                },
                mode: 'cors',
                credentials: 'omit'
            });

            console.log('Réponse reçue:', response.status, response.statusText);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Réponse API:', response.status, errorText);
                throw new Error(`Erreur HTTP: ${response.status} - ${errorText}`);
            }

            const responseText = await response.text();
            console.log('Réponse brute:', responseText);
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('Erreur parsing JSON:', e, 'Texte reçu:', responseText);
                throw new Error('Réponse invalide du serveur');
            }

            if (!data || typeof data !== 'object') {
                console.error('Format de réponse inattendu:', data);
                throw new Error('Format de réponse inattendu');
            }

            if (!Array.isArray(data.analysis)) {
                console.error('Tableau analysis manquant:', data);
                throw new Error('Format de réponse invalide: tableau analysis manquant');
            }

            // Formatage des résultats
            const formattedResult = {
                predictions: data.analysis.map((pred, idx) => ({
                    score: parseFloat(pred["Score (%)"]) / 100 || 0,
                    original_image: URL.createObjectURL(files[idx] || new Blob()),
                    statut: pred.Statut || 'FAKE',
                    filename: pred.Image || `Image ${idx + 1}`,
                    explanation: pred.Details || '',
                    heatmap: pred.Heatmap ? `data:image/png;base64,${pred.Heatmap}` : null,
                    ponderation: pred.Pondération || 1.0
                }))
            };

            // Enregistrement de l'analyse dans la BDD
            try {
                console.log('Tentative de sauvegarde avec les données:', {
                    nombre_images: files.length,
                    resultats: formattedResult
                });

                // Préparation des données pour la sauvegarde
                const cleanPredictions = formattedResult.predictions.map(pred => ({
                    score: parseFloat(pred.score.toFixed(2)),
                    statut: pred.statut,
                    filename: pred.filename,
                    explanation: pred.explanation || '',
                    ponderation: parseFloat(pred.ponderation || 1.0)
                }));

                const dataToSave = {
                    nombre_images: files.length,
                    resultats: {
                        predictions: cleanPredictions,
                        average_score: parseFloat(data.average_score.replace('%', '')) / 100,
                        date_analyse: new Date().toISOString(),
                        version: '1.0'
                    }
                };

                // Validation des données avant envoi
                if (!dataToSave.nombre_images || dataToSave.nombre_images <= 0) {
                    throw new Error('Nombre d\'images invalide');
                }

                if (!dataToSave.resultats.predictions || !Array.isArray(dataToSave.resultats.predictions)) {
                    throw new Error('Structure des prédictions invalide');
                }

                // Vérification que chaque prédiction a les champs requis
                dataToSave.resultats.predictions.forEach((pred, index) => {
                    if (typeof pred.score !== 'number' || isNaN(pred.score)) {
                        throw new Error(`Score invalide pour l'image ${index + 1}`);
                    }
                    if (!pred.statut || !pred.filename) {
                        throw new Error(`Données manquantes pour l'image ${index + 1}`);
                    }
                });

                // Log détaillé des données avant sauvegarde
                console.log('Données nettoyées à sauvegarder:', JSON.stringify(dataToSave, null, 2));

                // Tentative de sauvegarde
                const saveResponse = await fetch('/ProjetFileRouge/Backend/IA-Check/save_analysis.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dataToSave),
                    credentials: 'same-origin'
                });

                console.log('Headers de la requête de sauvegarde:', {
                    'Content-Type': 'application/json',
                    'Cookie': document.cookie
                });

                console.log('Statut de la réponse de sauvegarde:', saveResponse.status);
                const rawSaveResponse = await saveResponse.text();
                console.log('Réponse brute de sauvegarde:', rawSaveResponse);

                let parsedResponse;
                try {
                    parsedResponse = JSON.parse(rawSaveResponse);
                    console.log('Données de sauvegarde parsées:', parsedResponse);
                } catch (e) {
                    console.error('Erreur lors du parsing de la réponse:', e);
                    console.log('Réponse non-JSON reçue:', rawSaveResponse);
                }

                if (!saveResponse.ok) {
                    throw new Error(`Erreur serveur (${saveResponse.status}): ${parsedResponse?.message || rawSaveResponse}`);
                }

                if (!parsedResponse.success) {
                    throw new Error(parsedResponse.message || 'Erreur inconnue lors de la sauvegarde');
                }

                // Si on arrive ici, la sauvegarde a réussi
                console.log('Sauvegarde réussie, ID analyse:', parsedResponse.analyse_id);
                this.displayResults(formattedResult, parsedResponse.analyse_id);

            } catch (saveError) {
                console.error('Erreur détaillée lors de la sauvegarde:', {
                    message: saveError.message,
                    stack: saveError.stack,
                    response: saveError.response
                });
                
                // Afficher un message plus détaillé à l'utilisateur
                alert(`Erreur lors de la sauvegarde de l'analyse : ${saveError.message}\n\nLes résultats sont temporaires et ne seront pas conservés.`);
                
                // Afficher quand même les résultats
                this.displayResults(formattedResult);
            }

        } catch (error) {
            console.error('Erreur analyse:', error);
            alert(`Erreur lors de l'analyse: ${error.message}`);
        } finally {
            this.hideLoading();
        }
    }

    displayResults(result, analyse_id) {
        this.resultsSection.style.display = 'block';
        this.resultsContent.innerHTML = '';

        if (!result.predictions || result.predictions.length === 0) {
            this.resultsContent.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Aucun résultat disponible</p>
                </div>`;
            return;
        }

        const grid = document.createElement('div');
        grid.className = 'results-grid';

        result.predictions.forEach((prediction, index) => {
            if (!prediction) return;

            const card = document.createElement('div');
            card.className = 'result-card-new';

            card.innerHTML = `
                <div class="result-img-container">
                    <img src="${prediction.original_image}" alt="Image analysée" class="result-img-main">
                    ${prediction.heatmap ? `
                        <div class="result-heatmap">
                            <img src="${prediction.heatmap}" alt="Heatmap" class="heatmap-img">
                        </div>
                    ` : ''}
                </div>
                <div class="result-info">
                    <span class="result-badge ${prediction.statut === 'LEGIT' ? 'badge-legit' : 'badge-fake'}">${prediction.statut}</span>
                    <span class="result-score-badge">${(prediction.score * 100).toFixed(2)}%</span>
                    ${prediction.ponderation ? `<span class="result-ponderation">Pondération: ${prediction.ponderation}</span>` : ''}
                </div>
                <div class="result-filename">${prediction.filename}</div>
                ${prediction.explanation ? `<div class="result-explanation">${prediction.explanation}</div>` : ''}
            `;
            grid.appendChild(card);
        });
        this.resultsContent.appendChild(grid);
        this.resultsSection.scrollIntoView({ behavior: 'smooth' });
    }

    getStatusClass(score) {
        if (score >= 0.8) return 'status-legit';
        if (score <= 0.2) return 'status-fake';
        return 'status-doubt';
    }

    getStatusText(score) {
        if (score >= 0.8) return 'LÉGITIME';
        if (score <= 0.2) return 'FAKE';
        return 'DOUTEUX';
    }

    getConfidenceText(score) {
        if (score >= 0.8) return 'Confiance élevée';
        if (score <= 0.2) return 'Confiance élevée';
        if (score >= 0.6) return 'Confiance moyenne';
        if (score <= 0.4) return 'Confiance moyenne';
        return 'Confiance faible';
    }

    showLoading() {
        this.loadingOverlay.style.display = 'flex';
    }

    hideLoading() {
        this.loadingOverlay.style.display = 'none';
    }
}

// Initialisation quand le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    window.iaCheck = new IACheck();
});

document.addEventListener('DOMContentLoaded', function() {
  const grid = document.getElementById('sneakerUploadGrid');
  // Gestion de l'upload sur chaque case
  function setSlotUpload(slot) {
    const input = slot.querySelector('input[type="file"]');
    const slotImg = slot.querySelector('.slot-img');
    slot.addEventListener('click', function(e) {
      if (e.target.classList.contains('remove-img-btn')) return;
      input.click();
    });
    input.addEventListener('change', function(e) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(ev) {
          slotImg.innerHTML = '';
          const img = document.createElement('img');
          img.src = ev.target.result;
          img.className = 'preview-img';
          slotImg.appendChild(img);
          let btn = slot.querySelector('.remove-img-btn');
          if (!btn) {
            btn = document.createElement('button');
            btn.className = 'remove-img-btn';
            btn.innerHTML = '<i class="fas fa-times"></i>';
            btn.onclick = function(ev2) {
              ev2.stopPropagation();
              input.value = '';
              slotImg.innerHTML = slot.dataset.label && slot.dataset.label.includes('Couture') ? '<i class="fas fa-grip-lines-vertical"></i>' :
                slot.dataset.label && slot.dataset.label.includes('Apparence') ? '<i class="fas fa-shoe-prints"></i>' :
                slot.dataset.label && slot.dataset.label.includes('Box') ? '<i class="fas fa-box"></i>' :
                '<i class="fas fa-barcode"></i>';
              btn.remove();
            };
            slot.appendChild(btn);
          }
        };
        reader.readAsDataURL(input.files[0]);
      }
    });
  }
  grid.querySelectorAll('.upload-slot:not(.add-slot)').forEach(setSlotUpload);
  // Ajout dynamique de case supplémentaire
  grid.querySelector('.add-btn').addEventListener('click', function() {
    const newSlot = document.createElement('div');
    newSlot.className = 'upload-slot';
    newSlot.innerHTML = `
      <label>Autre</label>
      <input type="file" accept="image/*" style="display:none;">
      <div class="slot-img"><i class="fas fa-plus"></i></div>
    `;
    grid.insertBefore(newSlot, grid.querySelector('.add-slot'));
    setSlotUpload(newSlot);
  });
});