const app = document.querySelector('#app');
const headerContainer = document.querySelector('#appHeader');
const footerContainer = document.querySelector('#appFooter');
const notificationContainer = document.querySelector('#appNotifications');
const backendControllerPath = '../Backend/PHP/controller/controller.php';

const routes = {
    home: './Views/home.php',
    login: './Views/login.html',
    postulacion: './Views/postulacion.html',
    postulacionusuario: './Views/userPostulacion.html'
};

let catalogosCache = null;
let documentTemplateCache = null;
let documentIndex = 0;

function getRouteFromHash() {
    const hash = window.location.hash.replace('#/', '').trim();
    const route = hash.split('?')[0].split('&')[0].trim();
    return route || 'home';
}

function getHashParams() {
    const rawHash = window.location.hash.replace('#/', '').trim();
    const query = rawHash.split('?')[1] || '';

    return new URLSearchParams(query);
}

async function loadHtml(container, path) {
    try {
        const response = await fetch(path, { cache: 'no-cache', credentials: 'same-origin' });

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
                    <p>Verifica que el proyecto se esté ejecutando desde un servidor local con PHP, como XAMPP, WAMP o Laragon.</p>
                </div>
            </section>
        `;
    }
}

async function loadView() {
    const route = getRouteFromHash();

    if (route === 'logout') {
        await handleLogout();
        return;
    }

    const viewPath = routes[route] || routes.home;
    const hashParams = getHashParams();

    app.innerHTML = `
        <section class="loading-view">
            <div class="loader"></div>
            <p>Cargando vista...</p>
        </section>
    `;

    await loadHtml(app, viewPath);
    showRouteFeedback(route, hashParams);
    setActiveLink(routes[route] ? route : 'home');
    closeNavbarOnNavigation();
    bindPasswordToggles();
    bindBackendForms();
}

function showRouteFeedback(route, hashParams) {
    if (route !== 'login' && route !== 'postulacionusuario') return;

    const message = hashParams.get('error') || hashParams.get('success');
    if (!message) return;

    const type = hashParams.has('error') ? 'error' : 'success';
    showNotification(message, type, { title: type === 'error' ? 'Error' : 'Listo' });
}

function showNotification(message, type = 'info', options = {}) {
    if (!notificationContainer) return null;

    const notification = document.createElement('article');
    notification.className = `app-notification is-${type}`;
    notification.setAttribute('role', type === 'error' ? 'alert' : 'status');

    const title = options.title || (type === 'error' ? 'Error' : type === 'success' ? 'Hecho' : 'Aviso');

    notification.innerHTML = `
        <div class="app-notification__body">
            <div class="app-notification__title">${escapeHtml(title)}</div>
            <div class="app-notification__message">${escapeHtml(message)}</div>
        </div>
        <button type="button" class="app-notification__close" aria-label="Cerrar notificación">×</button>
    `;

    const closeButton = notification.querySelector('.app-notification__close');
    const removeNotification = () => {
        notification.remove();
    };

    closeButton?.addEventListener('click', removeNotification);
    notificationContainer.prepend(notification);

    if (type !== 'error') {
        window.setTimeout(() => {
            if (notification.isConnected) {
                notification.remove();
            }
        }, options.timeout ?? 4500);
    }

    return notification;
}

async function loadLayout() {
    await Promise.all([
        loadHtml(headerContainer, './Partials/header.php'),
        loadHtml(footerContainer, './Partials/footer.html')
    ]);

    setActiveLink(getRouteFromHash());
    bindHeaderActions();
}

function bindHeaderActions() {
    document.querySelectorAll('[data-action="logout"]').forEach((link) => {
        if (link.dataset.bound === 'true') return;
        link.dataset.bound = 'true';

        link.addEventListener('click', async (event) => {
            event.preventDefault();
            await handleLogout();
        });
    });
}

async function handleLogout() {
    try {
        await fetch(`${backendControllerPath}?action=logout`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        showNotification('Sesión cerrada correctamente.', 'success', { title: 'Sesión' });
    } catch (error) {
        showNotification('No se pudo cerrar la sesión.', 'error', { title: 'Sesión' });
    }

    catalogosCache = null;
    window.location.hash = '#/login';
    await loadLayout();
    await loadView();
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
        if (button.dataset.bound === 'true') return;
        button.dataset.bound = 'true';

        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);
            if (!input) return;

            const shouldShow = input.type === 'password';
            input.type = shouldShow ? 'text' : 'password';
            button.textContent = shouldShow ? 'Ocultar' : 'Ver';
        });
    });
}

function bindBackendForms() {
    document.querySelectorAll('.bank-form').forEach((form) => {
        if (form.dataset.bound === 'true') return;
        form.dataset.bound = 'true';

        if (form.id === 'iniciaSesionForm') {
            setupLoginForm(form);
            return;
        }

        if (form.id === 'registrarUserForm') {
            setupRegisterForm(form);
            return;
        }

        if (form.id === 'postulacionForm') {
            setupPostulacionForm(form);
        }
    });
}

function setupLoginForm(form) {
    form.action = backendControllerPath;
    form.method = 'post';

    const actionField = ensureHiddenField(form, 'action');
    const usernameField = ensureHiddenField(form, 'username');
    const passwordField = ensureHiddenField(form, 'password');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        actionField.value = 'login';
        usernameField.value = form.querySelector('#inputUser')?.value.trim() || '';
        passwordField.value = form.querySelector('#inputPassword')?.value || '';

        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Entrando...';

        try {
            const response = await fetch(backendControllerPath, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });

            const result = await readJsonResponse(response);

            if (!response.ok || !result.ok) {
                showNotification(result.message || 'No se pudo iniciar sesión.', 'error', { title: 'Inicio de sesión' });
                showFormNotice(form, result.message || 'No se pudo iniciar sesión.', 'error');
                return;
            }

            showNotification(result.message || 'Login exitoso.', 'success', { title: 'Inicio de sesión' });
            showFormNotice(form, result.message || 'Login exitoso.', 'success');
            catalogosCache = null;
            window.location.hash = '#/home';
            await loadLayout();
            await loadView();
        } catch (error) {
            showNotification('No se pudo iniciar sesión. Revisa la conexión con la base de datos o el archivo bd.php.', 'error', { title: 'Inicio de sesión' });
            showFormNotice(form, 'No se pudo iniciar sesión. Revisa la conexión con la base de datos o el archivo bd.php.', 'error');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Iniciar sesión';
        }
    });
}

function setupRegisterForm(form) {
    form.action = backendControllerPath;
    form.method = 'post';

    const actionField = ensureHiddenField(form, 'action');
    const nombreUsuarioField = ensureHiddenField(form, 'nombreUsuario');
    const usernameField = ensureHiddenField(form, 'username');
    const correoField = ensureHiddenField(form, 'correo');
    const passwordField = ensureHiddenField(form, 'password');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const usernameValue = form.querySelector('#inputUserReg')?.value.trim() || '';

        actionField.value = 'register';
        nombreUsuarioField.value = usernameValue;
        usernameField.value = usernameValue;
        correoField.value = form.querySelector('#inputEmailReg')?.value.trim() || '';
        passwordField.value = form.querySelector('#inputPasswordReg')?.value || '';

        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Creando...';

        try {
            const response = await fetch(backendControllerPath, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });

            const result = await readJsonResponse(response);

            if (!response.ok || !result.ok) {
                showNotification(result.message || 'No se pudo registrar el usuario.', 'error', { title: 'Registro' });
                showFormNotice(form, result.message || 'No se pudo registrar el usuario.', 'error');
                return;
            }

            showNotification(result.message || 'Registro exitoso.', 'success', { title: 'Registro' });
            showFormNotice(form, result.message || 'Registro exitoso.', 'success');
            form.reset();
        } catch (error) {
            showNotification('No se pudo registrar el usuario. Revisa la conexión con la base de datos o el archivo bd.php.', 'error', { title: 'Registro' });
            showFormNotice(form, 'No se pudo registrar el usuario. Revisa la conexión con la base de datos o el archivo bd.php.', 'error');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Crear cuenta';
        }
    });
}

async function setupPostulacionForm(form) {
    form.action = backendControllerPath;
    form.method = 'post';
    form.enctype = 'multipart/form-data';
    documentIndex = 0;

    bindNumericInputs(form);
    bindApellidoCasada(form);

    const documentsList = form.querySelector('[data-documents-list]');
    const addButton = form.querySelector('[data-add-document]');
    let catalogos = {};
    let data = { postulante: null, documentos: [] };

    try {
        catalogos = await getCatalogos(true);
        fillCatalogSelects(form, catalogos);
        bindLocationSelects(form, catalogos);
        showCatalogStatus(form, catalogos);
    } catch (error) {
        showFormNotice(form, error.message || 'No se pudieron cargar los catálogos desde la base de datos.', 'error');
    }

    addButton?.addEventListener('click', () => {
        createDocumentCard(form, null, catalogos);
        applyReadonlyMode(form, false);
        renumberDocumentCards(form);
    });

    try {
        data = await getMiPostulacion();
        populatePostulacion(form, data?.postulante || null, catalogos);
    } catch (error) {
        if (form.dataset.mode === 'view') {
            showFormNotice(form, error.message || 'No se pudo cargar la postulación guardada.', 'error');
        }
    }

    if (Array.isArray(data?.documentos) && data.documentos.length > 0) {
        data.documentos.forEach((documento) => createDocumentCard(form, documento, catalogos));
    } else {
        createDocumentCard(form, null, catalogos);
    }

    if (!documentsList?.children.length) {
        createDocumentCard(form, null, catalogos);
    }

    renumberDocumentCards(form);

    if (form.dataset.mode === 'view') {
        applyReadonlyMode(form, true);
        setupEditToggle(form);
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (form.classList.contains('is-readonly')) {
            showFormNotice(form, 'Activa la edición antes de guardar cambios.', 'info');
            return;
        }

        const errors = validatePostulacionForm(form);
        if (errors.length > 0) {
            showFormNotice(form, errors, 'error');
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Guardando...';

        try {
            const formData = new FormData(form);
            formData.set('action', 'guardar_postulacion');

            const response = await fetch(backendControllerPath, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });

            const result = await readJsonResponse(response);

            if (!response.ok || !result.ok) {
                showNotification(Array.isArray(result.errors) && result.errors.length ? result.errors[0] : (result.message || 'No se pudo guardar la postulación.'), 'error', { title: 'Postulación' });
                showFormNotice(form, result.errors?.length ? result.errors : result.message, 'error');
                return;
            }

            showNotification(result.message || 'Postulación guardada correctamente.', 'success', { title: 'Postulación' });
            showFormNotice(form, result.message || 'Postulación guardada correctamente.', 'success');

            const refreshed = await getMiPostulacion(true);
            const catalogos = await getCatalogos(true);
            form.querySelector('[data-documents-list]').innerHTML = '';
            documentIndex = 0;
            fillCatalogSelects(form, catalogos);
            populatePostulacion(form, refreshed?.postulante || null, catalogos);
            (refreshed?.documentos || []).forEach((documento) => createDocumentCard(form, documento, catalogos));
            renumberDocumentCards(form);

            if (form.dataset.mode === 'view') {
                applyReadonlyMode(form, true);
                const editButton = form.querySelector('[data-edit-toggle]');
                if (editButton) editButton.textContent = 'Editar';
            }
        } catch (error) {
            showFormNotice(form, 'No se pudo enviar la postulación. Revisa tu conexión o la configuración de PHP.', 'error');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = form.dataset.mode === 'view' ? 'Guardar cambios' : 'Guardar postulación';
        }
    });
}

async function readJsonResponse(response) {
    const text = await response.text();

    try {
        return JSON.parse(text);
    } catch (error) {
        const resumen = text.replace(/\s+/g, ' ').slice(0, 250);
        throw new Error(resumen || 'El servidor no devolvió una respuesta JSON válida.');
    }
}

async function getCatalogos(force = false) {
    if (catalogosCache && !force) return catalogosCache;

    const response = await fetch(`${backendControllerPath}?action=catalogos`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    });

    const result = await readJsonResponse(response);
    if (!response.ok || !result.ok) {
        throw new Error(result.message || 'No se pudieron cargar los catálogos desde la base de datos.');
    }

    catalogosCache = result.data;
    return catalogosCache;
}

async function getMiPostulacion(force = false) {
    if (force) {
        // Solo fuerza la petición. No se guarda caché porque la postulación cambia con frecuencia.
    }

    const response = await fetch(`${backendControllerPath}?action=mi_postulacion`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    });

    const result = await readJsonResponse(response);
    if (!response.ok || !result.ok) {
        throw new Error(result.message || 'No se pudo cargar la postulación.');
    }

    return result.data;
}

function fillCatalogSelects(scope, catalogos) {
    scope.querySelectorAll('select[data-catalog]').forEach((select) => {
        const catalogName = select.dataset.catalog;
        const valueKey = select.dataset.value;
        const labelKey = select.dataset.label;
        const currentValue = String(select.value || '');
        const placeholder = select.querySelector('option[value=""]')?.outerHTML || '<option value="" disabled selected>Seleccione</option>';
        const items = Array.isArray(catalogos[catalogName]) ? catalogos[catalogName] : [];

        select.innerHTML = placeholder;
        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = String(item[valueKey] ?? '');
            option.textContent = String(item[labelKey] ?? '');
            select.appendChild(option);
        });

        if (currentValue && [...select.options].some((option) => option.value === currentValue)) {
            select.value = currentValue;
        }
    });
}

function showCatalogStatus(form, catalogos) {
    const requiredCatalogs = ['provincias', 'provinciasCedula', 'estadosCiviles', 'rangosAcademicos', 'tiposSangre', 'gradosDocumento', 'instituciones'];
    const missing = requiredCatalogs.filter((key) => !Array.isArray(catalogos[key]) || catalogos[key].length === 0);

    if (missing.length > 0) {
        showFormNotice(form, `No se encontraron datos en estos catálogos de la base de datos: ${missing.join(', ')}. Revisa que hayas importado ambos SQL finales.`, 'error');
    }
}

function bindLocationSelects(form, catalogos) {
    const provincia = form.querySelector('#slctProvincia');
    const distrito = form.querySelector('#slctDistrito');
    const corregimiento = form.querySelector('#slctCorregimiento');

    provincia?.addEventListener('change', () => {
        fillDistritos(distrito, catalogos, provincia.value);
        fillCorregimientos(corregimiento, catalogos, '');
    });

    distrito?.addEventListener('change', () => {
        fillCorregimientos(corregimiento, catalogos, distrito.value);
    });
}

function fillDistritos(select, catalogos, provinciaCodigo, selected = '') {
    if (!select) return;

    select.innerHTML = '<option value="" disabled selected>Seleccione</option>';
    (catalogos.distritos || [])
        .filter((item) => String(item.codigo_provincia) === String(provinciaCodigo))
        .forEach((item) => {
            const option = document.createElement('option');
            option.value = String(item.codigo_distrito);
            option.textContent = String(item.nombre_distrito);
            select.appendChild(option);
        });

    if (selected) select.value = String(selected);
}

function fillCorregimientos(select, catalogos, distritoCodigo, selected = '') {
    if (!select) return;

    select.innerHTML = '<option value="" disabled selected>Seleccione</option>';
    (catalogos.corregimientos || [])
        .filter((item) => String(item.codigo_distrito) === String(distritoCodigo))
        .forEach((item) => {
            const option = document.createElement('option');
            option.value = String(item.codigo_corregimiento);
            option.textContent = String(item.nombre_corregimiento);
            select.appendChild(option);
        });

    if (selected) select.value = String(selected);
}

function populatePostulacion(form, postulante, catalogos) {
    if (!postulante) return;

    const mapping = {
        slctNivelEstudios: 'rangoAcademico',
        slctprovinciaCedula: 'prefijo',
        tomo: 'tomo',
        asiento: 'asiento',
        nombre: 'nombre',
        nombre2: 'nombre2',
        apellido: 'apellido',
        apellido2: 'apellido2',
        sexo: 'genero',
        fechaNacimiento: 'fechaNacimiento',
        slctEstadoCivil: 'estadoCivil',
        slctTipoSangre: 'tipoSangre',
        slctpreguntaApelCasada: 'usaCasada',
        apellidoCasada: 'apelCasada',
        comunidad: 'comunidad',
        calle: 'calle',
        casa: 'casa',
        detalleUbicacion: 'detalleDireccion',
        correoElectronico: 'correoPostulante',
        telefono: 'telefono',
        telefono2: 'telefono2',
        celular: 'celular',
        celular2: 'celular2'
    };

    Object.entries(mapping).forEach(([fieldId, key]) => {
        const field = form.querySelector(`#${fieldId}`);
        if (field && postulante[key] !== null && postulante[key] !== undefined) {
            field.value = postulante[key];
        }
    });

    const provincia = form.querySelector('#slctProvincia');
    const distrito = form.querySelector('#slctDistrito');
    const corregimiento = form.querySelector('#slctCorregimiento');

    if (provincia) provincia.value = postulante.codigo_provincia || '';
    fillDistritos(distrito, catalogos, postulante.codigo_provincia || '', postulante.codigo_distrito || '');
    fillCorregimientos(corregimiento, catalogos, postulante.codigo_distrito || '', postulante.codigo_corregimiento || '');
    toggleApellidoCasada(form);
}

async function getDocumentTemplate() {
    if (documentTemplateCache) return documentTemplateCache;

    try {
        const response = await fetch('./Views/formuDocumentos.html', { cache: 'no-cache' });
        const html = await response.text();
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        documentTemplateCache = wrapper.querySelector('#documentoTemplate');
    } catch (error) {
        documentTemplateCache = null;
    }

    return documentTemplateCache;
}

function createDocumentCard(form, data = null, catalogos = {}) {
    const list = form.querySelector('[data-documents-list]');
    if (!list) return;

    const index = documentIndex++;
    const number = list.querySelectorAll('[data-document-card]').length + 1;
    const templateHtml = `
        <article class="document-card document-subform" data-document-card>
            <input type="hidden" data-doc-field="idDocumentoPostulante">
            <div class="document-card-header">
                <div>
                    <span class="document-kicker">Formulario de documento <strong data-document-number>${number}</strong></span>
                    <h3 data-document-title>Documento académico</h3>
                    <p>Cada bloque registra un PDF independiente con su información académica.</p>
                </div>
                <button type="button" class="btn-remove-document" data-remove-document>Quitar formulario</button>
            </div>
            <div class="form-grid two-columns document-form-grid">
                <div class="form-group full-row">
                    <label>Archivo PDF</label>
                    <input type="file" accept=".pdf,application/pdf" data-doc-field="archivoDocumento">
                    <small data-current-file>Solo PDF. Tamaño máximo: 5 MB.</small>
                </div>
                <div class="form-group full-row">
                    <label>Título del documento</label>
                    <input type="text" maxlength="100" required data-doc-field="tituloDocumento" placeholder="Ej. Certificado de seminario de atención al cliente">
                </div>
                <div class="form-group">
                    <label>Tipo / grado</label>
                    <select required data-doc-field="slctGradoEstudio" data-catalog="gradosDocumento" data-value="idGradoEst" data-label="nombreGradoEst"><option value="" disabled selected>Seleccione</option></select>
                </div>
                <div class="form-group">
                    <label>Institución educativa</label>
                    <select required data-doc-field="slctInstitucionEducativa" data-catalog="instituciones" data-value="idInstitucion" data-label="nombreInstitucion"><option value="" disabled selected>Seleccione</option></select>
                </div>
                <div class="form-group">
                    <label>Otra institución</label>
                    <input type="text" maxlength="250" disabled data-doc-field="nombreOtraInstitucion" placeholder="Solo si seleccionas Otra institución">
                </div>
                <div class="form-group">
                    <label>Provincia del documento</label>
                    <select required data-doc-field="provinciaDocumento" data-catalog="provincias" data-value="codigo_provincia" data-label="nombre_provincia"><option value="" disabled selected>Seleccione</option></select>
                </div>
                <div class="form-group">
                    <label>Total de horas</label>
                    <input type="number" min="40" step="1" required data-doc-field="horasTotales" placeholder="Mínimo 40">
                </div>
                <div class="form-group">
                    <label>Fecha de inicio</label>
                    <input type="date" required data-doc-field="fechaInicioEstudios">
                </div>
                <div class="form-group">
                    <label>Fecha de finalización</label>
                    <input type="date" required data-doc-field="fechaFinEstudios">
                </div>
                <div class="form-group">
                    <label>Fecha de emisión</label>
                    <input type="date" required data-doc-field="fechaEmision">
                </div>
            </div>
        </article>
    `;

    const wrapper = document.createElement('div');
    wrapper.innerHTML = templateHtml.trim();
    const card = wrapper.firstElementChild;

    card.querySelectorAll('[data-doc-field]').forEach((field) => {
        const fieldName = field.dataset.docField;
        field.name = `documentos[${index}][${fieldName}]`;
        if (field.type !== 'hidden') {
            field.id = `${fieldName}_${index}`;
        }
    });

    fillCatalogSelects(card, catalogos);

    const fileInput = card.querySelector('[data-doc-field="archivoDocumento"]');
    const fileInfo = card.querySelector('[data-current-file]');

    if (data) {
        setDocValue(card, 'idDocumentoPostulante', data.idDocumentoPostulante);
        setDocValue(card, 'tituloDocumento', data.titulo);
        setDocValue(card, 'slctGradoEstudio', data.idGradoEst);
        setDocValue(card, 'slctInstitucionEducativa', data.institucion);
        setDocValue(card, 'nombreOtraInstitucion', data.nombreOtraInstitucion);
        setDocValue(card, 'provinciaDocumento', data.codigo_provincia);
        setDocValue(card, 'horasTotales', data.totalHoras);
        setDocValue(card, 'fechaInicioEstudios', data.fechaInicio);
        setDocValue(card, 'fechaFinEstudios', data.fechaFinalizacion);
        setDocValue(card, 'fechaEmision', data.fechaEmision);

        card.querySelector('[data-document-title]').textContent = data.titulo || 'Documento académico';
        fileInput.required = false;

        if (data.ruta) {
            const link = document.createElement('a');
            link.href = `${backendControllerPath}?action=descargar_documento&id=${encodeURIComponent(data.idDocumentoPostulante)}`;
            link.textContent = 'Descargar PDF actual';
            link.target = '_blank';
            fileInfo.replaceChildren(link);
        }
    } else {
        fileInput.required = true;
    }

    bindDocumentCardEvents(form, card);
    list.appendChild(card);
    toggleOtraInstitucion(card);
    renumberDocumentCards(form);

    if (form.classList.contains('is-readonly')) {
        applyReadonlyMode(form, true);
    }
}

function renumberDocumentCards(form) {
    form.querySelectorAll('[data-document-card]').forEach((card, index) => {
        const number = card.querySelector('[data-document-number]');
        if (number) number.textContent = String(index + 1);
    });
}

function setDocValue(card, field, value) {
    const input = card.querySelector(`[data-doc-field="${field}"]`);
    if (input && value !== null && value !== undefined) {
        input.value = value;
    }
}

function bindDocumentCardEvents(form, card) {
    const fileInput = card.querySelector('[data-doc-field="archivoDocumento"]');
    const titleInput = card.querySelector('[data-doc-field="tituloDocumento"]');
    const institutionSelect = card.querySelector('[data-doc-field="slctInstitucionEducativa"]');
    const removeButton = card.querySelector('[data-remove-document]');

    fileInput?.addEventListener('change', () => validatePdfInput(fileInput));

    titleInput?.addEventListener('input', () => {
        const title = titleInput.value.trim();
        card.querySelector('[data-document-title]').textContent = title || 'Documento académico';
    });

    institutionSelect?.addEventListener('change', () => toggleOtraInstitucion(card));

    removeButton?.addEventListener('click', () => {
        const cards = form.querySelectorAll('[data-document-card]');
        if (cards.length <= 1) {
            showFormNotice(form, 'Debe quedar al menos un documento en la postulación.', 'info');
            return;
        }

        const id = card.querySelector('[data-doc-field="idDocumentoPostulante"]')?.value;
        if (id) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'documentosEliminados[]';
            hidden.value = id;
            form.appendChild(hidden);
        }

        card.remove();
        renumberDocumentCards(form);
    });
}

function toggleOtraInstitucion(card) {
    const institutionSelect = card.querySelector('[data-doc-field="slctInstitucionEducativa"]');
    const otraInput = card.querySelector('[data-doc-field="nombreOtraInstitucion"]');
    if (!institutionSelect || !otraInput) return;

    const isOtra = institutionSelect.value === '1';
    otraInput.disabled = !isOtra;
    otraInput.required = isOtra;
    if (!isOtra) otraInput.value = '';
}

function bindApellidoCasada(form) {
    const select = form.querySelector('#slctpreguntaApelCasada');
    const sexo = form.querySelector('#sexo');
    select?.addEventListener('change', () => toggleApellidoCasada(form));
    sexo?.addEventListener('change', () => toggleApellidoCasada(form));
    toggleApellidoCasada(form);
}

function toggleApellidoCasada(form) {
    const select = form.querySelector('#slctpreguntaApelCasada');
    const input = form.querySelector('#apellidoCasada');
    const sexo = form.querySelector('#sexo');
    const questionGroup = form.querySelector('[data-married-name-question]') || select?.closest('.form-group');
    const fieldGroup = form.querySelector('[data-married-name-field]') || input?.closest('.form-group');
    if (!select || !input) return;

    const isFemale = sexo?.value === '0';
    const enabled = isFemale && select.value === '1';

    if (questionGroup) questionGroup.hidden = !isFemale;
    if (fieldGroup) fieldGroup.hidden = !enabled;

    select.disabled = !isFemale;
    if (!isFemale) select.value = '0';

    input.disabled = !enabled;
    input.required = enabled;
    if (!enabled) input.value = '';
}

function bindNumericInputs(scope) {
    scope.querySelectorAll('input[inputmode="numeric"]').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '');
        });
    });
}

function validatePdfInput(input) {
    const file = input.files?.[0];
    if (!file) return true;

    const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
    const maxSize = 5 * 1024 * 1024;

    if (!isPdf) {
        alert('Solo se permiten archivos PDF.');
        input.value = '';
        return false;
    }

    if (file.size > maxSize) {
        alert('El archivo no debe superar los 5 MB.');
        input.value = '';
        return false;
    }

    return true;
}

function validatePostulacionForm(form) {
    const errors = [];

    if (!form.checkValidity()) {
        form.reportValidity();
        errors.push('Revisa los campos obligatorios o con formato incorrecto.');
    }

    const tomo = form.querySelector('#tomo')?.value.trim() || '';
    const asiento = form.querySelector('#asiento')?.value.trim() || '';

    if (!/^[1-9][0-9]{0,3}$/.test(tomo)) {
        errors.push('El tomo debe tener máximo 4 dígitos y no puede iniciar con 0.');
    }

    if (!/^[1-9][0-9]{0,4}$/.test(asiento)) {
        errors.push('El asiento debe tener máximo 5 dígitos y no puede iniciar con 0.');
    }

    form.querySelectorAll('[data-document-card]').forEach((card, index) => {
        const fileInput = card.querySelector('[data-doc-field="archivoDocumento"]');
        const idDocumento = card.querySelector('[data-doc-field="idDocumentoPostulante"]')?.value;
        const fechaInicio = card.querySelector('[data-doc-field="fechaInicioEstudios"]')?.value;
        const fechaFin = card.querySelector('[data-doc-field="fechaFinEstudios"]')?.value;
        const fechaEmision = card.querySelector('[data-doc-field="fechaEmision"]')?.value;
        const horas = Number(card.querySelector('[data-doc-field="horasTotales"]')?.value || 0);

        if (!idDocumento && (!fileInput?.files || fileInput.files.length === 0)) {
            errors.push(`Documento #${index + 1}: debes cargar un PDF.`);
        }

        if (fileInput?.files?.length && !validatePdfInput(fileInput)) {
            errors.push(`Documento #${index + 1}: el archivo debe ser PDF y menor a 5 MB.`);
        }

        if (fechaInicio && fechaFin && new Date(fechaFin) <= new Date(fechaInicio)) {
            errors.push(`Documento #${index + 1}: la fecha de finalización debe ser posterior a la fecha de inicio.`);
        }

        if (fechaFin && fechaEmision && new Date(fechaEmision) <= new Date(fechaFin)) {
            errors.push(`Documento #${index + 1}: la fecha de emisión debe ser posterior a la fecha de finalización.`);
        }

        if (fechaEmision) {
            const emision = new Date(`${fechaEmision}T00:00:00`);
            const hoy = new Date();
            const limite = new Date();
            limite.setFullYear(hoy.getFullYear() - 5);

            if (emision > hoy || emision < limite) {
                errors.push(`Documento #${index + 1}: la fecha de emisión debe estar dentro de los últimos 5 años.`);
            }
        }

        if (horas < 40) {
            errors.push(`Documento #${index + 1}: el total de horas debe ser mínimo 40.`);
        }
    });

    const sexo = form.querySelector('#sexo')?.value || '';
    const usaCasada = form.querySelector('#slctpreguntaApelCasada')?.value === '1';
    const apellidoCasada = form.querySelector('#apellidoCasada')?.value.trim() || '';

    if (sexo !== '0' && usaCasada) {
        errors.push('El apellido de casada solo aplica cuando el sexo es femenino.');
    }

    if (sexo === '0' && usaCasada && !apellidoCasada) {
        errors.push('Debe indicar el apellido de casada.');
    }

    return [...new Set(errors)];
}

function setupEditToggle(form) {
    const button = form.querySelector('[data-edit-toggle]');
    button?.addEventListener('click', () => {
        const readonly = form.classList.contains('is-readonly');
        applyReadonlyMode(form, !readonly);
        showFormNotice(form, readonly ? 'Edición activada. Ya puedes modificar la postulación.' : 'Modo lectura activado.', 'info');
        button.textContent = readonly ? 'Cancelar edición' : 'Editar';
    });
}

function applyReadonlyMode(form, readonly) {
    form.classList.toggle('is-readonly', readonly);

    form.querySelectorAll('input, select, textarea').forEach((field) => {
        if (field.type === 'hidden') return;
        field.disabled = readonly;
    });

    form.querySelectorAll('[data-add-document], [data-remove-document], button[type="submit"]').forEach((button) => {
        button.disabled = readonly;
    });

    const editButton = form.querySelector('[data-edit-toggle]');
    if (editButton) editButton.disabled = false;

    if (!readonly) {
        toggleApellidoCasada(form);
        form.querySelectorAll('[data-document-card]').forEach(toggleOtraInstitucion);
    }
}

function ensureHiddenField(form, name) {
    let field = form.querySelector(`input[name="${name}"]`);

    if (!field) {
        field = document.createElement('input');
        field.type = 'hidden';
        field.name = name;
        form.appendChild(field);
    }

    return field;
}

function showFormNotice(form, message, type = 'info') {
    const notice = form.querySelector('[data-form-message]') || form.querySelector('.form-notice');
    const list = Array.isArray(message) ? message : [message];

    if (!notice) return;

    notice.className = `form-notice is-${type}`;
    if (list.length > 1) {
        notice.innerHTML = `<strong>Revisa lo siguiente:</strong><ul>${list.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`;
    } else {
        notice.textContent = list[0] || '';
    }
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

window.addEventListener('hashchange', loadView);

document.addEventListener('DOMContentLoaded', async () => {
    await loadLayout();
    await loadView();
});
