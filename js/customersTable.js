let currentPage = 1,
    rowsPerPage = 40;

if (rowsCustomers.length <= rowsPerPage) {
    const pagination = document.getElementById('paginationCustomer');
    pagination.classList.remove('visible');
    pagination.classList.add('invisible');
} else {
    const pagination = document.getElementById('paginationCustomer');
    pagination.classList.remove('invisible');
    pagination.classList.add('visible');
}

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
            row.nom.toLowerCase().includes(searchTerm) ||
            row.prenom.toLowerCase().includes(searchTerm) ||
            row.email.toLowerCase().includes(searchTerm) ||
            row.telephone.toLowerCase().includes(searchTerm) ||
            row.adresse.toLowerCase().includes(searchTerm) ||
            row.ville.toLowerCase().includes(searchTerm) ||
            row.cp.toLowerCase().includes(searchTerm) ||
            row.numero_cni.toLowerCase().includes(searchTerm)
        );

        pagination.classList.remove('visible');
        pagination.classList.add('invisible');
    }

    const tbody = document.getElementById("customersTableBody");
    tbody.innerHTML = filteredRows
        .slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage)
        .map(row => {
            const realIndex = rowsCustomers.findIndex(r => r.email === row.email);
            return `
                <tr data-index="${realIndex}">
                    <td ondblclick="editCellClient(this, 'nom', ${realIndex})">${row.nom}</td>
                    <td ondblclick="editCellClient(this, 'prenom', ${realIndex})">${row.prenom}</td>
                    <td ondblclick="editCellClient(this, 'email', ${realIndex})">${row.email}</td>
                    <td ondblclick="editCellClient(this, 'telephone', ${realIndex})">${row.telephone}</td>
                    <td ondblclick="editCellClient(this, 'adresse', ${realIndex})">${row.adresse}</td>
                    <td ondblclick="editCellClient(this, 'ville', ${realIndex})">${row.ville}</td>
                    <td ondblclick="editCellClient(this, 'cp', ${realIndex})">${row.cp}</td>
                    <td ondblclick="editCellClient(this, 'numero_cni', ${realIndex})">${row.numero_cni}</td>
                    <td>
                        <input type="radio" name="selectedCustomer" value="${row.email}">
                    </td>
                </tr>
            `;
        }).join('');
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
        .map(row => {
            const realIndex = rowsCustomers.findIndex(r => r.email === row.email);
            return `
                <tr data-index="${realIndex}">
                    <td ondblclick="editCellClient(this, 'nom', ${realIndex})">${row.nom}</td>
                    <td ondblclick="editCellClient(this, 'prenom', ${realIndex})">${row.prenom}</td>
                    <td ondblclick="editCellClient(this, 'email', ${realIndex})">${row.email}</td>
                    <td ondblclick="editCellClient(this, 'telephone', ${realIndex})">${row.telephone}</td>
                    <td ondblclick="editCellClient(this, 'adresse', ${realIndex})">${row.adresse}</td>
                    <td ondblclick="editCellClient(this, 'ville', ${realIndex})">${row.ville}</td>
                    <td ondblclick="editCellClient(this, 'cp', ${realIndex})">${row.cp}</td>
                    <td ondblclick="editCellClient(this, 'numero_cni', ${realIndex})">${row.numero_cni}</td>
                    <td>
                        <input type="radio" name="selectedCustomer" value="${row.email}">
                    </td>
                </tr>
            `;
        }).join('');
    updatePagination();
};

const editCellClient = (td, field, index) => {
    const oldValue = td.innerText.trim();
    const input = document.createElement("input");
    input.type = "text";
    input.value = oldValue;
    input.classList.add("edit-input");
    
    td.innerHTML = "";
    td.appendChild(input);
    input.focus();

    const saveChanges = () => {
        const newValue = input.value.trim();

        if (newValue === "" || newValue === oldValue) {
            td.innerHTML = oldValue;
            return;
        }

        rowsCustomers[index][field] = newValue;
        updateDatabaseClient(rowsCustomers[index]);
        
        td.innerHTML = newValue;
    };

    input.addEventListener("blur", saveChanges);
    input.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            saveChanges();
        }
    });
};


const updateDatabaseClient = (customer) => {
    fetch("./php/tableEdit/update_customer.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(customer)
    })
};

updateTable();