<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // User Registration
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'role_id' => 'required|integer|exists:role,id', // Ensure it's an integer and exists in the roles table
            'username' => 'required|string|unique:users', // Ensure the username is provided
        ]);
        
        $name = $request->name;
        $password = Hash::make($request->password);
        $username = $request->username;
        $state = $request->state;
        $role_id = $request->role_id;

        $userImgObj = $request->file('profile_img');
        $path = './assets/user_images';
        $userImg = time().'_'.$userImgObj->getClientOriginalName();
        $userImgObj->move($path,$userImg);

        $user = User::create([
            'name' => $name,
            'role_id' => $role_id,
            'email' => $request->email,
            'username' => $username,
            'password' => Hash::make($request->password),
            'profile_img' => $userImg,
            'state' => $state 
        ]);

        // dd($user);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'User registered', 'token' => $token], 201);
    }


    // User Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Attempt to find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and password matches
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Create a token for the user using Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }


    // User Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}

