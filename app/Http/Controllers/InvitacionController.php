<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvitacionController extends Controller
{
    public function show(string $token)
    {
        $user = User::where('invitation_token', $token)
            ->whereNull('invitation_accepted_at')
            ->firstOrFail();

        return view('invitacion.activar', compact('user', 'token'));
    }

    public function store(Request $request, string $token)
    {
        $user = User::where('invitation_token', $token)
            ->whereNull('invitation_accepted_at')
            ->firstOrFail();

        $request->validate([
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ], [
            'password.required'              => 'La contraseña es obligatoria.',
            'password.min'                   => 'Mínimo 8 caracteres.',
            'password.confirmed'             => 'Las contraseñas no coinciden.',
            'password_confirmation.required' => 'Confirma tu contraseña.',
        ]);

        $user->update([
            'password'               => Hash::make($request->password),
            'invitation_accepted_at' => now(),
            'invitation_token'       => null,
            'email_verified_at'      => now(),
        ]);

        return redirect('/admin/login')->with('success', '¡Contraseña creada! Ya puedes iniciar sesión.');
    }
}
