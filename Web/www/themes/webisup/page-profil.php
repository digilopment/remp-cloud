<?php
/* Template Name: REMP SSO Login */

class REMP_SSO_Login_Page
{

    private $login_error = '';
    private $api_token = 'cc324b06f58d7e8b673857612821a137';

    public function __construct()
    {
        $this->handle_post();
    }

    // --------- cURL request ---------
    private function remp_request($url, $post_data = [], $headers = [])
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($post_data),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err)
            return ['error' => $err];

        $data = json_decode($response, true);
        if ($data === null)
            return ['error' => 'Chyba pri parsovaní JSON: ' . $response];

        return $data;
    }

    // --------- Login ---------
    private function handle_login()
    {
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        $url = 'http://crm.remp.press/api/v1/users/login';
        $headers = [
            'Authorization: Bearer ' . $this->api_token,
            'Cookie: n_token=77979c5945eae80ac55fb45096e704d3; _nss=1; tracy-session=1ee9f24c28'
        ];

        $post_data = ['email' => $email, 'password' => $password];
        $response = $this->remp_request($url, $post_data, $headers);

        if (isset($response['error'])) {
            $this->login_error = $response['error'];
            return;
        }

        if (isset($response['status']) && $response['status'] === 'ok' && isset($response['user'])) {
            $user_data = $response['user'];
            $username = $user_data['email'];

            $wp_user = get_user_by('login', $username);
            if (!$wp_user) {
                $wp_user_id = wp_create_user($username, wp_generate_password(), $user_data['email']);
                $wp_user = get_user_by('id', $wp_user_id);
            }

            wp_set_current_user($wp_user->ID);
            wp_set_auth_cookie($wp_user->ID);
            do_action('wp_login', $username, $wp_user);

            wp_redirect(get_permalink());
            exit;
        } elseif (isset($response['status']) && $response['status'] === 'error') {
            $this->login_error = $response['message'] ?? 'Prihlásenie zlyhalo';
        } else {
            $this->login_error = 'Neznáma odpoveď API';
        }
    }

    // --------- Logout ---------
    private function handle_logout()
    {
        wp_logout();
        wp_redirect(get_permalink());
        exit;
    }

    // --------- POST spracovanie ---------
    private function handle_post()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Logout
            if (isset($_POST['remp_logout_nonce']) && wp_verify_nonce($_POST['remp_logout_nonce'], 'remp_logout_action')) {
                $this->handle_logout();
            }

            // Login
            if (isset($_POST['remp_login_nonce']) && wp_verify_nonce($_POST['remp_login_nonce'], 'remp_login_action')) {
                $this->handle_login();
            }
        }
    }

    // --------- Render ---------
    public function render_form()
    {
        ob_start();
        if (is_user_logged_in()) :

            ?>
            <div class="remp-login-wrapper" style="max-width:400px;margin:50px auto;padding:20px;border:1px solid #ddd;border-radius:6px;background:#f9f9f9;text-align:center;">
                <p>Vitaj, <?php echo esc_html(wp_get_current_user()->display_name ?: wp_get_current_user()->user_login); ?>!</p>
                <form method="post">
            <?php wp_nonce_field('remp_logout_action', 'remp_logout_nonce'); ?>
                    <button type="submit" style="padding:10px 20px;background:#d9534f;color:#fff;border:none;border-radius:4px;cursor:pointer;">Odhlásiť sa</button>
                </form>
            </div>
            <?php
        else :

            ?>
            <div class="remp-login-wrapper" style="max-width:400px;margin:50px auto;padding:20px;border:1px solid #ddd;border-radius:6px;background:#f9f9f9;">
                <h2 style="text-align:center;">Prihlásenie cez REMP SSO</h2>

                    <?php if ($this->login_error) : ?>
                    <div style="color:red;text-align:center;margin-bottom:10px;"><?php echo esc_html($this->login_error); ?></div>
            <?php endif; ?>

                <form method="post">
            <?php wp_nonce_field('remp_login_action', 'remp_login_nonce'); ?>
                    <p>
                        <label for="email">Email:</label><br>
                        <input type="email" name="email" id="email" required style="width:100%;padding:8px;">
                    </p>
                    <p>
                        <label for="password">Heslo:</label><br>
                        <input type="password" name="password" id="password" required style="width:100%;padding:8px;">
                    </p>
                    <p>
                        <button type="submit" style="width:100%;padding:10px;background:#0073aa;color:#fff;border:none;border-radius:4px;">Prihlásiť sa</button>
                    </p>
                </form>
            </div>
        <?php
        endif;

        return ob_get_clean();
    }

}

// ------------------ Inicializácia ------------------
$remp_sso_page = new REMP_SSO_Login_Page();
get_header();
echo $remp_sso_page->render_form();
get_footer();
