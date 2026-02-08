<?php

namespace App\Controllers;

use App\Configuration;
use App\Models\User;
use Exception;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class AuthController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        switch ($action) {
            case 'index':
            case 'login':
            case 'register':
                return true;
            case 'logout':
                return $this->user->isLoggedIn();
            default:
                return false;
        }
    }

    public function index(Request $request): Response
    {
        return $this->redirect(Configuration::LOGIN_URL);
    }

    /**
     * @throws Exception
     */
    public function login(Request $request): Response
    {
        if ($this->user->isLoggedIn()) {
            return $this->redirect($this->url("home.index"));
        }

        $logged = null;
        if ($request->hasValue('submit')) {
            $logged = $this->app->getAuthenticator()->login($request->value('username'), $request->value('password'));
            if ($logged) {
                return $this->redirect($this->url("home.index"));
            }
        }

        $message = $logged === false ? 'Bad username or password' : null;
        return $this->html(compact("message"));
    }

    /**
     * @throws Exception
     */
    public function logout(): Response
    {
        $this->app->getAuthenticator()->logout();
        return $this->redirect($this->url("home.index"));
    }

    /**
     * @throws Exception
     */
    public function register(Request $request): Response
    {
        if ($this->user->isLoggedIn()) {
            return $this->redirect($this->url("home.index"));
        }

        $message = null;

        if ($request->hasValue('submit')) {
            $username = trim((string)$request->value('username'));
            $email    = trim((string)$request->value('email'));
            $password = (string)$request->value('password');
            $password2 = (string)$request->value('password2');

            if ($username === '' || $password === '') {
                $message = 'Vyplň používateľské meno aj heslo.';
            } elseif ($password !== $password2) {
                $message = 'Heslá sa nezhodujú.';
            } elseif (mb_strlen($password) < 6) {
                $message = 'Heslo musí mať aspoň 6 znakov.';
            } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Email nie je v správnom formáte.';
            } else {
                $existsUser = User::getAll('`username` = ?', [$username]);
                $existsEmail = $email !== '' ? User::getAll('`email` = ?', [$email]) : [];

                if (!empty($existsUser)) {
                    $message = 'Toto používateľské meno už existuje.';
                } elseif (!empty($existsEmail)) {
                    $message = 'Tento email už je použitý.';
                } else {
                    $u = new User();
                    $u->setUsername($username);
                    $u->setEmail($email !== '' ? $email : null);
                    $u->setPasswordHash(password_hash($password, PASSWORD_DEFAULT));
                    $u->save();

                    $this->app->getAuthenticator()->login($username, $password);
                    return $this->redirect($this->url('home.index'));
                }
            }
        }
        return $this->html(compact('message'));
    }
}