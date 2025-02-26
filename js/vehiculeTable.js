let currentPageVehicule = 1,
    rowsPerPageVehicule = 50;

if (rowsVehicules.length <= rowsPerPage) {
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

    const tbody = document.getElementById("vehiculeTableBody");
    tbody.innerHTML = filteredRowsVehicule
        .slice((currentPageVehicule - 1) * rowsPerPageVehicule, currentPageVehicule * rowsPerPageVehicule)
        .map(row => {
            const realIndex = rowsVehicules.findIndex(r => r.immatriculation === row.immatriculation);
            return `
                <tr data-index="${realIndex}">
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

const updatePaginationVehicule = () => {
    const pageInfo = document.getElementById('pageInfoVehicule');
    const totalPagesVehicules = Math.ceil(rowsVehicules.length / rowsPerPageVehicule);
    pageInfo.textContent = `Page ${currentPageVehicule} / ${totalPagesVehicules}`;
};

const prevPageVehicule = () => {
    if (currentPageVehicule > 1) {
        currentPageVehicule--;
        updateTableVehicule();
    }
};

const nextPageVehicule = () => {
    const totalPagesVehicules = Math.ceil(rowsVehicules.length / rowsPerPageVehicule);

    if (currentPageVehicule < totalPagesVehicules) {
        currentPageVehicule++;
        updateTableVehicule();
    }
};

const updateTableVehicule = () => {
    const tbody = document.getElementById("vehiculeTableBody");
    tbody.innerHTML = rowsVehicules
        .slice((currentPageVehicule - 1) * rowsPerPageVehicule, currentPageVehicule * rowsPerPageVehicule)
        .map((row, index) => `
                <tr>
                    <td ondblclick="editCellVehicle(this, 'immatriculation', ${index})">${row.immatriculation}</td>
                    <td ondblclick="editCellVehicle(this, 'marque', ${index})">${row.marque}</td>
                    <td ondblclick="editCellVehicle(this, 'model', ${index})">${row.model}</td>
                    <td ondblclick="editCellVehicle(this, 'puissance', ${index})">${row.puissance}</td>
                    <td ondblclick="editCellVehicle(this, 'type_boite', ${index})">${row.type_boite}</td>
                    <td ondblclick="editCellVehicle(this, 'couleur', ${index})">${row.couleur}</td>
                    <td ondblclick="editCellVehicle(this, 'kilometrage', ${index})">${row.kilometrage}</td>
                    <td>
                        <input type="radio" name="selectedVehicule" value="${row.immatriculation}">
                    </td>
                </tr>
            `).join('');
    updatePaginationVehicule();
};


const editCellVehicle = (td, field, index) => {
    const oldValue = td.innerText.trim();
    const input = document.createElement("input");
    input.type = "text";
    input.value = oldValue;
    input.classList.add("edit-input");

    td.innerHTML = "";
    td.appendChild(input);
    input.focus();
    input.select();

    const saveChanges = () => {
        const newValue = input.value.trim();

        if (newValue == "" || newValue === oldValue) {
            td.innerHTML = oldValue;
            return;
        }

        rowsVehicules[index][field] = newValue;
        updateDatabaseVehicle(rowsVehicules[index]);

        td.innerHTML = newValue;
    };

    input.addEventListener("blur", saveChanges);
    input.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            saveChanges();
        }
    });
};



const updateDatabaseVehicle = (vehicules) => {
    fetch("./php/tableEdit/update_vehicules.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(vehicules)
    })
};

updateTableVehicule();