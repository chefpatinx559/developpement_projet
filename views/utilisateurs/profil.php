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

// Récupération des données
$prenom = trim($_POST['prenom'] ?? '');
$nom = trim($_POST['nom'] ?? '');
$nom_complet = trim("$prenom $nom");
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$nouveau_mdp = !empty($_POST['password']) ? $_POST['password'] : null;

try {
    // 1. Mise à jour des infos principales
    $sql = "UPDATE utilisateurs SET nom_prenom = ?, email = ?, telephone = ? WHERE utilisateur_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom_complet, $email, $telephone, $id]);

    // 2. Mot de passe (si renseigné)
    if ($nouveau_mdp) {
        if ($nouveau_mdp !== ($_POST['password_confirm'] ?? '')) {
            $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
        } else {
            $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET mdp = ? WHERE utilisateur_id = ?");
            $stmt->execute([$hash, $id]);
        }
    }

    // 3. Photo de profil
    if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo = file_get_contents($_FILES['photo']['tmp_name']);
        $type = $_FILES['photo']['type'];

        if (strlen($photo) > 2 * 1024 * 1024) {
            $_SESSION['error'] = "La photo est trop volumineuse (max 2 Mo).";
        } else {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET photo = ?, type_photo = ? WHERE utilisateur_id = ?");
            $stmt->execute([$photo, $type, $id]);

            // Mise à jour immédiate en session
            $_SESSION['photo'] = $photo;
            $_SESSION['type_photo'] = $type;
        }
    }

    // Mise à jour des variables de session
    $_SESSION['nom_prenom'] = $nom_complet;
    $_SESSION['email'] = $email;
    $_SESSION['telephone'] = $telephone;

    // Message de succès (seulement s'il n'y a pas d'erreur)
    if (!isset($_SESSION['error'])) {
        $_SESSION['success'] = "Votre profil a été mis à jour avec succès !";
    }

} catch (Exception $e) {
    error_log("Erreur mise à jour profil (user $id) : " . $e->getMessage());
    $_SESSION['error'] = "Une erreur est survenue lors de la sauvegarde.";
}

// Redirection finale vers le dashboard
header("Location: http://localhost/developpement_projet/utilisateur/dashboard");
exit;
?>