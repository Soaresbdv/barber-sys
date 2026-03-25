<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', 
            'phone' => 'nullable|string',
        ]);

        // Criação do usuário
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $request->phone,
            'role' => 'client', 
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Cadastro realizado com sucesso!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciais inválidas' 
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user 
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Deslogado com sucesso']);
    }

    public function getServices(Request $request)
    {
        if ($request->user()->role !== 'admin') return response()->json(['message' => 'Acesso negado.'], 403);
        
        return Service::orderBy('name', 'asc')->get();
    }

    public function storeService(Request $request)
    {
        if ($request->user()->role !== 'admin') return response()->json(['message' => 'Acesso negado.'], 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1'
        ]);

        $service = Service::create($request->all());

        return response()->json(['message' => 'Serviço criado com sucesso!', 'service' => $service]);
    }

    public function updateService(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') return response()->json(['message' => 'Acesso negado.'], 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1'
        ]);

        $service = Service::findOrFail($id);
        $service->update($request->all());

        return response()->json(['message' => 'Serviço atualizado com sucesso!', 'service' => $service]);
    }

    public function destroyService(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') return response()->json(['message' => 'Acesso negado.'], 403);

        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Serviço removido com sucesso!']);
    }
}