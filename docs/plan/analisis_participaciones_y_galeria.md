# 📋 Análisis de Participaciones, Duplicados y Estructura de la Nueva Galería

**Proyecto:** Concurso Fotográfico «Cáceres en el Foco» — 40º Aniversario Patrimonio de la Humanidad  
**Fecha del análisis:** 23 de noviembre de 2026  
**Ubicación de imágenes públicas:** `public/fotos/` (`https://servicios.hoy.es/caceresenelfoco/public/fotos/`)  
**Ubicación de la galería web:** `galeria/index.php` (`https://servicios.hoy.es/caceresenelfoco/galeria/`)

---

## 1. Resumen General de Datos

| Métrica | Cantidad | Detalle |
| :--- | :---: | :--- |
| **Total de registros en CSV maestro** | **67** | 58 registros exportados de Qualifio + 9 registros de 3 nuevos participantes |
| **Total de archivos físicos en `public/fotos/`** | **67** | Todos con nomenclatura `upload-3543086-<id_foto>.jpg` / `.jpeg` |
| **Fotografías únicas visibles en la galería (`visible_galeria = 1`)** | **61** | Pertenecientes a los **22 participantes reales** del certamen |
| **Envíos duplicados ocultos (`visible_galeria = 0`)** | **5** | Archivos idénticos byte a byte (mismo hash MD5) subidos dos veces por el mismo autor |
| **Prueba técnica previa oculta (`visible_galeria = 0`)** | **1** | Subida de prueba del 26/10/2026 (`Antonio Horrillo`, infografía de gasolina) |

> **Importante:** **Ninguna participación ni archivo ha sido eliminado.** Los 67 registros están presentes en los CSV y los 67 archivos de imagen están guardados en `public/fotos/`. El control de visibilidad en la galería pública se gestiona exclusivamente mediante la columna `visible_galeria` (`1` = visible, `0` = oculto).

---

## 2. Detalle de las 5 Participaciones Duplicadas y la Prueba Técnica (`visible_galeria = 0`)

Según las bases legales del concurso, cada participante podía presentar un **máximo de 3 fotografías**. Durante la subida en Qualifio, varios usuarios pulsaron enviar 4 veces o subieron dos veces exactamente el mismo fichero de imagen.

A continuación se documentan los datos exactos, tamaños en bytes y hashes criptográficos **MD5** para poder rescatarlos o consultarlos rápidamente en cualquier momento:

### 2.1. Jordana Campona (`chochonia@gmail.com`) — 4 envíos en Qualifio (3 fotos únicas + 1 duplicada)
- **ID Participante (`guid`):** `38544d95-65be-4d49-8d0c-31ab536750e7`
- **Fotos visibles en galería (`visible_galeria = 1`):**
  1. `55C5ED7A-903F-4907-A1CB-B2ABCD4D5382` (21/11/2026 14:56:42) ➔ `upload-3543086-55c5ed7a-903f-4907-a1cb-b2abcd4d5382.jpg`
  2. `DB1DAC72-5228-46F6-9546-0809FD749F8B` (21/11/2026 14:56:19) ➔ `upload-3543086-db1dac72-5228-46f6-9546-0809fd749f8b.jpg`
  3. `7088C58E-0981-450A-AAC5-A92282C81D56` (21/11/2026 14:55:54) ➔ `upload-3543086-7088c58e-0981-450a-aac5-a92282c81d56.jpg` (`169.562 bytes`, MD5: `f4bd7bfe7cafed4f9d01bb15c7bf3446`)
- **Envío duplicado oculto (`visible_galeria = 0`):**
  - **ID Foto:** `6131522E-9DEE-462B-A8E2-816B3AC451C9` (21/11/2026 14:55:21)
  - **Archivo:** `upload-3543086-6131522e-9dee-462b-a8e2-816b3ac451c9-4.jpg`
  - **Comprobación:** `169.562 bytes`, MD5 `f4bd7bfe7cafed4f9d01bb15c7bf3446` (**100% idéntica byte a byte** a `7088C58E-0981-450A-AAC5-A92282C81D56`).

---

### 2.2. Pedro Reig Delgado (`marvao@live.com`) — 4 envíos en Qualifio (3 fotos únicas + 1 duplicada)
- **ID Participante (`guid`):** `e6bbe39c-7715-4b2d-b379-8c52a563f321`
- **Fotos visibles en galería (`visible_galeria = 1`):**
  1. `6EC7FBBD-5822-4B34-94FC-921865A226A1` (21/11/2026 14:55:44) ➔ `upload-3543086-6ec7fbbd-5822-4b34-94fc-921865a226a1.jpg` (`216.145 bytes`, MD5: `2ccd32da036a15b52c2100c77eb0a396`)
  2. `DE29B06B-6F90-4DCF-81BF-34525041E43C` (21/11/2026 14:54:15) ➔ `upload-3543086-de29b06b-6f90-4dcf-81bf-34525041e43c.jpg`
  3. `ED733C99-BF8A-4870-97E7-E97D3DAAA9B8` (21/11/2026 14:53:13) ➔ `upload-3543086-ed733c99-bf8a-4870-97e7-e97d3daaa9b8.jpg`
- **Envío duplicado oculto (`visible_galeria = 0`):**
  - **ID Foto:** `48E9BD50-3DFC-4D01-9292-754BAC6E9201` (21/11/2026 14:52:34)
  - **Archivo:** `upload-3543086-48e9bd50-3dfc-4d01-9292-754bac6e9201.jpg`
  - **Comprobación:** `216.145 bytes`, MD5 `2ccd32da036a15b52c2100c77eb0a396` (**100% idéntica byte a byte** a `6EC7FBBD-5822-4B34-94FC-921865A226A1`).

---

### 2.3. Elena S-R (`eschzrico@gmail.com`) — 4 envíos en Qualifio (3 fotos únicas + 1 duplicada)
- **ID Participante (`guid`):** `c2a0b858-a5f6-4b61-9001-aca2a7f35c99`
- **Fotos visibles en galería (`visible_galeria = 1`):**
  1. `67F1176C-121B-4FD2-89DE-A52C7A025676` (21/11/2026 14:52:30) ➔ `upload-3543086-67f1176c-121b-4fd2-89de-a52c7a025676-1.jpeg`
  2. `26CB286D-2048-4B0B-A1BD-EBD74DBAF1B5` (21/11/2026 14:51:37) ➔ `upload-3543086-26cb286d-2048-4b0b-a1bd-ebd74dbaf1b5.jpeg` (`104.704 bytes`, MD5: `e486c3bc2263ab76e234f9c24b11be36`)
  3. `5A846572-1A66-4BF2-8262-799192E7E999` (21/11/2026 14:41:03) ➔ `upload-3543086-5a846572-1a66-4bf2-8262-799192e7e999.jpeg`
- **Envío duplicado oculto (`visible_galeria = 0`):**
  - **ID Foto:** `65E2E6D6-C2F3-411A-9C7C-1CA8C6501A8B` (21/11/2026 14:40:06)
  - **Archivo:** `upload-3543086-65e2e6d6-c2f3-411a-9c7c-1ca8c6501a8b.jpeg`
  - **Comprobación:** `104.704 bytes`, MD5 `e486c3bc2263ab76e234f9c24b11be36` (**100% idéntica byte a byte** a `26CB286D-2048-4B0B-A1BD-EBD74DBAF1B5`).

---

### 2.4. Juan Antonio Carrasco (`jacarabog@gmail.com`) — 4 filas en CSV de Qualifio para sus 3 fotos
- **ID Participante (`guid`):** `5b314876-938a-48e2-8c55-95390186dba9`
- **Contexto:** En Qualifio figuraban 4 filas: 1 con foto subida (`52A2D506...`) y 3 intentos sin URL de foto (`C0013480...`, `53D26E07...` y `3E3E0A03...`). Al recibir sus 3 fotografías originales en `docs/qualifio/juan_antonio_carrasco/` (`1795268004117.jpg`, `20261121_121836.jpeg` y `1795268087707.jpg`), se comprobó que `1795268087707.jpg` es la misma imagen que ya se había subido en `52A2D506-C7C3-4E5A-89FA-5760BE19B735`.
- **Fotos visibles en galería (`visible_galeria = 1`):**
  1. `C0013480-7AFC-44BA-B713-949C1878714D` (21/11/2026 14:57:40) ➔ `upload-3543086-c0013480-7afc-44ba-b713-949c1878714d.jpg` (Original: `1795268004117.jpg`)
  2. `53D26E07-05D9-4409-84AB-449DE88CE87C` (21/11/2026 14:56:46) ➔ `upload-3543086-53d26e07-05d9-4409-84ab-449de88ce87c.jpeg` (Original: `20261121_121836.jpeg`)
  3. `52A2D506-C7C3-4E5A-89FA-5760BE19B735` (21/11/2026 14:55:27) ➔ `upload-3543086-52a2d506-c7c3-4e5a-89fa-5760be19b735.jpg` (Original: `1795268087707.jpg`, `448.066 bytes`, MD5: `7583c8e52962b462973e0863367aed26`)
- **4ª fila sobrante oculta (`visible_galeria = 0`):**
  - **ID Foto:** `3E3E0A03-9630-4952-9514-0F6AFE50D68D` (21/11/2026 14:56:14)
  - **Archivo en disco:** `upload-3543086-3e3e0a03-9630-4952-9514-0f6afe50d68d.jpg` (`448.066 bytes`, MD5: `7583c8e52962b462973e0863367aed26`, copia de respaldo de `1795268087707.jpg`).

---

### 2.5. Carmen Alvarado (`carmenalvaradoparra@gmail.com`) — 3 envíos en Qualifio (2 fotos únicas + 1 duplicada)
- **ID Participante (`guid`):** `d8cf7daa-f86b-40d7-8bcf-c17c26f5e7fe`
- **Fotos visibles en galería (`visible_galeria = 1`):**
  1. `AA01BF38-1339-49AA-9E28-02BD80C96003` (21/11/2026 14:53:31) ➔ `upload-3543086-aa01bf38-1339-49aa-9e28-02bd80c96003.jpg` (`291.202 bytes`, MD5: `ec5f3f459dd32b6ac87cb03c03029ba6`)
  2. `43B2BC40-56F0-4041-9C26-154F9E13440B` (21/11/2026 14:53:04) ➔ `upload-3543086-43b2bc40-56f0-4041-9c26-154f9e13440b.jpg`
- **Envío duplicado oculto (`visible_galeria = 0`):**
  - **ID Foto:** `14FD65C3-DEBC-4C1F-AD97-174F833D7219` (21/11/2026 14:52:35)
  - **Archivo:** `upload-3543086-14fd65c3-debc-4c1f-ad97-174f833d7219-2.jpg`
  - **Comprobación:** `291.202 bytes`, MD5 `ec5f3f459dd32b6ac87cb03c03029ba6` (**100% idéntica byte a byte** a `AA01BF38-1339-49AA-9E28-02BD80C96003`).

---

### 2.6. Prueba Técnica Previa al Concurso (`Antonio Horrillo`, `ahorrillo@hoy.es`)
- **ID Participante (`guid`):** `7ad25081-bc5c-4666-bb85-88fa61c3cc98`
- **ID Foto:** `2E46A75A-0572-4EA3-AB1A-A42955B4D8C2` (Fecha: `2026-10-26 13:35:41`)
- **Archivo en disco:** `upload-3543086-2e46a75a-0572-4ea3-ab1a-a42955b4d8c2.jpg`
- **Contenido:** Infografía de prueba *"El precio de la gasolina HOY"* subida un mes antes del concurso durante la validación de la campaña en Qualifio. Marcada con `visible_galeria = 0`.

---

## 3. Nuevos Participantes Incorporados Manualmente (Fuera del Listado Original de Qualifio)

Se han generado identificadores UUID deterministas compatibles con el formato de Qualifio tanto para el participante (`id_participante` / `guid` en minúsculas) como para cada fotografía (`id_foto` / `Game Id` en mayúsculas y archivo `upload-3543086-<id_foto>.jpg` / `.jpeg`):

### 3.1. Thales Hernández Flores (`docs/qualifio/thales/`)
- **ID Participante (`guid`):** `fb31df35-2ff2-5c4c-8fa1-75eb0ef18dc1`
- **DNI:** `76021207C` · **Teléfono:** `666945722` · **CP:** `10003`
- **Fotografías (3):**
  1. Original: `marco1.JPG` ➔ **ID Foto:** `C8329597-88DF-510D-A61A-FC9E02C801CE` ➔ `upload-3543086-c8329597-88df-510d-a61a-fc9e02c801ce.jpg`
  2. Original: `moviemiento.JPG` ➔ **ID Foto:** `CE1F92AF-A7AC-5C05-85AD-3E6F11C8412F` ➔ `upload-3543086-ce1f92af-a7ac-5c05-85ad-3e6f11c8412f.jpg`
  3. Original: `ojo_pez_1.JPG` ➔ **ID Foto:** `43E01DA0-6937-5F57-97AE-0E8B32270600` ➔ `upload-3543086-43e01da0-6937-5f57-97ae-0e8b32270600.jpg`

### 3.2. Sergio Guerra Álvarez (`docs/qualifio/Sergio Guerra Alvarez/`)
- **ID Participante (`guid`):** `e4b73915-7fd6-573e-973b-e57c0471cf88`
- **CP:** `10005`
- **Fotografías (3):**
  1. Original: `image3.jpeg` ➔ **ID Foto:** `C5E3C2AA-9E93-5217-B92E-65DFD1260F29` ➔ `upload-3543086-c5e3c2aa-9e93-5217-b92e-65dfd1260f29.jpeg`
  2. Original: `image4.jpeg` ➔ **ID Foto:** `80217EE9-1B06-53F2-AF16-77333C99ECBE` ➔ `upload-3543086-80217ee9-1b06-53f2-af16-77333c99ecbe.jpeg`
  3. Original: `image5.jpeg` ➔ **ID Foto:** `9059751F-2135-561A-A601-C63042B471FB` ➔ `upload-3543086-9059751f-2135-561a-a601-c63042b471fb.jpeg`

### 3.3. Miguel José Del Corral Romero (`docs/qualifio/romero/`)
- **ID Participante (`guid`):** `0e2517d9-89e9-5591-9b96-1431b606cc44`
- **DNI:** `76015700X` · **Teléfono:** `610334864` · **Email:** `miguelj.dcr.fotografia@gmail.com`
- **Dirección:** `C/ Profesor Hernandez Pacheco No13 Portal 3 – 2ºC (Cáceres)` · **CP:** `10005`
- **Fotografías (3):**
  1. Original: `EU8A2490.JPG` (corregida rotación EXIF 90°) ➔ **ID Foto:** `C02D0A40-9E29-5505-9E09-2FBF8FF71BF0` ➔ `upload-3543086-c02d0a40-9e29-5505-9e09-2fbf8ff71bf0.jpg`
  2. Original: `EU8A2527.jpg` ➔ **ID Foto:** `7C1E7A0A-F21D-5E86-8D97-73C1566039E0` ➔ `upload-3543086-7c1e7a0a-f21d-5e86-8d97-73c1566039e0.jpg`
  3. Original: `IMG_20261121_133553.jpg` ➔ **ID Foto:** `64C1098C-E383-539D-BD3A-23F1746AD764` ➔ `upload-3543086-64c1098c-e383-539d-bd3a-23f1746AD764.jpg` (en minúsculas: `upload-3543086-64c1098c-e383-539d-bd3a-23f1746ad764.jpg`)

---

### 3.4. Actualización de Titularidad: Jesús Fernández de Tejada Pérez de Acevedo
- **ID Participante (`guid`):** `9f4cb8e3-d146-4dfc-a4f3-0eb2a34fa000`
- **Detalle:** En el registro original de Qualifio figuraban los datos del padre (`Jaime Fernández de Tejada De Almeida`). Se ha actualizado el nombre en todos los nuevos CSV y en la galería a nombre de su hijo participante: **`Jesús Fernández de Tejada Pérez de Acevedo`** (`nombre`: `Jesús`, `apellidos`: `Fernández de Tejada Pérez de Acevedo`).
- **Fotografías (3):**
  1. `7F956710-DA2C-43CE-87D1-F1D0B6D50FB6` ➔ `upload-3543086-7f956710-da2c-43ce-87d1-f1d0b6d50fb6.jpeg`
  2. `987BD650-8B1E-4CF7-9D0C-642D9A0614A6` ➔ `upload-3543086-987bd650-8b1e-4cf7-9d0c-642d9a0614a6.jpeg`
  3. `47F4E41D-AB66-4983-A0D2-9A5879D8D995` ➔ `upload-3543086-47f4e41d-ab66-4983-a0d2-9a5879d8d995.jpeg`

---

## 4. Cómo Rescatar o Modificar la Visibilidad de Cualquier Foto

La galería (`galeria/index.php`) lee dinámicamente el archivo `galeria/participantes_galeria.csv` (cuya copia maestra está en `docs/participaciones/participantes_y_fotos_completo.csv`).

- Para **volver a mostrar** cualquiera de las 5 fotos duplicadas o la foto de prueba en la galería web:
  1. Abre `galeria/participantes_galeria.csv`.
  2. Busca el `id_foto` correspondiente.
  3. Cambia el valor de la columna `visible_galeria` de `"0"` a `"1"` y guarda el archivo.
  4. El cambio se aplica de forma instantánea al recargar la página web.

---

## 5. Gestión de Ganadores Oficiales (Etapa 5 — `/ganadores/`)

- En la portada (`index.php`) se ha habilitado el **4º botón (`4. Ganadores Oficiales` ➔ `ganadores/`)**, que se activa como botón primario cuando el estado del concurso pasa a `GANADORES_PUBLICADOS` (manteniendo el **Botón 3 (`3. Ver Galería Completa` ➔ `galeria/`)** activo como acceso secundario).
- Desde el **Panel de Administración (`panel/index.php`)**, en el bloque **«🏆 4. Selección de Ganadores Oficiales (Etapa 5)»**, se pueden seleccionar mediante desplegable con vista previa en miniatura las 3 fotografías premiadas:
  - `premio_1`: 🥇 **1er Premio** (Samsung Galaxy S25 FE)
  - `premio_2`: 🥈 **2º Premio** (Tarjeta Regalo Amazon de 200€)
  - `premio_3`: 🥉 **3er Premio** (Bono Desayuno + Visita Bodega en Atrio)
- La selección se guarda en `panel/config.json`, pero **no se hace pública al guardarla**: el pódium, el enlace a ganadores y las medallas aparecen únicamente cuando el estado resuelto es `GANADORES_PUBLICADOS`. Con el calendario actual, la publicación está programada para el **2 de diciembre de 2026 a las 10:00 (Europe/Madrid)**.
- Selección registrada a partir de la captura `docs/ganadores/image.png`:
  - 🥇 **Miguel Ángel Muñoz Ruiz** — foto `1BCE1CF4-0AB1-4B2A-833C-6E6C8D3EBCFE`.
  - 🥈 **Pedro Reig Delgado** — foto `6EC7FBBD-5822-4B34-94FC-921865A226A1`.
  - 🥉 **Sara María Rubio Largo** — foto `9DEC633F-EDE4-4664-8718-ADBEE25DFACA`.
