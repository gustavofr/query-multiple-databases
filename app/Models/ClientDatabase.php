<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientDatabase extends Model
{
    /**
     * Define o nome da conexão que será usada pelo modelo
     *
     * @var string
     */
    protected $connection = 'superadmin';
}
