<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->senha, $user->senha)) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        if (! $user->is_ativo) {
            return response()->json([
                'message' => 'Usuário inativo. Entre em contato com o administrador.',
            ], 403);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'user' => $user,
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:usuario,email'],
            'senha' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'senha' => $request->senha,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Conta criada com sucesso.',
            'user' => $user,
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // TODO: implementar envio de e-mail de reset
        return response()->json([
            'message' => 'Se o e-mail existir, enviaremos um link de recuperação.',
        ]);
    }

    /**
     * Gera um token API para sistemas externos.
     * POST /api/tokens
     */
    public function createToken(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required'],
            'device_name' => ['required', 'string'],
            'abilities' => ['sometimes', 'array'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->senha, $user->senha)) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        if (! $user->is_ativo) {
            return response()->json([
                'message' => 'Usuário inativo.',
            ], 403);
        }

        $abilities = $request->input('abilities', ['*']);
        $token = $user->createToken($request->device_name, $abilities);

        return response()->json([
            'token' => $token->plainTextToken,
            'abilities' => $abilities,
        ]);
    }

    /**
     * Revoga o token atual.
     * DELETE /api/tokens/current
     */
    public function revokeCurrentToken(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Token revogado com sucesso.',
        ]);
    }

    /**
     * Lista todos os tokens do usuário.
     * GET /api/tokens
     */
    public function listTokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens->map(fn ($token) => [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $token->abilities,
            'last_used_at' => $token->last_used_at,
            'created_at' => $token->created_at,
        ]);

        return response()->json($tokens);
    }

    /**
     * Revoga um token específico por ID.
     * DELETE /api/tokens/{id}
     */
    public function revokeToken(Request $request, string $id): JsonResponse
    {
        $request->user()->tokens()->where('id', $id)->delete();

        return response()->json([
            'message' => 'Token revogado com sucesso.',
        ]);
    }
}
