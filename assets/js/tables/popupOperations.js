import { fetchData, showSpinner, hideSpinner } from './utils.js';

export const openPopup = (tableName, realIndex) => {
    const popup = document.getElementById("cardItem");
    const overlay = document.getElementById("overlay");

    overlay.classList.remove("hidden");
    popup.classList.remove("hidden");

    cardShow(tableName, realIndex);
};

export const cardShow = (tableName, realIndex) => {
    const cardItemContent = document.getElementById("cardItem_content");
    const spinner = document.getElementById("loadingSpinner");
    showSpinner(spinner);

    const tableSQL = tableName === "Vehicles" ? "vehicules" : "clients";

    fetchData(`src/functions/getInfo.php?table=${tableSQL}&id=${realIndex}`)
        .then(item => {
            if (!item || Object.keys(item).length === 0) {
                cardItemContent.innerHTML = '<p>Aucune donnée trouvée.</p>';
                return;
            }

            const formatDate = (dateString) => {
                if (!dateString) return "Aucune date.";
                const date = new Date(dateString);
                return `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
            };

            const formatDateHours = (dateString) => {
                if (!dateString) return "Aucune date.";
                const date = new Date(dateString);
                const options = {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                };
                return date.toLocaleDateString('fr-FR', options).replace(',', ' -');
            };

            let contentHTML = `
                <div class="cardItem_header">
                    <span onclick="closePopup()" class="material-symbols-outlined close-button">close</span>
                    <div class="cardItem_header_title">
            `;

            if (tableName === "CustomersSell" || tableName === "CustomersBuy") {
                contentHTML += `
                    <h2>Informations du client</h2>
                    <p><strong>Nom : </strong> ${item.clients_nom}</p>
                    <p><strong>Prénom : </strong> ${item.clients_prenom}</p>
                    <p><strong>Email : </strong> ${item.clients_email}</p>
                    <p><strong>Téléphone : </strong> ${item.clients_telephone}</p>
                    <p><strong>Date d'anniversaire : </strong> ${formatDate(item.clients_anniversaire)}</p>
                    <p><strong>Lieu de naissance : </strong> ${item.clients_lieu_naissance}</p>
                    <p><strong>Type de client : </strong> ${item.clients_type}</p>
                    <p><strong>Adresse : </strong> ${item.clients_rue}</p>
                    <p><strong>Code postal : </strong> ${item.clients_cp}</p>
                    <p><strong>Ville : </strong> ${item.clients_ville}</p>
                    <p><strong>Numéro CNI : </strong> ${item.clients_numero_cni}</p>
                </div>
                <div class="client-photo-container">
                    ${item.clients_copie_cni?.startsWith("data:application/pdf")
                        ? `<embed src="${item.clients_copie_cni}" type="application/pdf" class="client-photo" />`
                        : `<img src="${item.clients_copie_cni}" alt="Photo du client" class="client-photo" loading="lazy" />`
                    }
                </div>
                `;
            } else if (tableName === "Vehicles") {
                contentHTML += `
                    <h2>Informations du véhicule</h2>
                    <p><strong>Immatriculation : </strong> ${item.vehicules_immatriculation}</p>
                    <p><strong>Marque : </strong> ${item.vehicules_marque}</p>
                    <p><strong>Modèle : </strong> ${item.vehicules_model}</p>
                    <p><strong>Année : </strong> ${item.vehicules_annee}</p>
                    <p><strong>Puissance : </strong> ${item.vehicules_puissance}</p>
                    <p><strong>Type de boîte : </strong> ${item.vehicules_type_boite}</p>
                    <p><strong>Couleur : </strong> ${item.vehicules_couleur}</p>
                    <p><strong>Finition : </strong> ${item.vehicules_finition}</p>
                    <p><strong>Origine : </strong> ${item.vehicules_origine}</p>
                    <p><strong>Kilométrage : </strong> ${item.vehicules_kilometrage} km</p>
                    <p><strong>Nombre de main : </strong> ${item.vehicules_nombre_main}</p>
                    <p><strong>Date de mise en circulation : </strong> ${formatDate(item.vehicules_date_mise_en_circu)}</p>
                    <p><strong>Date entretien : </strong> ${formatDate(item.vehicules_date_entretetien)}</p>
                    <p><strong>Frais récent : </strong> ${item.vehicules_frais_recent}</p>
                    <p><strong>Frais à prévoir : </strong> ${item.vehicules_frais_prevoir}</p>
                </div>
                <div class="client-photo-container">
                    ${item.vehicules_carte_grise?.startsWith("data:application/pdf")
                        ? `<embed src="${item.vehicules_carte_grise}" type="application/pdf" class="client-photo" />`
                        : `<img src="${item.vehicules_carte_grise}" alt="Carte grise" class="client-photo" loading="lazy" />`
                    }
                </div>
                `;
            }

            contentHTML += '</div>';
            cardItemContent.innerHTML = contentHTML;

            let notesUrl = "";
            let notesTitle = "";

            if (tableName === "CustomersSell" || tableName === "CustomersBuy") {
                notesUrl = `src/functions/getNotes.php?clients_id=${item.clients_id}`;
                notesTitle = "Notes du client";
            } else if (tableName === "Vehicles") {
                notesUrl = `src/functions/getNotes.php?vehicle_id=${item.vehicules_id}`;
                notesTitle = "Notes du véhicule";
            }

            fetchData(notesUrl)
                .then(notes => {
                    let html = `
                        <form class="note-form" onsubmit="submitNote(event, '${tableName}', ${item.clients_id || item.vehicules_id})">
                            <h4>Ajouter une note :</h4>
                            <textarea name="note_content" rows="4" required placeholder="Écrire une note..."></textarea>
                            <button type="submit">Ajouter la note</button>
                        </form>
                    `;

                    html += `<div class="client-notes-container"><h3>${notesTitle} :</h3>`;

                    if (notes && notes.length > 0) {
                        html += '<ul class="notes-list">';
                        notes.forEach(note => {
                            html += `
                                <li>
                                    <p class="date_notes">${formatDateHours(note.notes_date)}<label class=""> - ${note.notes_text}</label></p>
                                </li>
                            `;
                        });
                        html += '</ul>';
                    } else {
                        html += '<p>Aucune note disponible.</p>';
                    }

                    html += '</div>';

                    const notesContainer = document.createElement('div');
                    notesContainer.innerHTML = html;

                    const headerTitle = cardItemContent.querySelector('.cardItem_header_title');
                    headerTitle.insertAdjacentElement('afterend', notesContainer);
                })
                .catch(error => {
                    console.error("Erreur récupération des notes:", error);
                });

        })
        .catch(error => {
            console.error("Erreur lors de la récupération des infos :", error);
            cardItemContent.innerHTML = `
                <div id="loadingSpinner" class="spinner-box hidden">
                    <div class="spinner"></div>
                    <span class="spinner-text">Erreur lors de la récupération des infos</span>
                </div>
            `;
        }).finally(() => {
            hideSpinner(spinner);
        });
};

export const submitNote = (event, tableName, id) => {
    event.preventDefault();

    const form = event.target;
    const noteContent = form.note_content.value;

    const data = {
        notes_content: noteContent
    };

    if (tableName === "CustomersSell" || tableName === "CustomersBuy") {
        data.clients_id = id;
    } else {
        data.vehicules_id = id;
    }

    fetch('src/functions/addNote.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            const dataList = window[tableName];
            let index = -1;

            if (tableName === "Vehicles") {
                index = dataList.findIndex(el => el.vehicules_id == id || el.vehicle_id == id);
            } else {
                index = dataList.findIndex(el => el.clients_id == id || el.client_id == id);
            }

            if (index !== -1) {
                cardShow(tableName, id);
            } else {
                console.warn("Élément non trouvé pour le refresh (id:", id, ")");
            }
        }
    })
    .catch(error => {
        console.error("Erreur réseau :", error);
    });
};

export const closePopup = () => {
    const popup = document.getElementById("cardItem");
    const overlay = document.getElementById("overlay");

    popup.classList.add("hidden");
    overlay.classList.add("hidden");
};
