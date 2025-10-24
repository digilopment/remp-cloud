<?php
/*
  Template Name: Contact Form
  Description: Vlastný kontaktný formulár.
*/

get_header();
?>

<main class="ct-container">
    <h1><?php the_title(); ?></h1>
    <?php the_custom_logo(); ?>

    <?php
    $adminEmail = get_option('admin_email'); // odoslanie na admin email

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_nonce']) && wp_verify_nonce($_POST['contact_nonce'], 'submit_contact')) {

        $name    = sanitize_text_field($_POST['name'] ?? '');
        $email   = sanitize_email($_POST['email'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        if ($name && $email && $message) {

            $to      = $adminEmail; // správa príde adminovi
            $subject = "Nová správa od $name";
            $headers = [
                "From: $name <$email>",   // návštevník ako odosielateľ
                "Reply-To: $email",       // reply pôjde na návštevníka
                "Content-Type: text/plain; charset=UTF-8"
            ];

            if (wp_mail($to, $subject, $message, $headers)) {
                // presmerovanie, aby sa zabránilo opätovnému POST a 404
                wp_redirect(add_query_arg('success', '1', get_permalink()));
                exit;
            } else {
                echo '<p class="error">Chyba pri odosielaní správy. Skúste neskôr.</p>';
            }

        } else {
            echo '<p class="error">Vyplňte všetky polia.</p>';
        }
    }

    // Hlásenie úspechu
    if (isset($_GET['success'])) {
        echo '<p class="success">Správa bola odoslaná.</p>';
    }
    ?>

    <form method="post" action="<?php echo esc_url(get_permalink()); ?>">
        <?php wp_nonce_field('submit_contact', 'contact_nonce'); ?>
        <label>Menom:<br><input type="text" name="name" required></label><br>
        <label>Email:<br><input type="email" name="email" required></label><br>
        <label>Správa:<br><textarea name="message" required></textarea></label><br>
        <button type="submit">Odoslať</button>
    </form>
</main>

<?php get_footer(); ?>
