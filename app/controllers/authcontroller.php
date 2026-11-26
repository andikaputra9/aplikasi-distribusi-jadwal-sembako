
<?php

require_once __DIR__ . '/../models/pengguna.php';

class AuthController
{
    public function login($username, $password)
    {
        $username = trim($username);

        if ($username === '' || $password === '') {
            return false;
        }

        $dataPengguna = Pengguna::findByUsername($username);

        if (!$dataPengguna) {
            return false;
        }

        if (!Pengguna::verifikasiPassword(
            $password,
            $dataPengguna['password']
        )) {
            return false;
        }

        $this->buatSession($dataPengguna);

        return true;
    }

public function buatSession($dataPengguna)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'       => (int) $dataPengguna['id_pengguna'],
        'nama'     => $dataPengguna['nama'],
        'username' => $dataPengguna['username'],
    ];

    $_SESSION['peran'] = $dataPengguna['peran'];
}

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}
