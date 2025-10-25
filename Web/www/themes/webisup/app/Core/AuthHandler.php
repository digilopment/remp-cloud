<?php
namespace App\Core;

class AuthHandler
{
    private array $config;
    private string $loginError = '';

    public function __construct()
    {
        $this->config = include __DIR__ . '/../Config/remp.php';
    }

    public function login(string $email, string $password): bool
    {
        $headers = [
            'Authorization: Bearer ' . $this->config['api_token'],
        ];

        $response = HttpClient::post($this->config['api_url'], compact('email', 'password'), $headers);

        if (isset($response['error'])) {
            $this->loginError = $response['error'];
            return false;
        }

        if (($response['status'] ?? '') === 'ok' && isset($response['user'])) {
            $user = $response['user'];
            $username = $user['email'];

            $wp_user = get_user_by('login', $username);
            if (!$wp_user) {
                $wp_user_id = wp_create_user($username, wp_generate_password(), $user['email']);
                $wp_user = get_user_by('id', $wp_user_id);
            }

            wp_set_current_user($wp_user->ID);
            wp_set_auth_cookie($wp_user->ID);
            do_action('wp_login', $username, $wp_user);

            return true;
        }

        $this->loginError = $response['message'] ?? 'Prihlásenie zlyhalo';
        return false;
    }

    public function logout(): void
    {
        wp_logout();
    }

    public function getError(): string
    {
        return $this->loginError;
    }
}
