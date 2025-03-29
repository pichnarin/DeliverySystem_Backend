<?php
namespace App\Http\Controllers;

use Google_Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Kreait\Firebase\Exception\Auth\InvalidToken;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;

class AuthController extends Controller
{
    protected $auth;

    // User Registration
    public function register(Request $request)
    {
        // $request->validate([
        //     'name' => 'required|string',
        //     'email' => 'required|string|email|unique:users',
        //     'password' => 'required|string|min:6',
        //     'role_id' => 'required|integer|exists:roles,id', // Ensure it's an integer and exists in the roles table
        // ]);

        $userImgObj = $request->file('profile_img');
        $path = './assets/user_images';
        $userImg = time() . '_' . $userImgObj->getClientOriginalName();
        $userImgObj->move($path, $userImg);

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'phone' => $request->get('phone_number'),
            'avatar' => $userImg,
            'status' => "active",
            'noti_token' => null,
            'provider' => null,
            'provider_id' => null,
            'email_verified_at' => now(),
            'role_id' => $request->get('role_id')
        ]);

        // dd($user);
        //generate token, the fromUser method mean that it generate token base of User model
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'messsage' => 'Registered successful',
            'user_data' => $user,
            'jwt_token' => $token,
        ], 201);
    }


    // User Login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            // Get the authenticated user.
            $user = JWTAuth::user();

            // return response()->json(compact('token'));
            return response()->json([
                'message' => 'login successful',
                'data' => $user,
                'token' => $token
            ]);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }


    // User Logout
    public function logout(Request $request)
    {
        try {
            // Invalidate the current token
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'message' => 'Successfully logged out',
            ], 200);

        } catch (\Exception $e) {
            // If there's an error during invalidation, return a response
            return response()->json([
                'message' => 'Error while logging out',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(storage_path('app/pizzasprintnotification-firebase-adminsdk-fbsvc-880451d579.json'));
        $this->auth = $factory->createAuth();
    }

    // public function __construct()
    // {
    //     // Ensure the Firebase service account file exists
    //     $serviceAccountPath = storage_path('app/pizzasprintnotification-firebase-adminsdk-fbsvc-880451d579.json');

    //     if (!file_exists($serviceAccountPath)) {
    //         throw new \Exception("Firebase service account file not found at: {$serviceAccountPath}");
    //     }

    //     // Initialize Firebase with the service account
    //     $factory = (new Factory)->withServiceAccount($serviceAccountPath);

    //     // Create the authentication object
    //     $this->auth = $factory->createAuth();
    // }

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