<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use App\Services\SistemaService;

class UserController extends Controller
{
    // Listar usuarios
    public function index()
    {
        // Traemos todos MENOS al usuario actual
        $users = User::where('id', '!=', auth()->id())->latest()->get();

        // CORRECCIÓN: Devolver JSON para que Vue lo consuma con Axios
        return response()->json($users);
    }

    // Crear nuevo usuario
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,analyst',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        SistemaService::incrementarOperaciones();

        return back()->with('success', 'Usuario creado correctamente.');
    }

     // Actualizar usuario existente
     public function update(Request $request, User $user)
     {
         // Validaciones
         $rules = [
             'name' => 'required|string|max:255',
             // 'unique' debe ignorar el ID del usuario actual para que no diga "el correo ya existe"
             'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
             'role' => 'required|in:admin,analyst',
             // La contraseña es opcional (nullable) al editar
             'password' => 'nullable|confirmed|min:8',
         ];

         $request->validate($rules);

         // Preparamos los datos a actualizar
         $userData = [
             'name' => $request->name,
             'email' => $request->email,
             'role' => $request->role,
         ];

         // Solo si escribió una contraseña nueva, la encriptamos y la agregamos
         if ($request->filled('password')) {
             $userData['password'] = Hash::make($request->password);
         }

         $user->update($userData);

         SistemaService::incrementarOperaciones();

         return response()->json(['message' => 'Usuario actualizado correctamente.']);
     }

    // Eliminar usuario
    public function destroy(User $user)
    {
        // Doble seguridad: No permitir borrar a otros admins (opcional) o a sí mismo
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();

        SistemaService::incrementarOperaciones();

        return back()->with('success', 'Usuario eliminado correctamente.');
    }


}
