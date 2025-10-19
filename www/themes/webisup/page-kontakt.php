<?php
/*
  Template Name: Contact Form
  Description: Vlastný kontaktný formulár.
 */

get_header();

?>

<main class="ct-container">
    <h1><?php the_title(); ?></h1>

    <?php
    // Spracovanie POST requestu
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_nonce']) && wp_verify_nonce($_POST['contact_nonce'], 'submit_contact')) {
        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        if ($name && $email && $message) {
            wp_mail('tvoj@email.sk', "Nová správa od $name", $message, ["From: $name <$email>"]);
            echo '<p class="success">Správa bola odoslaná.</p>';
        } else {
            echo '<p class="error">Vyplňte všetky polia.</p>';
        }
    }

    ?>

    <form method="post">
        <?php wp_nonce_field('submit_contact', 'contact_nonce'); ?>
        <label>Menom:<br><input type="text" name="name" required></label><br>
        <label>Email:<br><input type="email" name="email" required></label><br>
        <label>Správa:<br><textarea name="message" required></textarea></label><br>
        <button type="submit">Odoslať</button>
    </form>

</main>

<?php get_footer(); ?>
