import { fetchData, showSpinner, hideSpinner } from './utils.js';

let currentPage = 1;
let totalPages = 1;
let editingCell = null;
let searchTimeout;
let selectedAgenceId = "All";

export const selectAgence = (tableName, agenceSelectId, sqlTableName, clientType) => {
    selectedAgenceId = document.getElementById(agenceSelectId).value;
    fetchDataAndUpdateTable(tableName, sqlTableName, 1, "", selectedAgenceId, clientType);
};

export const searchTable = (tableNameSQL, searchBarId) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        const spinner = document.getElementById("loadingSpinner");
        showSpinner(spinner);

        const searchTerm = document.getElementById(searchBarId).value.toLowerCase();
        const tableName = getTableName(tableNameSQL);
        currentPage = 1;

        try {
            const data = await fetchData(`src/functions/search.php?table=${tableNameSQL}&term=${encodeURIComponent(searchTerm)}&page=${currentPage}`);
            updateTable(data.results, tableName);
            totalPages = data.totalPages;
            updatePaginationControls(tableName);
        } finally {
            hideSpinner(spinner);
        }
    }, 500);
};

const getTableName = (tableNameSQL) => {
    switch (tableNameSQL) {
        case "vehicules": return "Vehicles";
        case "clientsVendeur": return "CustomersSell";
        default: return "CustomersBuy";
    }
};

export const updateTable = (rows, tableName) => {
    const tbody = document.getElementById(`${tableName}TableBody`);

    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="100%" style="text-align: center;">Aucune donnée trouvée</td></tr>`;
        currentPage = 0;
        return;
    }

    const uniqueKey = tableName === "Vehicles" ? "vehicules_immatriculation" : "clients_email";
    const typeValue = tableName === "Vehicles" ? "selectedVehicles" : "selectedCustomers";
    const idKey = tableName === "Vehicles" ? "vehicules_id" : "clients_id";
    const agenceKey = tableName === "Vehicles" ? "vehicules_agence_id" : "clients_agence_id";

    window[tableName] = rows;

    const fieldsToDisplay = rows.length > 0
        ? Object.keys(rows[0]).filter(field => ![idKey, agenceKey].includes(field))
        : [];

    tbody.innerHTML = rows.map((row) => {
        const realIndex = row[idKey];
        const agenceValue = row[agenceKey];

        return `
            <tr data-index="${realIndex}" data-real-index="${realIndex}" onclick="selectRow(this, '${tableName}')">
                ${fieldsToDisplay.map(field => {
                    const cellValue = row[field] ?? '';
                    return `<td ondblclick="editCell(this, '${field}', ${realIndex}, '${tableName}')">${cellValue}</td>`;
                }).join('')}
                <td class="btn_card" onclick="openPopup('${tableName}', ${realIndex})">Voir</td>
                <td><input type="radio" name="${typeValue}" value="${row[uniqueKey]}"></td>
                <td><input type="text" value="${agenceValue}" hidden></td>
            </tr>
        `;
    }).join('');

    updatePaginationControls(tableName);
};

export const updatePaginationControls = (tableName) => {
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

    const sqlTableName = tableName === "Vehicles" ? "vehicules" : "clients";

    if (currentPage > 1) {
        paginationContainer.appendChild(createPageButton(currentPage - 1, "Page précédente", sqlTableName));
    }

    if (currentPage < totalPages) {
        paginationContainer.appendChild(createPageButton(currentPage + 1, "Page suivante", sqlTableName));
    }

    paginationCounter.innerHTML = `Page ${currentPage} sur ${totalPages}`;
};

export const changePage = (tableName, sqlTableName, page) => {
    const typeClient = tableName === "Vehicles" ? "" : tableName === "CustomersSell" ? "Vendeur" : "Acheteur";
    fetchDataAndUpdateTable(tableName, sqlTableName, page, "", selectedAgenceId, typeClient);
};

export const editCell = (td, field, id, tableName) => {
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

export const fetchDataAndUpdateTable = async (tableName, sqlTableName, page = 1, term = "", agenceId = selectedAgenceId, clientType = "") => {
    currentPage = page;

    const spinner = document.getElementById("loadingSpinner");
    showSpinner(spinner);

    const params = new URLSearchParams({
        table: sqlTableName,
        page: page,
        term: term,
        agence: agenceId,
    });

    if (clientType) {
        params.append("client_type", clientType);
    }

    try {
        const data = await fetchData(`src/functions/fetchTable.php?${params.toString()}`);
        if (data.rows && Array.isArray(data.rows)) {
            window[tableName] = data.rows;
            totalPages = data.totalPages;
            updateTable(data.rows, tableName);
        } else {
            console.error(`Données invalides pour ${tableName}`, data);
        }
    } catch (err) {
        console.error("Erreur de chargement des données:", err);
    } finally {
        hideSpinner(spinner);
    }
};