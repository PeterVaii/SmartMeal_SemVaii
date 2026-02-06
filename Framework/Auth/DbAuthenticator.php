<?php

namespace Framework\Auth;

use Exception;
use Framework\Core\App;
use Framework\Core\IIdentity;
use App\Models\User;

class DbAuthenticator extends SessionAuthenticator
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * @throws Exception
     */
    protected function authenticate(string $username, string $password): ?IIdentity
    {
        $user = User::getAll('`username` = ?', [$username])[0] ?? null;
        if (!$user)
        {
            return null;
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            return null;
        }

        $id = $user->getId();
        if ($id === null) {
            return null;
        }

        return new AppIdentity(id: $id, name: $user->getUsername());
    }
}