<?php
require "database/database.php";

// Wave envoie un JSON → on le lit
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Vérification obligatoire
if (!isset($data['status']) || !isset($data['metadata'])) {
    http_response_code(400);
    exit("Invalid webhook");
}

// Vérifier si le paiement est réussi
if ($data['status'] === "completed") {
    
    $user_id = $data['metadata']['user_id'];
    $amount  = intval($data['metadata']['montant']);

    // Mise à jour dans la base
    $stmt = $pdo->prepare("UPDATE utilisateurs SET solde = solde + ? WHERE utilisateur_id = ?");
    $stmt->execute([$amount, $user_id]);
}

http_response_code(200); // Wave doit recevoir 200
echo "OK";
