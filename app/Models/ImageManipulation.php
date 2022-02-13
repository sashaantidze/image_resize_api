<?php

namespace App\Models;

use App\Models\Album;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageManipulation extends Model
{
    use HasFactory;

    const TYPE_RESIZE = 'resize';

    protected $fillable = [
        'name',
        'path',
        'type',
        'data',
        'output_path',
        'user_id',
        'album_id',
    ];


    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
