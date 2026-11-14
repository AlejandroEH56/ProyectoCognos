# Historial de cambios de ProyectoCognos

Actualizado el **2026-11-07**. Las fechas de commits se presentan en la zona `America/Mexico_City` (UTC−06:00). No se han asignado versiones semánticas a estos cambios.

## Referencias de la comparación

| Referencia | Commit | Fecha | Descripción |
| --- | --- | --- | --- |
| Base `main` y referencia local `origin/main` | `84607a6` | 2025-11-18 | Add Composer autoloading and initial setup |
| Rama actual `dev/updates` | `994f482` | 2026-11-07 10:28:27 | Correcciones de evaluaciones, raíz pública y configuración de entorno |
| Commit intermedio | `9210f18` | 2026-11-07 10:27:59 | Retirada de archivos generados de Composer del seguimiento |

La base común de `main` y `dev/updates` es `84607a6`. Las referencias describen el checkout inspeccionado; no implican una consulta nueva al servidor remoto.

```powershell
git log --oneline main..dev/updates
git diff --stat main...dev/updates
git diff main...dev/updates -- app/Models/BaseModel.php
```

## 2026-11-07 — PHPDoc de controladores y modelos, pendiente de commit

Se documentan las 16 clases de controladores y modelos, sus 105 métodos propios y sus 26 propiedades. Cada método incluye sus entradas formales y resultado; las acciones HTTP detallan los campos POST y datos de sesión utilizados. Se precisan estructuras de arreglos, resultados nulos, redirecciones que terminan la ejecución y excepciones relevantes. Se corrigen etiquetas antiguas de parámetros y conteos que no coincidían con las firmas o consultas vigentes.

| Archivo | Cobertura añadida o corregida |
| --- | --- |
| `app/Controllers/BaseController.php` | Clase y nueve métodos: campos POST, límites, sesión, inicialización, renderizado y redirecciones. |
| `app/Models/BaseModel.php` | Clase, 18 métodos y siete propiedades: consultas, transacciones genéricas, CRUD, identificadores y contratos de retorno. |
| `Modules/Evaluaciones/Controllers/EvaluacionController.php` | Clase y dos métodos: contexto de evaluación, token y entradas del envío HTTP. |
| `Modules/Evaluaciones/Models/CuestionarioModel.php` | Clase y dos métodos: datos del test y estructura de las opciones. |
| `Modules/Evaluaciones/Models/EvaluacionModel.php` | Clase y cuatro métodos: personas/perfiles, huella del catálogo, respuestas y aplicación guardada. |
| `Modules/PerfilProfesional/Controllers/PerfilProfesional.php` | Clase y tres métodos: consulta del catálogo, nombre, estado y redirecciones. |
| `Modules/PerfilProfesional/Models/PerfilProfesionalModel.php` | Clase, tres métodos y tres propiedades: catálogo, bloqueo y guardado de perfiles. |
| `Modules/FormularioPersona/Controllers/Persona.php` | Clase, ocho métodos y una propiedad: captura, perfil, IDs, recuperación de datos y acciones HTTP. |
| `Modules/FormularioPersona/Models/PersonaModel.php` | Clase, seis métodos y tres propiedades: estructura de personas, perfil, conteo, edición y baja lógica. |
| `Modules/FormularioKostick/Controllers/KostickTest.php` | Clase, 12 métodos y una propiedad: pares, valores POST, vistas y envío de evaluación. |
| `Modules/FormularioKostick/Models/DetPreguntaModel.php` | Clase, diez métodos y tres propiedades: opciones, conteo global, pertenencia y operaciones sobre pares. |
| `Modules/FormularioZavick/Controllers/ZavickTest.php` | Clase, 16 métodos y dos propiedades: grupos, tuplas de opciones, validaciones, IDs y acciones HTTP. |
| `Modules/FormularioZavick/Models/DetPreguntaModel.php` | Clase, ocho métodos y tres propiedades: preguntas estructuradas y edición/baja atómica de sus opciones. |
| `Modules/FormularioZavick/Models/PreguntaModel.php` | Clase, cuatro métodos y tres propiedades: encabezados, conteos y corrección del parámetro dataPregunta. |
| `Modules/testmodule/Controllers/testmoduleController.php` | Documentación de la clase de ejemplo y referencia al comportamiento heredado. |
| `Modules/testmodule/Models/testmodule.php` | Documentación de la clase de ejemplo y configuración necesaria para usar el CRUD heredado. |
| `stitch` | PHPDoc de `crearModulo()` y de las clases y el método `initialize()` que genera. |
| `README.md`, `CHANGELOG.md` | Convenciones, alcance de las anotaciones y verificación. |

Verificación: reflexión de las 16 clases con correspondencia exacta entre los parámetros documentados y las 105 firmas, presencia de `@return` en todos los métodos y `@var` en las 26 propiedades. Se compararon los tokens de código de los 16 archivos con las copias tomadas antes de esta tarea, excluyendo comentarios y espacios: no cambian firmas ni lógica. Se comprobó la sintaxis de los controladores/modelos y de `stitch`, se generaron dos clases temporales documentadas y pasaron los cuatro casos existentes de inicialización de controladores. Esta actualización no requirió repetir las pruebas de MySQL porque las clases solo cambian en sus comentarios.

## 2026-11-07 — Inicialización común de controladores, pendiente de commit

`BaseController` utiliza un constructor `final` que inicia la sesión cuando falta y después ejecuta el método protegido `initialize()`. Los módulos heredan el constructor y solo declaran su inicialización propia; dejan de repetir `parent::__construct()`.

| Archivo | Funciones y cambio |
| --- | --- |
| `app/Controllers/BaseController.php` | `__construct()` pasa a ser `final` y llama a `initialize()` después de `startSession()`. Nuevo método protegido `initialize(): void`, vacío por defecto. |
| `Modules/FormularioPersona/Controllers/Persona.php` | Sustituye `__construct()` por `initialize(): void` para crear `PersonaModel`. |
| `Modules/FormularioKostick/Controllers/KostickTest.php` | Sustituye `__construct()` por `initialize(): void` para crear `DetPreguntaModel`. |
| `Modules/FormularioZavick/Controllers/ZavickTest.php` | Sustituye `__construct()` por `initialize(): void` para crear sus dos modelos. |
| `stitch` | `crearModulo()` genera el punto de extensión `initialize()` en los controladores nuevos. |
| `tests/controladores.php` (local, excluido de Git) | Nueva suite de cuatro casos. Comprueba sesión activa antes de inicializar dependencias, conservación de sesión, herencia intermedia y comportamiento sin inicialización propia. |
| `tests/formularios.php` (local, excluido de Git) | Amplía la comprobación de `stitch` para instanciar el controlador generado y verificar su sesión. |
| `README.md`, `CHANGELOG.md`, `docs/arquitectura-modelos.md`, `docs/pruebas.md` | Documentan el patrón y la adaptación de controladores anteriores. Los archivos bajo `docs` permanecen excluidos de Git. |

Compatibilidad: los controladores personalizados deben sustituir su constructor por `protected function initialize(): void`; no pueden sobrescribir el constructor `final`. El método se ejecuta una vez por instancia. Los controladores sin inicialización propia heredan el comportamiento vacío. `public/index.php` conserva su comprobación de sesión; la inicialización del controlador respeta una sesión ya abierta.

Verificación: **4 casos de inicialización y 105 casos de formularios, cero fallos**. La suite de formularios incluye la instanciación del controlador generado por `stitch`. También se comprobó la sintaxis PHP del código y del generador.

## 2026-11-07 — Separación del framework y corrección de update(), pendiente de commit

Se trasladan los modelos de negocio a `Modules`, junto con el flujo y la vista compartida de evaluaciones. `app/Models` conserva únicamente `BaseModel`. Se agregan operaciones generales para reutilizarlas desde los módulos y se corrige el resultado de `update()` al guardar valores idénticos.

| Archivo | Cambio y funciones afectadas |
| --- | --- |
| `app/Models/BaseModel.php` | Nuevas: `queryOne()`, `find()`, `exists()`, `count()` y `quoteIdentifier()`. Modificadas: `create()`, `update()`, `delete()` y prefijo de savepoints en `transaction()`. Búsquedas, conteos, identificadores SQL y guardado válido sin cambios quedan en el framework. |
| `app/Models/CuestionarioModel.php` → `Modules/Evaluaciones/Models/CuestionarioModel.php` | Traslada namespace y dependencia de `BaseModel`. `getTest()` utiliza `queryOne()`; `getOpciones()` conserva las consultas de negocio en el módulo. |
| `app/Models/EvaluacionModel.php` → `Modules/Evaluaciones/Models/EvaluacionModel.php` | Traslada namespace e importa el modelo de perfiles desde su módulo. `guardar()` utiliza `queryOne()` para aplicación y persona; conserva validación, transacciones e historial. |
| `app/Models/PerfilProfesionalModel.php` → `Modules/PerfilProfesional/Models/PerfilProfesionalModel.php` | Traslada namespace y configura tabla, clave y campos permitidos. `getPerfil()` utiliza `find()`; `guardarPerfil()` reutiliza `create()` y `update()`. |
| `app/Controllers/BaseController.php` | Retira `evaluationData()` y `submitEvaluation()`. `redirectError()` utiliza un mensaje general para relaciones que impiden eliminar registros. |
| `Modules/Evaluaciones/Controllers/EvaluacionController.php` | Nueva clase abstracta que hereda de `BaseController`; recibe `evaluationData()` y `submitEvaluation()` con los imports actuales. |
| `app/Views/evaluacionContexto.php` → `Modules/Evaluaciones/Views/evaluacionContexto.php` | Traslada la vista de negocio y actualiza la URL de su JavaScript. |
| `public/js/evaluacionContexto.js` → `public/Modules/Evaluaciones/Resources/js/evaluacionContexto.js` | Reubica el recurso del módulo compartido; conserva la interacción existente. |
| `Modules/FormularioKostick/Controllers/KostickTest.php` | Cambia import y herencia a `EvaluacionController`. |
| `Modules/FormularioZavick/Controllers/ZavickTest.php` | Cambia import y herencia a `EvaluacionController`. |
| `Modules/FormularioKostick/Models/DetPreguntaModel.php` | Importa `CuestionarioModel` desde su módulo; `getRespuestaCount()` usa `count()` y `getIDPreguntaKostick()` usa `queryOne()`. |
| `Modules/FormularioZavick/Models/DetPreguntaModel.php` | Actualiza import. `getRespuestaCount()` utiliza `count()`; `lockQuestion()` utiliza `find()`; `updatePregunta()` delega en `update()` después de comprobar pertenencia de opciones. |
| `Modules/FormularioZavick/Models/PreguntaModel.php` | Actualiza import y reutiliza `count()` en `getPreguntaCount()`. |
| `Modules/FormularioPersona/Models/PersonaModel.php` | Importa perfiles desde su módulo; `getPersonaCount()` utiliza `count()`; `updatePersona()` reutiliza `find()` y `parent::update()` conservando las reglas de estado y perfil. |
| `Modules/FormularioPersona/Controllers/Persona.php` | Actualiza el import de perfiles. |
| `Modules/PerfilProfesional/Controllers/PerfilProfesional.php` | Actualiza el import de su modelo. |
| `Modules/FormularioKostick/Views/formularioTest2.php` | Incluye el contexto desde `Modules/Evaluaciones/Views`. |
| `Modules/FormularioZavick/Views/formularioTest3.php` | Incluye el contexto desde `Modules/Evaluaciones/Views`. |
| `tests/modelos.php` (local, excluido de Git) | Nueva suite de 19 casos con tablas genéricas. Incluye `assertModel()`, `modelCase()` y modelos auxiliares para probar claves y columnas personalizadas. |
| `tests/formularios.php` (local, excluido de Git) | Actualiza la URL del JavaScript compartido y verifica las 105 regresiones con los namespaces y herencia actuales. |
| `README.md`, `CHANGELOG.md` | Documentan distribución, API de consultas, compatibilidad y contrato de `update()`. |
| `docs/arquitectura-modelos.md` (local, excluido de Git) | Nuevo detalle de responsabilidades, contrato de persistencia, reubicaciones y verificación. |
| `docs/README.md`, `docs/pruebas.md`, `docs/perfiles-profesionales.md` (locales, excluidos de Git) | Actualizan referencias y documentación de la arquitectura y sus pruebas. |

`update($id, $data): bool` mantiene la firma pública. Devuelve `true` si el registro existe y la sentencia tiene éxito, incluso con cero valores modificados; devuelve `false` si el ID no existe. Los errores SQL y los datos sin campos permitidos lanzan excepciones. La comprobación de existencia se realiza con bloqueo dentro de una transacción para evitar una carrera entre lectura y escritura. Las llamadas anidadas utilizan savepoints. `executePrepared()` conserva su retorno de filas modificadas.

Validación: **19 casos de persistencia, 105 de formularios y 29 de configuración, cero fallos**. Se verificaron además sintaxis PHP de 43 archivos y `composer validate`. La prueba con dos conexiones confirmó que una escritura concurrente no modifica el registro mientras la transacción exterior mantiene el bloqueo. Las pruebas utilizaron bases temporales; no se realizaron migraciones de las bases de trabajo.

Las rutas HTTP de la aplicación se conservan. Cambian los namespaces PHP de los tres modelos compartidos y la URL del recurso de evaluaciones; todas las referencias de ejecución del repositorio se actualizaron. Las referencias antiguas de las secciones siguientes describen el estado histórico anterior a este traslado.

## 2026-11-07 — Catálogo de perfiles y relación de personas, pendiente de commit

Se reutiliza la clave foránea existente `persona.id_perfil → perfil_profesional.id_perfil`. La inspección de la conexión configurada confirmó esa relación, cero perfiles y dos personas sin perfil asignado. No se ejecutaron migraciones ni asignaciones automáticas sobre esos registros. Las pruebas funcionales utilizaron bases temporales.

El flujo vigente es crear un perfil activo, asignarlo al crear o editar una persona y seleccionar únicamente la persona al evaluar. El perfil de la aplicación se obtiene desde MySQL. Las aplicaciones anteriores conservan su perfil aunque cambie posteriormente la asignación de la persona.

| Archivo | Tipo | Funciones nuevas o modificadas | Cambio |
| --- | --- | --- | --- |
| `app/Models/PerfilProfesionalModel.php` | Nuevo | Nuevas: `getPerfiles()`, `getPerfil()`, `guardarPerfil()` | Catálogo compartido, búsqueda con bloqueo, alta y edición de nombre/estado con validación. |
| `Modules/PerfilProfesional/Controllers/PerfilProfesional.php` | Nuevo | Nuevas: `index()`, `create()`, `update()` | Lista perfiles y procesa altas y cambios desde la interfaz. |
| `Modules/PerfilProfesional/Views/listaPerfiles.php` | Nuevo | Plantilla sin funciones declaradas | Apartado de perfiles con nombre, estado, mensajes y enlace al alta de personas. |
| `app/Router/Rutes.php` | Modificado | `groupRutes()` | Agrega GET `/perfiles-profesionales`, POST `/crear-perfil-profesional` y POST `/actualizar-perfil-profesional`. |
| `app/Views/header.php` | Modificado | Sin funciones declaradas | Agrega la pestaña Perfiles profesionales. |
| `Modules/FormularioPersona/Controllers/Persona.php` | Modificado | Nueva: `readPersona()`. Modificadas: `__construct()`, `index()`, `viewNewPersona()`, `genPersonaId()`, `create()`, `update()`, `delete()` | Consulta el catálogo, valida el perfil seleccionado, genera IDs aleatorios, recupera datos del alta fallida y centraliza errores. |
| `Modules/FormularioPersona/Models/PersonaModel.php` | Modificado | Nueva: `withPerfil()`. Modificadas: `getPersonas()`, `guardarPersona()`, `updatePersona()`, `getPersonaCount()`, `updateStatus()` | Guarda la FK con validación y bloqueo del perfil; consulta el nombre mediante `LEFT JOIN`. Admite edición sin cambios y conservación de una asignación inactiva existente. |
| `Modules/FormularioPersona/Views/selectorPerfil.php` | Nuevo | Plantilla sin funciones declaradas | Selector compartido del catálogo para las altas y la edición. |
| `Modules/FormularioPersona/Views/nuevaPersona.php` | Modificado | Sin funciones declaradas | Sustituye texto libre por `id_perfil`, recupera campos tras errores, informa cuando falta catálogo y corrige la etiqueta CURP y una referencia JavaScript inexistente. |
| `Modules/FormularioPersona/Views/listaPersonas.php` | Modificado | Sin funciones declaradas | Entrega perfiles a los modales y señala asignaciones pendientes o inactivas. |
| `Modules/FormularioPersona/Views/modals_persona.php` | Modificado | Sin funciones declaradas | Incorpora selectores para crear y editar personas. |
| `public/Modules/FormularioPersona/Resources/js/personaModals.js` | Modificado | Callbacks de carga y apertura de modales | Precarga `id_perfil` en edición, admite ausencia de la librería de tablas y construye el texto de confirmación sin interpretar HTML. |
| `app/Controllers/BaseController.php` | Modificado | `evaluationData()`, `submitEvaluation()` | Deja de entregar un catálogo independiente y de exigir `id_perfil` en el POST de evaluación. |
| `app/Models/EvaluacionModel.php` | Modificado | Modificadas: `getPersonas()`, `guardar()`. Retirada: `getPerfiles()` | Obtiene el perfil desde la persona bajo bloqueo y exige que esté activo. `guardar()` deja de aceptar el argumento de perfil. Conserva los reintentos y el perfil histórico de aplicaciones guardadas. |
| `app/Views/evaluacionContexto.php` | Modificado | Sin funciones declaradas | Muestra el perfil de la persona en un campo de solo lectura y avisa si faltan asignaciones activas. |
| `public/js/evaluacionContexto.js` | Nuevo | Callback de carga y función local `mostrarPerfil()` | Actualiza el perfil mostrado al seleccionar persona y al reiniciar el formulario. |
| `Modules/FormularioKostick/Views/formularioTest2.php`, `Modules/FormularioZavick/Views/formularioTest3.php` | Modificados | Sin funciones declaradas | Ajustan PHPDoc a los datos actuales del contexto de evaluación. |
| `tests/formularios.php` (local, excluido de Git) | Modificado | Nueva: `personaPayload()`. Modificadas: `fixture()`, `evaluation()`, `runCase()`; casos de prueba y arranque CLI | Agrega cobertura del catálogo, asignación, perfiles inactivos, entradas manipuladas e historial. Ofrece `--browser` para preparar un entorno manual temporal. |
| `docs/perfiles-profesionales.md` (local, excluido de Git) | Nuevo | No aplica | Relación, uso, compatibilidad de datos antiguos y pruebas. |
| `docs/README.md`, `docs/pruebas.md` (locales, excluidos de Git) | Modificados | No aplica | Actualizan el flujo vigente y los resultados de pruebas. |
| `docs/img/perfil-persona-evaluacion.png` (local, excluido de Git) | Nuevo | No aplica | Evidencia visual con datos sintéticos del perfil automático. |
| `docs/resultados-perfiles.txt` (local, excluido de Git) | Nuevo | No aplica | Salida de los 105 casos de la suite final de formularios. |
| `README.md`, `CHANGELOG.md` | Nuevos y actualizados en el trabajo local | No aplica | Documentan instalación, rutas, flujo de perfiles y cambios pendientes. |

Validación: **105 casos de formularios y evaluaciones y 29 de configuración, cero fallos**; sintaxis PHP de 41 archivos y JavaScript de los archivos nuevos/modificados. Navegador: alta de perfil, alta de persona asociada, precarga en edición, guardado Kostick, perfil automático en ambos formularios y reinicio en Zavick; sin errores ni avisos JavaScript registrados. El entorno manual y su base temporal se eliminaron al finalizar.

## 2026-11-07 — Documentación inicial nueva, pendiente de commit

| Archivo | Estado | Contenido |
| --- | --- | --- |
| `README.md` | Nuevo | Requisitos, instalación, `.env`, preparación de MySQL, raíz `public`, rutas, uso de formularios, generador de módulos, API básica y verificación. |
| `CHANGELOG.md` | Nuevo | Comparación con `main`, fechas verificables, inventario de archivos y métodos, cambios de despliegue y resultados de pruebas. |

Estos documentos se crean después de `994f482`. No modifican funciones de la aplicación.

## 2026-11-07 — Integración en `dev/updates`

Los dos commits tienen el título `feat: corregir evaluaciones y configurar acceso desde public`, pero contienen cambios distintos:

- `9210f18`: elimina del índice los 11 archivos previamente versionados bajo `vendor`.
- `994f482`: incorpora las correcciones y los archivos descritos a continuación.

La fecha de las tablas siguientes es la de **integración en Git**. Las comprobaciones de formularios del 5 de noviembre y de la raíz pública del 6 de noviembre se registran por separado al final; no corresponden a commits adicionales.

### Configuración y dependencias

| Archivo | Tipo | Funciones nuevas o modificadas | Cambio respecto de `main` |
| --- | --- | --- | --- |
| `app/Config/Environment.php` | Nuevo | Nuevas: `__construct()`, `get()`, `database()`, `validateDatabase()` | Carga `.env` una vez por instancia, consulta una variable con valor predeterminado y prioridad del entorno, valida campos y puerto. Captura `InvalidFileException` con un mensaje general de sintaxis. |
| `app/Config/ConfigDB.php` | Modificado | Código de configuración, sin funciones declaradas | Sustituye credenciales literales por consultas individuales `Environment::get()` y validación del arreglo. Carga Composer desde la ubicación del proyecto. |
| `app/Config/Database.php` | Modificado | Modificadas: `__construct()`, `connect()` | Corrige el namespace a `App\Config`, incluye la configuración con `__DIR__` y pasa el puerto a `mysqli`. |
| `.env.example` | Nuevo | No aplica | Plantilla de las cinco variables `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`. |
| `.gitignore` | Nuevo | No aplica | Excluye dependencias, credenciales, temporales, exportaciones, `Database`, `tests`, `docs` y `artifacts`; permite versionar `.env.example`. |
| `composer.json` | Modificado | No aplica | Agrega `vlucas/phpdotenv` con restricción `^5.6@stable`. Mantiene autoload PSR-4 de `App\` y `Modules\`. |
| `composer.lock` | Modificado | No aplica | Fija phpdotenv 5.8.0 y sus dependencias para reconstruir `vendor` con `composer install`. |
| `vendor/autoload.php`, `vendor/composer/*` previamente versionados | Retirados de Git | Archivos generados de Composer | Se retiran 11 archivos del seguimiento; la instalación local los reconstruye. Inventario exacto al final. |

La consulta individual conserva valores vacíos y `"0"`. Una variable ausente devuelve `null` o el valor predeterminado. La validación exige los datos de conexión y convierte `DB_PORT` a entero de 1 a 65535.

### Núcleo MVC y persistencia

| Archivo | Tipo | Funciones nuevas o modificadas | Cambio respecto de `main` |
| --- | --- | --- | --- |
| `app/Controllers/BaseController.php` | Modificado | Nuevas: `postString()`, `postInteger()`, `redirectMessage()`, `redirectError()`, `evaluationData()`, `submitEvaluation()` | Centraliza validación, redirecciones 303, mensajes, token de sesión, huella de catálogo y recuperación de selecciones. Registra las excepciones y traduce errores de validación o claves foráneas. |
| `app/Models/BaseModel.php` | Modificado | Nuevas: `newId()`, `transaction()`. Modificadas: `connect()`, `queryPrepared()`, `executePrepared()`, `getParamTypes()` | Comparte la conexión entre modelos, cierra sentencias sin cerrar esa conexión, soporta transacciones anidadas con savepoints e incorpora tratamiento explícito de `NULL` en los parámetros. |
| `app/Models/CuestionarioModel.php` | Nuevo | Nuevas: `getTest()`, `getOpciones()` | Resuelve tests por nombre, permite bloquearlos y obtiene preguntas y opciones ordenadas conservando preguntas sin opciones mediante `LEFT JOIN`. |
| `app/Models/EvaluacionModel.php` | Nuevo | Nuevas: `getPersonas()`, `getPerfiles()`, `catalogHash()`, `selectedResponses()`, `guardar()` | Lista personas y perfiles activos; valida cuestionarios, pertenencia de respuestas y vigencia del catálogo; guarda aplicación, relación y respuestas de forma atómica. |
| `app/Router/Rutes.php` | Modificado | Modificada: `groupRutes()` | Registra POST `/guardar-evaluacion-kostick` y `/guardar-evaluacion-zavick`. |
| `app/Views/evaluacionContexto.php` | Nuevo | Plantilla, sin funciones declaradas | Comparte los selectores de persona y perfil, token oculto, recuperación de valores y aviso cuando faltan catálogos activos. |
| `app/Views/footer.php` | Modificado | Sin funciones declaradas | Cambio de formato: eliminación del salto de línea final; sin cambio funcional. |

`EvaluacionModel::guardar()` usa un ID de aplicación derivado del token. Un reintento idéntico devuelve la aplicación existente; otro contenido con ese token se rechaza. El perfil elegido se registra en `aplicacion.id_perfil`. Las fechas y horas se generan en `America/Mexico_City`.

### Módulo Kostick

| Archivo | Tipo | Funciones nuevas o modificadas | Cambio respecto de `main` |
| --- | --- | --- | --- |
| `Modules/FormularioKostick/Controllers/KostickTest.php` | Modificado | Nuevas: `readOptions()`, `guardarEvaluacion()`. Modificadas: `__construct()`, `index()`, `viewEditTest()`, `formView()`, `editFormView()`, `create()`, `update()`, `delete()`, `genPreguntaId()`, `getIdPreguntaKostick()` | Inicializa la sesión mediante el constructor base, integra contexto de evaluación y mensajes, valida ambas opciones, delega operaciones al modelo transaccional y usa IDs aleatorios. |
| `Modules/FormularioKostick/Models/DetPreguntaModel.php` | Modificado | Nuevas: `createPair()`, `lockPair()`, `updatePair()`, `deletePair()`. Modificadas: `getEvalTest2()`, `getRespuestaCount()`, `getIDPreguntaKostick()`. PHPDoc de `createDetPregunta()` | Hereda de `CuestionarioModel`, elimina la dependencia del nombre fijo de la base, valida pares vigentes y ejecuta altas, cambios y bajas completas. Crea la pregunta contenedora cuando falta. |
| `Modules/FormularioKostick/Views/formularioTest2.php` | Modificado | Plantilla, sin funciones declaradas | Configura POST de evaluación, incluye persona/perfil/token y ajusta includes mediante `__DIR__`; declara tipos de datos recibidos. |
| `Modules/FormularioKostick/Views/campoTest2.php` | Modificado | Plantilla, sin funciones declaradas | Envía el ID real de cada opción en `respuestas[pregNNN]`, recupera selecciones, requiere respuesta por par y avisa sobre catálogos incompletos. Agrega PHPDoc. |
| `Modules/FormularioKostick/Views/updateFormTest2.php` | Modificado | Plantilla, sin funciones declaradas | Ajusta includes, presentación de mensajes y anotaciones de variables recibidas. |
| `Modules/FormularioKostick/Views/updateCampoTest2.php` | Modificado | Plantilla, sin funciones declaradas | Corrige los identificadores y la precarga de valores de las dos opciones; ajusta escape de datos y PHPDoc. |

La actualización admite cambiar solo una opción o guardar sin cambios. La baja debe corresponder a un par completo. Los identificadores nuevos mantienen juntas sus dos opciones al ordenar el catálogo. Los errores revierten las escrituras de ambas opciones.

### Módulo Zavick

| Archivo | Tipo | Funciones nuevas o modificadas | Cambio respecto de `main` |
| --- | --- | --- | --- |
| `Modules/FormularioZavick/Controllers/ZavickTest.php` | Modificado | Nuevas: `readRespuestas()`, `guardarEvaluacion()`. Modificadas: `__construct()`, `index()`, `formPreguntas()`, `updateform()`, `updateField()`, `sanitizedData()`, `formatData()`, `create()`, `update()`, `validateRespuestas()`, `delete()`, `genIDPregunta()`, `genIDRespuesta()` | Inicializa la sesión mediante el constructor base, usa datos estructurados, valida cuatro opciones distintas y valores enteros, admite ediciones parciales y guarda evaluaciones. Genera IDs aleatorios. |
| `Modules/FormularioZavick/Models/DetPreguntaModel.php` | Modificado | Nuevas: `lockQuestion()`, `createQuestion()`, `deleteQuestion()`. Modificadas: `getEvalTest3()`, `getRespuestaCount()`, `updatePregunta()`, `createDetPreguntas()`, `deleteDetPreguntas()` | Sustituye el empaquetado de textos por arreglos de preguntas y opciones. Verifica pertenencia al test y a la pregunta, usa transacciones y permite eliminar preguntas antiguas incompletas. |
| `Modules/FormularioZavick/Models/PreguntaModel.php` | Modificado | Modificadas: `getIDTest()`, `getPreguntaCount()`; propiedad `$table` | Hereda de `CuestionarioModel`, resuelve el test por nombre y usa tablas de la conexión configurada. |
| `Modules/FormularioZavick/Views/formularioTest3.php` | Modificado | Plantilla, sin funciones declaradas | Agrega POST de evaluación y contexto compartido; ajusta includes y PHPDoc. |
| `Modules/FormularioZavick/Views/campoTest3.php` | Modificado | Plantilla, sin funciones declaradas | Identifica controles y nombres por ID de opción; conserva textos repetidos o con barras y recupera respuestas. Declara tipos y avisa si una pregunta no tiene cuatro opciones. |
| `Modules/FormularioZavick/Views/updateZavick.php` | Modificado | Plantilla, sin funciones declaradas | Ajusta includes, mensajes y tipos de variables. |
| `Modules/FormularioZavick/Views/updateCampoZavick.php` | Modificado | Plantilla, sin funciones declaradas | Renderiza opciones estructuradas con sus IDs, datos para modales y anotaciones PHPDoc. |
| `Modules/FormularioZavick/Views/modals_zavick.php` | Modificado | Plantilla, sin funciones declaradas | Corrige el título de alta a «Nueva Pregunta» y precisa los límites de valores enteros. |
| `public/Modules/FormularioZavick/Resources/js/formCheck.js` | Reubicado y modificado | Modificadas: `validarRadiosGrupo()`, `adjuntarListenersChange()`, `detectarGrupos()`; callbacks de carga, envío y reset | Detecta todos los grupos, exige cuatro opciones completas con valores diferentes y limpia errores al cancelar. |
| `public/Modules/FormularioZavick/Resources/js/zavickModals.js` | Reubicado y modificado | Callbacks de `DOMContentLoaded` y `show.bs.modal`, sin funciones con nombre | Completa IDs desde las opciones reales y construye el mensaje con nodos de texto; tolera preguntas incompletas. |

El servidor exige las puntuaciones 1, 2, 3 y 4 exactamente una vez por pregunta al guardar la evaluación. Los valores administrativos de las opciones se validan como enteros de MySQL; esa validación es distinta de la puntuación enviada por la persona.

### Personas y módulo generado

| Archivo | Tipo | Funciones nuevas o modificadas | Cambio respecto de `main` |
| --- | --- | --- | --- |
| `Modules/FormularioPersona/Models/PersonaModel.php` | Modificado | Modificadas: `getPersonas()`, `guardarPersona()`, `getPersonaCount()`; propiedad `$table` | Usa `persona` en la conexión seleccionada, en lugar de `db_cognos.persona`. |
| `Modules/FormularioPersona/Views/listaPersonas.php` | Modificado | Plantilla, sin funciones declaradas | Resuelve includes con `__DIR__` y declara los tipos de variables recibidas. |
| `Modules/FormularioPersona/Views/nuevaPersona.php` | Modificado | Plantilla, sin funciones declaradas | Resuelve includes con `__DIR__` y declara los tipos de variables recibidas. |
| `Modules/testmodule/Controllers/testmoduleController.php` | Nuevo | Ningún método propio | Clase generada que hereda de `BaseController`; sin rutas registradas. |
| `Modules/testmodule/Models/testmodule.php` | Nuevo | Ningún método propio | Clase generada que hereda de `BaseModel`. |

`Modules/FormularioPersona/Controllers/Persona.php` no cambió respecto de `main`: `create()` continúa enviando `id_perfil = NULL`. El esquema de `persona` debe admitir ese valor. La selección obligatoria de perfil pertenece al guardado de la aplicación de evaluación.

### Raíz pública y generador

| Archivo | Tipo | Funciones nuevas o modificadas | Cambio respecto de `main` |
| --- | --- | --- | --- |
| `public/index.php` | Nuevo punto de entrada; sustituye al de raíz | Código de arranque, sin funciones declaradas | Carga Composer desde la raíz del proyecto, inicia sesión, normaliza `/index.php` a `/` y ejecuta el router. |
| `public/router.php` | Nuevo | Código de enrutamiento, sin funciones declaradas | Soporta `php -S`: sirve recursos públicos permitidos y envía las demás solicitudes a `index.php`. |
| `public/.htaccess` | Nuevo; sustituye al de raíz | No aplica | Define entrada y reescritura Apache, desactiva listado y restringe ocultos y `router.php`. |
| `index.php` | Eliminado de la raíz | Se retira el arranque anterior | El servidor debe apuntar a `public`. |
| `.htaccess` | Eliminado de la raíz | No aplica | Las reglas de publicación se ubican en `public/.htaccess`. |
| `stitch` | Modificado | Modificada: `crearModulo()` | Conserva código en `Modules/<nombre>` y crea recursos en `public/Modules/<nombre>/Resources`. |
| 58 archivos bajo `Modules/*/Resources` | Traslado | Solo los dos JavaScript de Zavick cambian comportamiento | Pasan a `public/Modules/*/Resources`, manteniendo URL `/Modules/...`. Los otros 56 archivos conservan su contenido. |

**Cambio de despliegue:** configurar el document root en `public`. El comando de desarrollo es `php -S 127.0.0.1:8000 -t public public/router.php`. No se conserva un punto de entrada PHP en la raíz del repositorio.

### Última comprobación del editor — 2026-11-07

Se verificó que `Dotenv\Exception\InvalidFileException` existe en la dependencia instalada y que Composer puede cargarla. El aviso de tipo indefinido de `Environment.php` desapareció al ejecutar **Intelephense: Index workspace** en VS Code. La corrección del índice no requirió modificar código ni dependencias.

### Inventario de recursos publicados

Cada ruta de este inventario corresponde a un archivo que antes estaba bajo `Modules/` con el mismo resto de la ruta. Los dos scripts de Zavick se reescribieron durante el traslado; Git los representa como baja y alta. Los demás aparecen como renombres con contenido idéntico.

```text
public/Modules/FormularioKostick/Resources/css/bootstrap.min.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/brands.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/brands.min.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/fontawesome.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/fontawesome.min.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/regular.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/regular.min.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/solid.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/solid.min.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/svg-with-js.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/svg-with-js.min.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/svg.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/svg.min.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/v4-font-face.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/v4-font-face.min.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/v4-shims.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/v4-shims.min.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/v5-font-face.css
public/Modules/FormularioKostick/Resources/css/fontawesome/css/v5-font-face.min.css
public/Modules/FormularioKostick/Resources/css/fontawesome/webfonts/fa-brands-400.woff2
public/Modules/FormularioKostick/Resources/css/fontawesome/webfonts/fa-regular-400.woff2
public/Modules/FormularioKostick/Resources/css/fontawesome/webfonts/fa-solid-900.woff2
public/Modules/FormularioKostick/Resources/css/fontawesome/webfonts/fa-v4compatibility.woff2
public/Modules/FormularioKostick/Resources/css/main.css
public/Modules/FormularioKostick/Resources/js/bootstrap.bundle.min.js
public/Modules/FormularioKostick/Resources/js/kostickModals.js
public/Modules/FormularioPersona/Resources/css/bootstrap.min.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/brands.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/brands.min.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/fontawesome.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/fontawesome.min.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/regular.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/regular.min.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/solid.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/solid.min.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/svg-with-js.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/svg-with-js.min.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/svg.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/svg.min.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/v4-font-face.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/v4-font-face.min.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/v4-shims.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/v4-shims.min.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/v5-font-face.css
public/Modules/FormularioPersona/Resources/css/fontawesome/css/v5-font-face.min.css
public/Modules/FormularioPersona/Resources/css/fontawesome/webfonts/fa-brands-400.woff2
public/Modules/FormularioPersona/Resources/css/fontawesome/webfonts/fa-regular-400.woff2
public/Modules/FormularioPersona/Resources/css/fontawesome/webfonts/fa-solid-900.woff2
public/Modules/FormularioPersona/Resources/css/fontawesome/webfonts/fa-v4compatibility.woff2
public/Modules/FormularioPersona/Resources/css/main.css
public/Modules/FormularioPersona/Resources/js/bootstrap.bundle.min.js
public/Modules/FormularioPersona/Resources/js/main.js
public/Modules/FormularioPersona/Resources/js/personaModals.js
public/Modules/FormularioZavick/Resources/css/bootstrap.min.css
public/Modules/FormularioZavick/Resources/css/main.css
public/Modules/FormularioZavick/Resources/js/bootstrap.bundle.min.js
public/Modules/FormularioZavick/Resources/js/formCheck.js
public/Modules/FormularioZavick/Resources/js/zavickModals.js
```

### Archivos de Composer retirados del seguimiento

```text
vendor/autoload.php
vendor/composer/ClassLoader.php
vendor/composer/InstalledVersions.php
vendor/composer/LICENSE
vendor/composer/autoload_classmap.php
vendor/composer/autoload_namespaces.php
vendor/composer/autoload_psr4.php
vendor/composer/autoload_real.php
vendor/composer/autoload_static.php
vendor/composer/installed.json
vendor/composer/installed.php
```

## Verificaciones locales anteriores a la integración

Las fechas de esta sección proceden de los registros locales de pruebas. Los archivos indicados están excluidos de Git por la configuración actual, por lo que su existencia en este equipo no garantiza su disponibilidad en un clon.

| Fecha o alcance | Archivos locales | Funciones o cobertura | Resultado documentado |
| --- | --- | --- | --- |
| 2026-11-05: correcciones de formularios | `tests/formularios.php`, `docs/README.md`, `docs/pruebas.md` | CRUD, guardado, reintentos, rollback, catálogos, validación y comprobaciones de navegador | 58 casos, cero fallos; recorrido del navegador sin errores JavaScript. |
| 2026-11-06: raíz pública | `tests/formularios.php`, `docs/public.md` | Recursos, rutas internas, acceso desde `public` y generación de módulos | 74 casos, cero fallos. |
| Estado final de la configuración, comprobado el 2026-11-07 | `tests/configuracion.php`, `docs/configuracion-env.md` | Nuevas funciones auxiliares de prueba: `check()`, `readConfig()`, `rejects()`, `runCase()`; comillas, prioridad, valores vacíos, consultas individuales y validación de puerto | 29 casos, cero fallos. |
| Integración final de `.env`, registrada en la documentación local | `tests/formularios.php`, `docs/configuracion-env.md` | Amplía la suite con configuración sintética y solicitudes a rutas de `.env` fuera de `public` | 77 casos, cero fallos. La evidencia consultada no especifica otra fecha de ejecución. |
| Datos de prueba locales | `Database/seed_pruebas_kostick_zavick.sql` | SQL, sin nuevas funciones PHP: cinco pares Kostick y cinco preguntas Zavick con cuatro opciones | Datos sintéticos reutilizables; no constituyen un instrumento psicométrico validado. |

La suite de formularios incluye auxiliares `check()`, `copyTree()`, `queryAll()`, `countRows()`, `fixture()`, `rawHttp()`, `http()`, `xpath()`, `success()`, `rejected()`, `runCase()`, `kPayload()`, `zPayload()`, `zNew()` y `evaluation()`. Crea una base y un servidor temporales, ejecuta solicitudes por el flujo HTTP y limpia sus recursos al terminar. El [README](README.md) describe los requisitos para ejecutarla cuando se dispone de estos archivos locales.

Las anotaciones PHPDoc de 11 vistas se comprobaron con Intelephense 1.18.5 para los diagnósticos P1008. La vista nueva de contexto compartido también declara los datos que recibe. El contenido antiguo de `docs/public.md` aún muestra un delegado `index.php` en la raíz; el estado vigente es el descrito aquí y en el README: ese archivo ya fue eliminado.

## 2025-11-18 — Base principal

Commit `84607a6`, `Add Composer autoloading and initial setup`.

Base MVC con namespaces `App` y `Modules`, carga automática Composer, router por método/ruta, controladores y modelos base, módulos de personas/Kostick/Zavick, generador `stitch` y recursos dentro de cada módulo. La entrada HTTP y `.htaccess` estaban en la raíz. La conexión utilizaba configuración literal en `ConfigDB.php` y había archivos generados de `vendor` bajo seguimiento.

Los cambios de noviembre de 2026 se comparan con esta referencia, sin atribuir a la rama actual funciones que ya existían en la base.
