<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;

class ViaGoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $defualtCustomerRoleId = Role::where('name', 'customer')->first()->id;

        // dd($token);

        $user = User::updateOrCreate(
            ['provider_id' => $googleUser->getId()],
            [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
                'password' => bcrypt(Str::random(16)),
                'provider' => 'google',
                'email_verified_at' => now(),
                'role_id' => $defualtCustomerRoleId
            ]
        );

        dd($googleUser);

        // uncommand when the customer frontendd is ready

        // Auth::login($user);

        // return redirect(config('app.customer_frontend_url') . "/homepage");
    }
}
