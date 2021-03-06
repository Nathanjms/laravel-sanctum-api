<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password'])
        ]);

        $token = $user->CreateToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token,
        ];

        return response($response, Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // check email
        $user = User::where('email', $fields['email'])->first();

        // Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Bad Credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->CreateToken('appToken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token,
            'expiry' => Carbon::now()->addMinutes(60 * 24 * env('TOKEN_EXPIRATION_DAYS', '28'))->unix(),
        ];

        return response($response, Response::HTTP_CREATED);
    }

    public function logout(Request $request)
    {
        foreach ($request->user()->tokens as $token) {
            $token->delete();
        }

        return response([
            'message' => 'Logged Out'
        ], Response::HTTP_OK);
    }
}
