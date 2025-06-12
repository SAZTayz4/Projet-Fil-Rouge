const languagesByCountry = {
    'FR': ['fr'],
    'US': ['en'],
    'GB': ['en'],
    'DE': ['de'],
    'ES': ['es'],
    'IT': ['it'],
    'JP': ['jp'],
    'KR': ['kr']
};

function updateLanguages() {
    const countrySelect = document.getElementById('country');
    const languageSelect = document.getElementById('language');
    const selectedCountry = countrySelect.value;
    
    languageSelect.innerHTML = '<option value="">Sélectionnez votre langue</option>';
    
    if (selectedCountry && languagesByCountry[selectedCountry]) {
        languagesByCountry[selectedCountry].forEach(lang => {
            const option = document.createElement('option');
            option.value = lang;
            option.textContent = getLanguageName(lang);
            languageSelect.appendChild(option);
        });
    }
}

function getLanguageName(code) {
    const languageNames = {
        'fr': 'Français',
        'en': 'English',
        'de': 'Deutsch',
        'es': 'Español',
        'it': 'Italiano',
        'jp': '日本語',
        'kr': '한국어'
    };
    return languageNames[code] || code;
}

function saveLanguage() {
    const languageSelect = document.getElementById('language');
    const saveCheckbox = document.getElementById('saveLanguage');
    const selectedLang = languageSelect.value;
    
    if (selectedLang) {
        if (saveCheckbox.checked) {
            localStorage.setItem('userLanguage', selectedLang);
        }
        
        fetch('/ProjetFileRouge/Frontend/set_language.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'lang=' + selectedLang
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/ProjetFileRouge/Frontend/HTML/home.php';
            } else {
                alert('Erreur lors de la sauvegarde de la langue');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la sauvegarde de la langue');
        });
    } else {
        alert('Veuillez sélectionner une langue');
    }
}

function loadSavedLanguage() {
    const savedLang = localStorage.getItem('userLanguage');
    if (savedLang) {
        document.getElementById('saveLanguage').checked = true;
        for (const [country, langs] of Object.entries(languagesByCountry)) {
            if (langs.includes(savedLang)) {
                document.getElementById('country').value = country;
                updateLanguages();
                document.getElementById('language').value = savedLang;
                break;
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', loadSavedLanguage);