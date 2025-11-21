<?php
// test.php - Script de test complet pour application PHP SANS Composer
// À placer à la racine de ton projet
// Lancer avec : php test.php

echo "=======================================\n";
echo "   TEST COMPLET DE TON SITE PHP   \n";
echo "=======================================\n\n";

$errors = 0;
$baseUrl = "http://localhost:8000"; // Change le port si besoin

// 1. Démarrage du serveur PHP intégré en arrière-plan
echo "Démarrage du serveur PHP local sur $baseUrl...\n";
$command = "php -S localhost:8000 -t " . escapeshellarg(__DIR__) . " > /dev/null 2>&1 & echo $!";
$pid = trim(shell_exec($command));
sleep(3); // Attente démarrage

function ok($msg) {
    echo "\e[32m✓ $msg\e[0m\n";
}
function ko($msg) {
    global $errors;
    $errors++;
    echo "\e[31m✗ $msg\e[0m\n";
}

// 2. Test syntaxe PHP sur tous les fichiers
echo "\n1. Vérification de la syntaxe PHP...\n";
$files = glob("*.php");
array_push($files, ...glob("*/*.php"));
array_push($files, ...glob("*/*/*.php")); // jusqu'à 3 niveaux

foreach ($files as $file) {
    if (strpos($file, 'test.php') !== false) continue; // skip ce script
    $output = shell_exec("php -l " . escapeshellarg($file) . " 2>&1");
    if (strpos($output, "No syntax errors") !== false) {
        echo "\e[32m✓ $file\e[0m\n";
    } else {
        ko("Syntaxe invalide : $file");
        echo "   → $output\n";
    }
}

// 3. Pages à tester (modifie selon ton projet)
$pages = [
    "/" => "Page d'accueil",
    "/index.php" => "Index",
    "/login.php" => "Login",
    "/register.php" => "Inscription",
    "/contact.php" => "Contact",
    "/dashboard.php" => "Tableau de bord",
    "/profile.php" => "Profil",
    "/api/test.php" => "API test",
];

echo "\n2. Test d'accès aux pages (HTTP 200)...\n";
foreach ($pages as $url => $name) {
    $fullUrl = $baseUrl . $url;
    $ch = curl_init($fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code == 200 || $code == 301 || $code == 302) {
        ok("$name → $url (HTTP $code)");
    } else {
        ko("$name → $url (HTTP $code)");
    }
}

// 4. Test formulaire (exemple login)
echo "\n3. Test du formulaire de connexion...\n";
$ch = curl_init("$baseUrl/login.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "email=test@test.com&password=123456");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code >= 200 && $code < 400 && $response !== false) {
    ok("Formulaire login accessible et répond");
} else {
    ko("Problème avec login.php (code $code)");
}

// 5. Scan de code dangereux
echo "\n4. Scan de sécurité (fonctions dangereuses)...\n";
$dangerous = ["eval(", "system(", "exec(", "shell_exec(", "passthru(", "phpinfo(", "`", "include($_GET", "include(\$_REQUEST"];

foreach (glob("*.php") as $file) {
    if ($file === 'test.php') continue;
    $content = file_get_contents($file);
    foreach ($dangerous as $func) {
        if (stripos($content, $func) !== false) {
            ko("Fonction dangereuse '$func' trouvée dans $file");
        }
    }
}
if ($errors === 0) ok("Aucune fonction dangereuse détectée");

// 6. Fichiers sensibles accessibles ?
echo "\n5. Vérification des fichiers sensibles...\n";
$sensitive = ["/config.php", "/db.php", "/.env", "/phpinfo.php", "/adminer.php", "/backup.sql"];

foreach ($sensitive as $file) {
    $ch = curl_init($baseUrl . $file);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code == 200) {
        ko("FICHIER SENSIBLE ACCESSIBLE : $file");
    } else {
        ok("$file bien protégé (HTTP $code)");
    }
}

// Arrêt du serveur
echo "\nArrêt du serveur PHP...\n";
shell_exec("kill $pid 2>/dev/null");

// Résultat final
echo "\n=======================================\n";
if ($errors === 0) {
    echo "\e[32m🎉 TOUT EST PARFAIT ! Ton site passe tous les tests ! 🎉\e[0m\n";
} else {
    echo "\e[31m⚠️  $errors problème(s) détecté(s). Corrige avant de mettre en ligne !\e[0m\n";
}
echo "=======================================\n";