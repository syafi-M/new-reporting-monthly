<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cover extends Model
{
    /** @use HasFactory<\Database\Factories\CoverFactory> */
    use HasFactory;

    protected $fillable = [
        'clients_id',
        'img_src_1',
        'img_src_2',
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class);
    }
}
