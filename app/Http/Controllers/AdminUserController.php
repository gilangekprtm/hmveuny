<?php

namespace App\Http\Controllers;

use App\Support\JsonStore;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function __construct(private JsonStore $data) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'level_akses' => ['required', 'integer', 'in:1,2'],
        ]);

        try {
            $this->data->createUnique('users', 'email', $validated['email'], [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'level_akses' => (int) $validated['level_akses'],
                'remember_token' => null,
            ]);
        } catch (DomainException) {
            throw ValidationException::withMessages([
                'email' => 'Email tersebut sudah digunakan.',
            ]);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        $existing = $this->data->find('users', $user);
        abort_if($existing === null, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'level_akses' => ['required', 'integer', 'in:1,2'],
        ]);
        $isCurrentUser = (string) $request->user()->getAuthIdentifier() === (string) $user;
        $newLevel = (int) $validated['level_akses'];

        if ($isCurrentUser && $newLevel !== 2) {
            return back()->with('error', 'Anda tidak dapat menurunkan level akses akun yang sedang digunakan.');
        }

        if ((int) $existing->level_akses === 2 && $newLevel !== 2 && $this->adminCount() <= 1) {
            return back()->with('error', 'Level akses admin terakhir tidak dapat diturunkan.');
        }

        $attributes = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'level_akses' => $newLevel,
        ];

        if ($validated['password'] ?? null) {
            $attributes['password'] = Hash::make($validated['password']);
            $attributes['remember_token'] = null;
        }

        try {
            $this->data->updateUnique('users', $user, 'email', $validated['email'], $attributes);
        } catch (DomainException) {
            throw ValidationException::withMessages([
                'email' => 'Email tersebut sudah digunakan.',
            ]);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, int $user): RedirectResponse
    {
        $existing = $this->data->find('users', $user);
        abort_if($existing === null, 404);

        if ((string) $request->user()->getAuthIdentifier() === (string) $user) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        if ((int) $existing->level_akses === 2 && $this->adminCount() <= 1) {
            return back()->with('error', 'Admin terakhir tidak dapat dihapus.');
        }

        abort_unless($this->data->delete('users', $user), 404);

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    private function adminCount(): int
    {
        return $this->data->all('users')
            ->filter(fn (object $user) => (int) $user->level_akses === 2)
            ->count();
    }
}
