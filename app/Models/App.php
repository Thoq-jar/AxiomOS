<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class App extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'version',
        'category',
        'icon',
        'featured',
        'installed',
        'docker_image',
        'ports',
        'environment_vars'
    ];

    protected $casts = [
        'featured' => 'boolean',
        'installed' => 'boolean',
        'ports' => 'array',
        'environment_vars' => 'array'
    ];
}
