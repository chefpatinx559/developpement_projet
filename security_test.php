<?php

$base_url = "http://localhost/developpement_projet/";
echo "=== SECURITY TEST START ===\n\n";

// --- detection des endpoints existants (adaptation à votre workspace) ---
$fs_base = 'C:\\wamp64\\www\\developpement_projet\\';

function fs_exists_rel($rel) {
    global $fs_base;
    return file_exists($fs_base . str_replace('/', '\\', $rel));
}

// priorités pour chaque type de test
$login_candidates = [
    'api/connexion.php',
    'views/utilisateurs/connexion-utilisateur.php',
    'connexion.php',
    'index.php'
];

$xss_candidates = [
    'views/utilisateurs/inscription-utilisateur.php',
    'views/utilisateurs/connexion-utilisateur.php',
    'views/api/succes.php',
    'index.php'
];

$upload_candidates = [
    'upload.php',
    'api/upload.php',
    'router.php'
];

function pick_url($candidates, $base_url) {
    foreach ($candidates as $c) {
        if (fs_exists_rel($c)) {
            return $base_url . str_replace('\\', '/', $c);
        }
    }
    return null;
}

$login_url = pick_url($login_candidates, $base_url);
$xss_url   = pick_url($xss_candidates, $base_url);
$upload_url= pick_url($upload_candidates, $base_url);

echo "Endpoints détectés :\n";
echo " - login: " . ($login_url ?? 'aucun') . "\n";
echo " - xss  : " . ($xss_url   ?? 'aucun') . "\n";
echo " - upload: " . ($upload_url?? 'aucun') . "\n\n";

// -------------------------------------------
// 1️⃣  TEST SQL INJECTION
// -------------------------------------------
$sql_payloads = [
    "' OR '1'='1",
    "' OR 1=1 --",
    "'; DROP TABLE users; --"
];

foreach ($sql_payloads as $payload) {
    if (!$login_url) {
        echo "[SQL Injection] SKIP - aucun endpoint de login détecté\n\n";
        break;
    }

    $url = $login_url . "?email=" . urlencode($payload) . "&password=" . urlencode($payload);
    $response = @file_get_contents($url);

    echo "[SQL Injection] Testing payload $payload\n";

    if ($response === false) {
        echo "⚠ ERREUR - Endpoint introuvable ou réponse HTTP non disponible pour $url\n\n";
        continue;
    }

    if (strpos($response, 'SQL') !== false 
        || strpos($response, 'PDO') !== false 
        || strpos($response, 'Warning') !== false) {
        echo "⚠ VULNERABLE - Erreur SQL détectée !!!\n\n";
    } else {
        echo "✔ OK - Pas de fuite SQL détectée\n\n";
    }
}

// -------------------------------------------
// 2️⃣  TEST XSS
// -------------------------------------------
$xss_payloads = [
    "<script>alert('XSS')</script>",
    "<img src=x onerror=alert(1)>",
    "<svg onload=alert(1)>"
];

foreach ($xss_payloads as $payload) {
    if (!$xss_url) {
        echo "[XSS] SKIP - aucun endpoint de type vue/contact détecté\n\n";
        break;
    }

    $url = $xss_url . "?name=" . urlencode($payload);
    $response = @file_get_contents($url);

    echo "[XSS] Testing payload $payload\n";

    if ($response === false) {
        echo "⚠ ERREUR - Endpoint introuvable ou réponse HTTP non disponible pour $url\n\n";
        continue;
    }

    if (strpos($response, $payload) !== false) {
        echo "⚠ VULNERABLE - Le script apparaît dans la page !!!\n\n";
    } else {
        echo "✔ OK - Le contenu a été échappé (ou non reflété)\n\n";
    }
}

// -------------------------------------------
// 3️⃣  TEST AUTHENTIFICATION
// -------------------------------------------
$emails = ["test@test.com", "admin@site.com"];
$passwords = ["123456", "admin", "password"];

foreach ($emails as $email) {
    foreach ($passwords as $pass) {
        echo "[Auth Test] Email $email - Pass $pass\n";

        if (!$login_url) {
            echo "⚠ SKIP - aucun endpoint login détecté\n\n";
            break 2;
        }

        $url = $login_url . "?email=" . urlencode($email) . "&password=" . urlencode($pass);
        $response = @file_get_contents($url);

        if ($response === false) {
            echo "⚠ ERREUR - Endpoint introuvable ou réponse HTTP non disponible pour $url\n\n";
            continue;
        }

        if (strpos($response, 'dashboard') !== false || strpos($response, 'success') !== false) {
            echo "⚠ ATTENTION - Connexion réussie avec faibles identifiants !!!\n\n";
        } else {
            echo "✔ OK - Connexion protégée (ou non accessible)\n\n";
        }
    }
}

// -------------------------------------------
// 4️⃣  TEST UPLOAD DANGEREUX
// -------------------------------------------
echo "[Upload Test] Vérification upload de fichier PHP\n";

if (!$upload_url) {
    echo "⚠ SKIP - aucun endpoint d'upload détecté\n\n";
} else {
    $malicious_file = "<?php echo 'HACKED'; ?>";
    file_put_contents("malicious.php", $malicious_file);

    $boundary = uniqid();
    $delimiter = '-------------' . $boundary;

    $post_data = buildDataFiles('file', 'malicious.php', file_get_contents('malicious.php'), $delimiter);

    $context = stream_context_create([
        'http' => [
            'header' => "Content-Type: multipart/form-data; boundary=$delimiter",
            'method' => 'POST',
            'content' => $post_data,
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents($upload_url, false, $context);

    if ($response === false) {
        echo "⚠ ERREUR - Endpoint d'upload introuvable ou réponse HTTP non disponible pour $upload_url\n";
    } elseif (strpos($response, 'HACKED') !== false) {
        echo "⚠ VULNÉRABLE - Le fichier PHP a été exécuté !\n";
    } else {
        echo "✔ OK - Upload sécurisé (ou non testé)\n";
    }

    unlink("malicious.php");
}

echo "\n=== SECURITY TEST FINISHED ===\n";

function buildDataFiles($name, $filename, $content, $boundary) {
    $eol = "\r\n";
    $data = "--$boundary$eol";
    $data .= "Content-Disposition: form-data; name=\"$name\"; filename=\"$filename\"$eol";
    $data .= "Content-Type: application/octet-stream$eol$eol";
    $data .= $content . $eol;
    $data .= "--$boundary--$eol";
    return $data;
}

?>