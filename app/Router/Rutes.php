<?php

namespace App\Router;

use Modules\FormularioKostick\Controllers\KostickTest;
use Modules\FormularioZavick\Controllers\ZavickTest;
use Modules\FormularioPersona\Controllers\Persona;

class Rutes
{
    public function groupRutes()
    {
        return [
            'GET' => [
                '/'                   => [Persona::class, 'index'],
                '/nueva-persona'      => [Persona::class, 'viewNewPersona'],
                '/test-kostick'       => [KostickTest::class, 'index'],
                '/update-kostick'     => [KostickTest::class, 'viewEditTest'],
                '/test-zavic'         => [ZavickTest::class, 'index'],
                '/update-zavick'      => [ZavickTest::class, 'updateform'],

            ],
            'POST' => [
                '/create-user'      => [Persona::class, 'create'],
                '/delete-user'      => [Persona::class, 'delete'],
                '/update-user'      => [Persona::class, 'update'],
                '/create-kostick'   => [KostickTest::class, 'create'],
                '/update-kostick'   => [KostickTest::class, 'update'],
                '/delete-kostick'   => [KostickTest::class, 'delete'],
                '/update-zavick'    => [ZavickTest::class, 'update'],
                '/create-zavick'    => [ZavickTest::class, 'create'],
                '/delete-zavick'    => [ZavickTest::class, 'delete'],
            ]
        ];
    }
}
