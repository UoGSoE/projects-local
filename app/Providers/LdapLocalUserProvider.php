<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class LdapLocalUserProvider extends \Illuminate\Auth\EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        $user = parent::retrieveByCredentials($credentials);
        if ($user) {
            return $user;
        }

        $ldapUser = \Ldap::findUser($credentials['username']);
        if (!$ldapUser) {
            return null;
        }
        $user = new User;
        $user->password = bcrypt(str_random(64));
        $user->username = $ldapUser->username;
        $user->email = $ldapUser->email;
        $user->surname = $ldapUser->surname;
        $user->forenames = $ldapUser->forenames;
        if ($this->looksLikeMatric($ldapUser->username)) {
            $user->is_staff = false;
        } else {
            $user->is_staff = true;
        }
        $user->save();
        return $user;
    }

    public function validateCredentials(UserContract $user, array $credentials)
    {
        $password = $credentials['password'];
        if (! $password) {
            return false;
        }

        if ($this->hasher->check($password, $user->getAuthPassword())) {
            return true;
        }

        if (\Ldap::authenticate($credentials['username'], $password)) {
            $ldapUser = \Ldap::findUser($credentials['username']);
            $localUser = $this->retrieveByCredentials($credentials);
            if (! $localUser) {
                $localUser = new self;
                $localUser->password = bcrypt(str_random(64));
            }
            $localUser->username = $ldapUser->username;
            $localUser->email = $ldapUser->email;
            $localUser->surname = $ldapUser->surname;
            $localUser->forenames = $ldapUser->forenames;
            $localUser->save();
            return true;
        }

        return false;
    }

    protected function looksLikeMatric($username)
    {
        if (preg_match('/^[0-9]/', $username) === 1) {
            return true;
        }
        return false;
    }
}
