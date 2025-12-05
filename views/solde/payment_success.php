<?php
// session_start();
require "database/database.php";

// Vérification session
if (!isset($_SESSION['utilisateur_id'])) {
    die("Session expirée. Veuillez vous reconnecter.");
}

$user_id = $_SESSION['utilisateur_id'];

// Récupération sécurisée du montant
if (!isset($_GET['montant']) || !ctype_digit($_GET['montant'])) {
    die("Montant manquant ou invalide.");
}

$montant = (int)$_GET['montant'];

if ($montant > 100) {
    die("Montant minimum : 100 FCFA");
}

try {
    // Crédit du solde (toujours fait en premier)
    $stmt = $pdo->prepare("UPDATE utilisateurs SET solde = solde + ? WHERE utilisateur_id = ?");
    $stmt->execute([$montant, $user_id]);

    // Log du dépôt (si la table existe, sinon on ignore l'erreur)
    // $pdo->prepare("
    //     INSERT INTO transactions (utilisateur_id, type, montant, methode, statut, date_transaction) 
    //     VALUES (?, 'dépôt', ?, 'Wave', 'succès', NOW())
    // ")->execute([$user_id, $montant]);

} catch (Exception $e) {
    // Si erreur SQL (ex: table transactions n'existe pas), on log mais on continue
    error_log("Erreur lors du dépôt Wave (user $user_id, montant $montant) : " . $e->getMessage());
    
    // Le solde est déjà crédité → on ne bloque pas l'utilisateur !
}

$_SESSION['message_success'] = "Paiement de " . number_format($montant) . " FCFA reçu ! Votre solde a été crédité.";

header("Location:https://soutra.pro/utilisateur/balance");
exit;
?>