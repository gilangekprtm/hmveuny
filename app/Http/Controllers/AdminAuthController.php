<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\JsonStore;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function __construct(private JsonStore $data) {}

    public function create(Request $request): RedirectResponse|View
    {
        if ($request->user()) {
            return (int) $request->user()->level_akses === 2
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        return view('admin.login');
    }

    public function registration(Request $request): RedirectResponse|View
    {
        if ($request->user()) {
            return (int) $request->user()->level_akses === 2
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        return view('admin.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return (int) $request->user()->level_akses === 2
            ? redirect()->intended(route('admin.dashboard'))
            : redirect()->route('home')->with('status', 'Akun ini belum memiliki akses ke control panel.');
    }

    public function register(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $validator->after(function ($validator) use ($request): void {
            if ($this->data->firstWhere('users', 'email', $request->input('email'))) {
                $validator->errors()->add('email', 'Email tersebut sudah digunakan.');
            }
        });

        $validated = $validator->validate();
        $attributes = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'level_akses' => 1,
            'remember_token' => null,
        ];
        try {
            $record = $this->data->createUnique('users', 'email', $attributes['email'], $attributes);
        } catch (DomainException) {
            throw ValidationException::withMessages([
                'email' => 'Email tersebut sudah digunakan.',
            ]);
        }
        $user = new User;
        $user->forceFill([...$attributes, 'id' => $record->id]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Akun berhasil dibuat. Akses control panel belum diberikan.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
