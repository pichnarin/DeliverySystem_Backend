<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

//google auth routes
Route::get('/auth/google/redirect', function (Request $request) {
    return Socialite::driver('google')->redirect();
});

Route::get('/auth/google/callback', function (Request $request) {
    $googleUser = Socialite::driver('google')->user();

    $user = User::updateOrCreate(
        ['provider_id' => $googleUser->getId()],
        [
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'avatar' => $googleUser->getAvatar(),
            'password' => bcrypt(Str::random(16)),
            'provider' => 'google',
        ]
    );

    dd($user);

    // uncommand when the auth and the customer frontendd is ready

    // Auth::login($user);

    // return redirect(config('app.customer_frontend_url') . "/homepage");

});

require __DIR__ . '/auth.php';

