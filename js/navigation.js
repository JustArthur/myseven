const buttons = document.querySelectorAll('.tab-button');
const contents = document.querySelectorAll('.content');

const savedTab = localStorage.getItem('activeTab');

if (savedTab) {
    document.getElementById(savedTab)?.classList.add('active');
    document.querySelector(`[data-tab="${savedTab}"]`)?.classList.add('active');
} else {
    buttons[0].classList.add('active');
    contents[0].classList.add('active');
}

buttons.forEach(button => {
    button.addEventListener('click', () => {
        buttons.forEach(btn => btn.classList.remove('active'));
        contents.forEach(content => content.classList.remove('active'));

        button.classList.add('active');

        const tabId = button.getAttribute('data-tab');
        document.getElementById(tabId).classList.add('active');

        localStorage.setItem('activeTab', tabId);
    });
});
