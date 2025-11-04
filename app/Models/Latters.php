<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Latters extends Model
{
    /** @use HasFactory<\Database\Factories\LattersFactory> */
    use HasFactory;

    protected $fillable = [
        'cover_id',
        'latter_numbers',
        'latter_matters',
        'period',
        'report_content',
        'signature'
    ];

    public function cover(){
        return $this->belongsTo(Cover::class);
    }
}