export const fetchData = async (url, options = {}) => {
    const response = await fetch(url, options);
    if (!response.ok) {
        throw new Error(`Erreur de chargement des données: ${response.statusText}`);
    }
    return response.json();
};

export const showSpinner = (spinner) => {
    spinner.classList.remove("hidden");
};

export const hideSpinner = (spinner) => {
    spinner.classList.add("hidden");
};

export const debounce = (func, delay) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), delay);
    };
};
