<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'email' => 'nullable|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('name', 'password'))) {
            throw ValidationException::withMessages([
                'name' => ['帳號或密碼錯誤'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'user' => Auth::user()->only(['id', 'name', 'email']),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => '已登出']);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()->only(['id', 'name', 'email']),
        ]);
    }
}
