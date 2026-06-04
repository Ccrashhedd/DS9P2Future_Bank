
DROP TABLE IF EXISTS RUTA_DOCUMENTO;
DROP TABLE IF EXISTS DOCUMENTO_POSTULANTE;

DROP TABLE IF EXISTS INSTITUCIONES;


DROP TABLE IF EXISTS GRADOACADEMICO_DOCUMENTO;

CREATE TABLE GRADOACADEMICO_DOCUMENTO (
    idGradoEst INT AUTO_INCREMENT PRIMARY KEY,
    nombreGradoEst VARCHAR(20) NOT NULL UNIQUE
)ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE INSTITUCIONES (
    idInstitucion BIGINT AUTO_INCREMENT PRIMARY KEY,
    nombreInstitucion VARCHAR(250) NOT NULL UNIQUE
)ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE DOCUMENTO_POSTULANTE (
    idDocumentoPostulante INT AUTO_INCREMENT PRIMARY KEY,
    idGradoEst INT NOT NULL, --
    idPostulante BIGINT NOT NULL UNIQUE, -- Esta viene de otra base de datos llamada gestionGeneral

    codigo_provincia VARCHAR(2) NOT NULL, -- Esto para saber la localidad donde se solicito el documento
    
    titulo VARCHAR(100) NOT NULL,
    institucion BIGINT NOT NULL,
    otraInstitucion TINYINT(1) NULL,   -- En caso de que la institucion no este listada
    nombreOtraInstitucion VARCHAR(250) NULL, -- Nombre de la institucion no listada, desabilitada si hay institucion seleccionada valida
    fechaInicio DATE NOT NULL,
    fechaFinalizacion DATE NOT NULL,
    fechaEmision DATE NOT NULL,
    totalHoras INT NOT NULL,

    CONSTRAINT fk_gradoAcademico FOREIGN KEY (idGradoEst) REFERENCES GRADOACADEMICO_DOCUMENTO(idGradoEst),
    CONSTRAINT fk_institucion FOREIGN KEY (institucion) REFERENCES INSTITUCIONES(idInstitucion),

    CONSTRAINT chk_documento_fechaFinalizacionValida CHECK (fechaEmision <= CURDATE() AND fechaEmision >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR)),
    CONSTRAINT chk_documento_fechaFinzalizacionValida CHECK (fechaFinalizacion > fechaInicio),
    CONSTRAINT chk_documento_fechaEmisionValida CHECK (fechaEmision > fechaFinalizacion),
    CONSTRAINT chk_documento_totalHoras CHECK (totalHoras >= 40)
)ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
    
    


CREATE TABLE RUTA_DOCUMENTO (
    idRutadoc INT AUTO_INCREMENT PRIMARY KEY,
    idDocumentoPostulante INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    CONSTRAINT fk_documentoPostulante FOREIGN KEY (idDocumentoPostulante) REFERENCES DOCUMENTO_POSTULANTE(idDocumentoPostulante)

)ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
    

-- Validar los 3 primeros por fecha emision < 5 anios ; horas totales >= 40 horas en general

INSERT INTO GRADOACADEMICO_DOCUMENTO VALUES
(1, 'Certificado'),
(2, 'Curso'),
(3, 'Seminario'),
(4, 'Diploma'),
(5, 'Tecnico'),
(6, 'Licenciatura'),
(7, 'Postgrado'),
(8, 'Maestria'),
(9, 'Doctorado');

INSERT IGNORE INTO INSTITUCIONES (nombreInstitucion) VALUES
('Otra institución'),
('Universidad de Panamá'),
('Universidad Tecnológica de Panamá'),
('Universidad Autónoma de Chiriquí'),
('Universidad Especializada de las Américas'),
('Universidad Marítima Internacional de Panamá'),
('Universidad Católica Santa María La Antigua'),
('Universidad Latina de Panamá'),
('Universidad Interamericana de Panamá'),
('Universidad del Istmo'),
('ISAE Universidad'),
('Universidad Metropolitana de Educación, Ciencia y Tecnología'),
('Columbus University'),
('Universidad Americana'),
('Florida State University - Panamá'),
('Quality Leadership University'),
('Universidad Especializada del Contador Público Autorizado'),
('Universidad del Arte Ganexa'),
('Isthmus - Escuela de Arquitectura y Diseño'),
('ADEN University'),
('Universidad Latinoamericana de Comercio Exterior'),
('Universidad Internacional de Ciencia y Tecnología'),
('Centro Técnico de Estudios Superiores-San Miguelito'),
('Instituto Superior de Ciencias y Tecnología Aeronáuticas'),
('Instituto Superior de Formación Profesional Aeronáutica'),
('Instituto Superior de Ciencias y Tecnología Aeronáuticas'),
('Instituto Superior de Formación Profesional Aeronáutica'),
('Centro de Educación Superior Academia de Formación de Bomberos de Panamá'),
('Centro de Educación Superior Mundial de Capacitación'),
('Instituto Técnico Superior de Cocina'),
('Instituto de Enseñanza Superior Monte Horeb'),
('Centro Técnico de Estudios Superiores-sede Bella Vista Provincia de Panamá'),
('Instituto Bancario Internacional'),
('Centro Tecnológico de Panamá'),
('Instituto Superior Politécnico de América'),
('Instituto Internacional Superior de Comercio y Educación'),
('Instituto Superior de Ciencias y Tecnología'),
('Instituto Técnico Superior Bilingüe Tecno Plus Monterrey'),
('Instituto Nacional de Capacitación Profesional'),
('Instituto Superior Bíblico de las Asambleas de Dios'),
('Instituto Superior de Ingeniería'),
('Instituto Superior de Adiestramiento y Soporte Técnico All Computer'),
('Instituto Superior The Panamá Internacional Hotel School'),
('Instituto Superior Helicópteros Personales “Flight School Division”'),
('Instituto Superior Mag Flight Training'),
('Instituto Superior Policial “Presidente Belisario Porras”'),
('Instituto Superior de Bellas Artes'),
('Instituto Superior Especializado de Artes y Folklore'),
('Instituto Superior Canadian Technical Institute'),
('Instituto Técnico Superior SHADDAI'),
('Instituto Superior Académico de Panamá'),
('Instituto Superior Politécnico Internacional'),
('Instituto Superior de la Judicatura de Panamá Doctor César Augusto Quintero Correa'),
('Centro de Estudios Superiores en Seguridad y Ciencias Forenses'),
('Instituto Superior Flightmaxx Corporation'),
('Instituto Técnico Superior San Pablo Apóstol'),
('Instituto Técnico Superior Panameño'),
('Instituto Técnico Superior by TAC'),
('Instituto Superior Antequera'),
('Instituto Superior de Formación Integral en Seguros'),
('Instituto Superior Benjamín Rosales Pareja'),
('Instituto Superior American Christian School'),
('Instituto Superior de Estética y Belleza APEC, S.A.'),
('Instituto Superior TAGUA'),
('Instituto Superior Helipan Corp.'),
('Instituto Técnico Superior Kaleo'),
('Instituto Técnico Superior de Panamá'),
('Instituto Superior de las Américas S.A.'),
('Centro de Estudios Regionales de Panamá'),
('Instituto Superior de Administración, Investigación y Tecnología'),
('Instituto Superior Bellas Luces'),
('Centro Técnológico de Panamá'),
('Instituto Superior Especializado de Artes y Folklore'),
('Centro de Enseñanza Superior Panamá Pacífico'),
('Instituto Superior Integral del Éxito'),
('Centro de Estudios Superiores de Arte y Folklore, Changuinola'),
('Instituto Superior Los Llanos'),
('Instituto Superior de Estudios Computarizados'),
('Instituto Superior Aeronaval, Teniente de Fragata Manuel Castillo'),
('Instituto Superior Aeronaval, Teniente de Fragata Manuel Castillo'),
('Instituto Superior Especializado de Artes y Folklore'),
('Instituto Superior Publies Educa'),
('Instituto Superior de Competencias'),
('Instituto de Educación Cooperativa'),
('Instituto Superior Nueva Visión'),
('Centro Superior Cultural & Turismo'),
('Instituto Superior de Alta Cocina'),
('Instituto Superior para la Capacitación'),
('Instituto de Educación Superior Nueva Luz'),
('Instituto Superior Centro de Líderes'),
('Instituto Superior Maritime Profesional of Panamá'),
('Instituto Superior Tecnológico del Claustro Gómez'),
('Instituto Superior C&C Technologies'),
('Instituto Superior Nueva Visión'),
('Instituto Superior Bilingüe Culinario de Azuero'),
('Centro de Estudios Superiores de Arte y Folklore'),
('Centro de Estudios Superiores de Artesanía'),
('Instituto Superior Especializado de Artes y Folklore'),
('Centro Técnico de Estudios Superiores'),
('Instituto Superior Bilingüe de Centroamérica'),
('Escuela Nacional de Folklore'),
('Instituto Superior Especializado de Artes y Folklore'),
('Centro Técnico de Estudios Superiores'),
('Instituto Superior Istmeño'),
('Instituto Superior de Educación y Formación Profesional'),
('Centro de Estudio Superior de Panamá'),
('Instituto Superior Heli Training Panamá'),
('Centro Nacional Cooperativo de Formación y Educación Superior'),
('Instituto Superior STG Flight & Services'),
('Centro Técnológico de Panamá'),
('Instituto Superior de Educación Superior Nueva Luz'),
('Instituto Padagógico Superior Juan Demóstenes Arosemena'),
('Instituto Superior Latinoamericano de Administración y Tecnología Naval'),
('Instituto Superior IGA Panamá'),
('Instituto Superior de Investigaciones Criminales y Ciencias Forenses'),
('Instituto Superior de Seguridad Especializada'),
('Instituto Superior de Sistema Computarizado y Docencia'),
('Instituto Superior de Microfinanzas'),
('Instituto Politécnico de Azuero'),
('Instituto de Enseñanza Superior OTEIMA'),
('Institute Superior BRIDGE COMMUNITY COLLEGE'),
('Centro de Estudios Superiores Ingenium'),
('Instituto Superior Panamá Community College'),
('Instituto Superior Panamá Tech');


