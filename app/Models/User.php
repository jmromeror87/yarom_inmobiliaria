<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password',
        'invitation_token', 'invitation_sent_at', 'invitation_accepted_at',
    ];
    protected $hidden = ['password', 'remember_token'];
    protected $casts  = [
        'email_verified_at'      => 'datetime',
        'invitation_sent_at'     => 'datetime',
        'invitation_accepted_at' => 'datetime',
        'password'               => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function hasPendingInvitation(): bool
    {
        return $this->invitation_token !== null && $this->invitation_accepted_at === null;
    }

    public function hasAcceptedInvitation(): bool
    {
        return $this->invitation_accepted_at !== null;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
