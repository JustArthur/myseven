let rowsPerPage = 50;
let editingCell = null;

window.selectAgence = (tableName, AgenceId) => {
    const selectedAgenceId = document.getElementById(AgenceId).value;

    let filteredRows;
    if (selectedAgenceId === "All") {
        filteredRows = window[tableName];
    } else {
        filteredRows = window[tableName].filter(row => {
            const agenceField = tableName === "Vehicles" ? "vehicules_agence_id" : "clients_agence_id";
            return row[agenceField] == selectedAgenceId;
        });
    }

    updateTable(filteredRows, tableName);
};


// Barre de recherche
const searchTable = (tableName, searchBarId) => {
    const searchTerm = document.getElementById(searchBarId).value.toLowerCase();

    let filteredRows;

    if (searchTerm === "") {
        filteredRows = window[tableName];
    } else {
        filteredRows = window[tableName].filter(row =>
            Object.values(row).some(value => value.toString().toLowerCase().includes(searchTerm))
        );
    }

    updateTable(filteredRows, tableName);
};


// update le tableau
const updateTable = (rows, tableName) => {
    const tbody = document.getElementById(`${tableName}TableBody`);

    const uniqueKey = tableName === "Vehicles" ? "vehicules_immatriculation" : "clients_email";
    const typeValue = tableName === "Vehicles" ? "selectedVehicles" : "selectedCustomers";

    const tableMap = {
        Vehicles: "noteVehicles",
        CustomersSell: "noteCustomersSell",
        CustomersBuy: "noteCustomersBuy"
    };
    const tableFull = tableMap[tableName];

    const lastIndexKey = tableName === "Vehicles" ? "vehicules_agence_id" : "clients_agence_id";

    tbody.innerHTML = rows
        .map((row, index) => {
            const realIndex = window[tableName].findIndex(r => r[uniqueKey] === row[uniqueKey]);
            const lastIndexValue = row[lastIndexKey];

            return `
                <tr data-index="${realIndex}" data-real-index="${realIndex}" onclick="selectRow(this, '${tableName}')">
                    ${Object.keys(row).filter(field => field !== lastIndexKey).map(field => {
                        return `<td ondblclick="editCell(this, '${field}', ${realIndex}, '${tableName}')">${row[field]}</td>`;
                    }).join('')}
                    <td class="btn_card" onclick="openPopup('${tableFull}', ${realIndex})">Voir</td>
                    <td><input type="radio" name="${typeValue}" value="${row[uniqueKey]}"></td>
                    <td><input type="text" value="${lastIndexValue}" hidden="true"></td>
                </tr>
            `;
        })
        .join('');
};



// Editer une cellule du tableau
const editCell = (td, field, index, tableName) => {
    if (td.querySelector("input, select")) {
        return;
    }

    if (editingCell && editingCell !== td) {
        const currentInput = editingCell.querySelector("input, select");
        if (currentInput) {
            currentInput.blur();
        }
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
            if (optionValue === oldValue) {
                option.selected = true;
            }
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

    const uniqueKey = tableName === "Vehicles" ? "vehicules_immatriculation" : "clients_email";
    const oldUniqueValue = window[tableName][index][uniqueKey];

    editingCell = td;
    input.focus();

    const validateInput = (field, value) => {
        if (value === "") return false;

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const numberRegex = /^[0-9]+$/;

        if (field === "email" && !emailRegex.test(value)) {
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

        window[tableName][index][field] = newValue;
        updateDatabase(window[tableName][index], oldUniqueValue, tableName);
        
        td.innerHTML = newValue;
        editingCell = null;
    };

    input.addEventListener("change", (e) => {
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

// Met à jour la BDD
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

    // Afficher l'overlay et la popup
    overlay.classList.remove("hidden");
    popup.classList.remove("hidden");

    // Charger les informations spécifiques dans la popup
    cardShow(tableName, realIndex);
};

// Fonction pour afficher le contenu de la popup
const cardShow = (tableName, realIndex) => {
    const cardItemContent = document.getElementById("cardItem_content");

    // Définir le tableau en fonction du tableName
    let data;
    if (tableName === "noteCustomersSell" || tableName === "noteCustomersBuy") {
        data = window[tableName];
    } else if (tableName === "noteVehicles") {
        data = window[tableName];
    }

    console.log(realIndex);
    console.log(data[realIndex]);

    if (data) {
        const item = data[realIndex];  // Récupérer l'élément au bon index
        if (tableName === "noteCustomersSell" || tableName === "noteCustomersBuy") {
            // Afficher les informations du client
            cardItemContent.innerHTML = `
                <span onclick="closePopup()" class="material-symbols-outlined">close</span>
                <h2>Informations du client</h2>
                <p><strong>Nom : </strong> ${item.clients_nom}</p>
                <p><strong>Prénom : </strong> ${item.clients_prenom}</p>
                <p><strong>Email : </strong> ${item.clients_email}</p>
                <p><strong>Téléphone : </strong> ${item.clients_telephone}</p>
                <p><strong>Date d'anniversaire : </strong> ${item.clients_anniversaire}</p>
                <p><strong>Lieu de naissance : </strong> ${item.clients_lieu_naissance}</p>
                <p><strong>Type de client : </strong> ${item.clients_type}</p>
                <p><strong>Adresse : </strong> ${item.clients_rue}</p>
                <p><strong>Code postal : </strong> ${item.clients_cp}</p>
                <p><strong>Ville : </strong> ${item.clients_ville}</p>
                <p><strong>Numéro CNI : </strong> ${item.clients_numero_cni}</p>
            `;
        } else if (tableName === "noteVehicles") {
            // Afficher les informations du véhicule
            const formatDate = (dateString) => {
                if (!dateString) return "N/A";
                const date = new Date(dateString);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            };

            cardItemContent.innerHTML = `
                <span onclick="closePopup()" class="material-symbols-outlined">close</span>
                <h2>Informations du véhicule</h2>
                <p><strong>Immatriculation : </strong> ${item.vehicules_immatriculation}</p>
                <p><strong>Marque : </strong> ${item.vehicules_marque}</p>
                <p><strong>Modèle : </strong> ${item.vehicules_model}</p>
                <p><strong>Année : </strong> ${item.vehicules_annee}</p>
                <p><strong>Puissance : </strong> ${item.vehicules_puissance}</p>
                <p><strong>Type de boîte : </strong> ${item.vehicules_type_boite}</p>
                <p><strong>Couleur : </strong> ${item.vehicules_couleur}</p>
                <p><strong>Finition : </strong> ${item.vehicules_finition}</p>
                <p><strong>Origne : </strong> ${item.vehicules_origine}</p>
                <p><strong>Kilometrage : </strong> ${item.vehicules_kilometrage} km</p>
                <p><strong>Nombre de main : </strong> ${item.vehicules_nombre_main}</p>
                <p><strong>Date de mise en circulation : </strong> ${formatDate(item.vehicules_date_mise_en_circu)}</p>
                <p><strong>Date entretien : </strong> ${formatDate(item.vehicules_date_entretetien)}</p>
                <p><strong>Frais récent : </strong> ${item.vehicules_frais_recent}</p>
                <p><strong>Frais à prévoir : </strong> ${item.vehicules_frais_prevoir}</p>
            `;
        }
    } else {
        // Gestion d'erreur si le tableau n'est pas trouvé
        cardItemContent.innerHTML = `<p>Erreur: Le tableau de données est introuvable.</p>`;
    }
};


// Fonction pour fermer la popup
const closePopup = () => {
    const popup = document.getElementById("cardItem");
    const overlay = document.getElementById("overlay");

    // Cacher la popup et l'overlay
    popup.classList.add("hidden");
    overlay.classList.add("hidden");
};

// initialisation des tableaux
const initTable = (tableName, rows) => {
    window[tableName] = rows;
    updateTable(rows, tableName);
};

initTable("CustomersSell", rowsCustomersSell);
initTable("CustomersBuy", rowsCustomersBuy);
initTable("Vehicles", rowsVehicles);