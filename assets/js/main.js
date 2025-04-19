import { selectAgence, searchTable, changePage, editCell, updatePaginationControls, fetchDataAndUpdateTable } from './tables/tableOperations.js';
import { openPopup, closePopup, submitNote } from './tables/popupOperations.js';
import { showSpinner, hideSpinner } from './tables/utils.js';

window.selectAgence = selectAgence;
window.searchTable = searchTable;
window.changePage = changePage;
window.editCell = editCell;
window.updatePaginationControls = updatePaginationControls;
window.openPopup = openPopup;
window.closePopup = closePopup;
window.submitNote = submitNote;

document.addEventListener('DOMContentLoaded', async () => {
    const spinner = document.getElementById("loadingSpinner");
    showSpinner(spinner);

    await new Promise(resolve => setTimeout(resolve, 200));

    try {
        await Promise.all([
            fetchDataAndUpdateTable("CustomersSell", "clients", 1, "", "All", "Vendeur"),
            fetchDataAndUpdateTable("CustomersBuy", "clients", 1, "", "All", "Acheteur"),
            fetchDataAndUpdateTable("Vehicles", "vehicules", 1)
        ]);
    } catch (error) {
        console.error("Erreur pendant le chargement des données :", error);
    } finally {
        hideSpinner(spinner);
    }
});
