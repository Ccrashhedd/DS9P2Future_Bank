package com.example.project2

import android.content.Intent
import android.graphics.Bitmap
import android.graphics.Canvas
import android.graphics.Color as AndroidColor
import android.graphics.pdf.PdfRenderer
import android.os.Bundle
import android.os.ParcelFileDescriptor
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.Crossfade
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.horizontalScroll
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ColumnScope
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.ArrowForward
import androidx.compose.material.icons.outlined.AccountCircle
import androidx.compose.material.icons.outlined.CreditCard
import androidx.compose.material.icons.outlined.Description
import androidx.compose.material.icons.outlined.Email
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.Logout
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.PictureAsPdf
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material.icons.outlined.Settings
import androidx.compose.material.icons.outlined.VerifiedUser
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.core.content.FileProvider
import com.example.project2.data.ApiClient
import com.example.project2.data.AuthSession
import com.example.project2.data.DocumentoDto
import com.example.project2.data.PostulanteDto
import com.example.project2.data.TipoDocumentoDto
import com.example.project2.ui.components.BankBottomBar
import com.example.project2.ui.components.BankBottomItem
import com.example.project2.ui.components.BankDangerButton
import com.example.project2.ui.components.BankGlassCard
import com.example.project2.ui.components.BankGradientBackground
import com.example.project2.ui.components.BankPasswordField
import com.example.project2.ui.components.BankPrimaryButton
import com.example.project2.ui.components.BankSearchField
import com.example.project2.ui.components.BankSecondaryButton
import com.example.project2.ui.components.BankTextField
import com.example.project2.ui.components.BankTopAppBar
import com.example.project2.ui.components.DocumentCard
import com.example.project2.ui.components.EmptyStateView
import com.example.project2.ui.components.ErrorStateView
import com.example.project2.ui.components.InfoRow
import com.example.project2.ui.components.InfoSectionCard
import com.example.project2.ui.components.LoadingView
import com.example.project2.ui.components.StatusChip
import com.example.project2.ui.components.TwoColumnInfoRow
import com.example.project2.ui.theme.DarkPurple
import com.example.project2.ui.theme.GlassBorder
import com.example.project2.ui.theme.GlassSurfaceStrong
import com.example.project2.ui.theme.PrimaryPurple
import com.example.project2.ui.theme.Silver
import com.example.project2.ui.theme.SilverDark
import com.example.project2.ui.theme.SoftPurple
import com.example.project2.ui.theme.TextPrimary
import com.example.project2.ui.theme.TextSecondary
import com.example.project2.ui.theme.Project2Theme
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.io.File
import java.io.IOException
import kotlin.math.roundToInt

private const val SCREEN_HOME = "home"
private const val SCREEN_PROFILE = "profile"
private const val SCREEN_DOCUMENTS = "documents"
private const val SCREEN_SETTINGS = "settings"

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContent {
            Project2Theme {
                FutureBankApp()
            }
        }
    }
}

@Composable
private fun FutureBankApp() {
    val context = LocalContext.current
    val api = remember { ApiClient(context.applicationContext) }
    var splashVisible by remember { mutableStateOf(true) }
    var session by remember { mutableStateOf(api.loadSession()) }

    LaunchedEffect(Unit) {
        delay(900)
        splashVisible = false
    }

    BankGradientBackground {
        Crossfade(targetState = splashVisible, label = "splash") { showingSplash ->
            if (showingSplash) {
                SplashScreen()
            } else if (session == null) {
                AuthScreen(
                    onLoggedIn = { session = it },
                    api = api
                )
            } else {
                DashboardScreen(
                    session = session!!,
                    api = api,
                    onLogout = {
                        api.clearSession()
                        session = null
                    }
                )
            }
        }
    }
}

@Composable
private fun SplashScreen() {
    Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
        Column(horizontalAlignment = Alignment.CenterHorizontally) {
            BrandMark(size = 86)
            Spacer(Modifier.height(18.dp))
            Text(
                text = "Future Bank",
                color = TextPrimary,
                style = MaterialTheme.typography.headlineMedium
            )
            Text(
                text = "Consultas",
                color = TextSecondary,
                style = MaterialTheme.typography.titleMedium
            )
            Spacer(Modifier.height(26.dp))
            LoadingView(message = "")
        }
    }
}

@Composable
private fun AuthScreen(
    onLoggedIn: (AuthSession) -> Unit,
    api: ApiClient
) {
    var registerMode by remember { mutableStateOf(false) }
    Crossfade(targetState = registerMode, label = "authMode") { register ->
        if (register) {
            RegisterScreen(
                onLoggedIn = onLoggedIn,
                onLoginClick = { registerMode = false },
                api = api
            )
        } else {
            LoginScreen(
                onLoggedIn = onLoggedIn,
                onRegisterClick = { registerMode = true },
                api = api
            )
        }
    }
}

@Composable
private fun LoginScreen(
    onLoggedIn: (AuthSession) -> Unit,
    onRegisterClick: () -> Unit,
    api: ApiClient
) {
    val scope = rememberCoroutineScope()
    var usuario by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var loading by remember { mutableStateOf(false) }
    var error by remember { mutableStateOf<String?>(null) }

    fun submit() {
        loading = true
        error = null
        scope.launch {
            val result = runCatching { api.login(usuario, password) }
            loading = false
            result.onSuccess(onLoggedIn)
            result.onFailure { error = it.message ?: "No se pudo conectar." }
        }
    }

    AuthLayout(
        title = "Bienvenido",
        subtitle = "Consulta tu postulacion y documentos de Future Bank."
    ) {
        BankGlassCard(modifier = Modifier.fillMaxWidth()) {
            BankTextField(
                value = usuario,
                onValueChange = { usuario = it },
                label = "Correo o usuario",
                leadingIcon = Icons.Outlined.AccountCircle
            )
            Spacer(Modifier.height(12.dp))
            BankPasswordField(value = password, onValueChange = { password = it }, label = "Contrasena")
            Spacer(Modifier.height(18.dp))
            BankPrimaryButton(text = "Iniciar sesion", onClick = ::submit, loading = loading, enabled = !loading)
            ErrorText(error)
            AuthSeparator()
            Text(
                text = "Crear cuenta",
                color = Silver,
                style = MaterialTheme.typography.titleMedium,
                modifier = Modifier
                    .align(Alignment.CenterHorizontally)
                    .clip(RoundedCornerShape(999.dp))
                    .clickable(onClick = onRegisterClick)
                    .padding(horizontal = 16.dp, vertical = 8.dp)
            )
        }
        Spacer(Modifier.height(14.dp))
        BankGlassCard(modifier = Modifier.fillMaxWidth(), contentPadding = PaddingValues(14.dp)) {
            Text("App movil de consulta", color = TextSecondary, textAlign = TextAlign.Center, modifier = Modifier.fillMaxWidth())
        }
    }
}

@Composable
private fun RegisterScreen(
    onLoggedIn: (AuthSession) -> Unit,
    onLoginClick: () -> Unit,
    api: ApiClient
) {
    val scope = rememberCoroutineScope()
    var nombreUsuario by remember { mutableStateOf("") }
    var correo by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var passwordConfirm by remember { mutableStateOf("") }
    var loading by remember { mutableStateOf(false) }
    var error by remember { mutableStateOf<String?>(null) }

    fun submit() {
        loading = true
        error = null
        scope.launch {
            val result = runCatching { api.register(nombreUsuario, correo, password, passwordConfirm) }
            loading = false
            result.onSuccess(onLoggedIn)
            result.onFailure { error = it.message ?: "No se pudo registrar." }
        }
    }

    AuthLayout(
        title = "Crear cuenta",
        subtitle = "Registra tu acceso para consultar tu informacion."
    ) {
        BankGlassCard(modifier = Modifier.fillMaxWidth()) {
            BankTextField(value = nombreUsuario, onValueChange = { nombreUsuario = it }, label = "Nombre de usuario", leadingIcon = Icons.Outlined.Person)
            Spacer(Modifier.height(12.dp))
            BankTextField(value = correo, onValueChange = { correo = it }, label = "Correo", leadingIcon = Icons.Outlined.Email)
            Spacer(Modifier.height(12.dp))
            BankPasswordField(value = password, onValueChange = { password = it }, label = "Contrasena")
            Spacer(Modifier.height(12.dp))
            BankPasswordField(value = passwordConfirm, onValueChange = { passwordConfirm = it }, label = "Confirmar contrasena")
            Spacer(Modifier.height(8.dp))
            Text(
                text = "La app solo crea el acceso de consulta. La postulacion se gestiona desde la plataforma web.",
                color = TextSecondary,
                style = MaterialTheme.typography.labelMedium
            )
            Spacer(Modifier.height(18.dp))
            BankPrimaryButton(text = "Crear cuenta", onClick = ::submit, loading = loading, enabled = !loading)
            ErrorText(error)
            AuthSeparator()
            Text(
                text = "Ya tengo cuenta",
                color = Silver,
                style = MaterialTheme.typography.titleMedium,
                modifier = Modifier
                    .align(Alignment.CenterHorizontally)
                    .clip(RoundedCornerShape(999.dp))
                    .clickable(onClick = onLoginClick)
                    .padding(horizontal = 16.dp, vertical = 8.dp)
            )
        }
    }
}

@Composable
private fun AuthLayout(title: String, subtitle: String, content: @Composable ColumnScope.() -> Unit) {
    LazyColumn(
        modifier = Modifier.fillMaxSize(),
        contentPadding = PaddingValues(horizontal = 24.dp, vertical = 44.dp),
        verticalArrangement = Arrangement.Center
    ) {
        item {
            AnimatedVisibility(visible = true, enter = fadeIn(), exit = fadeOut()) {
                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    BrandMark(size = 72)
                    Spacer(Modifier.height(18.dp))
                    Text(title, color = TextPrimary, style = MaterialTheme.typography.headlineLarge)
                    Text(
                        subtitle,
                        color = TextSecondary,
                        style = MaterialTheme.typography.bodyLarge,
                        textAlign = TextAlign.Center
                    )
                    Spacer(Modifier.height(24.dp))
                    content()
                }
            }
        }
    }
}

@Composable
private fun DashboardScreen(session: AuthSession, api: ApiClient, onLogout: () -> Unit) {
    val scope = rememberCoroutineScope()
    val context = LocalContext.current
    var screen by remember { mutableStateOf(SCREEN_HOME) }
    var selectedDocumento by remember { mutableStateOf<DocumentoDto?>(null) }
    var postulante by remember { mutableStateOf<PostulanteDto?>(null) }
    var documentos by remember { mutableStateOf<List<DocumentoDto>>(emptyList()) }
    var tipos by remember { mutableStateOf<List<TipoDocumentoDto>>(emptyList()) }
    var search by remember { mutableStateOf("") }
    var tipo by remember { mutableStateOf("") }
    var loading by remember { mutableStateOf(false) }
    var error by remember { mutableStateOf<String?>(null) }

    fun cargarDatos() {
        loading = true
        error = null
        scope.launch {
            val result = runCatching {
                postulante = api.getPostulante()
                tipos = api.getTiposDocumento()
                documentos = api.getDocumentos(search, tipo)
            }
            loading = false
            result.onFailure { error = it.message ?: "No se pudo cargar la informacion." }
        }
    }

    fun abrirPdf(documento: DocumentoDto) {
        loading = true
        error = null
        scope.launch {
            val result = runCatching { api.downloadPdf(documento.idDocumentoPostulante) }
            loading = false
            result.onSuccess { file ->
                val uri = FileProvider.getUriForFile(context, "${context.packageName}.fileprovider", file)
                val intent = Intent(Intent.ACTION_VIEW)
                    .setDataAndType(uri, "application/pdf")
                    .addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
                runCatching { context.startActivity(Intent.createChooser(intent, documento.titulo)) }
                    .onFailure { error = "No hay una app disponible para abrir PDF." }
            }
            result.onFailure { error = it.message ?: "No se pudo abrir el PDF." }
        }
    }

    LaunchedEffect(Unit) {
        cargarDatos()
    }

    val bottomItems = listOf(
        BankBottomItem(SCREEN_HOME, "Home", Icons.Outlined.Home),
        BankBottomItem(SCREEN_PROFILE, "Perfil", Icons.Outlined.Person),
        BankBottomItem(SCREEN_DOCUMENTS, "Docs", Icons.Outlined.Description),
        BankBottomItem(SCREEN_SETTINGS, "Ajustes", Icons.Outlined.Settings)
    )

    Scaffold(
        containerColor = Color.Transparent,
        bottomBar = {
            if (selectedDocumento == null) {
                BankBottomBar(
                    items = bottomItems,
                    selectedKey = screen,
                    onSelected = {
                        screen = it
                        selectedDocumento = null
                    }
                )
            }
        }
    ) { padding ->
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
        ) {
            Crossfade(targetState = selectedDocumento ?: screen, label = "dashboard") { target ->
                when (target) {
                    is DocumentoDto -> DocumentDetailScreen(
                        documento = target,
                        api = api,
                        loading = loading,
                        error = error,
                        onBack = { selectedDocumento = null },
                        onOpenPdf = { abrirPdf(target) }
                    )
                    SCREEN_HOME -> HomeScreen(
                        session = session,
                        postulante = postulante,
                        documentos = documentos,
                        loading = loading,
                        error = error,
                        onNavigate = { screen = it }
                    )
                    SCREEN_PROFILE -> ProfileScreen(postulante = postulante, loading = loading, error = error)
                    SCREEN_DOCUMENTS -> DocumentsScreen(
                        documentos = documentos,
                        tipos = tipos,
                        search = search,
                        tipo = tipo,
                        loading = loading,
                        error = error,
                        onSearchChange = { search = it },
                        onTipoChange = {
                            tipo = it
                            cargarDatos()
                        },
                        onRefresh = { cargarDatos() },
                        onOpenDetail = { selectedDocumento = it },
                        onOpenPdf = ::abrirPdf
                    )
                    SCREEN_SETTINGS -> SettingsScreen(session = session, onLogout = onLogout)
                }
            }
        }
    }
}

@Composable
private fun HomeScreen(
    session: AuthSession,
    postulante: PostulanteDto?,
    documentos: List<DocumentoDto>,
    loading: Boolean,
    error: String?,
    onNavigate: (String) -> Unit
) {
    LazyColumn(
        modifier = Modifier.fillMaxSize(),
        contentPadding = PaddingValues(20.dp),
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        item {
            ScreenHeader(
                title = "Hola, ${session.user.nombreUsuario}",
                subtitle = "Bienvenido a Future Bank Consultas"
            )
        }
        item {
            StatusBankCard(
                hasPostulacion = postulante?.tienePostulacion == true,
                documentos = documentos.size
            )
        }
        if (loading) item { LoadingView() }
        error?.let { item { ErrorStateView(it) } }
        item {
            Text("Accesos rapidos", color = TextPrimary, style = MaterialTheme.typography.titleLarge)
        }
        item {
            QuickAccessCard(
                icon = Icons.Outlined.Person,
                title = "Mis datos generales",
                description = "Perfil, contacto y direccion registrada",
                onClick = { onNavigate(SCREEN_PROFILE) }
            )
        }
        item {
            QuickAccessCard(
                icon = Icons.Outlined.Description,
                title = "Mis documentos",
                description = "${documentos.size} documentos disponibles",
                onClick = { onNavigate(SCREEN_DOCUMENTS) }
            )
        }
        item {
            QuickAccessCard(
                icon = Icons.Outlined.Settings,
                title = "Configuracion",
                description = "Datos de cuenta y cierre de sesion",
                onClick = { onNavigate(SCREEN_SETTINGS) }
            )
        }
    }
}

@Composable
private fun ProfileScreen(postulante: PostulanteDto?, loading: Boolean, error: String?) {
    Column(modifier = Modifier.fillMaxSize()) {
        BankTopAppBar(title = "Datos generales")
        LazyColumn(
            modifier = Modifier.fillMaxSize(),
            contentPadding = PaddingValues(20.dp),
            verticalArrangement = Arrangement.spacedBy(14.dp)
        ) {
            if (loading && postulante == null) item { LoadingView() }
            error?.let { item { ErrorStateView(it) } }
            if (!loading && postulante?.tienePostulacion != true) {
                item {
                    EmptyStateView(
                        title = "No tienes postulacion registrada",
                        message = "Esta app es solo de consulta. La postulacion debe realizarse desde la plataforma web.",
                        icon = Icons.Outlined.VerifiedUser
                    )
                }
            }
            postulante?.takeIf { it.tienePostulacion }?.let { p ->
                item {
                    BankGlassCard(modifier = Modifier.fillMaxWidth()) {
                        Text(p.nombreCompleto.orEmpty(), color = TextPrimary, style = MaterialTheme.typography.headlineMedium)
                        Spacer(Modifier.height(8.dp))
                        StatusChip(text = "Cedula ${p.cedula ?: "No especificado"}")
                    }
                }
                item {
                    InfoSectionCard("Informacion personal") {
                        TwoColumnInfoRow("Genero", p.genero, "Estado civil", p.estadoCivil)
                        p.apellidoCasada?.let { InfoRow("Apellido de casada", it) }
                        TwoColumnInfoRow("Fecha nacimiento", p.fechaNacimiento, "Tipo sangre", p.tipoSangre)
                    }
                }
                item {
                    InfoSectionCard("Contacto") {
                        InfoRow("Correo", p.correoPostulante)
                        TwoColumnInfoRow("Telefono", p.telefono, "Telefono 2", p.telefono2)
                        TwoColumnInfoRow("Celular", p.celular, "Celular 2", p.celular2)
                    }
                }
                item {
                    InfoSectionCard("Direccion") {
                        InfoRow("Provincia", p.provincia)
                        TwoColumnInfoRow("Distrito", p.distrito, "Corregimiento", p.corregimiento)
                        InfoRow("Direccion completa", p.direccionCompleta)
                    }
                }
                item {
                    InfoSectionCard("Datos academicos") {
                        InfoRow("Rango academico", p.rangoAcademico)
                    }
                }
            }
        }
    }
}

@Composable
private fun DocumentsScreen(
    documentos: List<DocumentoDto>,
    tipos: List<TipoDocumentoDto>,
    search: String,
    tipo: String,
    loading: Boolean,
    error: String?,
    onSearchChange: (String) -> Unit,
    onTipoChange: (String) -> Unit,
    onRefresh: () -> Unit,
    onOpenDetail: (DocumentoDto) -> Unit,
    onOpenPdf: (DocumentoDto) -> Unit
) {
    Column(modifier = Modifier.fillMaxSize()) {
        BankTopAppBar(
            title = "Documentos",
            actions = {
                IconButton(onClick = onRefresh) {
                    Icon(Icons.Outlined.Search, contentDescription = "Buscar", tint = TextPrimary)
                }
            }
        )
        LazyColumn(
            modifier = Modifier.fillMaxSize(),
            contentPadding = PaddingValues(horizontal = 20.dp, vertical = 12.dp),
            verticalArrangement = Arrangement.spacedBy(14.dp)
        ) {
            item {
                BankSearchField(value = search, onValueChange = onSearchChange)
            }
            item {
                LazyRow(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    item {
                        StatusChip(text = "Todos", selected = tipo.isBlank(), onClick = { onTipoChange("") })
                    }
                    items(tipos, key = { it.idGradoEst }) { item ->
                        StatusChip(
                            text = item.nombreGradoEst,
                            selected = tipo == item.idGradoEst.toString(),
                            onClick = { onTipoChange(item.idGradoEst.toString()) }
                        )
                    }
                }
            }
            if (loading) item { LoadingView("Cargando documentos...") }
            error?.let { item { ErrorStateView(it) } }
            if (!loading && documentos.isEmpty()) {
                item {
                    EmptyStateView(
                        title = "No hay documentos",
                        message = "Cuando tengas documentos registrados apareceran aqui.",
                        icon = Icons.Outlined.Description
                    )
                }
            }
            items(documentos, key = { it.idDocumentoPostulante }) { documento ->
                DocumentCard(
                    documento = documento,
                    onClick = { onOpenDetail(documento) },
                    onOpenPdf = onOpenPdf
                )
            }
        }
    }
}

@Composable
private fun DocumentDetailScreen(
    documento: DocumentoDto,
    api: ApiClient,
    loading: Boolean,
    error: String?,
    onBack: () -> Unit,
    onOpenPdf: () -> Unit
) {
    var previewBitmap by remember(documento.idDocumentoPostulante) { mutableStateOf<Bitmap?>(null) }
    var previewLoading by remember(documento.idDocumentoPostulante) { mutableStateOf(false) }
    var previewError by remember(documento.idDocumentoPostulante) { mutableStateOf<String?>(null) }

    LaunchedEffect(documento.idDocumentoPostulante, documento.tienePdf) {
        previewBitmap = null
        previewError = null
        if (!documento.tienePdf) return@LaunchedEffect

        previewLoading = true
        val result = runCatching {
            val file = api.downloadPdf(documento.idDocumentoPostulante)
            renderPdfFirstPage(file)
        }
        previewLoading = false
        result
            .onSuccess { previewBitmap = it }
            .onFailure { previewError = it.message ?: "No se pudo cargar la vista previa." }
    }

    Column(modifier = Modifier.fillMaxSize()) {
        BankTopAppBar(title = "Detalle", onBack = onBack)
        LazyColumn(
            modifier = Modifier.fillMaxSize(),
            contentPadding = PaddingValues(20.dp),
            verticalArrangement = Arrangement.spacedBy(14.dp)
        ) {
            item {
                BankGlassCard(modifier = Modifier.fillMaxWidth()) {
                    Icon(Icons.Outlined.PictureAsPdf, contentDescription = null, tint = Silver, modifier = Modifier.size(52.dp))
                    Spacer(Modifier.height(12.dp))
                    Text(documento.titulo, color = TextPrimary, style = MaterialTheme.typography.headlineMedium)
                    Spacer(Modifier.height(10.dp))
                    StatusChip(text = documento.tipo)
                }
            }
            item {
                InfoSectionCard("Informacion del documento") {
                    InfoRow("Institucion", documento.institucion)
                    TwoColumnInfoRow("Fecha inicio", documento.fechaInicio, "Fecha finalizacion", documento.fechaFinalizacion)
                    TwoColumnInfoRow("Fecha emision", documento.fechaEmision, "Total horas", documento.totalHoras?.toString())
                }
            }
            item {
                PdfPreviewCard(
                    bitmap = previewBitmap,
                    loading = previewLoading,
                    error = previewError,
                    hasPdf = documento.tienePdf
                )
            }
            error?.let { item { ErrorStateView(it) } }
            item {
                BankPrimaryButton(
                    text = "Abrir PDF",
                    onClick = onOpenPdf,
                    enabled = documento.tienePdf && !loading,
                    loading = loading
                )
                if (!documento.tienePdf) {
                    Spacer(Modifier.height(8.dp))
                    Text("Este documento no tiene PDF disponible.", color = TextSecondary)
                }
            }
        }
    }
}

@Composable
private fun PdfPreviewCard(
    bitmap: Bitmap?,
    loading: Boolean,
    error: String?,
    hasPdf: Boolean
) {
    InfoSectionCard("Vista previa del PDF") {
        when {
            !hasPdf -> Text("Este documento no tiene PDF disponible.", color = TextSecondary)
            loading -> LoadingView("Cargando vista previa...")
            bitmap != null -> {
                Image(
                    bitmap = bitmap.asImageBitmap(),
                    contentDescription = "Vista previa del PDF",
                    contentScale = ContentScale.Fit,
                    modifier = Modifier
                        .fillMaxWidth()
                        .aspectRatio(bitmap.width.toFloat() / bitmap.height.toFloat())
                        .clip(RoundedCornerShape(8.dp))
                        .background(Color.White)
                )
            }
            error != null -> Text(error, color = MaterialTheme.colorScheme.error)
            else -> Text("Vista previa no disponible.", color = TextSecondary)
        }
    }
}

private suspend fun renderPdfFirstPage(file: File): Bitmap = withContext(Dispatchers.IO) {
    ParcelFileDescriptor.open(file, ParcelFileDescriptor.MODE_READ_ONLY).use { descriptor ->
        PdfRenderer(descriptor).use { renderer ->
            if (renderer.pageCount == 0) {
                throw IOException("El PDF no tiene paginas.")
            }

            renderer.openPage(0).use { page ->
                val targetWidth = 900
                val targetHeight = (targetWidth * (page.height.toFloat() / page.width.toFloat())).roundToInt()
                val bitmap = Bitmap.createBitmap(targetWidth, targetHeight, Bitmap.Config.ARGB_8888)
                Canvas(bitmap).drawColor(AndroidColor.WHITE)
                page.render(bitmap, null, null, PdfRenderer.Page.RENDER_MODE_FOR_DISPLAY)
                bitmap
            }
        }
    }
}

@Composable
private fun SettingsScreen(session: AuthSession, onLogout: () -> Unit) {
    var confirmLogout by remember { mutableStateOf(false) }
    Column(modifier = Modifier.fillMaxSize()) {
        BankTopAppBar(title = "Configuracion")
        LazyColumn(
            modifier = Modifier.fillMaxSize(),
            contentPadding = PaddingValues(20.dp),
            verticalArrangement = Arrangement.spacedBy(14.dp)
        ) {
            item {
                InfoSectionCard("Usuario") {
                    InfoRow("Nombre de usuario", session.user.nombreUsuario)
                    InfoRow("Correo", session.user.correo)
                    InfoRow("Rol", if (session.user.rolUsuario == 0) "Administrador" else "Usuario")
                }
            }
            item {
                BankGlassCard(modifier = Modifier.fillMaxWidth()) {
                    Text("Future Bank Consultas", color = TextPrimary, style = MaterialTheme.typography.titleLarge)
                    Spacer(Modifier.height(8.dp))
                    Text("Aplicacion movil de solo consulta", color = TextSecondary)
                }
            }
            item {
                BankDangerButton(text = "Cerrar sesion", onClick = { confirmLogout = true }, modifier = Modifier.fillMaxWidth())
            }
        }
    }
    if (confirmLogout) {
        AlertDialog(
            onDismissRequest = { confirmLogout = false },
            containerColor = Color(0xFF15101F),
            tonalElevation = 0.dp,
            titleContentColor = TextPrimary,
            textContentColor = TextSecondary,
            title = { Text("Cerrar sesion") },
            text = { Text("Tu token local se eliminara de este dispositivo.") },
            confirmButton = {
                BankDangerButton(text = "Salir", onClick = onLogout, modifier = Modifier.width(130.dp))
            },
            dismissButton = {
                BankSecondaryButton(text = "Cancelar", onClick = { confirmLogout = false }, modifier = Modifier.width(130.dp))
            }
        )
    }
}

@Composable
private fun ScreenHeader(title: String, subtitle: String) {
    Column(verticalArrangement = Arrangement.spacedBy(6.dp)) {
        Text(title, color = TextPrimary, style = MaterialTheme.typography.headlineMedium)
        Text(subtitle, color = TextSecondary, style = MaterialTheme.typography.bodyLarge)
    }
}

@Composable
private fun StatusBankCard(hasPostulacion: Boolean, documentos: Int) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(30.dp))
            .background(Brush.linearGradient(listOf(DarkPurple, PrimaryPurple, SoftPurple.copy(alpha = 0.85f))))
            .padding(22.dp)
    ) {
        Column(verticalArrangement = Arrangement.spacedBy(14.dp)) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.Outlined.CreditCard, contentDescription = null, tint = Silver, modifier = Modifier.size(34.dp))
                Spacer(Modifier.width(12.dp))
                Text("Estado de postulacion", color = Silver, style = MaterialTheme.typography.titleMedium)
            }
            Text(
                text = if (hasPostulacion) "Consulta disponible" else "Sin postulacion registrada",
                color = Color.White,
                style = MaterialTheme.typography.headlineMedium
            )
            Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                StatusChip(text = "$documentos documentos")
                StatusChip(text = "Solo consulta")
            }
        }
    }
}

@Composable
private fun QuickAccessCard(
    icon: ImageVector,
    title: String,
    description: String,
    onClick: () -> Unit
) {
    Surface(
        modifier = Modifier.fillMaxWidth(),
        onClick = onClick,
        color = Color.Transparent
    ) {
        BankGlassCard(modifier = Modifier.fillMaxWidth()) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(icon, contentDescription = null, tint = Silver, modifier = Modifier.size(34.dp))
                Column(modifier = Modifier.weight(1f).padding(horizontal = 14.dp)) {
                    Text(title, color = TextPrimary, style = MaterialTheme.typography.titleMedium)
                    Text(description, color = TextSecondary, style = MaterialTheme.typography.bodyMedium)
                }
                Icon(Icons.AutoMirrored.Outlined.ArrowForward, contentDescription = null, tint = SilverDark)
            }
        }
    }
}

@Composable
private fun BrandMark(size: Int) {
    Box(
        modifier = Modifier
            .size(size.dp)
            .clip(RoundedCornerShape((size / 3).dp))
            .background(Brush.linearGradient(listOf(PrimaryPurple, SoftPurple))),
        contentAlignment = Alignment.Center
    ) {
        Text(
            text = "FB",
            color = Color.White,
            style = MaterialTheme.typography.headlineMedium,
            fontWeight = FontWeight.Bold
        )
    }
}

@Composable
private fun ErrorText(message: String?) {
    if (message != null) {
        Spacer(Modifier.height(12.dp))
        Text(
            text = message,
            color = MaterialTheme.colorScheme.error,
            style = MaterialTheme.typography.bodyMedium
        )
    }
}

@Composable
private fun AuthSeparator() {
    Spacer(Modifier.height(16.dp))
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .height(1.dp)
            .background(GlassBorder)
    )
    Spacer(Modifier.height(6.dp))
}
