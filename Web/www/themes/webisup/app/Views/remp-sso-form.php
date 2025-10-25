<div class="remp-login-wrapper" style="max-width:400px;margin:50px auto;padding:20px;border:1px solid #ddd;border-radius:6px;background:#f9f9f9;">
    <?php if (is_user_logged_in()): ?>
        <?php $current = wp_get_current_user(); 
        ?>
        <p>Vitaj, <?= esc_html(trim($current->first_name . ' ' . $current->last_name) ?: $current->user_login) ?>!</p>
        <form method="post">
            <?php wp_nonce_field('remp_logout_action', 'remp_logout_nonce'); ?>
            <button type="submit" style="padding:10px 20px;background:#d9534f;color:#fff;border:none;border-radius:4px;">Odhlásiť sa</button>
        </form>
    <?php else: ?>
        <h2 style="text-align:center;">Prihlásenie cez REMP SSO</h2>
        <?php if (!empty($error)): ?>
            <div style="color:red;text-align:center;margin-bottom:10px;"><?= esc_html($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('remp_login_action', 'remp_login_nonce'); ?>
            <p><label>Email:<br><input type="email" name="email" required style="width:100%;padding:8px;"></label></p>
            <p><label>Heslo:<br><input type="password" name="password" required style="width:100%;padding:8px;"></label></p>
            <button type="submit" style="width:100%;padding:10px;background:#0073aa;color:#fff;border:none;border-radius:4px;">Prihlásiť sa</button>
        </form>
    <?php endif; ?>
</div>
