<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageRate extends Model
{
    protected $fillable = ['upload_image_id', 'image_path_rate', 'name', 'email', 'rate', 'comment'];

    public function uploadImage()
    {
        return $this->belongsTo(UploadImage::class);
    }
}
