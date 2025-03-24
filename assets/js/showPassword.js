const showPassword = () => {
    const password = document.getElementById('password');
    const showPassword = document.getElementById('showPassword');

    if (password.type === 'password') {
        password.type = 'text';
        showPassword.textContent = 'visibility_off';
    } else {
        password.type = 'password';
        showPassword.textContent = 'visibility';
    }
}