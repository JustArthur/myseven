let currentPage = 1,
    rowsPerPage = 50;
    editingCell = null

const initPagination = (tableName) => {
    const rows = window[tableName];
    const pagination = document.getElementById(`pagination${tableName}`);

    if (rows.length <= rowsPerPage) {
        pagination.classList.remove('visible');
        pagination.classList.add('invisible');
    } else {
        pagination.classList.remove('invisible');
        pagination.classList.add('visible');
    }
};

const searchTable = (tableName, searchBarId) => {
    const searchTerm = document.getElementById(searchBarId).value.toLowerCase();
    const pagination = document.getElementById(`pagination${tableName}`);

    let filteredRows;

    if (searchTerm === "") {
        filteredRows = window[tableName];
        pagination.classList.remove('invisible');
        pagination.classList.add('visible');
    } else {
        filteredRows = window[tableName].filter(row =>
            Object.values(row).some(value => value.toString().toLowerCase().includes(searchTerm))
        );
        pagination.classList.remove('visible');
        pagination.classList.add('invisible');
    }

    updateTable(filteredRows, tableName);
    updatePagination(tableName);
};

const updatePagination = (tableName) => {
    const pageInfo = document.getElementById(`pageInfo${tableName}`);
    const totalPages = Math.ceil(window[tableName].length / rowsPerPage);
    pageInfo.textContent = `Page ${currentPage} / ${totalPages}`;
};

const prevPage = (tableName) => {
    if (currentPage > 1) {
        currentPage--;
        updateTable(window[tableName], tableName);
    }
};

const nextPage = (tableName) => {
    const totalPages = Math.ceil(window[tableName].length / rowsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        updateTable(window[tableName], tableName);
    }
};

const updateTable = (rows, tableName) => {
    const tbody = document.getElementById(`${tableName}TableBody`);
    tbody.innerHTML = rows
        .slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage)
        .map((row) => {
            const uniqueKey = tableName === "Customers" ? "clients_email" : "vehicules_immatriculation";
            const realIndex = rows.findIndex(r => r[uniqueKey] === row[uniqueKey]);
            return `
                <tr data-index="${realIndex} onclick="selectRow(this, '${tableName}')">
                    ${Object.keys(row).map(field => {
                        if (field !== 'email') {
                            return `<td ondblclick="editCell(this, '${field}', ${realIndex}, '${tableName}')">${row[field]}</td>`;
                        } else {
                            return `<td ondblclick="editCell(this, '${field}', ${realIndex}, '${tableName}')">${row[field]}</td>`;
                        }
                    }).join('')}
                    <td class="btn_card" onclick="openCloseCard('${tableName}', ${realIndex})">Voir</td>
                    <td><input type="radio" name="selected${tableName}" value="${row[uniqueKey]}"></td>
                </tr>
            `;
        }).join('');
    updatePagination(tableName);
};

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

    const uniqueKey = tableName === "Customers" ? "clients_email" : "vehicules_immatriculation";
    const oldUniqueValue = window[tableName][index][uniqueKey];

    editingCell = td;
    input.focus();

    setTimeout(() => {
        input.select();
    }, 0);

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
    })

    input.addEventListener("blur", saveChanges);
    input.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            saveChanges();
        }
    });
};

const updateDatabase = (item, oldUniqueValue, tableName) => {
    fetch(`src/controllers/controller${tableName}.php`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ ...item, oldUniqueValue })
    });
};

const openCloseCard = (tableName, realIndex) => {
    const card = document.getElementById("cardItem");
    card.classList.toggle("hidden");

    if(card.classList.contains("hidden")) {
        return;
    } else {
        cardShow(tableName, realIndex);
    }
}

const cardShow = (tableName, realIndex) => {
    const cardItemContent = document.getElementById("cardItem_content");

    if(tableName === "Customers") {
        const customer = window[tableName][realIndex];
        cardItemContent.innerHTML = `
            <span onclick="openCloseCard(${tableName}, ${realIndex})" class="material-symbols-outlined">close</span>
            <h2>Informations du client</h2>
            <p><strong>Nom : </strong> ${customer.clients_nom}</p>
            <p><strong>Prénom : </strong> ${customer.clients_prenom}</p>
            <p><strong>Email : </strong> ${customer.clients_email}</p>
            <p><strong>Téléphone : </strong> ${customer.clients_telephone}</p>
            <p><strong>Adresse : </strong> ${customer.clients_rue}</p>
            <p><strong>Code postal : </strong> ${customer.clients_cp}</p>
            <p><strong>Ville : </strong> ${customer.clients_ville}</p>
            <p><strong>Numéro CNI : </strong> ${customer.clients_numero_cni}</p>
        `;
    } else {
        const vehicle = window[tableName][realIndex];
        cardItemContent.innerHTML = `
            <span onclick="openCloseCard(${tableName}, ${realIndex})" class="material-symbols-outlined">close</span>
            <h2>Informations du client</h2>
            <p><strong>Marque : </strong> ${vehicle.vehicules_marque}</p>
            <p><strong>Model : </strong> ${vehicle.vehicules_model}</p>
            <p><strong>Annee : </strong> ${vehicle.vehicules_annee}</p>
            <p><strong>Immatriculation : </strong> ${vehicle.vehicules_immatriculation}</p>
            <p><strong>Puissance : </strong> ${vehicle.vehicules_puissance}</p>
            <p><strong>Type boite : </strong> ${vehicle.vehicules_type_boite}</p>
            <p><strong>Couleur : </strong> ${vehicle.vehicules_couleur}</p>
            <p><strong>Finition : </strong> ${vehicle.vehicules_finition}</p>
        `;
    }
}

const initTable = (tableName, rows) => {
    window[tableName] = rows;
    initPagination(tableName);
    updateTable(rows, tableName);
};

initTable("Customers", rowsCustomers);
initTable("Vehicles", rowsVehicles);