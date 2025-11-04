<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    protected $connection = 'dbAbsensi';
    protected $table = 'clients';
}
