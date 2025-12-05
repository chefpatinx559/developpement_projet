<?php
session_start();

// ==================== CONNEXION BDD ====================
$host = 'localhost';
$dbname = 'u738064605_soutra';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $e) {
    die('<div style="text-align:center;padding:100px;background:#000;color:#fff;font-size:24px;">Erreur de connexion à la base de données.</div>');
}

// ==================== UTILISATEUR CONNECTÉ ====================
$user_connected = false;
$user_name = "Mon Compte";
$user_photo = "uploads/avatars/default.jpg";

if (isset($_SESSION['user_id'])) {
    $user_connected = true;
    $user_name = $_SESSION['user_name'] ?? "Mon Compte";
    $user_photo = $_SESSION['user_photo'] ?? "uploads/avatars/default.jpg";
}

// ==================== FONCTIONS BDD ====================
function getRoomDetailsFromDB($pdo, $code_hotel) {
    $stmt = $pdo->prepare("
        SELECT c.* FROM chambres c
        LEFT JOIN reservations r ON c.code_chambre = r.code_chambre 
            AND r.etat_reservation = 'actif' 
            AND r.statut_reservation = 'réservé'
            AND r.date_fin >= CURDATE()
        WHERE c.code_hotel = ? 
          AND c.etat_chambre = 'actif'
          AND r.code_chambre IS NULL
        ORDER BY c.prix_chambre ASC
    ");
    $stmt->execute([$code_hotel]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRoomByCodeFromDB($pdo, $code_chambre) {
    $stmt = $pdo->prepare("
        SELECT c.* FROM chambres c
        LEFT JOIN reservations r ON c.code_chambre = r.code_chambre 
            AND r.etat_reservation = 'actif' 
            AND r.statut_reservation = 'réservé'
            AND r.date_fin >= CURDATE()
        WHERE c.code_chambre = ? 
          AND c.etat_chambre = 'actif'
          AND r.code_chambre IS NULL
    ");
    $stmt->execute([$code_chambre]);
    $chambre = $stmt->fetch(PDO::FETCH_ASSOC);
    return $chambre ?: false;
}

function checkRoomAvailability($pdo, $code_chambre, $date_debut, $date_fin) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM reservations 
        WHERE code_chambre = ?
        AND statut_reservation = 'réservé'
        AND etat_reservation = 'actif'
        AND (
            (date_debut <= ? AND date_fin >= ?) OR
            (date_debut <= ? AND date_fin >= ?) OR
            (date_debut >= ? AND date_fin <= ?)
        )
    ");
    $stmt->execute([$code_chambre, $date_debut, $date_debut, $date_fin, $date_fin, $date_debut, $date_fin]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] == 0;
}

function calculateDaysDuration($date_debut, $date_fin) {
    $start = new DateTime($date_debut);
    $end = new DateTime($date_fin);
    return $start->diff($end)->days;
}

// ==================== GESTION DES IMAGES RÉELLES ====================
// Dossier où tu uploades tes photos
$upload_dir = 'uploads/';
$default_hotel = $upload_dir . 'hotels/default.jpg';
$default_chambre = $upload_dir . 'chambres/default.jpg';
$hero_bg = $upload_dir . 'hero.jpg'; // Tu mets ta plus belle photo d'Abidjan ici

$bg = $hero_bg;
$message = '';
$searchResults = [];
$isSearching = false;

// ==================== TRAITEMENT RÉSERVATION ====================
if (isset($_POST['reserver'])) {
    $code_hotel = $_POST['code_hotel'] ?? '';
    $code_chambre = $_POST['code_chambre'] ?? ''; 
    $type_chambre = $_POST['type_chambre_reservee'] ?? '';
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';
    
    $today = date('Y-m-d');
    
    if (empty($code_hotel) || empty($code_chambre) || empty($type_chambre) || empty($date_debut) || empty($date_fin)) {
        $message = "<div class='alert alert-danger'>Tous les champs sont requis.</div>";
    } elseif ($date_debut < $today) {
        $message = "<div class='alert alert-danger'>Date d'arrivée invalide.</div>";
    } elseif ($date_fin <= $date_debut) {
        $message = "<div class='alert alert-danger'>Date de départ incorrecte.</div>";
    } elseif (!checkRoomAvailability($pdo, $code_chambre, $date_debut, $date_fin)) {
        $message = "<div class='alert alert-danger'>Chambre non disponible.</div>";
    } else {
    
    }
}

// ==================== PAGES ====================
if (isset($_POST['search']) && !empty(trim($_POST['query'] ?? ''))) {
    $isSearching = true;
    $query = trim($_POST['query']);
    $q = '%' . $query . '%';
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE etat_hotel = 'actif' AND (ville_hotel LIKE ? OR quartier_hotel LIKE ? OR nom_hotel LIKE ?) ORDER BY nom_hotel LIMIT 30");
    $stmt->execute([$q, $q, $q]);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

elseif (isset($_GET['code_chambre'])) {
    $chambre = getRoomByCodeFromDB($pdo, $_GET['code_chambre']);
    if (!$chambre) {
        die("<div style='text-align:center;padding:200px;background:#f8f9fa;color:#d63031;font-size:28px;'>
                Cette chambre n'est plus disponible.<br><br>
                <a href='.' style='background:var(--orange);color:white;padding:15px 40px;border-radius:50px;text-decoration:none;font-weight:600;'>Retour</a>
             </div>");
    }
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE code_hotel = ?");
    $stmt->execute([$chambre['code_hotel']]);
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);

    $photo_chambre = !empty($chambre['photo_chambre']) ? $upload_dir.'chambres/'.$chambre['photo_chambre'] : $default_chambre;
    $photo_hotel = !empty($hotel['photo_principale']) ? $upload_dir.'hotels/'.$hotel['photo_principale'] : $default_hotel;
    $chambrePhotos = [$photo_hotel, $photo_chambre, $photo_chambre, $photo_chambre]; // tu peux améliorer plus tard
}

elseif (isset($_GET['hotel'])) {
    $hotel = $pdo->query("SELECT * FROM hotels WHERE code_hotel = '".$_GET['hotel']."' AND etat_hotel='actif'")->fetch(PDO::FETCH_ASSOC);
    if (!$hotel) die("Hôtel non trouvé.");

    $chambres = getRoomDetailsFromDB($pdo, $hotel['code_hotel']);

    $photo_principale = !empty($hotel['photo_principale']) ? $upload_dir.'hotels/'.$hotel['photo_principale'] : $default_hotel;
    $photos = array_fill(0, 8, $photo_principale); // temporaire → tu ajouteras une galerie JSON plus tard
}

else {
    $offres = $pdo->query("SELECT * FROM hotels WHERE etat_hotel='actif' ORDER BY RAND() LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>soutra – Réservez votre hôtel en Côte d'Ivoire</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
        :root{--orange:#ff6b35;--bleu:#0a2647;--gris:#f8f9fa;}
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:var(--gris);color:#222;overflow-x:hidden;}

        /* HEADER + MENU PROFIL MODERNE */
        .header{position:fixed;top:0;left:0;right:0;height:80px;background:rgba(10,38,71,0.95);backdrop-filter:blur(12px);display:flex;align-items:center;justify-content:space-between;padding:0 5%;z-index:1000;}
        .logo-header{font-family:'Playfair Display',serif;font-size:38px;color:white;font-weight:900;letter-spacing:3px;text-decoration:none;}

        .user-menu-container {position:relative;display:inline-block;}

        .user-profile-btn-modern {
            background:rgba(255,255,255,0.15);
            border:1.5px solid rgba(255,255,255,0.3);
            border-radius:50px;
            padding:8px 16px;
            display:flex;align-items:center;gap:12px;
            cursor:pointer;transition:.3s;backdrop-filter:blur(10px);
            color:white;font-weight:600;
        }
        .user-profile-btn-modern:hover {background:rgba(255,255,255,0.25);border-color:var(--orange);}
        .user-avatar-modern {width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid white;}

        .dropdown-menu-modern {
            position:absolute;top:100%;right:0;margin-top:12px;
            background:white;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,0.22);
            width:240px;overflow:hidden;opacity:0;visibility:hidden;transform:translateY(-10px);transition:.3s;z-index:999;
        }
        .dropdown-menu-modern.active {opacity:1;visibility:visible;transform:translateY(0);}
        .dropdown-item-modern {display:flex;align-items:center;gap:14px;padding:14px 20px;color:#222;text-decoration:none;font-size:15px;transition:.2s;}
        .dropdown-item-modern:hover {background:#f7f7f7;}
        .dropdown-divider-modern {height:1px;background:#eee;margin:8px 0;}

        /* HERO */
        .hero{background:linear-gradient(rgba(10,38,71,0.45),rgba(10,38,71,0.60)),url('<?=$bg?>') center/cover no-repeat fixed;min-height:100vh;display:flex;align-items:center;justify-content:center;padding-top:80px;}
        .logo{font-family:'Playfair Display',serif;font-size:130px;color:white;text-shadow:0 20px 40px rgba(0,0,0,0.6);animation:float 6s infinite;}
        @keyframes float{0%,100%{transform:translateY(0);}50%{transform:translateY(-20px);}}

        /* BOUTON RECHERCHE */
        .search-box{max-width:960px;width:90%;background:white;border-radius:80px;box-shadow:0 30px 80px rgba(0,0,0,0.25);overflow:hidden;display:flex;margin:50px auto;}
        .search-input{flex:1;padding:28px 40px;font-size:24px;border:none;outline:none;}
        .search-btn{background:var(--orange);color:white;border:none;width:80px;height:80px;border-radius:50%;font-size:28px;cursor:pointer;}

        /* GALERIE & CARTES */
        .main-photo{height:620px;background:center/cover;border-radius:32px;box-shadow:0 40px 100px rgba(0,0,0,0.3);}
        .chambre-card-img{height:200px;background:center/cover no-repeat;}
        .offer-img{height:360px;background:center/cover no-repeat;position:relative;}

        /* RESPONSIVE */
        @media (max-width:768px){
            .logo{font-size:80px;}
            .search-box{border-radius:60px;width:95%;}
            .search-input{padding:20px 30px;font-size:18px;}
            .user-profile-btn-modern span{display:none;}
            .user-profile-btn-modern{padding:10px;}
        }
    </style>
</head>
<body>

<!-- HEADER AVEC MENU PROFIL MODERNE -->
<div class="header">
    <a href="." class="logo-header">soutra</a>

    <div class="user-menu-container">
        <button class="user-profile-btn-modern" onclick="toggleDropdown(event)">
            <div style="display:flex;align-items:center;gap:12px;">
                <?php if ($user_connected && file_exists($user_photo)): ?>
                    <img src="<?=htmlspecialchars($user_photo)?>" alt="Profil" class="user-avatar-modern">
                <?php else: ?>
                    <div class="user-avatar-modern" style="background:var(--orange);color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <span><?=htmlspecialchars($user_name)?></span>
                <i class="fas fa-chevron-down" style="font-size:14px;"></i>
            </div>
        </button>

        <div class="dropdown-menu-modern" id="userDropdown">
            <?php if ($user_connected): ?>
                <a href="profil.php" class="dropdown-item-modern"><i class="fas fa-user-circle"></i> Mon profil</a>
                <a href="mes-reservations.php" class="dropdown-item-modern"><i class="fas fa-calendar-check"></i> Mes réservations</a>
                <div class="dropdown-divider-modern"></div>
                <a href="deconnexion.php" class="dropdown-item-modern" style="color:#d63031;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php" class="dropdown-item-modern" style="font-weight:600;"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
                <a href="inscription.php" class="dropdown-item-modern" style="background:var(--orange);color:white;border-radius:8px;margin:8px 12px;padding:12px;">
                    <i class="fas fa-user-plus"></i> Créer un compte
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($message) echo $message; ?>

<?php if (isset($chambre)): ?>
    <!-- PAGE DÉTAIL CHAMBRE -->
    <div class="chambre-detail-content" style="padding-top:100px;">
        <a href="?hotel=<?= $hotel['code_hotel'] ?>" class="back-btn">Retour</a>
        <h1><?= htmlspecialchars($chambre['type_chambre']) ?></h1>
        <div class="gallery" style="grid-template-columns:1fr;">
            <div class="main-photo" style="background-image:url('<?=$photo_chambre?>')"></div>
        </div>
        <!-- Formulaire réservation inchangé -->
    </div>

<?php elseif (isset($hotel)): ?>
    <!-- PAGE HÔTEL -->
    <div style="padding-top:90px;">
        <div class="gallery">
            <div class="main-photo" style="background-image:url('<?=$photo_principale?>')"></div>
        </div>
        <h1><?= htmlspecialchars($hotel['nom_hotel']) ?></h1>
        <!-- Liste des chambres -->
    </div>

<?php elseif ($isSearching): ?>
    <!-- RÉSULTATS RECHERCHE -->

<?php else: ?>
    <!-- ACCUEIL -->
    <div class="hero">
        <div style="text-align:center;">
            <div class="logo">soutra</div>
            <form method="post" class="search-box">
                <input type="text" name="query" class="search-input" placeholder="Rechercher..." required>
                <button type="submit" name="search" class="search-btn"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
function toggleDropdown(e) {
    e.stopPropagation();
    document.getElementById('userDropdown').classList.toggle('active');
}
document.addEventListener('click', function(e) {
    const menu = document.getElementById('userDropdown');
    if (menu && !menu.parentElement.contains(e.target)) {
        menu.classList.remove('active');
    }
});
</script>
</body>
</html>