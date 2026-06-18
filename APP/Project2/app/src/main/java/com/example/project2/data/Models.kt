package com.example.project2.data

data class UserDto(
    val idUsuario: Long,
    val rolUsuario: Int,
    val nombreUsuario: String,
    val correo: String
)

data class AuthSession(
    val token: String,
    val expiraEn: String,
    val user: UserDto
)

data class PostulanteDto(
    val tienePostulacion: Boolean,
    val idPostulante: Long?,
    val nombreCompleto: String?,
    val cedula: String?,
    val genero: String?,
    val estadoCivil: String?,
    val apellidoCasada: String?,
    val rangoAcademico: String?,
    val tipoSangre: String?,
    val fechaNacimiento: String?,
    val correoPostulante: String?,
    val telefono: String?,
    val telefono2: String?,
    val celular: String?,
    val celular2: String?,
    val provincia: String?,
    val distrito: String?,
    val corregimiento: String?,
    val comunidad: String?,
    val calle: String?,
    val casa: String?,
    val detalleDireccion: String?,
    val direccionCompleta: String?
)

data class DocumentoDto(
    val idDocumentoPostulante: Long,
    val titulo: String,
    val tipo: String,
    val institucion: String,
    val fechaInicio: String?,
    val fechaFinalizacion: String?,
    val fechaEmision: String?,
    val totalHoras: Int?,
    val tienePdf: Boolean
)

data class TipoDocumentoDto(
    val idGradoEst: Int,
    val nombreGradoEst: String
)
