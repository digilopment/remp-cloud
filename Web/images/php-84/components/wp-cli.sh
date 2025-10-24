#!/bin/bash
set -e

echo ">>> Inštalujem WP-CLI..."

# Stiahni wp-cli.phar
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar

# Over funkčnosť
php wp-cli.phar --info >/dev/null 2>&1 || {
    echo "Chyba: PHP nie je dostupné alebo wp-cli.phar je poškodený."
    exit 1
}

# Nastav spustiteľné práva
chmod +x wp-cli.phar

# Presuň do /usr/local/bin
if [ -w /usr/local/bin ]; then
    mv wp-cli.phar /usr/local/bin/wp
else
    echo "Nemáš práva na /usr/local/bin – používam sudo..."
    sudo mv wp-cli.phar /usr/local/bin/wp
fi

# Over inštaláciu
if command -v wp >/dev/null 2>&1; then
    echo "✅ WP-CLI úspešne nainštalovaný."
    wp --info
else
    echo "❌ Inštalácia zlyhala – skontroluj PATH alebo práva."
    exit 1
fi
