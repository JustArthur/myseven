let currentPageVehicule = 1,
    rowsPerPageVehicule = 50;

if (rowsVehicules.length >= rowsPerPage) {
    const pagination = document.getElementById('paginationCustomer');
    pagination.classList.remove('visible');
    pagination.classList.add('invisible');
} else {
    const pagination = document.getElementById('paginationCustomer');
    pagination.classList.remove('invisible');
    pagination.classList.add('visible');
}

const searchVehicule = () => {
    const searchTerm = document.getElementById('searchBarVehicule').value.toLowerCase();
    const pagination = document.getElementById('paginationVehicule');

    let filteredRowsVehicule;

    if (searchTerm === "") {
        filteredRowsVehicule = rowsVehicules;
        pagination.classList.remove('invisible');
        pagination.classList.add('visible');
    } else {
        filteredRowsVehicule = rowsVehicules.filter(row =>
            row.immatriculation.toLowerCase().includes(searchTerm) ||
            row.marque.toLowerCase().includes(searchTerm) ||
            row.model.toLowerCase().includes(searchTerm) ||
            row.puissance.toLowerCase().includes(searchTerm) ||
            row.type_boite.toLowerCase().includes(searchTerm) ||
            row.couleur.toLowerCase().includes(searchTerm) ||
            row.kilometrage.toLowerCase().includes(searchTerm)
        );

        pagination.classList.remove('visible');
        pagination.classList.add('invisible');
    }

    updateTableVehicule(filteredRowsVehicule);
    updatePaginationVehicule();
};

const updatePaginationVehicule = () => {
    const pageInfo = document.getElementById('pageInfoVehicule');
    const totalPagesVehicules = Math.ceil(rowsVehicules.length / rowsPerPageVehicule);
    pageInfo.textContent = `Page ${currentPageVehicule} / ${totalPagesVehicules}`;
};

const prevPageVehicule = () => {
    if (currentPageVehicule > 1) {
        currentPageVehicule--;
        updateTableVehicule(rowsVehicules);
    }
};

const nextPageVehicule = () => {
    const totalPagesVehicules = Math.ceil(rowsVehicules.length / rowsPerPageVehicule);

    if (currentPageVehicule < totalPagesVehicules) {
        currentPageVehicule++;
        updateTableVehicule(rowsVehicules);
    }
};

const updateTableVehicule = (tabRowsVehicule) => {
    const tbody = document.getElementById("vehiculeTableBody");
    tbody.innerHTML = tabRowsVehicule
        .slice((currentPageVehicule - 1) * rowsPerPageVehicule, currentPageVehicule * rowsPerPageVehicule)
        .map(row => {
            const realIndex = rowsVehicules.findIndex(r => r.immatriculation === row.immatriculation);
            return `
                <tr>
                    <td ondblclick="editCellVehicle(this, 'immatriculation', ${realIndex})">${row.immatriculation}</td>
                    <td ondblclick="editCellVehicle(this, 'marque', ${realIndex})">${row.marque}</td>
                    <td ondblclick="editCellVehicle(this, 'model', ${realIndex})">${row.model}</td>
                    <td ondblclick="editCellVehicle(this, 'puissance', ${realIndex})">${row.puissance}</td>
                    <td ondblclick="editCellVehicle(this, 'type_boite', ${realIndex})">${row.type_boite}</td>
                    <td ondblclick="editCellVehicle(this, 'couleur', ${realIndex})">${row.couleur}</td>
                    <td ondblclick="editCellVehicle(this, 'kilometrage', ${realIndex})">${row.kilometrage}</td>
                    <td>
                        <input type="radio" name="selectedVehicule" value="${row.immatriculation}">
                    </td>
                </tr>
            `;
            }).join('');
    updatePaginationVehicule();
};


const editCellVehicle = (td, field, index) => {
    const oldValue = td.innerText.trim();

    let input;
    
    if (field === "type_boite") {
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
        input.focus();
        input.select();
    }

    td.innerHTML = "";
    td.appendChild(input);
    const oldImmat = rowsVehicules[index].immatriculation;

    const validateInputVehicule = (field, value) => {
        if (value === "") return false;

        const numberRegex = /^[0-9]+$/;

        const immatExist = rowsVehicules.some(vehicule => vehicule.immatriculation === value && vehicule.immatriculation !== oldImmat);
        if (immatExist) {
            input.removeEventListener("blur", saveChanges);
            alert("Cet email est déjà utilisé. Veuillez en choisir un autre.");
            return false;
        }

        if (["kilometrage", "puissance"].includes(field) && !numberRegex.test(value)) {
            input.removeEventListener("blur", saveChanges);
            alert("Ce champ ne peut contenir que des chiffres.");
            return false;
        }

        return true;
    };

    const saveChanges = () => {
        const newValue = input.value.trim();

        if (newValue == "" || newValue === oldValue) {
            td.innerHTML = oldValue;
            return;
        }

        if (!validateInputVehicule(field, newValue)) {
            td.innerHTML = oldValue;
            return;
        }

        rowsVehicules[index][field] = newValue;
        updateDatabaseVehicle(rowsVehicules[index], oldImmat);

        td.innerHTML = newValue;
    };

    input.addEventListener("blur", saveChanges);
    
    if (field === "type_boite") {
        input.addEventListener("change", saveChanges);
    } else {
        input.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                saveChanges();
            }
        });
    }
};



const updateDatabaseVehicle = (vehicules, oldImmat) => {
    fetch("./php/tableEdit/update_vehicules.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ ...vehicules, oldImmat })
    })
};

updateTableVehicule(rowsVehicules);