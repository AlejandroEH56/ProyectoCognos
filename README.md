# ProyectoCognos

Base MVC modular en PHP para administrar personas, configurar cuestionarios Kostick y Zavick y guardar sus evaluaciones en MySQL. Utiliza Composer para la carga PSR-4, `mysqli` para persistencia y Bootstrap para las vistas.

Estado documentado el **7 de noviembre de 2026**: rama `dev/updates`, último commit de aplicación `994f482`, con cambios locales posteriores para perfiles, separación de lógica de negocio y mejoras de `BaseModel`. La base de comparación es `main`, en `84607a6`. El historial por archivo y función está en [CHANGELOG.md](CHANGELOG.md).

## Requisitos

- PHP **8.4**, versión utilizada en las verificaciones locales. Composer no declara una versión mínima del framework; la compatibilidad con otras versiones debe verificarse antes de usarlas.
- Extensiones `mysqli` con `mysqlnd` (`get_result()`), `mbstring`, `iconv` y `pcre`; sesiones habilitadas.
- Composer y Git disponibles desde la terminal.
- MySQL 8 con tablas InnoDB y claves foráneas. El esquema local incluye sintaxis como índices `VISIBLE`.
- Un navegador con JavaScript habilitado.
- Para Apache: `mod_rewrite` y permiso para aplicar el `.htaccess` de `public`.

No hay un paso de compilación de frontend: CSS, JavaScript y fuentes se sirven directamente desde `public/Modules`.

## Instalación

### 1. Obtener el código y las dependencias

```powershell
git clone --branch dev/updates https://github.com/AlejandroEH56/ProyectoCognos.git
cd ProyectoCognos
composer install
composer check-platform-reqs
```

`composer install` reconstruye `vendor` usando las versiones de `composer.lock`. Ejecutar los comandos desde la raíz del repositorio.

### 2. Configurar la conexión

En una instalación nueva, copiar la plantilla. Si ya existe `.env`, editar ese archivo para conservar su configuración.

```powershell
Copy-Item .env.example .env
```

En Linux o macOS, el equivalente de la copia es `cp .env.example .env`.

Completar el archivo con los datos del servidor:

```dotenv
DB_HOST="127.0.0.1"
DB_PORT=3306
DB_DATABASE="db_cognos"
DB_USERNAME="cognos_user"
DB_PASSWORD="cambiar_por_la_clave_local"
```

`DB_HOST`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` deben estar presentes. La contraseña puede estar explícitamente vacía si el usuario de MySQL lo permite. `DB_PORT` usa 3306 de forma predeterminada y admite enteros de 1 a 65535. Para contraseñas con `$`, `${...}`, espacios o `#`, usar comillas simples cuando se necesite conservar el texto literalmente.

El lector es `app/Config/Environment.php`. `ConfigDB.php` consulta cada variable y valida el arreglo antes de que `Database` abra la conexión. La prioridad de lectura es `getenv()` → `$_ENV` → `$_SERVER` → `.env` → valor predeterminado.

### 3. Preparar la base de datos

**El clon actual no incluye el esquema SQL.** `.gitignore` excluye `/Database/`; tampoco están versionados `/tests/` ni `/docs/`. Obtener el esquema del responsable del proyecto antes de iniciar la aplicación.

En el entorno local de desarrollo están disponibles:

| Archivo local | Uso |
| --- | --- |
| `Database/db_cognos.sql` | Crear el esquema y sus tablas. |
| `Database/seed_pruebas_kostick_zavick.sql` | Agregar cinco pares de Kostick y cinco preguntas de Zavick con datos sintéticos. |

Desde MySQL Workbench, abrir el esquema SQL y ejecutarlo sobre una instalación destinada al proyecto. El archivo define el esquema `db_cognos`; si se elige otro nombre, ajustar sus referencias antes de importarlo y configurar el mismo nombre en `DB_DATABASE`.

El esquema contiene además la función SQL `generar_id` con `DEFINER=root@localhost`. Revisar ese definidor y los permisos de creación de rutinas al importar con otro usuario. Los nuevos identificadores de cuestionarios y respuestas se generan desde PHP mediante `BaseModel::newId()`.

Las tablas requeridas por el flujo de evaluaciones son `persona`, `perfil_profesional`, `test`, `pregunta`, `det_pregunta`, `aplicacion`, `aplicacion_test` y `respuesta`. Deben conservarse sus claves foráneas y el campo `respuesta.valor`.

Después de crear el esquema, seleccionar la base de destino e importar el seed sintético, si se dispone de él. También se pueden preparar los catálogos manualmente:

```sql
USE db_cognos;

INSERT INTO test (id_test, nombre, status, descripcion)
SELECT 'TEST-KOSTICK', 'Kostick', 1, 'Cuestionario Kostick'
WHERE NOT EXISTS (SELECT 1 FROM test WHERE nombre = 'Kostick');

INSERT INTO test (id_test, nombre, status, descripcion)
SELECT 'TEST-ZAVICK', 'Zavick', 1, 'Cuestionario Zavick'
WHERE NOT EXISTS (SELECT 1 FROM test WHERE nombre = 'Zavick');

INSERT INTO perfil_profesional (id_perfil, nombre_perfil, status)
SELECT 'PERFIL-PRUEBA', 'Perfil de prueba', 1
WHERE NOT EXISTS (
    SELECT 1 FROM perfil_profesional WHERE id_perfil = 'PERFIL-PRUEBA'
);
```

Los nombres `Kostick` y `Zavick` se utilizan en las consultas de los modelos. Conservarlos. El perfil del ejemplo es opcional: también se puede crear desde `/perfiles-profesionales`. Después, registrar una persona desde la interfaz, seleccionar su perfil y agregar preguntas desde las pantallas de edición.

El esquema ya relaciona `persona.id_perfil` con `perfil_profesional.id_perfil`. Un perfil puede pertenecer a muchas personas y cada persona tiene un perfil asignado. No se necesita alterar ese esquema para los cambios actuales. Las personas antiguas con `id_perfil = NULL` se conservan: asignarles un perfil desde **Lista de personas → Editar** antes de evaluarlas.

### 4. Iniciar el servidor de desarrollo

```powershell
php -S 127.0.0.1:8000 -t public public/router.php
```

Abrir [http://127.0.0.1:8000/](http://127.0.0.1:8000/). La raíz web es `public`; el punto de entrada es `public/index.php`. El `index.php` de la raíz del repositorio fue eliminado y no es necesario para esta configuración.

### 5. Configuración de Apache

Ejemplo para una instalación local en Windows; adaptar la ruta al equipo:

```apache
<VirtualHost *:80>
    ServerName cognos.local
    DocumentRoot "C:/GitHubRepositories/ProyectoCognos/public"

    <Directory "C:/GitHubRepositories/ProyectoCognos/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Habilitar `mod_rewrite`, registrar `127.0.0.1 cognos.local` en el archivo hosts y reiniciar Apache. `public/.htaccess` reescribe las rutas hacia `index.php`, desactiva el listado de directorios y restringe archivos ocultos y `router.php`. El servidor integrado se utiliza para desarrollo; la configuración Apache debe comprobarse en el servidor de destino.

## Uso de la aplicación

1. Abrir `/perfiles-profesionales` desde la pestaña **Perfiles profesionales** y agregar al menos un perfil activo. En este apartado también se puede renombrar, desactivar o reactivar un perfil.
2. Abrir `/nueva-persona` o **Lista de personas → Nuevo**, completar los datos y seleccionar un perfil del catálogo. Para personas existentes, asignarlo o cambiarlo desde **Editar**. Preparar también los registros de ambos tests.
3. Abrir `/update-kostick` para administrar pares de opciones, o `/update-zavick` para administrar preguntas de cuatro opciones. Configurar sus descripciones y valores enteros.
4. Abrir `/test-kostick` o `/test-zavic` y seleccionar la persona. Su perfil se muestra automáticamente en un campo de solo lectura. El servidor utiliza el perfil asociado a esa persona al guardar.
5. En Kostick, elegir una opción por par. En Zavick, asignar 1, 2, 3 y 4 una sola vez por pregunta.
6. Enviar el formulario. La confirmación muestra el identificador de la aplicación guardada.

Cada envío guarda una aplicación, su relación con el test y sus respuestas en una transacción. Kostick conserva el valor configurado de la opción elegida; Zavick guarda el valor asignado durante la evaluación. Las fechas se generan en `America/Mexico_City`.

Las altas y nuevas asignaciones requieren un perfil activo. Se puede conservar el perfil inactivo de una persona al editar otros datos, pero las nuevas evaluaciones requieren que esté activo. La desactivación conserva las relaciones existentes. Cambiar el perfil de una persona afecta a las evaluaciones futuras: las aplicaciones ya guardadas conservan su `id_perfil` original.

El formulario utiliza un token de sesión y una huella del catálogo. Un reenvío idéntico con el mismo token devuelve la aplicación existente; otros datos con ese token se rechazan. Si el catálogo cambia después de abrir el formulario, debe revisarse de nuevo antes de enviar. Ante errores de validación se recuperan las selecciones anteriores.

### Rutas disponibles

| Método | Ruta | Acción |
| --- | --- | --- |
| GET | `/` | `Persona::index()` |
| GET | `/nueva-persona` | `Persona::viewNewPersona()` |
| POST | `/create-user`, `/update-user`, `/delete-user` | `Persona::create()`, `update()`, `delete()` |
| GET | `/perfiles-profesionales` | `PerfilProfesional::index()` |
| POST | `/crear-perfil-profesional` | `PerfilProfesional::create()` |
| POST | `/actualizar-perfil-profesional` | `PerfilProfesional::update()` |
| GET | `/test-kostick` | `KostickTest::index()` |
| GET | `/update-kostick` | `KostickTest::viewEditTest()` |
| POST | `/create-kostick`, `/update-kostick`, `/delete-kostick` | Administración de pares |
| POST | `/guardar-evaluacion-kostick` | `KostickTest::guardarEvaluacion()` |
| GET | `/test-zavic` | `ZavickTest::index()` |
| GET | `/update-zavick` | `ZavickTest::updateform()` |
| POST | `/create-zavick`, `/update-zavick`, `/delete-zavick` | Administración de preguntas |
| POST | `/guardar-evaluacion-zavick` | `ZavickTest::guardarEvaluacion()` |

La ruta pública de Zavick se escribe `/test-zavic`, tal como está registrada en `Rutes.php`.

## Desarrollar con la base MVC

```text
app/
  Config/             Conexión y lector de entorno
  Controllers/        BaseController
  Models/             BaseModel: persistencia general del framework
  Router/             Router y Rutes
  Views/              Vistas compartidas
Modules/<Modulo>/
  Controllers/        Acciones HTTP
  Models/             Persistencia del módulo
  Views/              Plantillas PHP
Modules/Evaluaciones/
  Controllers/        EvaluacionController: flujo común de los tests
  Models/             CuestionarioModel y EvaluacionModel
  Views/              Contexto compartido de las evaluaciones
Modules/PerfilProfesional/Models/
  PerfilProfesionalModel.php  Catálogo y reglas de perfiles
public/
  index.php           Punto de entrada
  router.php          Enrutador para php -S
  .htaccess           Reglas Apache
  Modules/<Modulo>/Resources/  CSS, JavaScript y fuentes
stitch                Generador de módulos por CLI
```

Una solicitud pasa por `public/index.php`, `Router::route()`, `Rutes::groupRutes()` y la acción del controlador. El controlador consulta modelos y entrega datos a las vistas. Composer relaciona `App\` con `app/` y `Modules\` con `Modules/`.

Las clases base del framework contienen persistencia, validación HTTP, sesión y renderizado generales. Las consultas de Cognos, sus reglas, el flujo de evaluaciones y su vista compartida se encuentran en `Modules`. Los controladores de Kostick y Zavick heredan de `Modules\Evaluaciones\Controllers\EvaluacionController`, que a su vez hereda de `BaseController`. `Rutes.php` y la navegación compartida conectan los módulos de esta aplicación.

### Crear un módulo

```powershell
php stitch Ejemplo
```

Genera `Modules/Ejemplo/Controllers/EjemploController.php`, `Modules/Ejemplo/Models/Ejemplo.php`, el directorio `Views` y `public/Modules/Ejemplo/Resources`. El generador crea clases vacías; las acciones, vistas, rutas y subdirectorios `css` o `js` se agregan después.

`BaseController` centraliza la construcción: su constructor `final` comprueba la sesión y después llama a `initialize()`. Los controladores de los módulos definen sus dependencias en ese método protegido; el constructor común se hereda automáticamente y no requiere repetir `parent::__construct()`.

```php
protected function initialize(): void
{
    $this->modelo = new MiModelo();
}
```

En ese ejemplo se debe declarar la propiedad `$modelo` e importar `MiModelo` en el controlador. Si no hay dependencias propias, se puede conservar el método vacío generado por `stitch` o usar directamente el comportamiento heredado. Para adaptar un controlador anterior, trasladar su inicialización de `__construct()` a `initialize(): void` y retirar la llamada al constructor padre. El constructor de la clase base no se sobrescribe.

Agregar al controlador generado:

```php
public function index(): void
{
    $this->renderModule('inicio', ['titulo' => 'Mi módulo']);
}
```

Crear `Modules/Ejemplo/Views/inicio.php`:

```php
<?php /** @var string $titulo */ ?>
<h1><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>
```

Registrar en el grupo `GET` de `app/Router/Rutes.php`:

```php
'/ejemplo' => [\Modules\Ejemplo\Controllers\EjemploController::class, 'index'],
```

El método `renderModule()` resuelve la vista según el namespace del controlador y convierte las claves del arreglo de datos en variables mediante `extract()`. Documentar esas variables con PHPDoc para que Intelephense reconozca sus tipos. Usar rutas basadas en `__DIR__` para incluir archivos y URL como `/Modules/Ejemplo/Resources/css/main.css` para recursos públicos.

### Modelos y operaciones compuestas

Los modelos heredan de `BaseModel` y definen `$table`, `$primaryKey` y `$fillable` para utilizar el CRUD general. Los nombres de tablas y columnas se configuran en el código del modelo; admiten letras ASCII, números y guiones bajos, comenzando por letra o guion bajo. `$table` admite también `esquema.tabla`. Los identificadores se validan y entrecomillan, y los valores se envían como parámetros preparados.

| Método | Acceso | Resultado |
| --- | --- | --- |
| `find($id, $lock = false)` | Público | Fila por clave primaria o `null`. Con bloqueo exige una llamada activa a `transaction()`. |
| `exists($id)` | Público | `true` si existe esa clave; `false` si no existe. |
| `count()` | Público | Total entero de registros de la tabla, sin filtrar por estado. |
| `queryOne($sql, $params)` | Protegido | Primera fila o `null`; la consulta define su orden y límite. |
| `queryPrepared($sql, $params)` | Protegido | Arreglo de filas. |
| `executePrepared($sql, $params)` | Protegido | Número de filas modificadas por una escritura. |
| `create($data)` / `delete($id)` | Públicos | Booleano según inserción o eliminación. |
| `update($id, $data)` | Público | `true` si el registro existe y la sentencia tiene éxito, incluso sin cambios; `false` si no existe. |

`BaseModel::newId('PREFIJO')` crea identificadores aleatorios. `transaction(callable)` confirma la operación completa o revierte sus escrituras ante una excepción; las llamadas anidadas utilizan savepoints sobre la conexión compartida.

`update()` valida los campos permitidos y busca el registro con `SELECT ... FOR UPDATE` dentro de una transacción antes de ejecutar el cambio. Así admite un guardado idéntico sin confundirlo con un ID inexistente y mantiene el bloqueo durante la operación. Los errores SQL lanzan excepciones y un arreglo sin campos permitidos también se rechaza. Se conserva la firma booleana del método; si un módulo necesita el conteo de filas modificadas, puede utilizar `executePrepared()` desde un método específico.

### Documentación PHPDoc

Los 16 controladores y modelos del framework y sus módulos tienen documentación de clase. Sus 105 métodos declaran los parámetros formales con `@param`, los resultados con `@return` y las excepciones relevantes con `@throws`. Las 26 propiedades propias declaran su tipo y finalidad mediante `@var`.

En las acciones HTTP se describen también los campos POST y la información de sesión que actúan como entradas implícitas. Las anotaciones distinguen entre filas o `null`, listas, arreglos con claves concretas, HTML escrito directamente y redirecciones que terminan la ejecución (`never`). Los modelos de ejemplo sin métodos propios remiten a las operaciones heredadas.

Al agregar un método, documentar los nombres reales de sus parámetros y la estructura que devuelve. Las reglas de negocio, como la pertenencia de opciones o el estado de perfiles, se describen junto al método que las aplica. `stitch` incluye PHPDoc en las clases nuevas y en el método `initialize()` generado.

```php
/**
 * Busca un perfil profesional por su identificador.
 *
 * @param string $id Identificador del perfil.
 * @param bool $lock Solicita bloqueo dentro de una transacción.
 * @return array{id_perfil: string, nombre_perfil: string|null, status: int|null}|null Perfil encontrado o null.
 */
```

### Consultar variables individuales

Con el autoload cargado:

```php
use App\Config\Environment;

$environment = new Environment();
$host = $environment->get('DB_HOST');
$port = $environment->get('DB_PORT', 3306);
```

El constructor lee `.env` una vez por instancia. `get()` devuelve `null` cuando falta una variable sin valor predeterminado y conserva cadenas vacías y `"0"`. Para validar una conexión completa se usa `Environment::validateDatabase($config)`; `Environment::database($directorio)` carga y valida el arreglo completo. El puerto queda convertido a entero después de validarlo.

## Verificación y archivos locales

```powershell
composer validate
composer check-platform-reqs
```

Si se dispone de los archivos locales excluidos de Git:

```powershell
php tests/configuracion.php
php tests/modelos.php
php tests/controladores.php
php tests/formularios.php
```

Las suites de configuración y controladores utilizan datos temporales y no necesitan MySQL. La de controladores verifica sesión disponible antes de inicializar dependencias, conservación de sesión e inicialización heredada. La suite de modelos utiliza tablas genéricas InnoDB en una base temporal con prefijo `cognos_model_test_`; requiere conexión y permisos para crear y eliminar esa base. Comprueba también bloqueos entre dos conexiones. La suite de formularios requiere además `curl`, `dom`, `mbstring`, el esquema y el seed locales y posibilidad de iniciar un proceso PHP local. Utiliza una base con prefijo `cognos_regression_`, una copia temporal y su propio servidor; al terminar limpia esos recursos.

Los resultados registrados son **29 casos de configuración, 19 de modelos, 4 de controladores y 105 de formularios, con cero fallos**. Incluyen el contrato de `update()`, las transacciones y bloqueos, la inicialización de controladores, el catálogo de perfiles, la relación de personas y las evaluaciones con perfil automático. El [CHANGELOG](CHANGELOG.md) distingue estos resultados de las verificaciones anteriores. La documentación ampliada y las evidencias locales se encuentran en `docs`, cuando esa carpeta está disponible.

Para una revisión manual en una base temporal con datos sintéticos, ejecutar `php tests/formularios.php --browser`. El proceso muestra su URL local y espera Enter; al pulsarlo detiene el servidor y elimina la base temporal. Este modo prepara el entorno de navegador y omite la suite automatizada.

## Problemas frecuentes

| Mensaje o síntoma | Revisión |
| --- | --- |
| No existe `vendor/autoload.php` | Ejecutar `composer install` desde la raíz. |
| Falta una variable `DB_*` | Completar `.env` o el entorno del servidor; verificar el nombre y las comillas. |
| Falla la conexión | Comprobar servicio MySQL, host, puerto, base, usuario y permisos. |
| No hay perfiles para seleccionar | Abrir **Perfiles profesionales** y agregar o activar uno. |
| La persona no tiene perfil asociado | Asignarlo desde **Lista de personas → Editar**. Las nuevas altas exigen un perfil activo; las personas antiguas con `NULL` se conservan hasta asignarlo. |
| El perfil de la persona está inactivo | Reactivarlo en el catálogo o asignar otro activo a la persona antes de evaluar. |
| El test no está registrado | Crear los catálogos `Kostick` y `Zavick` antes de administrar o responder sus preguntas. |
| Las rutas devuelven 404 | Confirmar que la raíz web sea `public` y que se use `public/router.php` con `php -S` o `mod_rewrite` con Apache. |
| Intelephense marca una clase de Dotenv como indefinida | Confirmar las dependencias y ejecutar `Intelephense: Index workspace` desde la paleta de VS Code. |
| Intelephense marca variables de vistas como indefinidas | Pasar los valores desde el controlador y declarar sus tipos con `@var`. |

`.env` contiene la configuración privada. Se versionan `.env.example`, el código del lector y ambos archivos de Composer. `vendor` se reconstruye durante la instalación.
