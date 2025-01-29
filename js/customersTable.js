let currentPage = 1,
    rowsPerPage = 50;

const searchCustomers = () => {
    const searchTerm = document.getElementById('searchBarCustomer').value.toLowerCase();
    const pagination = document.getElementById('paginationCustomer');

    let filteredRows;

    if (searchTerm === "") {
        filteredRows = rowsCustomers;
        pagination.classList.remove('invisible');
        pagination.classList.add('visible');

    } else {
        filteredRows = rowsCustomers.filter(row =>
            row.lastName.toLowerCase().includes(searchTerm) ||
            row.firstName.toLowerCase().includes(searchTerm) ||
            row.email.toLowerCase().includes(searchTerm) ||
            row.phone.toLowerCase().includes(searchTerm) ||
            row.adress.toLowerCase().includes(searchTerm) ||
            row.numCNI.toLowerCase().includes(searchTerm)
        );

        pagination.classList.remove('visible');
        pagination.classList.add('invisible');
    }

    const tbody = document.getElementById("customersTableBody");
    tbody.innerHTML = filteredRows
        .slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage)
        .map(row => `
                <tr>
                    <td>${row.lastName}</td>
                    <td>${row.firstName}</td>
                    <td>${row.email}</td>
                    <td>${row.phone}</td>
                    <td>${row.adress}</td>
                    <td>${row.numCNI}</td>
                </tr>
            `).join('');
    updatePagination();
};

const updatePagination = () => {
    const pageInfo = document.getElementById('pageInfoCustomer');
    const totalPages = Math.ceil(rowsCustomers.length / rowsPerPage);
    pageInfo.textContent = `Page ${currentPage} / ${totalPages}`;
};

const prevPage = () => {
    if (currentPage > 1) {
        currentPage--;
        updateTable();
    }
};

const nextPage = () => {
    const totalPages = Math.ceil(rowsCustomers.length / rowsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        updateTable();
    }
};

const updateTable = () => {
    const tbody = document.getElementById("customersTableBody");
    tbody.innerHTML = rowsCustomers
        .slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage)
        .map(row => `
                <tr>
                    <td>${row.lastName}</td>
                    <td>${row.firstName}</td>
                    <td>${row.email}</td>
                    <td>${row.phone}</td>
                    <td>${row.adress}</td>
                    <td>${row.numCNI}</td>
                </tr>
            `).join('');
    updatePagination();
};

updateTable();