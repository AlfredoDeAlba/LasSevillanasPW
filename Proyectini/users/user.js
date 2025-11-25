document.addEventListener('DOMContentLoaded', function() {
    const loginBtn = document.getElementById('login-user-btn');
    const registerBtn = document.getElementById('register-user-btn');

    // Al hacer clic, redirigir a las páginas dedicadas
    if (loginBtn) {
        loginBtn.addEventListener('click', () => {
            window.location.href = './users/login.php';
        });
    }

    if (registerBtn) {
        registerBtn.addEventListener('click', () => {
            window.location.href = './users/register.php';
        });
    }
});