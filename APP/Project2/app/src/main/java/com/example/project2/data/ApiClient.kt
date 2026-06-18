package com.example.project2.data

import android.content.Context
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import org.json.JSONArray
import org.json.JSONException
import org.json.JSONObject
import java.io.File
import java.io.IOException
import java.net.HttpURLConnection
import java.net.URL
import java.net.URLEncoder
import java.nio.charset.StandardCharsets

class ApiClient(private val context: Context) {
    private val prefs = context.getSharedPreferences("mobile_api_session", Context.MODE_PRIVATE)

    var baseUrl: String
        get() = DEFAULT_BASE_URL
        set(value) {
            prefs.edit().remove(KEY_BASE_URL).apply()
        }

    var token: String?
        get() = prefs.getString(KEY_TOKEN, null)
        private set(value) {
            prefs.edit().putString(KEY_TOKEN, value).apply()
        }

    fun loadSession(): AuthSession? {
        val savedToken = prefs.getString(KEY_TOKEN, null) ?: return null
        val expiraEn = prefs.getString(KEY_EXPIRES, "") ?: ""
        val idUsuario = prefs.getLong(KEY_USER_ID, -1L)
        val rolUsuario = prefs.getInt(KEY_USER_ROLE, -1)
        val nombreUsuario = prefs.getString(KEY_USER_NAME, "") ?: ""
        val correo = prefs.getString(KEY_USER_EMAIL, "") ?: ""
        if (idUsuario <= 0 || rolUsuario < 0 || nombreUsuario.isBlank()) return null
        return AuthSession(savedToken, expiraEn, UserDto(idUsuario, rolUsuario, nombreUsuario, correo))
    }

    fun clearSession() {
        prefs.edit()
            .remove(KEY_TOKEN)
            .remove(KEY_EXPIRES)
            .remove(KEY_USER_ID)
            .remove(KEY_USER_ROLE)
            .remove(KEY_USER_NAME)
            .remove(KEY_USER_EMAIL)
            .apply()
    }

    suspend fun login(usuario: String, password: String): AuthSession = withContext(Dispatchers.IO) {
        val root = requestJson(
            endpoint = "auth/login.php",
            method = "POST",
            body = JSONObject()
                .put("usuario", usuario)
                .put("password", password),
            auth = false
        )
        parseAndSaveSession(root.getJSONObject("data"))
    }

    suspend fun register(
        nombreUsuario: String,
        correo: String,
        password: String,
        passwordConfirm: String
    ): AuthSession = withContext(Dispatchers.IO) {
        val root = requestJson(
            endpoint = "auth/register.php",
            method = "POST",
            body = JSONObject()
                .put("nombreUsuario", nombreUsuario)
                .put("correo", correo)
                .put("password", password)
                .put("passwordConfirm", passwordConfirm),
            auth = false
        )
        parseAndSaveSession(root.getJSONObject("data"))
    }

    suspend fun getPostulante(): PostulanteDto = withContext(Dispatchers.IO) {
        val data = requestJson("postulante/me.php", auth = true).getJSONObject("data")
        PostulanteDto(
            tienePostulacion = data.optBoolean("tienePostulacion", false),
            idPostulante = data.optLongOrNull("idPostulante"),
            nombreCompleto = data.optStringOrNull("nombreCompleto"),
            cedula = data.optStringOrNull("cedula"),
            genero = data.optStringOrNull("genero"),
            estadoCivil = data.optStringOrNull("estadoCivil"),
            apellidoCasada = data.optStringOrNull("apellidoCasada"),
            rangoAcademico = data.optStringOrNull("rangoAcademico"),
            tipoSangre = data.optStringOrNull("tipoSangre"),
            fechaNacimiento = data.optStringOrNull("fechaNacimiento"),
            correoPostulante = data.optStringOrNull("correoPostulante"),
            telefono = data.optStringOrNull("telefono"),
            telefono2 = data.optStringOrNull("telefono2"),
            celular = data.optStringOrNull("celular"),
            celular2 = data.optStringOrNull("celular2"),
            provincia = data.optStringOrNull("provincia"),
            distrito = data.optStringOrNull("distrito"),
            corregimiento = data.optStringOrNull("corregimiento"),
            comunidad = data.optStringOrNull("comunidad"),
            calle = data.optStringOrNull("calle"),
            casa = data.optStringOrNull("casa"),
            detalleDireccion = data.optStringOrNull("detalleDireccion"),
            direccionCompleta = data.optStringOrNull("direccionCompleta")
        )
    }

    suspend fun getDocumentos(search: String = "", tipo: String = ""): List<DocumentoDto> =
        withContext(Dispatchers.IO) {
            val query = buildList {
                if (search.isNotBlank()) add("search=${search.urlEncode()}")
                if (tipo.isNotBlank()) add("tipo=${tipo.urlEncode()}")
            }.joinToString("&")
            val endpoint = if (query.isBlank()) "documentos/list.php" else "documentos/list.php?$query"
            val array = requestJson(endpoint, auth = true)
                .getJSONObject("data")
                .getJSONArray("documentos")
            array.toDocumentoList()
        }

    suspend fun getTiposDocumento(): List<TipoDocumentoDto> = withContext(Dispatchers.IO) {
        val array = requestJson("catalogos/tipos_documentos.php", auth = true)
            .getJSONObject("data")
            .getJSONArray("tipos")
        buildList {
            for (index in 0 until array.length()) {
                val item = array.getJSONObject(index)
                add(TipoDocumentoDto(item.getInt("idGradoEst"), item.getString("nombreGradoEst")))
            }
        }
    }

    suspend fun downloadPdf(idDocumento: Long): File = withContext(Dispatchers.IO) {
        val currentToken = token ?: throw IOException("Sesion no iniciada.")
        val connection = openConnection("documentos/download.php?id=$idDocumento", "GET").apply {
            setRequestProperty("Authorization", "Bearer $currentToken")
        }

        try {
            val code = connection.responseCode
            if (code !in 200..299) {
                throw IOException(readErrorMessage(connection))
            }

            val dir = File(context.cacheDir, "pdfs").also { it.mkdirs() }
            val file = File(dir, "documento-$idDocumento.pdf")
            connection.inputStream.use { input ->
                file.outputStream().use { output -> input.copyTo(output) }
            }
            file
        } finally {
            connection.disconnect()
        }
    }

    private fun parseAndSaveSession(data: JSONObject): AuthSession {
        val userJson = data.getJSONObject("user")
        val session = AuthSession(
            token = data.getString("token"),
            expiraEn = data.optString("expiraEn"),
            user = UserDto(
                idUsuario = userJson.getLong("idUsuario"),
                rolUsuario = userJson.getInt("rolUsuario"),
                nombreUsuario = userJson.getString("nombreUsuario"),
                correo = userJson.getString("correo")
            )
        )
        prefs.edit()
            .putString(KEY_TOKEN, session.token)
            .putString(KEY_EXPIRES, session.expiraEn)
            .putLong(KEY_USER_ID, session.user.idUsuario)
            .putInt(KEY_USER_ROLE, session.user.rolUsuario)
            .putString(KEY_USER_NAME, session.user.nombreUsuario)
            .putString(KEY_USER_EMAIL, session.user.correo)
            .apply()
        return session
    }

    private fun requestJson(
        endpoint: String,
        method: String = "GET",
        body: JSONObject? = null,
        auth: Boolean
    ): JSONObject {
        val connection = openConnection(endpoint, method)
        connection.setRequestProperty("Accept", "application/json")
        if (auth) {
            connection.setRequestProperty("Authorization", "Bearer ${token ?: ""}")
        }
        if (body != null) {
            val bytes = body.toString().toByteArray(StandardCharsets.UTF_8)
            connection.doOutput = true
            connection.setRequestProperty("Content-Type", "application/json; charset=utf-8")
            connection.setRequestProperty("Content-Length", bytes.size.toString())
            connection.outputStream.use { it.write(bytes) }
        }

        return try {
            val code = connection.responseCode
            val text = if (code in 200..299) {
                connection.inputStream.bufferedReader().use { it.readText() }
            } else {
                connection.errorStream?.bufferedReader()?.use { it.readText() }.orEmpty()
            }
            val json = try {
                JSONObject(text.ifBlank { "{}" })
            } catch (_: JSONException) {
                throw IOException("Respuesta no valida del servidor.")
            }
            if (code !in 200..299 || !json.optBoolean("ok", false)) {
                throw IOException(json.optString("message", "Error de comunicacion."))
            }
            json
        } finally {
            connection.disconnect()
        }
    }

    private fun openConnection(endpoint: String, method: String): HttpURLConnection {
        return (URL(normalizeBaseUrl(baseUrl) + endpoint).openConnection() as HttpURLConnection).apply {
            requestMethod = method
            connectTimeout = 15_000
            readTimeout = 30_000
        }
    }

    private fun readErrorMessage(connection: HttpURLConnection): String {
        val text = connection.errorStream?.bufferedReader()?.use { it.readText() }.orEmpty()
        return runCatching { JSONObject(text).optString("message") }
            .getOrNull()
            ?.takeIf { it.isNotBlank() }
            ?: "No se pudo descargar el PDF."
    }

    private fun JSONArray.toDocumentoList(): List<DocumentoDto> = buildList {
        for (index in 0 until length()) {
            val item = getJSONObject(index)
            add(
                DocumentoDto(
                    idDocumentoPostulante = item.getLong("idDocumentoPostulante"),
                    titulo = item.optString("titulo"),
                    tipo = item.optString("tipo"),
                    institucion = item.optString("institucion"),
                    fechaInicio = item.optStringOrNull("fechaInicio"),
                    fechaFinalizacion = item.optStringOrNull("fechaFinalizacion"),
                    fechaEmision = item.optStringOrNull("fechaEmision"),
                    totalHoras = item.optIntOrNull("totalHoras"),
                    tienePdf = item.optBoolean("tienePdf", false)
                )
            )
        }
    }

    private fun JSONObject.optStringOrNull(name: String): String? {
        if (!has(name) || isNull(name)) return null
        return optString(name).takeIf { it.isNotBlank() }
    }

    private fun JSONObject.optLongOrNull(name: String): Long? {
        if (!has(name) || isNull(name)) return null
        return optLong(name)
    }

    private fun JSONObject.optIntOrNull(name: String): Int? {
        if (!has(name) || isNull(name)) return null
        return optInt(name)
    }

    private fun String.urlEncode(): String = URLEncoder.encode(this, StandardCharsets.UTF_8.name())

    private fun normalizeBaseUrl(value: String): String = value.trim().let {
        if (it.endsWith("/")) it else "$it/"
    }

    companion object {
        const val DEFAULT_BASE_URL = "http://10.0.2.2/DS92026/DS9P2Future_Bank/APP/Backend/PHP/api/"
        private const val KEY_BASE_URL = "base_url"
        private const val KEY_TOKEN = "token"
        private const val KEY_EXPIRES = "expires"
        private const val KEY_USER_ID = "user_id"
        private const val KEY_USER_ROLE = "user_role"
        private const val KEY_USER_NAME = "user_name"
        private const val KEY_USER_EMAIL = "user_email"
    }
}
