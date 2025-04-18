let currentPage = 1,
    totalPages = 1,
    editingCell = null,
    searchTimeout;

const rowsPerPage = 25;

window.selectAgence = (tableName, agenceSelectId, sqlTableName, clientType) => {
    const agenceId = document.getElementById(agenceSelectId).value;
    fetchData(tableName, sqlTableName, 1, "", agenceId, clientType);
};

const searchTable = (tableNameSQL, searchBarId) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const searchTerm = document.getElementById(searchBarId).value.toLowerCase();
        const tableName = tableNameSQL === "vehicules" ? "Vehicles" :
                          tableNameSQL === "clientsVendeur" ? "CustomersSell" :
                          "CustomersBuy";

        fetch(`src/functions/search.php?table=${tableNameSQL}&term=${encodeURIComponent(searchTerm)}&page=${currentPage}`)
            .then(response => response.json())
            .then(data => {
                updateTable(data, tableName);
            });
    }, 500);
};


const updateTable = (rows, tableName) => {
    const tbody = document.getElementById(`${tableName}TableBody`);

    const uniqueKey = tableName === "Vehicles" ? "vehicules_immatriculation" : "clients_email";
    const typeValue = tableName === "Vehicles" ? "selectedVehicles" : "selectedCustomers";
    const lastIndexKey = tableName === "Vehicles" ? "vehicules_agence_id" : "clients_agence_id";

    window[tableName] = rows;

    const clientFields = [
        "clients_nom", 
        "clients_prenom", 
        "clients_email", 
        "clients_telephone", 
        "clients_rue", 
        "clients_ville", 
        "clients_cp", 
        "clients_numero_cni"
    ];

    const vehicleFields = [
        "vehicules_immatriculation", 
        "vehicules_marque", 
        "vehicules_model", 
        "vehicules_annee", 
        "vehicules_puissance", 
        "vehicules_type_boite", 
        "vehicules_couleur", 
        "vehicules_kilometrage"
    ];

    const fieldsToDisplay = tableName === "Vehicles" ? vehicleFields : clientFields;

    tbody.innerHTML = rows
        .map((row) => {
            const idKey = tableName === "Vehicles" ? "vehicules_id" : "clients_id";
            const realIndex = row[idKey];
            const lastIndexValue = row[lastIndexKey];

            return `
                <tr data-index="${realIndex}" data-real-index="${realIndex}" onclick="selectRow(this, '${tableName}')">
                    ${fieldsToDisplay.map(field => {
                        return `<td ondblclick="editCell(this, '${field}', ${realIndex}, '${tableName}')">${row[field]}</td>`;
                    }).join('')}
                    <td class="btn_card" onclick="openPopup('${tableName}', ${realIndex})">Voir</td>
                    <td><input type="radio" name="${typeValue}" value="${row[uniqueKey]}"></td>
                    <td><input type="text" value="${lastIndexValue}" hidden="true"></td>
                </tr>
            `;
        })
        .join('');

    updatePaginationControls(tableName);
};


const updatePaginationControls = (tableName) => {
    const paginationContainer = document.getElementById(`paginationControls_${tableName}`);
    const paginationCounter = document.getElementById(`paginationCounter_${tableName}`);

    if (!paginationContainer) {
        console.error(`Conteneur de pagination introuvable pour ${tableName}`);
        return;
    }

    paginationContainer.innerHTML = '';

    const createPageButton = (page, text, sqlTableName) => {
        const link = document.createElement("a");
        link.href = "#";
        link.textContent = text;
        link.onclick = (e) => {
            e.preventDefault();
            changePage(tableName, sqlTableName, page);
        };
        return link;
    };

    let sqlTableName;
    sqlTableName = tableName === "Vehicles" ? "vehicules" : "clients";

    if (currentPage > 1) {
        paginationContainer.appendChild(createPageButton(currentPage - 1, "Page précédente", sqlTableName));
        paginationCounter.innerHTML = `Page ${currentPage} sur ${totalPages}`;
    }

    if (currentPage < totalPages) {
        paginationContainer.appendChild(createPageButton(currentPage + 1, "Page suivante", sqlTableName));
        paginationCounter.innerHTML = `Page ${currentPage} sur ${totalPages}`;
    }
};

const changePage = (tableName, sqlTableName, page) => {
    typeClient = tableName === "Vehicles" ? "" : tableName === "CustomersSell" ? "Vendeur" : "Acheteur";
    fetchData(tableName, sqlTableName, page, "", "All", typeClient);
};

const editCell = (td, field, id, tableName) => {
    if (td.querySelector("input, select")) return;

    if (editingCell && editingCell !== td) {
        const currentInput = editingCell.querySelector("input, select");
        if (currentInput) currentInput.blur();
    }

    const oldValue = td.innerText.trim();
    let input;

    if (field === "vehicules_type_boite" && tableName === "Vehicles") {
        input = document.createElement("select");
        const options = ["Manuelle", "Automatique"];

        options.forEach(optionValue => {
            const option = document.createElement("option");
            option.value = optionValue;
            option.textContent = optionValue;
            if (optionValue === oldValue) option.selected = true;
            input.appendChild(option);
        });

    } else {
        input = document.createElement("input");
        input.type = "text";
        input.value = oldValue;
        input.classList.add("edit-input");
    }

    td.innerHTML = "";
    td.appendChild(input);

    const idKey = tableName === "Vehicles" ? "vehicules_id" : "clients_id";
    const uniqueKey = tableName === "Vehicles" ? "vehicules_immatriculation" : "clients_email";

    const rowIndex = window[tableName].findIndex(item => item[idKey] == id);
    const rowData = window[tableName][rowIndex];
    const oldUniqueValue = rowData[uniqueKey];

    editingCell = td;
    input.focus();

    const validateInput = (field, value) => {
        if (value === "") return false;

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const numberRegex = /^[0-9]+$/;

        if (field === "clients_email" && !emailRegex.test(value)) {
            input.removeEventListener("blur", saveChanges);
            alert("Veuillez entrer une adresse email valide.");
            return false;
        }

        const valueExists = window[tableName].some(item => item[uniqueKey] === value && item[uniqueKey] !== oldUniqueValue);
        if (valueExists) {
            input.removeEventListener("blur", saveChanges);
            alert(`Cette ${uniqueKey === "clients_email" ? "adresse email" : "immatriculation"} est déjà utilisée. Veuillez en choisir une autre.`);
            return false;
        }

        if (["clients_telephone", "clients_cp", "clients_numero_cni", "vehicules_kilometrage", "vehicules_puissance"].includes(field) && !numberRegex.test(value)) {
            input.removeEventListener("blur", saveChanges);
            alert("Ce champ ne peut contenir que des chiffres.");
            return false;
        }

        return true;
    };

    const saveChanges = () => {
        const newValue = input.value.trim();

        if (newValue === oldValue) {
            td.innerHTML = oldValue;
            editingCell = null;
            return;
        }

        if (!validateInput(field, newValue)) {
            td.innerHTML = oldValue;
            editingCell = null;
            return;
        }

        window[tableName][rowIndex][field] = newValue;
        updateDatabase(window[tableName][rowIndex], oldUniqueValue, tableName);

        td.innerHTML = newValue;
        editingCell = null;
    };

    input.addEventListener("change", () => {
        if (field === "vehicules_type_boite" && tableName === "Vehicles") {
            saveChanges();
        }
    });

    input.addEventListener("blur", saveChanges);
    input.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            saveChanges();
        }
    });
};

const updateDatabase = (item, oldUniqueValue, tableName) => {
    const controllerFile = (tableName === "CustomersSell" || tableName === "CustomersBuy") ? "controllerCustomers.php" : "controllerVehicles.php";

    fetch(`src/controllers/${controllerFile}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ ...item, oldUniqueValue })
    });
};

const openPopup = (tableName, realIndex) => {
    const popup = document.getElementById("cardItem");
    const overlay = document.getElementById("overlay");

    overlay.classList.remove("hidden");
    popup.classList.remove("hidden");

    cardShow(tableName, realIndex);

    console.log("Popup ouvert pour l'élément avec l'index réel:", realIndex);
};

const cardShow = (tableName, realIndex) => {
    const cardItemContent = document.getElementById("cardItem_content");
    cardItemContent.innerHTML = "<p>Chargement...</p>";

    const idKey = tableName === "Vehicles" ? "vehicules_id" : "clients_id";
    const tableSQL = tableName === "Vehicles" ? "vehicules" : "clients";

    fetch(`src/functions/getInfo.php?table=${tableSQL}&id=${realIndex}`)
        .then(response => response.json())
        .then(item => {
            if (!item || Object.keys(item).length === 0) {
                cardItemContent.innerHTML = `<p>Erreur : Élément introuvable.</p>`;
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
                    ${
                        item.clients_copie_cni?.startsWith("data:application/pdf")
                            ? `<embed src="${item.clients_copie_cni}" type="application/pdf" class="client-photo" />`
                            : `<img src="${item.clients_copie_cni}" alt="Photo du client" class="client-photo" />`
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
                    ${
                        item.vehicules_carte_grise?.startsWith("data:application/pdf")
                            ? `<embed src="${item.vehicules_carte_grise}" type="application/pdf" class="client-photo" />`
                            : `<img src="${item.vehicules_carte_grise}" alt="Carte grise" class="client-photo" />`
                    }
                </div>
                `;
            }

            contentHTML += '</div>';
            cardItemContent.innerHTML = contentHTML;

            // 👇 Gestion des notes
            let notesUrl = "";
            let notesTitle = "";

            if (tableName === "CustomersSell" || tableName === "CustomersBuy") {
                notesUrl = `src/functions/getNotes.php?clients_id=${item.clients_id}`;
                notesTitle = "Notes du client";
            } else if (tableName === "Vehicles") {
                notesUrl = `src/functions/getNotes.php?vehicle_id=${item.vehicules_id}`;
                notesTitle = "Notes du véhicule";
            }

            fetch(notesUrl)
                .then(res => res.json())
                .then(notes => {
                    let html = `
                        <form class="note-form" onsubmit="submitNote(event, '${tableName}', ${item.clients_id || item.vehicules_id})">
                            <h4>Ajouter une note :</h4>
                            <textarea name="note_content" rows="4" required placeholder="Écrire une note..."></textarea>
                            <button type="submit">Enregistrer</button>
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
                        html += `<p>Aucune note disponible.</p>`;
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
            cardItemContent.innerHTML = `<p>Erreur lors de la récupération des données.</p>`;
        });
};

function submitNote(event, tableName, id) {
    event.preventDefault();

    const form = event.target;
    const noteContent = form.note_content.value;

    const data = {
        notes_content: noteContent
    };

    if (tableName === "CustomersSell" || tableName === "CustomersBuy") {
        data.clients_id = id;
    } else if (tableName === "Vehicles") {
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
                cardShow(tableName, index);
            } else {
                console.warn("Élément non trouvé pour le refresh (id:", id, ")");
                console.log("Liste disponible :", dataList.map(el => el.clients_id || el.vehicules_id));
            }
        }
    })
    .catch(error => {
        console.error("Erreur réseau :", error);
    });
}

const closePopup = () => {
    const popup = document.getElementById("cardItem");
    const overlay = document.getElementById("overlay");

    popup.classList.add("hidden");
    overlay.classList.add("hidden");
};

const initTable = (tableName, sqlTableName) => {
    fetchData(tableName, sqlTableName, 1);
};

const fetchData = (tableName, sqlTableName, page = 1, term = "", agenceId = "All", clientType = "") => {
    currentPage = page;

    const params = new URLSearchParams({
        table: sqlTableName,
        page: page,
        term: term,
        agence: agenceId,
    });

    if (clientType) {
        params.append("client_type", clientType);
    }

    fetch(`src/functions/fetchTable.php?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            if (data.rows && Array.isArray(data.rows)) {
                window[tableName] = data.rows;
                totalPages = data.totalPages;
                updateTable(data.rows, tableName);
            } else {
                console.error(`Données invalides pour ${tableName}`, data);
            }
        })
        .catch(err => console.error("Erreur de chargement des données:", err));
};



fetchData("CustomersSell", "clients", 1, "", "All", "Vendeur");
fetchData("CustomersBuy", "clients", 1, "", "All", "Acheteur");
fetchData("Vehicles", "vehicules", 1);