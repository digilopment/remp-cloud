<?php
require_once __DIR__ . '/autoload.php';

/**
 * Pridá nového používateľa do WordPress.
 *
 * @param string $login Používateľské meno
 * @param string $password Heslo
 * @param string $email Email (voliteľné, automaticky generovaný, ak nie je)
 * @param string $role Rola používateľa, default 'administrator'
 */
function add_user($login, $password, $email = '', $role = 'administrator')
{
    if (username_exists($login)) {
        echo "Používateľ $login už existuje.\n";
        return false;
    }

    if (empty($email)) {
        $email = $login . '@example.com';
    }

    $user_id = wp_create_user($login, $password, $email);

    if (is_wp_error($user_id)) {
        echo "Chyba pri vytváraní používateľa $login: " . $user_id->get_error_message() . "\n";
        return false;
    }

    $user = new WP_User($user_id);
    $user->set_role($role);

    echo "Používateľ $login bol úspešne vytvorený.\n";
    return $user_id;
}

// --- Príklad použitia ---
//roles: administrator,editor,author,contributor,subscriber
add_user(getenv('DEFAULT_USER'), getenv('DEFAULT_PASSWORD'), getenv('DEFAULT_EMAIL'), 'administrator'); // hlavný admin
//add_user('root', 'root');                            // ďalší admin
//add_user('editor', 'editor', 'editor@domain.com', 'editor'); // editor
//add_user('author', 'author', 'author@domain.com', 'author');        // author
//add_user('contributor', 'contributor', 'contributor@domain.com', 'contributor');   // contributor
//add_user('subscriber', 'subscriber', 'subscriber@domain.com', 'subscriber');    // subscriber

