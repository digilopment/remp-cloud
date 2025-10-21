<?php

$pluginsDir = __DIR__ . '/www/plugins';
$buildBase = __DIR__ . '/build';

// --- Získanie názvu pluginu zo vstupu CLI ---
$pluginName = $argv[1] ?? null;

// --- Ak nie je zadaný názov pluginu, vypíš všetky dostupné pluginy ---
if (!$pluginName) {
    echo "Dostupné pluginy:\n";
    $dirs = glob($pluginsDir . '/*', GLOB_ONLYDIR);
    foreach ($dirs as $dir) {
        echo basename($dir) . "\n";
    }
    exit(0);
}

$srcDir = $pluginsDir . '/' . $pluginName;
$buildDir = $buildBase . '/' . $pluginName;
$outputZip = $buildBase . '/' . $pluginName . '.zip';

// --- Skontroluj, či existuje zdrojový priečinok ---
if (!is_dir($srcDir)) {
    fwrite(STDERR, "Plugin '$pluginName' neexistuje.\n");
    exit(1);
}

// --- Vyčistenie predchádzajúceho buildu ---
if (is_dir($buildDir)) {
    $it = new RecursiveDirectoryIterator($buildDir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        $file->isDir() ? rmdir($file) : unlink($file);
    }
    rmdir($buildDir);
}

if (file_exists($outputZip)) {
    unlink($outputZip);
}

mkdir($buildDir, 0755, true);

// --- Kopírovanie pluginu do dočasného buildu s vylúčením súborov ---
$exclude = ['.git', 'node_modules', 'tests'];
$excludePatterns = ['*.log', '*.DS_Store'];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $relPath = substr($item->getPathname(), strlen($srcDir) + 1);

    // preskočiť vylúčené priečinky
    foreach ($exclude as $ex) {
        if (strpos($relPath, $ex . DIRECTORY_SEPARATOR) === 0) continue 2;
    }

    // preskočiť vylúčené súbory podľa masky
    foreach ($excludePatterns as $pattern) {
        if (fnmatch($pattern, basename($relPath))) continue 2;
    }

    $dest = $buildDir . '/' . $relPath;

    if ($item->isDir()) {
        mkdir($dest, 0755, true);
    } else {
        copy($item->getPathname(), $dest);
    }
}

// --- Vytvorenie ZIP súboru ---
$zip = new ZipArchive();
if ($zip->open($outputZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Nepodarilo sa vytvoriť ZIP: $outputZip\n");
    exit(1);
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($buildDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($files as $file) {
    $filePath = $file->getPathname();
    $localPath = substr($filePath, strlen($buildBase) + 1);
    $zip->addFile($filePath, $localPath);
}

$zip->close();

// --- Vyčistenie dočasného priečinka ---
$it = new RecursiveDirectoryIterator($buildDir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
foreach ($files as $file) {
    $file->isDir() ? rmdir($file) : unlink($file);
}
rmdir($buildDir);

echo "Build hotový: $outputZip\n";
