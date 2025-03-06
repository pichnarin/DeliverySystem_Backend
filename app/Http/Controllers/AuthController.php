<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Google_Client;
use App\Models\Role;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Auth as LaravelAuth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Kreait\Firebase\Exception\Auth\InvalidToken;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;


class AuthController extends Controller
{


    protected $auth;

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
        $userImg = time() . '_' . $userImgObj->getClientOriginalName();
        $userImgObj->move($path, $userImg);

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



    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(storage_path('app/firebase-credentials.json'));
        $this->auth = $factory->createAuth();
    }

    // public function firebaseLogin(Request $request)
    // {
    //     $token = $request->input('token');

    //     try {
    //         $verifiedIdToken = $this->auth->verifyIdToken($token);
    //         $uid = $verifiedIdToken->claims()->get('sub');
    //         $user = $this->auth->getUser($uid);

    //         // Check if the user exists in your database
    //         $localUser = User::where('email', $user->email)->first();
    //         $defaultCustomerRole = Role::where('name', 'customer')->first();

    //         if (!$localUser) {
    //             // Create a new user
    //             $localUser = User::create([
    //                 'name' => $user->displayName,
    //                 'email' => $user->email,
    //                 'password' => bcrypt(uniqid()), // Random password
    //                 'provider' => 'firebase',
    //                 'provider_id' => $user->uid,
    //                 'email_verified_at' => now(),
    //                 'role_id' => $defaultCustomerRole ? $defaultCustomerRole->id : null,
    //             ]);
    //         }

    //         // Log in the user
    //         LaravelAuth::login($localUser);

    //         // Generate JWT token
    //         $jwtToken = JWTAuth::fromUser($localUser);

    //         // Return a response with the JWT token
    //         return response()->json([
    //             'message' => 'Login successful',
    //             'user' => $localUser,
    //             'token' => $jwtToken,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Invalid token'], 401);
    //     }
    // }

    public function googleLogin(Request $request)
    {
        $idToken = $request->input('idToken');

        if (!$idToken) {
            return response()->json(['error' => 'No token provided'], 401);
        }

        try {
            $auth = app('firebase.auth');
            $verifiedIdToken = $auth->verifyIdToken($idToken);

            // Extract user data
            $uid = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');

            // Fetch or create the default customer role
            $defaultCustomerRole = Role::where('name', 'customer')->first();

            // Check if the user exists
            $user = User::where('email', $email)->first();

            if ($user) {
                // Update the existing user
                $user->update([
                    'provider_id' => $uid,
                    'provider' => 'google',
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)),
                    'role_id' => $defaultCustomerRole->id ?? null,
                    'name' => $verifiedIdToken->claims()->get('name'),
                    'avatar' => $verifiedIdToken->claims()->get('picture'),
                    'status' => 'active',
                ]);
            } else {
                // Create a new user
                $user = User::create([
                    'provider_id' => $uid,
                    'email' => $email,
                    'provider' => 'google',
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)),
                    'role_id' => $defaultCustomerRole->id ?? null,
                    'name' => $verifiedIdToken->claims()->get('name'),
                    'avatar' => $verifiedIdToken->claims()->get('picture'),
                    'status' => 'active',
                ]);
            }

            // Generate JWT Token for app authentication
            $jwtToken = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Authentication successful',
                'jwtToken' => $jwtToken,
                'uid' => $uid,
                'email' => $email,
            ], 200);

        } catch (InvalidToken $e) {
            return response()->json(['error' => 'Invalid token: ' . $e->getMessage()], 401);
        } catch (RevokedIdToken $e) {
            return response()->json(['error' => 'Token revoked'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication error: ' . $e->getMessage()], 500);
        }
    }

}

