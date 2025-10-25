<?php
namespace App\Controllers;

use App\Core\AuthHandler;

class RempSsoController
{
    private AuthHandler $auth;

    public function __construct()
    {
        $this->auth = new AuthHandler();
    }

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Logout
        if (isset($_POST['remp_logout_nonce']) && wp_verify_nonce($_POST['remp_logout_nonce'], 'remp_logout_action')) {
            $this->auth->logout();
            wp_redirect(get_permalink());
            exit;
        }

        // Login
        if (isset($_POST['remp_login_nonce']) && wp_verify_nonce($_POST['remp_login_nonce'], 'remp_login_action')) {
            $email = sanitize_email($_POST['email']);
            $password = $_POST['password'];

            if ($this->auth->login($email, $password)) {
                wp_redirect(get_permalink());
                exit;
            }
        }
    }

    public function render(): void
    {
        $error = $this->auth->getError();
        include __DIR__ . '/../Views/remp-sso-form.php';
    }
}
