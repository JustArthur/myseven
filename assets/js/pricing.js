const inputEuroVenteSouhaite = document.getElementById('inputEuroVenteSouhaite');
const inputEuroPrixVente = document.getElementById('inputEuroPrixVente');


inputEuroVenteSouhaite.addEventListener('input', (event) => {
    let valeur = event.target.value.replace(/\s+/g, '');

    if (!/^\d*$/.test(valeur)) {
        valeur = valeur.replace(/\D/g, '');
    }

    const valeurFormatee = valeur.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

    event.target.value = valeurFormatee;
});

inputEuroPrixVente.addEventListener('input', (event) => {
    let valeur = event.target.value.replace(/\s+/g, '');

    if (!/^\d*$/.test(valeur)) {
        valeur = valeur.replace(/\D/g, '');
    }

    const valeurFormatee = valeur.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

    event.target.value = valeurFormatee;
});