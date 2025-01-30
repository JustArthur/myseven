let currentPageVehicule = 1,
    rowsPerPageVehicule = 10;

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
        .map(row => `
                <tr>
                    <td>${row.immatriculation}</td>
                    <td>${row.marque}</td>
                    <td>${row.model}</td>
                    <td>${row.puissance}</td>
                    <td>${row.type_boite}</td>
                    <td>${row.couleur}</td>
                    <td>${row.kilometrage}</td>
                    <td>
                        <input type="radio" name="selectedVehicule" value="${row.immatriculation}">
                    </td>
                </tr>
            `).join('');
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
        .map((row) => `
                <tr>
                    <td>${row.immatriculation}</td>
                    <td>${row.marque}</td>
                    <td>${row.model}</td>
                    <td>${row.puissance}</td>
                    <td>${row.type_boite}</td>
                    <td>${row.couleur}</td>
                    <td>${row.kilometrage}</td>
                    <td>
                        <input type="radio" name="selectedVehicule" value="${row.immatriculation}">
                    </td>
                </tr>
            `).join('');
    updatePaginationVehicule();
};

updateTableVehicule();