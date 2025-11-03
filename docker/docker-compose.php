<?php
/**
 * docker-compose.php
 *
 * Generates docker-compose.yml from PHP array configuration.
 */
loadEnv();

$projectName = getenv('PROJECT_NAME') ?: '';
$uid = getenv('UID') ?: 1000;
$gid = getenv('GID') ?: 1000;
$uname = getenv('UNAME') ?: 'docker';

$config = [
    'version' => '3.8',
    'services' => [
        'nginx' => [
            'container_name' => '${PROJECT_NAME}-nginx',
            'image' => 'nginx:stable',
            'environment' => ['NGINX_PORT' => '[::]:80'],
            'ports' => ['80:80'],
            'volumes' => [
                '../apps/:/var/www/html:rw',
                './images/nginx/nginx.conf:/etc/nginx/conf.d/default.template:ro',
            ],
            'healthcheck' => [
                'test' => ['CMD-SHELL', 'service nginx status || exit 1'],
                'timeout' => '2s',
                'retries' => 10,
                'interval' => '5s',
            ],
            'restart' => 'unless-stopped',
            'networks' => [
                "${projectName}-network" => [
                    'aliases' => [
                        'campaign.remp.press',
                        'mailer.remp.press',
                        'sso.remp.press',
                        'beam.remp.press',
                        'tracker.beam.remp.press',
                        'segments.beam.remp.press',
                        'mailhog.remp.press',
                        'crm.remp.press',
                        'webisup.loc',
                        'kibana.beam.remp.press',
                    ],
                ],
            ],
            'command' => "/bin/bash -c \"envsubst '\$\$NGINX_PORT' < /etc/nginx/conf.d/default.template > /etc/nginx/conf.d/default.conf && exec nginx -g 'daemon off;'\"",
        ],
        'proxyx' => [
            'container_name' => '${PROJECT_NAME}-proxyx',
            'build' => ['context' => './images/proxyx'],
            'ports' => ['443:443'],
            'expose' => ['80'],
            'restart' => 'always',
            'networks' => ['${PROJECT_NAME}-network'],
        ],
        'phpmyadmin' => [
            'container_name' => '${PROJECT_NAME}-phpmyadmin',
            'image' => 'phpmyadmin',
            'restart' => 'always',
            'environment' => [
                'PMA_ARBITRARY' => '1',
                'UPLOAD_LIMIT' => '2048M',
            ],
            'networks' => ['${PROJECT_NAME}-network'],
        ],
        'mysql' => [
            'container_name' => '${PROJECT_NAME}-mysql',
            'image' => 'mysql:8.0',
            'volumes' => [
                './volumes/mysql:/var/lib/mysql',
                './images/mysql/init.sql:/docker-entrypoint-initdb.d/init.sql',
            ],
            'command' => [
                '--character-set-server=utf8mb4',
                '--collation-server=utf8mb4_unicode_ci',
                '--skip-character-set-client-handshake',
                '--explicit_defaults_for_timestamp',
            ],
            'environment' => [
                'MYSQL_ALLOW_EMPTY_PASSWORD' => 'no',
                'MYSQL_ROOT_PASSWORD' => 'secret',
            ],
            'healthcheck' => [
                'test' => ['CMD', 'mysqladmin', 'ping', '-h', 'localhost'],
                'timeout' => '2s',
                'retries' => 10,
                'interval' => '5s',
            ],
            'restart' => 'unless-stopped',
            'networks' => ['${PROJECT_NAME}-network'],
        ],
        'redis' => [
            'container_name' => '${PROJECT_NAME}-redis',
            'image' => 'redis:6.2',
            'volumes' => ['redis-data:/data'],
            'healthcheck' => [
                'test' => ['CMD', 'redis-cli', '--raw', 'incr', 'ping'],
                'timeout' => '2s',
                'retries' => 10,
                'interval' => '5s',
            ],
            'networks' => ['${PROJECT_NAME}-network'],
        ],
        'mailhog' => [
            'container_name' => '${PROJECT_NAME}-mailhog',
            'image' => 'mailhog/mailhog:v1.0.0',
            'environment' => ['MH_HOSTNAME' => 'mailhog.remp.press'],
            'restart' => 'unless-stopped',
            'networks' => ['${PROJECT_NAME}-network'],
        ],
    ],
    'volumes' => [
        'kafka-data' => ['driver' => 'local'],
        'redis-data' => ['driver' => 'local'],
        'elastic-data' => ['driver' => 'local'],
    ],
    'networks' => [
        "${projectName}-network" => ['driver' => 'bridge'],
    ],
];
$extraServices = [
    'zookeeper' => [
        'container_name' => '${PROJECT_NAME}-zookeeper',
        'image' => 'wurstmeister/zookeeper',
        'hostname' => 'zookeeper',
        'ports' => ['2181:2181'],
        'healthcheck' => [
            'test' => ['CMD', 'nc', '-z', 'localhost', '2181'],
            'timeout' => '2s',
            'retries' => 10,
            'interval' => '5s',
        ],
        'restart' => 'unless-stopped',
        'networks' => ['${PROJECT_NAME}-network'],
    ],
    'kafka' => [
        'container_name' => '${PROJECT_NAME}-kafka',
        'image' => 'wurstmeister/kafka',
        'hostname' => 'kafka',
        'ports' => ['9092:9092'],
        'depends_on' => ['zookeeper'],
        'environment' => [
            'KAFKA_ADVERTISED_HOST_NAME' => 'kafka',
            'KAFKA_ZOOKEEPER_CONNECT' => 'zookeeper:2181',
            'KAFKA_CREATE_TOPICS' => 'beam_events:1:1',
            'KAFKA_BROKER_ID' => '1001',
            'KAFKA_RESERVED_BROKER_MAX_ID' => '1001',
        ],
        'volumes' => ['kafka-data:/data'],
        'healthcheck' => [
            'test' => 'nc -z localhost 9092',
            'timeout' => '2s',
            'retries' => 10,
            'interval' => '5s',
        ],
        'restart' => 'unless-stopped',
        'networks' => ['${PROJECT_NAME}-network'],
    ],
    'elasticsearch' => [
        'container_name' => '${PROJECT_NAME}-elasticsearch',
        'build' => './images/elasticsearch',
        'volumes' => [
            './images/elasticsearch/elasticsearch.yml:/usr/share/elasticsearch/config/elasticsearch.yml',
            'elastic-data:/usr/share/elasticsearch/data',
        ],
        'healthcheck' => [
            'test' => 'curl -s http://localhost:9200 >/dev/null || exit 1',
            'timeout' => '2s',
            'retries' => 10,
            'interval' => '5s',
        ],
        'restart' => 'unless-stopped',
        'networks' => ['${PROJECT_NAME}-network'],
    ],
    'kibana' => [
        'container_name' => '${PROJECT_NAME}-kibana',
        'image' => 'docker.elastic.co/kibana/kibana:8.6.1',
        'restart' => 'unless-stopped',
        'networks' => ['${PROJECT_NAME}-network'],
    ],
    'telegraf' => [
        'container_name' => '${PROJECT_NAME}-telegraf',
        'build' => './images/telegraf',
        'volumes' => [
            './images/telegraf/telegraf.conf:/etc/telegraf/telegraf.conf:ro',
        ],
        'depends_on' => [
            'elasticsearch' => ['condition' => 'service_healthy'],
            'kafka' => ['condition' => 'service_healthy'],
        ],
        'restart' => 'unless-stopped',
        'networks' => ['${PROJECT_NAME}-network'],
    ],
    'beam_tracker' => [
        'container_name' => '${PROJECT_NAME}-beam_tracker',
        'build' => '../apps/Beam/go/cmd/tracker',
        'depends_on' => ['zookeeper'],
        'restart' => 'unless-stopped',
        'networks' => ['${PROJECT_NAME}-network'],
    ],
    'beam_segments' => [
        'container_name' => '${PROJECT_NAME}-beam_segments',
        'build' => '../apps/Beam/go/cmd/segments',
        'depends_on' => ['elasticsearch'],
        'restart' => 'unless-stopped',
        'networks' => ['${PROJECT_NAME}-network'],
    ],
];

$config['services'] = array_merge($config['services'], $extraServices);

// common PHP app template
$phpApps = [
    'crm' => '../apps/Crm',
    'web' => '../apps/Web',
    'campaign' => '../apps/Campaign',
    'mailer' => '../apps/Mailer',
    'sso' => '../apps/Sso',
    'beam' => '../apps/Beam',
];

foreach ($phpApps as $name => $path) {
    
    
    if(ucfirst($name) == 'Web'){
        $dockerPath = '../apps/' . ucfirst($name) . '/images/php';
    }elseif(ucfirst($name) == 'Crm'){
        $dockerPath = '../apps/' . ucfirst($name) . '/docker/php';
    }else{
        $dockerPath = './images/php';
    }
    $config['services'][$name] = [
        'container_name' => '${PROJECT_NAME}-'."${name}",
        'user' => "${uid}:${gid}",
        'build' => [
            'context' => $dockerPath,
            'args' => [
                'UID' => $uid,
                'GID' => $gid,
                'UNAME' => $uname,
            ],
        ],
        'volumes' => [
            "{$path}:/var/www/html/" . ucfirst($name) . ":rw",
            '../apps/Composer:/var/www/html/Composer:rw',
            '../apps/Package:/var/www/html/Package:rw',
        ],
        'depends_on' => [
            'nginx' => ['condition' => 'service_healthy'],
            'mysql' => ['condition' => 'service_healthy'],
            'redis' => ['condition' => 'service_healthy'],
        ],
        'restart' => 'unless-stopped',
        'networks' => ['${PROJECT_NAME}-network'],
    ];
}

$config['services']['php-74-wp'] = [
        'container_name' => '${PROJECT_NAME}-php-74-wp',
        'user' => "${uid}:${gid}",
        'build' => [
            'context' => '../apps/Web/images/php-74',
            'args' => [
                'UID' => $uid,
                'GID' => $gid,
                'UNAME' => $uname,
            ],
        ],
        'volumes' => [
            '../apps/Web:/var/www/html/Web:rw',
            '../apps/Composer:/var/www/html/Composer:rw',
            '../apps/Package:/var/www/html/Package:rw',
        ],
        'depends_on' => [
            'nginx' => ['condition' => 'service_healthy'],
            'mysql' => ['condition' => 'service_healthy'],
            'redis' => ['condition' => 'service_healthy'],
        ],
        'restart' => 'unless-stopped',
        'networks' => ['${PROJECT_NAME}-network'],
    ];

// convert to YAML
function yamlEncode($data, $indent = 0): string
{
    $yaml = '';
    foreach ($data as $key => $value) {
        $prefix = str_repeat('  ', $indent);
        if (is_array($value)) {
            $isAssoc = array_keys($value) !== range(0, count($value) - 1);
            if ($isAssoc) {
                $yaml .= "{$prefix}{$key}:\n" . yamlEncode($value, $indent + 1);
            } else {
                $yaml .= "{$prefix}{$key}:\n";
                foreach ($value as $v) {
                    if (is_array($v)) {
                        $yaml .= "{$prefix}-\n" . yamlEncode($v, $indent + 2);
                    } else {
                        $yaml .= "{$prefix}- {$v}\n";
                    }
                }
            }
        } else {
            $yaml .= "{$prefix}{$key}: {$value}\n";
        }
    }
    return $yaml;
}

// write file
writeYamlFile('docker-compose.yml', $config);

echo "✅ docker-compose.yml generated successfully.\n";

function loadEnv(string $path = __DIR__ . '/.env'): void
{
    if (!file_exists($path)) {
        echo "⚠️  .env file not found at {$path}\n";
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // ignoruj komentáre
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // rozdeľ KEY=VALUE
        if (!str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // odstráni úvodzovky ak sú
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
    }
}

/**
 * Generate YAML safely and correctly.
 */
function writeYamlFile(string $path, array $data): void
{
    // Prefer native yaml extension if available
    if (function_exists('yaml_emit')) {
        $yaml = yaml_emit($data, YAML_UTF8_ENCODING, YAML_LN_BREAK);
    } else {
        // Fallback: JSON -> YAML cez symetrickú konverziu
        $yaml = convertToYaml($data);
    }

    // Odstráň trailing '---' ak sa pridáva
    $yaml = preg_replace('/^---\s*\n?/', '', $yaml);
    file_put_contents($path, $yaml);
}

/**
 * Simple recursive YAML converter (JSON-style fallback).
 */
function convertToYaml(array $data, int $indent = 0): string
{
    $yaml = '';
    $prefix = str_repeat('  ', $indent);
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $isAssoc = array_keys($value) !== range(0, count($value) - 1);
            if ($isAssoc) {
                $yaml .= "{$prefix}{$key}:\n" . convertToYaml($value, $indent + 1);
            } else {
                $yaml .= "{$prefix}{$key}:\n";
                foreach ($value as $item) {
                    if (is_array($item)) {
                        $yaml .= "{$prefix}  -\n" . convertToYaml($item, $indent + 2);
                    } else {
                        $yaml .= "{$prefix}  - " . escapeYamlValue($item) . "\n";
                    }
                }
            }
        } else {
            $yaml .= "{$prefix}{$key}: " . escapeYamlValue($value) . "\n";
        }
    }
    return $yaml;
}

/**
 * Ensures YAML-safe values (adds quotes if necessary).
 */
function escapeYamlValue($value): string
{
    if ($value === null)
        return 'null';
    if (is_bool($value))
        return $value ? 'true' : 'false';
    if (is_numeric($value))
        return (string) $value;

    // Add quotes if string contains spaces, colons, or special chars
    if (preg_match('/[:#\-\s]|^$/', (string) $value)) {
        return '"' . addcslashes((string) $value, '"') . '"';
    }
    return (string) $value;
}
