<?php

namespace Modules\PerfilProfesional\Models;

use App\Models\BaseModel;

/**
 * Catálogo de perfiles profesionales y sus reglas de nombre y estado.
 */
class PerfilProfesionalModel extends BaseModel
{
    /**
     * @var string Tabla del catálogo de perfiles.
     */
    protected string $table = 'perfil_profesional';
    /**
     * @var string Columna que identifica un perfil profesional.
     */
    protected string $primaryKey = 'id_perfil';
    /**
     * @var list<string> Campos del catálogo admitidos para escritura.
     */
    protected array $fillable = ['id_perfil', 'nombre_perfil', 'status'];

    /**
     * Obtiene el catálogo ordenado por nombre e identificador.
     *
     * @param bool $soloActivos Si es true limita la lectura a status = 1.
     * @return list<array{id_perfil: string, nombre_perfil: string|null, status: int|null}> Perfiles encontrados.
     * @throws \Exception Si falla la consulta.
     */
    public function getPerfiles(bool $soloActivos = true): array
    {
        return $this->queryPrepared(
            'SELECT id_perfil, nombre_perfil, status FROM perfil_profesional'
            . ($soloActivos ? ' WHERE status = 1' : '') . ' ORDER BY nombre_perfil, id_perfil'
        );
    }

    /**
     * Busca un perfil por su identificador utilizando la búsqueda general del modelo.
     *
     * @param string $id Identificador del perfil profesional.
     * @param bool $lock Solicita bloqueo; debe llamarse dentro de transaction() cuando es true.
     * @return array{id_perfil: string, nombre_perfil: string|null, status: int|null}|null Perfil encontrado o null.
     * @throws \LogicException Si se solicita bloqueo sin transacción activa.
     * @throws \Exception Si falla la consulta.
     */
    public function getPerfil(string $id, bool $lock = false): ?array
    {
        return $this->find($id, $lock);
    }

    /**
     * Crea o actualiza un perfil después de validar su nombre y estado.
     *
     * @param string|null $id ID del perfil a modificar; null para crear uno nuevo.
     * @param string $nombre Nombre no vacío de hasta 64 caracteres UTF-8; se recortan los extremos.
     * @param int $status Estado permitido: 1 activo o 0 inactivo.
     * @return string ID del perfil creado o actualizado, también al guardar sin cambios.
     * @throws \InvalidArgumentException Si el nombre/estado es inválido o el perfil a editar no existe.
     * @throws \Exception Si falla la persistencia o la generación del identificador.
     */
    public function guardarPerfil(?string $id, string $nombre, int $status = 1): string
    {
        $nombre = trim($nombre);
        if ($nombre === '' || mb_strlen($nombre, 'UTF-8') > 64 || !in_array($status, [0, 1], true)) {
            throw new \InvalidArgumentException('Revisa el nombre del perfil (máximo 64 caracteres) y su estado.');
        }
        return $this->transaction(function () use ($id, $nombre, $status) {
            if ($id === null) {
                $id = self::newId('PERFIL');
                if (!$this->create(['id_perfil' => $id, 'nombre_perfil' => $nombre, 'status' => $status])) {
                    throw new \RuntimeException('No se pudo crear el perfil profesional.');
                }
            } else {
                if (!$this->update($id, ['nombre_perfil' => $nombre, 'status' => $status])) {
                    throw new \InvalidArgumentException('El perfil profesional no existe.');
                }
            }
            return $id;
        });
    }
}
