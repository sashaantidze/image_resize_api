<?php

namespace App\Models;

use App\Models\ImageManipulation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];


    public function images()
    {
        return $this->hasMany(ImageManipulation::class);
    }
}
