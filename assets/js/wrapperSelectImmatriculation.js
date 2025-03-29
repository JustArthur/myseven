const wrapper_marque = document.querySelector(".wrapper.marque"),
    selectBtn_marque = wrapper_marque.querySelector(".select-btn"),
    searchInp_marque = wrapper_marque.querySelector("input"),
    options_marque = wrapper_marque.querySelector(".options");

let brands = [];

$.ajax({
    url: '../controllers/controllerImmatriculation.php',
    type: 'GET',
    success: function(data) {
        brands = JSON.parse(data);
        addbrands();
    }
});

function addbrands(selectedImmat) {
    options_marque.innerHTML = "";
    brands.forEach(brand => {
        let isSelected = brand == selectedImmat ? "selected" : "";
        let li = `<li onclick="updateName(this)" class="${isSelected}">${brand}</li>`;
        options_marque.insertAdjacentHTML("beforeend", li);
    });
}

function updateName(selectedLi) {
    searchInp_marque.value = "";
    addbrands(selectedLi.innerText);
    wrapper_marque.classList.remove("active");
    selectBtn_marque.firstElementChild.innerText = selectedLi.innerText;

    const marqueIdInput = document.getElementById("marque_id");
    marqueIdInput.value = selectedLi.innerText;
}

searchInp_marque.addEventListener("keyup", () => {
    let arr = [];
    let searchWord = searchInp_marque.value.toLowerCase();
    arr = brands.filter(data => {
        return data.toLowerCase().startsWith(searchWord);
    }).map(data => {
        let isSelected = data == selectBtn_marque.firstElementChild.innerText ? "selected" : "";
        return `<li onclick="updateName(this)" class="${isSelected}">${data}</li>`;
    }).join("");
    options_marque.innerHTML = arr ? arr : `<p style="margin-top: 10px;">Aucune immatriculation trouvé</p>`;
});

selectBtn_marque.addEventListener("click", () => wrapper_marque.classList.toggle("active"));

// Verification function
function verifySelection() {
    const selectedValue = selectBtn_marque.firstElementChild.innerText;
    if (!selectedValue || selectedValue === "Select an option") {
        alert("Please select an immatriculation.");
        return false;
    }
    return true;
}

document.getElementById("submit_btn").addEventListener("click", (e) => {
    if (!verifySelection()) {
        e.preventDefault()
    }
});