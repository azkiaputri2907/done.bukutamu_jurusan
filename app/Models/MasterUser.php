<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class MasterUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'master_user';
    protected $fillable = ['role_id', 'name', 'email', 'password', 'foto']; // Tambahkan 'foto'
    protected $hidden = ['password', 'remember_token'];

    public function role()
    {
        return $this->belongsTo(MasterRole::class, 'role_id');
    }
}