<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = ["key", "userId", "uid", "width", "height", "contentType"];

    protected $hidden = ["id", "created_at", "updated_at", "key", "userId"];
}
