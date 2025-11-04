<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadImage extends Model
{
    protected $fillable = [
        "user_id",
        "clients_id",
        "img_before",
        "img_proccess",
        "img_final",
        "note",
        "max_data",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clients()
    {
        return $this->belongsTo(Clients::class);
    }
}
