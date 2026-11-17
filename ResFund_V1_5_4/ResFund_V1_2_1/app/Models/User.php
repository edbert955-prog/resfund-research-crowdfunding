<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Model Laravel standar untuk kemungkinan pengembangan autentikasi berbasis DB.
 * V1.2.1 tidak menggunakan model ini; login masih berupa simulasi session.
 */
class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];
}
