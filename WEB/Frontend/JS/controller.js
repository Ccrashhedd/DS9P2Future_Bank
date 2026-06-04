const app = document.querySelector('#app');
const headerContainer = document.querySelector('#appHeader');
const footerContainer = document.querySelector('#appFooter');

const routes = {
    home: './Views/home.html',
    login: './Views/login.html',
    postulacion: './Views/postulacion.html'
};

function getRouteFromHash() {
    const hash = window.location.hash.replace('#/', '').trim();
    return hash || 'home';
}

async function loadHtml(container, path) {
    try {
        const response = await fetch(path, { cache: 'no-cache' });

        if (!response.ok) {
            throw new Error(`No se pudo cargar: ${path}`);
        }

        container.innerHTML = await response.text();
    } catch (error) {
        container.innerHTML = `
            <section class="error-view section-shell">
                <div class="container error-card">
                    <h1>Error al cargar contenido</h1>
                    <p>${error.message}</p>
                    <p>Verifica que el proyecto se esté ejecutando desde un servidor local, como XAMPP, WAMP, Laragon o Live Server.</p>
                </div>
            </section>
        `;
    }
}

async function loadView() {
    const route = getRouteFromHash();
    const viewPath = routes[route] || routes.home;

    app.innerHTML = `
        <section class="loading-view">
            <div class="loader"></div>
            <p>Cargando vista...</p>
        </section>
    `;

    await loadHtml(app, viewPath);
    setActiveLink(routes[route] ? route : 'home');
    closeNavbarOnNavigation();
    bindPasswordToggles();
    bindDemoForms();
}

async function loadLayout() {
    await Promise.all([
        loadHtml(headerContainer, './Partials/header.html'),
        loadHtml(footerContainer, './Partials/footer.html')
    ]);

    setActiveLink(getRouteFromHash());
}

function setActiveLink(currentRoute) {
    document.querySelectorAll('[data-link]').forEach((link) => {
        const isActive = link.dataset.link === currentRoute;
        link.classList.toggle('active', isActive);
        if (link.classList.contains('nav-link')) {
            link.setAttribute('aria-current', isActive ? 'page' : 'false');
        }
    });
}

function closeNavbarOnNavigation() {
    if (!window.bootstrap) return;

    document.querySelectorAll('.navbar-collapse.show').forEach((navbar) => {
        const collapse = bootstrap.Collapse.getOrCreateInstance(navbar);
        collapse.hide();
    });
}

function bindPasswordToggles() {
    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);

            if (!input) return;

            const shouldShow = input.type === 'password';
            input.type = shouldShow ? 'text' : 'password';
            button.textContent = shouldShow ? 'Ocultar' : 'Ver';
        });
    });
}

function bindDemoForms() {
    document.querySelectorAll('.bank-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const action = form.getAttribute('action') || '';

            // Evita error visual mientras todavía no existen los controladores PHP.
            // Cuando programes el backend real, elimina este bloque o crea los archivos de action.
            if (action.includes('/controller/') && !form.dataset.backendReady) {
                event.preventDefault();
                showFormNotice(form, 'Formulario listo. Falta conectar este formulario con su controlador PHP.');
            }
        });
    });
}

function showFormNotice(form, message) {
    const oldNotice = form.querySelector('.form-notice');
    if (oldNotice) oldNotice.remove();

    const notice = document.createElement('div');
    notice.className = 'form-notice';
    notice.textContent = message;
    form.prepend(notice);
}

window.addEventListener('hashchange', loadView);

document.addEventListener('DOMContentLoaded', async () => {
    await loadLayout();
    await loadView();
});
