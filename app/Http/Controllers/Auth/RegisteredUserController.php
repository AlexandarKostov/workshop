<?php

namespace App\Http\Controllers\Auth;

use App\Events\RefferUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create($token= null)
    {
        $user = null;
        if ($token)
        {
            $user = User::where('token', $token)->first();
            if (!$user)
            {
                return to_route('register');
            }
        }
        return view('auth.register', compact('user'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'token' => Str::random(8),
        ]);

        event(new Registered($user));
        if ($request->token)
        {
            event(new RefferUser($request->token));
        }

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
