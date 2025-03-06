<?php

// namespace App\Http\Controllers;

// use Firebase\JWT\JWT;
// use Http;
// use Illuminate\Http\Request;
// use Laravel\Socialite\Facades\Socialite;
// use App\Models\User;
// use App\Models\Role;
// use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Validator;
// use Tymon\JWTAuth\Facades\JWTAuth;
// use Google_Client;

// class ViaGoogleController extends Controller
// {
//     public function redirect()
//     {
//         return Socialite::driver('google')->redirect();
//     }

//     public function callback()
//     {
//         $googleUser = Socialite::driver('google')->user();

//         $defualtCustomerRoleId = Role::where('name', 'customer')->first()->id;

//         // dd($token);

//         $user = User::updateOrCreate(
//             ['provider_id' => $googleUser->getId()],
//             [
//                 'name' => $googleUser->getName(),
//                 'email' => $googleUser->getEmail(),
//                 'avatar' => $googleUser->getAvatar(),
//                 'password' => bcrypt(Str::random(16)),
//                 'provider' => 'google',
//                 'email_verified_at' => now(),
//                 'role_id' => $defualtCustomerRoleId
//             ]
//         );

//         dd($googleUser);

        // uncommand when the customer frontendd is ready

        // Auth::login($user);

        // return redirect(config('app.customer_frontend_url') . "/homepage");
    // }


    // public function googleSignup(Request $request)
    // {
    //     try {
    //         $idToken = $request->input('id_token');

    //         if (!$idToken) {
    //             return response()->json(['error' => 'ID Token is required'], 400);
    //         }

    //         // 🔹 Verify ID Token with Google
    //         $googleResponse = Http::get("https://oauth2.googleapis.com/tokeninfo?id_token={$idToken}");

    //         if (!$googleResponse->ok()) {
    //             return response()->json(['error' => 'Invalid Google ID Token'], 401);
    //         }

    //         $googleUser = $googleResponse->json();

    //         // Extract user details
    //         $email = $googleUser['email'];
    //         $name = $googleUser['name'];
    //         $avatar = $googleUser['picture'];
    //         $providerId = $googleUser['sub']; // Google's unique user ID

    //         // 🔹 Check if user already exists
    //         $user = User::where('email', $email)->first();

    //         // Get the default customer role ID
    //         $defaultCustomerRoleId = Role::where('name', 'customer')->first()->id;

    //         if (!$user) {
    //             $user = User::create([
    //                 'name' => $name,
    //                 'email' => $email,
    //                 'avatar' => $avatar,
    //                 'password' => bcrypt(Str::random(16)), // Random password
    //                 'provider' => 'google',
    //                 'provider_id' => $providerId,
    //                 'email_verified_at' => now(),
    //                 'role_id' => $defaultCustomerRoleId
    //             ]);
    //         }

    //         // 🔹 Generate JWT Token
    //         $jwtToken = JWT::encode(['user_id' => $user->id], env('JWT_SECRET'), 'HS256');

    //         return response()->json([
    //             'status' => 'success',
    //             'token' => $jwtToken,
    //             'user' => $user
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // public function googleSignup(Request $request)
    // {    
    //     $request->validate([
    //         'id_token' => 'required|string',
    //     ]);

    //     try {
    //         // Verify the Google ID token
    //         $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
    //         $payload = $client->verifyIdToken($request->id_token);

    //         if (!$payload) {
    //             return response()->json(['status' => 'error', 'message' => 'Invalid ID token'], 401);
    //         }

    //         // Extract user details
    //         $email = $payload['email'];
    //         $name = $payload['name'];
    //         $avatar = $payload['picture'];
    //         $googleId = $payload['sub']; // Google user ID

    //         // Check if user exists
    //         $user = User::where('email', $email)->first();
    //         $defaultCustomerRole = Role::where('name', 'customer')->first();
    //         $defaultCustomerRoleId = $defaultCustomerRole ? $defaultCustomerRole->id : null;

    //         if (!$user) {
    //             $user = User::create([
    //                 'name' => $name,
    //                 'email' => $email,
    //                 'avatar' => $avatar,
    //                 'password' => bcrypt(Str::random(16)),
    //                 'provider' => 'google',
    //                 'provider_id' => $googleId,
    //                 'email_verified_at' => now(),   
    //                 'role_id' => $defaultCustomerRoleId
    //             ]);
    //         } else if (!$user->provider_id) {
    //             $user->update([
    //                 'provider_id' => $googleId,
    //                 'provider' => 'google',
    //             ]);
    //         }

    //         // Generate JWT token
    //         $jwtToken = JWT::encode(['user_id' => $user->id], env('JWT_SECRET'), 'HS256');

    //         return response()->json(['status' => 'success', 'token' => $jwtToken, 'user' => $user], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    //     }
    // }

    // public function googleSignup(Request $request)
    // {
    //     $request->validate([
    //         'id_token' => 'required|string',
    //     ]);

    //     // Log the received ID token to check its format
    //     \Log::info('Received ID Token: ' . $request->id_token);

    //     try {
    //         // Verify the Google ID token
    //         $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
    //         $payload = $client->verifyIdToken($request->id_token);

    //         if (!$payload) {
    //             return response()->json(['status' => 'error', 'message' => 'Invalid ID token'], 401);
    //         }

    //         // Extract user details
    //         $email = $payload['email'];
    //         $name = $payload['name'];
    //         $avatar = $payload['picture'];
    //         $googleId = $payload['sub']; // Google user ID

    //         // Check if user exists
    //         $user = User::where('email', $email)->first();
    //         $defaultCustomerRole = Role::where('name', 'customer')->first();
    //         $defaultCustomerRoleId = $defaultCustomerRole ? $defaultCustomerRole->id : null;

    //         if (!$user) {
    //             $user = User::create([
    //                 'name' => $name,
    //                 'email' => $email,
    //                 'avatar' => $avatar,
    //                 'password' => bcrypt(Str::random(16)),
    //                 'provider' => 'google',
    //                 'provider_id' => $googleId,
    //                 'email_verified_at' => now(),
    //                 'role_id' => $defaultCustomerRoleId
    //             ]);
    //         } else if (!$user->provider_id) {
    //             $user->update([
    //                 'provider_id' => $googleId,
    //                 'provider' => 'google',
    //             ]);
    //         }

    //         // Generate JWT token
    //         $jwtToken = JWT::encode(['user_id' => $user->id], env('JWT_SECRET'), 'HS256');

    //         return response()->json(['status' => 'success', 'token' => $jwtToken, 'user' => $user], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    //     }
    // }

// }
