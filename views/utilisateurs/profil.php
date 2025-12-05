<?php
session_start();
require 'database/database.php'; // Connexion PDO

// Sécurité : uniquement en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tableau-bord.php");
    exit;
}

// Vérification de la session
if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error'] = "Session expirée. Veuillez vous reconnecter.";
    header("Location: login.php");
    exit;
}

$id = $_SESSION['utilisateur_id'];

// Récupération et nettoyage des données
$prenom       = trim($_POST['prenom'] ?? '');
$nom          = trim($_POST['nom'] ?? '');
$nom_complet  = trim($prenom . ' ' . $nom);
$email        = trim($_POST['email'] ?? '');
$telephone    = trim($_POST['telephone'] ?? '');
$nouveau_mdp  = !empty($_POST['password']) ? $_POST['password'] : null;

try {
    // 1. Mise à jour des informations principales
    $stmt = $pdo->prepare("UPDATE utilisateurs SET nom_prenom = ?, email = ?, telephone = ? WHERE utilisateur_id = ?");
    $stmt->execute([$nom_complet, $email, $telephone, $id]);

    // 2. Changement de mot de passe (si saisi)
    if ($nouveau_mdp !== null) {
        if ($nouveau_mdp !== ($_POST['password_confirm'] ?? '')) {
            $_SESSION['error'] = "Les deux mots de passe ne correspondent pas.";
        } else {
            $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET mdp = ? WHERE utilisateur_id = ?");
            $stmt->execute([$hash, $id]);
        }
    }

    // 3. Upload de la photo de profil
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo = file_get_contents($_FILES['photo']['tmp_name']);
        $type  = $_FILES['photo']['type'];

        // Limitation à 2 Mo
        if (strlen($photo) > 2 * 1024 * 1024) {
            $_SESSION['error'] = "La photo est trop volumineuse (maximum 2 Mo).";
        } else {
            // Mise à jour en base
            $stmt = $pdo->prepare("UPDATE utilisateurs SET photo = ?, type_photo = ? WHERE utilisateur_id = ?");
            $stmt->execute([$photo, $type, $id]);

            // Mise à jour immédiate en session (important pour affichage instantané)
            $_SESSION['photo']      = $photo;
            $_SESSION['type_photo'] = $type;
        }
    }

    // Mise à jour des données en session
    $_SESSION['nom_prenom'] = $nom_complet;
    $_SESSION['email']       = $email;
    $_SESSION['telephone']   = $telephone;

    // Message de succès si aucune erreur
    if (!isset($_SESSION['error'])) {
        $_SESSION['success'] = "Votre profil a été mis à jour avec succès !";
    }

} catch (Exception $e) {
    error_log("Erreur mise à jour profil (utilisateur $id) : " . $e->getMessage());
    $_SESSION['error'] = "Une erreur inattendue est survenue. Veuillez réessayer.";
}

// Redirection finale vers le tableau de bord
header("Location: http://localhost/developpement_projet/utilisateur/dashboard");
exit;
?>