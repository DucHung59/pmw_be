<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, Notifiable;

    protected $table = 'tblUsers';
    protected $fillable = ['username', 'email', 'password', 'password_complex', 'verify'];
    protected $hidden = ['password'];

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class, 'tblWorkspaceMembers', 'userId', 'workspaceId')
                    ->withPivot('role')
                    ->withTimestamps();
    }
} 
