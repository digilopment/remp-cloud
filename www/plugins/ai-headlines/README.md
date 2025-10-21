# 1. Description
- Plugin **AI Headlines** pre WordPress generuje návrhy nadpisov pre články pomocou OpenAI.
- Pridáva tlačidlo do editora (Classic Editor) pre **koncepty**, ktoré po kliknutí navrhne **nadpisy** a názov hlavnej **témey**.
- Ukladá navrhnuté nadpisy do vlastnej databázovej tabuľky ako json.
- Podporuje hromadné generovanie nadpisov cez WP-CLI príkazy (wp ai-headlines generate).
- Umožňuje nastavenie **OpenAI API kľúča cez administračné rozhranie**.
- Používa AJAX na rýchle načítanie návrhov bez obnovy stránky.
- Podporuje možnosť nového vygenerovania návrhov (suggestion headlines) pre konkrétny článok, ak je zaskrtnutý checkbox *vynúťiť*, inak načíta už vygenerované **headlines** ak sú.
- Celkovo ide o nástroj na rýchle a automatizované generovanie SEO-friendly nadpisov pre články s flexibilitou pre opakované generovanie.

##### Webhooky
``` public function register()
    {
        add_action('wp_ajax_ai_headlines', [$this, 'getHeadlines']);
        add_action('wp_ajax_ai_set_title', [$this, 'setHeadline']);
    }
```
- `getHeadlines()` – načíta existujúce návrhy nadpisov alebo zavolá OpenAI a uloží nové, ak neexistujú alebo je zvolený parameter `force` (nové generovanie).
- `setHeadline()` – umožní nastaviť vybraný nadpis článku z navrhovaných priamo do WordPressu.

Chráni prístup cez nonce a kontrolu práv používateľa.

## 2. Požiadavky
- PHP ≥ 8.0  
- WordPress ≥ 6.0  
- WP-CLI (pre CLI príkazy)  
- OpenAI API key  

---

## 3. Inštalácia cez CLI

1. Rozbaľ a skopíruj celý priečinok **ai-headlines** do:
   `wp-content/plugins/` 
   alebo vytvor **symolicky link** 
  `ln -s ./www/wordpress/wp-content/plugins/ai-headlines ./www/plugins/ai-headlines`

2. Aktivuj plugin:
   `wp plugin activate ai-headlines`
   `wp plugin status ai-headlines` - skontroluj status pluginu

3. Zadaj svoj OpenAI API key.
  `wp option update ai_openai_api_key '<OPEN_API_KEY>'`
---

## 4. Inštalácia cez Web

1. Rozbaľ a skopíruj celý priečinok **ai-headlines** do:
   `wp-content/plugins/` 
   alebo vytvor symolicky link 
  `ln -s ./www/wordpress/wp-content/plugins/ai-headlines ./www/plugins/ai-headlines`

2. Aktivuj plugin v administrácii:
   WP Admin → Plugins → Installed Plugins → Activate

3. V nastaveniach pluginu zadaj svoj OpenAI API key.

---

## 5. Použitie WP-CLI pre prácu s pluginom

Generovanie headlines cez CLI pre konkrétny článok/články:
   ```
   wp ai-headlines generate <post_id>
   wp ai-headlines generate <post_id>
   wp ai-headlines generate <post_id>,<post_id>,<post_id>
   wp ai-headlines generate all
   ```
Parameter `--renew ` ako argument pregeneruje všetky headlines v databaze aj tie ktoré už boli vygenerované

## 6. Štruktúra pluginu
```

├── ai-headlines.php
├── src/
│   ├── Plugin.php
│   ├── Admin/
│   │   ├── ACFIntegration.php
│   │   ├── AdminSettings.php
│   │   ├── AdminUI.php
│   ├── Api/
│   │   ├── OpenAIClient.php
│   │   ├── Routes.php
│   ├── Cli/
│   │   └── GenerateTitlesCommand.php
│   ├── Storage/
│   │   └── TitlesRepository.php
│   └── Utils/
│       ├─── HeadlinePlaceHolder.php
│       └─── PromptBuilder.php
│
├── assets/
│   └── js/admin.js
├── vendor/
├── composer.json
└── README.md
```