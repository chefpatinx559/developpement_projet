<?php
session_start();
require "database/database.php";

setlocale(LC_ALL, 'C');
ini_set('default_charset', 'UTF-8');

if (!isset($_SESSION['utilisateur_id'])) {
    die("Session expirée.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['montant'])) {
    die("Données manquantes.");
}

$montant = (int)$_POST['montant'];
if ($montant < 100) die("Minimum 100 FCFA");

// Stocke le phone pour usage futur si besoin
if (!empty($_POST['phone'])) {
    $_SESSION['payment_phone'] = trim($_POST['phone']);
}

$url = "https://api.wave.com/v1/checkout/sessions";
$token = "wave_ci_prod_VUL28uJjZT0S3RXB5Ya0jXtWtzl9Rbk58YfVduX59n3MwJkppBqCkuPR8mQYoa5Ba5PY9Ql357U4T8i3OSbBmamjYBPPYtV2Aw";
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/utilisateur/';

$payload = [
    "amount"      => $montant,
    "currency"    => "XOF",
    "success_url" => $base_url . "succes?montant=$montant&user_id=" . $_SESSION['utilisateur_id'],
    "error_url"   => $base_url . "error?error=1"
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT        => 30
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

// Wave renvoie 200 (et non 201) → c'est normal !
if (($httpCode === 200 || $httpCode === 201) && !empty($result['wave_launch_url'])) {
    // REDIRECTION VERS LA VRAIE PAGE WAVE → PAIEMENT
    header("Location: " . $result['wave_launch_url']);
    exit;
} else {
    // En prod, tu peux faire une page d'erreur jolie
    die("Erreur Wave : " . htmlspecialchars($response));
}
?>