<?php
/**
 * Plugin Name: RempConnector
 * Description: Jednosúborový konektor medzi WordPress a REMP 2020 (Beam tracking + login)
 * Version: 1.0.0
 * Author: Tomas Doubek
 */

if (!defined('ABSPATH')) exit;

class RempConnector {

    private $api_url;
    private $api_token;

    public function __construct() {
        $this->api_url   = getenv('REMP_API_URL') ?: get_option('remp_api_url');
        $this->api_token = getenv('REMP_API_TOKEN') ?: get_option('remp_api_token');

        add_action('admin_menu', [$this, 'register_admin_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_head', [$this, 'inject_beam_tracking']);

        // WP login hook
        add_action('wp_authenticate', [$this, 'remp_authenticate'], 10, 2);
    }

    // Admin nastavenia
    public function register_admin_page() {
        add_options_page('RempConnector', 'RempConnector', 'manage_options', 'remp-connector', [$this, 'render_admin_page']);
    }

    public function register_settings() {
        register_setting('remp_connector_settings', 'remp_api_url');
        register_setting('remp_connector_settings', 'remp_api_token');
    }

    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>RempConnector nastavenia</h1>
            <form method="post" action="options.php">
                <?php settings_fields('remp_connector_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">REMP API URL</th>
                        <td><input type="text" name="remp_api_url" value="<?php echo esc_attr(get_option('remp_api_url')); ?>" size="50" /></td>
                    </tr>
                    <tr>
                        <th scope="row">REMP API token</th>
                        <td><input type="text" name="remp_api_token" value="<?php echo esc_attr(get_option('remp_api_token')); ?>" size="50" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <h2>Test API spojenia</h2>
            <form method="post">
                <input type="hidden" name="remp_test_api" value="1" />
                <?php submit_button('Otestuj spojenie'); ?>
            </form>

            <?php
            if (!empty($_POST['remp_test_api'])) {
                $result = $this->test_api_connection();
                echo '<pre>' . esc_html(print_r($result, true)) . '</pre>';
            }
            ?>
        </div>
        <?php
    }

    // Test API
    public function test_api_connection() {
        if (!$this->api_url || !$this->api_token) {
            return 'API URL alebo token nie je nastavený.';
        }

        $response = wp_remote_get($this->api_url . '/api/v1/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_token,
            ],
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            return $response->get_error_message();
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    // Beam tracking
    public function inject_beam_tracking() {
        if (!$this->api_url || !$this->api_token) return;

        $beam_url = rtrim($this->api_url, '/') . '/beam.min.js';
        echo "\n<!-- REMP Beam Tracking -->\n";
        echo '<script src="' . esc_url($beam_url) . '"></script>' . "\n";
        echo '<script>Beam.init({ property_token: "' . esc_js($this->api_token) . '" });</script>' . "\n";
    }

    // WP login cez REMP API
    public function remp_authenticate($username, $password) {
        if (!$username || !$password) return;

        $response = wp_remote_post($this->api_url . '/api/v1/login', [
            'body' => [
                'username' => $username,
                'password' => $password,
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_token,
            ],
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            wp_die('Chyba pripojenia k REMP API: ' . $response->get_error_message());
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($data['success']) || !$data['success']) {
            wp_die('Nesprávne prihlasovacie údaje');
        }

        $wp_user = get_user_by('login', $username);

        if (!$wp_user) {
            $wp_user_id = wp_create_user($username, wp_generate_password(), $data['email'] ?? '');
            $wp_user = get_user_by('id', $wp_user_id);
        }

        wp_set_current_user($wp_user->ID);
        wp_set_auth_cookie($wp_user->ID);
        do_action('wp_login', $username, $wp_user);

        wp_redirect(home_url());
        exit;
    }
}

// Inicializácia pluginu
new RempConnector();
