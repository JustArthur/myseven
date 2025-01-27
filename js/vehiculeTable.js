let currentPageVehicule = 1,
    rowsPerPageVehicule = 50;

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
            row.kilometrage.toLowerCase().includes(searchTerm)
        );

        pagination.classList.remove('visible');
        pagination.classList.add('invisible');
    }

    const tbody = document.getElementById("vehiculeTableBody");
    tbody.innerHTML = filteredRowsVehicule
        .slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage)
        .map(row => `
                <tr>
                    <td>${row.immatriculation}</td>
                    <td>${row.marque}</td>
                    <td>${row.model}</td>
                    <td>${row.kilometrage}</td>
                </tr>
            `).join('');
    updatePaginationVehicule();
};

const updatePaginationVehicule = () => {
    const pageInfo = document.getElementById('pageInfoVehicule');
    const totalPages = Math.ceil(rowsVehicules.length / rowsPerPage);
    pageInfo.textContent = `Page ${currentPage} / ${totalPages}`;
};

const prevPageVehicule = () => {
    if (currentPage > 1) {
        currentPage--;
        updateTable();
    }
};

const nextPageVehicule = () => {
    const totalPages = Math.ceil(rowsVehicules.length / rowsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        updateTable();
    }
};

const updateTableVehicule = () => {
    const tbody = document.getElementById("vehiculeTableBody");
    tbody.innerHTML = rowsVehicules
        .slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage)
        .map(row => `
                <tr>
                    <td>${row.immatriculation}</td>
                    <td>${row.marque}</td>
                    <td>${row.model}</td>
                    <td>${row.kilometrage}</td>
                </tr>
            `).join('');
    updatePaginationVehicule();
};

updateTableVehicule();