<?php

namespace App\Auth;

use App\Models\User;
use App\Support\JsonStore;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher;

class JsonUserProvider implements UserProvider
{
    public function __construct(
        private Hasher $hasher,
        private JsonStore $store,
    ) {}

    public function retrieveById($identifier): ?Authenticatable
    {
        $record = $this->store->find('users', $identifier);

        return $record ? $this->makeUser($record) : null;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $user = $this->retrieveById($identifier);

        if (! $user || ! $user->getRememberToken() || ! hash_equals($user->getRememberToken(), $token)) {
            return null;
        }

        return $user;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        $this->store->update('users', $user->getAuthIdentifier(), [
            $user->getRememberTokenName() => $token,
        ]);
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (! isset($credentials['email'])) {
            return null;
        }

        $record = $this->store->firstWhere('users', 'email', $credentials['email']);

        return $record ? $this->makeUser($record) : null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return isset($credentials['password'])
            && $this->hasher->check($credentials['password'], $user->getAuthPassword());
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        if ($force || $this->hasher->needsRehash($user->getAuthPassword())) {
            $this->store->update('users', $user->getAuthIdentifier(), [
                'password' => $this->hasher->make($credentials['password']),
            ]);
        }
    }

    private function makeUser(object $record): User
    {
        $user = new User;
        $user->forceFill((array) $record);

        return $user;
    }
}
