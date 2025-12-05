<?php
session_start(); // OBLIGATOIRE !

require "database/database.php";

// ==================== VÉRIFICATION DE L'UTILISATEUR CONNECTÉ ====================
$user_connected = false;
$user_name = "Mon Compte";
$user_photo = "https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=200&h=200&fit=crop&crop=face";

// Vérifier si l'utilisateur est connecté via la session
if (isset($_SESSION['user_id'])) {
    $user_connected = true;
    // Récupérer les infos utilisateur depuis la session
    $user_name = $_SESSION['user_name'] ?? "Mon Compte";
    $user_photo = $_SESSION['user_photo'] ?? "https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=200&h=200&fit=crop&crop=face";
}

// ==================== FONCTIONS BDD POUR LES CHAMBRES (REAL DATA) ====================
/**
 * Récupère TOUTES les chambres ACTIVES ET DISPONIBLES pour un hôtel donné
 * → Exclut les chambres qui ont une réservation active couvrant la période actuelle ou future
 */
function getRoomDetailsFromDB($pdo, $code_hotel) {
    $stmt = $pdo->prepare("
        SELECT c.* FROM chambres c
        LEFT JOIN reservations r ON c.code_chambre = r.code_chambre 
            AND r.etat_reservation = 'actif' 
            AND r.statut_reservation = 'réservé'
            AND r.date_fin >= CURDATE()
        WHERE c.code_hotel = ? 
          AND c.etat_chambre = 'actif'
          AND r.code_chambre IS NULL  -- Cette ligne magique exclut les chambres réservées !
        ORDER BY c.prix_chambre ASC
    ");
    $stmt->execute([$code_hotel]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère une chambre spécifique — mais seulement si elle est disponible
 */
function getRoomByCodeFromDB($pdo, $code_chambre) {
    $stmt = $pdo->prepare("
        SELECT c.* FROM chambres c
        LEFT JOIN reservations r ON c.code_chambre = r.code_chambre 
            AND r.etat_reservation = 'actif' 
            AND r.statut_reservation = 'réservé'
            AND r.date_fin >= CURDATE()
        WHERE c.code_chambre = ? 
          AND c.etat_chambre = 'actif'
          AND r.code_chambre IS NULL  -- Même protection ici
    ");
    $stmt->execute([$code_chambre]);
    $chambre = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($chambre && (empty($chambre['services']) || !is_array($chambre['services']))) {
        $chambre['services'] = ['WiFi gratuit', 'TV HD'];
    }
    return $chambre ?: false; // retourne false si réservée
}
/**
 * Vérifie la disponibilité d'une chambre pour les dates sélectionnées
 */
function checkRoomAvailability($pdo, $code_chambre, $date_debut, $date_fin) {
    // Vérifier si des réservations existent déjà pour ces dates
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

/**
 * Calcule la durée en jours entre deux dates
 */
function calculateDaysDuration($date_debut, $date_fin) {
    $start = new DateTime($date_debut);
    $end = new DateTime($date_fin);
    $interval = $start->diff($end);
    return $interval->days;
}

// ==================== LISTE DE PHOTOS (Simulées) ====================
$hotelPhotos = [
    'https://images.unsplash.com/photo-1611892441792-ae6af9366e2c?w=1200&q=90',
    'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&q=90',
    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200&q=90',
    'https://images.unsplash.com/photo-1549294413-26f195200cfa?w=1200&q=90',
    'https://images.unsplash.com/photo-1549294413-26f195200cfa?w=1200&q=90',
    'https://images.unsplash.com/photo-1596701062361-8cd7343ad887?w=1200&q=90',
    'https://images.unsplash.com/photo-1582719478252-65e100141e97?w=1200&q=90',
    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200&q=90',
];

$bg = $hotelPhotos[array_rand($hotelPhotos)];
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
    
    // Validation des dates
    $today = date('Y-m-d');
    
    if (empty($code_hotel) || empty($code_chambre) || empty($type_chambre) || empty($date_debut) || empty($date_fin)) {
        $message = "<div class='alert alert-danger'>Erreur : Tous les champs de réservation sont requis.</div>";
    } elseif ($date_debut < $today) {
        $message = "<div class='alert alert-danger'>La date d'arrivée ne peut pas être dans le passé.</div>";
    } elseif ($date_fin <= $date_debut) {
        $message = "<div class='alert alert-danger'>La date de départ doit être après la date d'arrivée.</div>";
    } elseif (!checkRoomAvailability($pdo, $code_chambre, $date_debut, $date_fin)) {
        $message = "<div class='alert alert-danger'>Cette chambre n'est pas disponible pour les dates sélectionnées. Veuillez choisir d'autres dates.</div>";
    } else {
        try {
            // Récupérer les informations de la chambre pour le prix
            $chambre_info = getRoomByCodeFromDB($pdo, $code_chambre);
            if (!$chambre_info) {
                throw new Exception("Chambre non trouvée.");
            }
            
            // Calculer les valeurs nécessaires
            $duree_jours = calculateDaysDuration($date_debut, $date_fin);
            $prix_chambre = $chambre_info['prix_chambre'];
            $montant_reservation = $duree_jours * $prix_chambre;
            
            // Générer un numéro de réservation unique
            $numero_reservation = 'RES' . date('YmdHis') . rand(100, 999);
            
            // CORRECTION : Générer un code client unique pour chaque réservation
            // En attendant que vous ayez un système d'authentification
            $code_client = 'CLI_' . date('YmdHis') . '_' . rand(1000, 9999);
            
            // Générer un code facture unique
            $code_facture = 'FACT_' . date('YmdHis') . '_' . rand(1000, 9999);
            
            // Insérer la réservation avec les bons noms de colonnes
            $stmt = $pdo->prepare("
                INSERT INTO reservations (
                    numero_reservation, 
                    date_reservation, 
                    heure_reservation, 
                    date_debut, 
                    date_fin, 
                    type_reservation, 
                    code_chambre, 
                    code_client, 
                    code_facture, 
                    duree_jours, 
                    prix_chambre, 
                    montant_reservation, 
                    statut_reservation, 
                    observation_reservation, 
                    etat_reservation
                ) VALUES (
                    ?, 
                    CURDATE(), 
                    CURTIME(), 
                    ?, 
                    ?, 
                    ?, 
                    ?, 
                    ?, 
                    ?, 
                    ?, 
                    ?, 
                    ?, 
                    'réservé', 
                    'Réservation effectuée via le site web', 
                    'actif'
                )
            ");
            
            $stmt->execute([
                $numero_reservation,
                $date_debut,
                $date_fin,
                $type_chambre,
                $code_chambre,
                $code_client,
                $code_facture,
                $duree_jours,
                $prix_chambre,
                $montant_reservation
            ]);
            
            $message = "<div class='success-box'>
                <i class='fas fa-check-circle'></i>
                <h2>Réservation confirmée !</h2>
                <p><strong>Numéro de réservation : " . htmlspecialchars($numero_reservation) . "</strong></p>
                <p><strong>Chambre : " . htmlspecialchars($type_chambre) . "</strong></p>
                <p>Du " . htmlspecialchars(date('d/m/Y', strtotime($date_debut))) . " au " . 
                htmlspecialchars(date('d/m/Y', strtotime($date_fin))) . " (" . $duree_jours . " nuits)</p>
                <p><strong>Montant total : " . number_format($montant_reservation) . " FCFA</strong></p>
                <p><strong>Code client : " . htmlspecialchars($code_client) . "</strong> (conservez-le pour vos références)</p>
                <a href='.' class='btn-back'>Retour à l'accueil</a>
            </div>";
            
        } catch(Exception $e) {
            $message = "<div class='alert alert-danger'>Erreur : La réservation n'a pas pu être enregistrée. " . 
                      htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// ==================== RECHERCHE (HOTELS DANS LA BDD) ====================
if (isset($_POST['search']) && !empty(trim($_POST['query'] ?? ''))) {
    $isSearching = true;
    $query = trim($_POST['query']);
    $q = '%' . $query . '%';
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE etat_hotel = 'actif' AND (ville_hotel LIKE ? OR quartier_hotel LIKE ? OR nom_hotel LIKE ?) ORDER BY nom_hotel LIMIT 30");
    $stmt->execute([$q, $q, $q]);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ==================== PAGE DÉTAIL CHAMBRE RÉELLE ====================
elseif (isset($_GET['code_chambre'])) {
    $code_chambre_reel = $_GET['code_chambre'];
    
    $chambre = getRoomByCodeFromDB($pdo, $code_chambre_reel); 
    if (!$chambre) {
    die("<div style='text-align:center;padding:200px;background:#f8f9fa;color:#d63031;font-size:28px;'>
            <i class='fas fa-ban' style='font-size:80px;margin-bottom:20px;display:block;'></i>
            Cette chambre n'est plus disponible.<br><br>
            <a href='.' style='background:var(--orange);color:white;padding:15px 40px;border-radius:50px;text-decoration:none;font-weight:600;'>Retour à l'accueil</a>
         </div>");
}

    if (!$chambre) {
        die("<div style='text-align:center;padding:200px;background:#000;color:#fff;font-size:32px;'>Chambre non trouvée.</div>");
    }

    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE code_hotel = ? AND etat_hotel = 'actif'");
    $stmt->execute([$chambre['code_hotel']]);
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hotel) {
        die("<div style='text-align:center;padding:200px;background:#000;color:#fff;font-size:32px;'>Hôtel lié non trouvé.</div>");
    }
    
    $photos = $hotelPhotos;
    shuffle($photos);
    $chambrePhotos = array_slice($photos, 0, 8);
    $note = number_format(8.1 + mt_rand(0, 18) / 10, 1);
    $avis = rand(200, 2800);

}

// ==================== PAGE DÉTAIL HÔTEL (Affichage des chambres réelles) ====================
elseif (isset($_GET['hotel'])) {
    $code = $_GET['hotel'];
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE code_hotel = ? AND etat_hotel = 'actif'");
    $stmt->execute([$code]);
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hotel) {
        die("<div style='text-align:center;padding:200px;background:#000;color:#fff;font-size:32px;'>Hôtel non trouvé ou désactivé.</div>");
    }

    $chambres = getRoomDetailsFromDB($pdo, $code);

    $photos = $hotelPhotos;
    shuffle($photos);
    $photos = array_slice($photos, 0, 8); // On prend jusqu'à 8 photos
    $note = number_format(8.1 + mt_rand(0, 18) / 10, 1);
    $avis = rand(200, 2800);
}

// ==================== ACCUEIL ====================
else {
    $villes = $pdo->query("SELECT ville_hotel, COUNT(*) as nb FROM hotels WHERE etat_hotel='actif' GROUP BY ville_hotel ORDER BY nb DESC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
    $offres = $pdo->query("SELECT * FROM hotels WHERE etat_hotel='actif' ORDER BY RAND() LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>soutra – 
        <?php if (isset($chambre)): ?>
            <?=htmlspecialchars($chambre['type_chambre'])?> - <?=htmlspecialchars($hotel['nom_hotel'])?>
        <?php elseif (isset($hotel)): ?>
            <?=htmlspecialchars($hotel['nom_hotel'])?>
        <?php else: ?>
            Réservez votre hôtel de rêve
        <?php endif; ?>
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
        :root{--orange:#ff6b35;--bleu:#0a2647;--gris:#f8f9fa;}
        *{margin:0;padding:0;box-sizing:border-box;}
        h1, h2, h3, h4 { word-wrap: break-word; }
        body{font-family:'Inter',sans-serif;background:var(--gris);color:#222;overflow-x:hidden;}
        
        /* Header avec menu déroulant */
        .header{position:fixed;top:0;left:0;right:0;height:90px;background:rgba(10,38,71,0.95);backdrop-filter:blur(12px);display:flex;align-items:center;justify-content:space-between;padding:0 5%;z-index:1000;box-shadow:0 10px 30px rgba(0,0,0,0.2);}
        .logo-header{font-family:'Playfair Display',serif;font-size:42px;color:white;font-weight:900;letter-spacing:4px;text-decoration:none;}
        
        /* Menu déroulant du profil */
        .user-menu-container {
            position: relative;
            display: inline-block;
        }
        
        .user-profile-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            background: transparent;
            border: 2px solid white;
            border-radius: 50px;
            padding: 8px 20px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-profile-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--orange);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            min-width: 250px;
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .dropdown-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-header {
            padding: 20px;
            background: var(--bleu);
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .dropdown-header .user-avatar {
            width: 50px;
            height: 50px;
            border: none;
        }
        
        .user-info h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }
        
        .user-info p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.8;
        }
        
        .dropdown-links {
            padding: 15px 0;
        }
        
        .dropdown-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .dropdown-link:hover {
            background: #f5f5f5;
            color: var(--orange);
        }
        
        .dropdown-link i {
            width: 20px;
            text-align: center;
            font-size: 18px;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 10px 0;
        }
        
        .dropdown-footer {
            padding: 15px 20px;
            background: #f9f9f9;
            border-top: 1px solid #eee;
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 12px;
            background: transparent;
            border: 1px solid #ddd;
            border-radius: 10px;
            color: #d63031;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #ffebee;
            border-color: #d63031;
        }
        
        /* Animation de la flèche */
        .arrow {
            transition: transform 0.3s ease;
        }
        
        .user-profile-btn.active .arrow {
            transform: rotate(180deg);
        }
        
        /* Reste des styles existants */
        .hero{background:linear-gradient(rgba(10,38,71,0.45),rgba(10,38,71,0.60)),url('<?=$bg?>') center no-repeat fixed;background-size: cover; min-height:100vh;display:flex;align-items:center;justify-content:center;padding-top:90px;position:relative;}
        .logo{font-family:'Playfair Display',serif;font-size:140px;color:white;text-shadow:0 20px 40px rgba(0,0,0,0.6);animation:float 6s infinite;}
        @keyframes float{0%,100%{transform:translateY(0);}50%{transform:translateY(-20px);}}
        .search-box{max-width:960px;width:90%;background:white;border-radius:80px;box-shadow:0 30px 80px rgba(0,0,0,0.25);overflow:hidden;display:flex;transition:0.4s;margin:50px auto;}
        .search-box:focus-within{box-shadow:0 40px 100px rgba(255,107,53,0.3);transform:scale(1.02);}
        .search-input{flex:1;padding:28px 40px;font-size:24px;border:none;outline:none;}
        .search-btn{background:var(--orange);color:white;border:none;width:80px;height:80px;border-radius:50%;font-size:28px;cursor:pointer;transition:0.3s;}
        .search-btn:hover{background:#e55a2b;transform:scale(1.1);}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:40px;margin-top:40px;}
        .card,.offer-card{border-radius:32px;overflow:hidden;box-shadow:0 25px 70px rgba(0,0,0,0.15);transition:0.4s;cursor:pointer;background:white;}
        .card:hover,.offer-card:hover{transform:translateY(-20px);box-shadow:0 50px 100px rgba(0,0,0,0.25);}
        .card-img,.offer-img{height:360px;background:center/cover no-repeat;position:relative;}
        .card-overlay{position:absolute;inset:0;background:linear-gradient(transparent 40%,rgba(0,0,0,0.9));display:flex;flex-direction:column;justify-content:end;padding:40px;color:white;}
        .card-overlay h3{font-size:32px;font-weight:700;}
        .offer-info{padding:32px;}
        .offer-info h3{font-size:26px;color:var(--bleu);margin-bottom:8px;}
        .heart{position:absolute;top:20px;right:20px;width:56px;height:56px;background:rgba(255,255,255,0.95);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:26px;transition:0.3s;color:#ccc;}
        .heart:hover{color:var(--orange);}
        .back-btn{position:fixed;top:120px;left:40px;z-index:100;background:rgba(0,0,0,0.6);color:white;width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;transition:0.3s;}
        .back-btn:hover{background:var(--orange);}

        /* GALERIE (Correction pour affichage 2/3 + 1/3) */
        .gallery{
            max-width:1500px;
            margin:40px auto;
            padding:0 5%;
            display:grid;
            grid-template-columns:2fr 1fr; 
            gap:20px;
        }
        .main-photo{
            height:620px; 
            background:center/cover;
            border-radius:32px;
            box-shadow:0 40px 100px rgba(0,0,0,0.3);
        }
        .side-photos{
            display:grid;
            gap:20px;
        }
        .side-photos div{
            height:148px; 
            background:center/cover;
            border-radius:24px;
        }

        .hotel-body, .chambre-body{max-width:1400px;margin:60px auto;padding:0 5%;display:grid;grid-template-columns:1fr 440px;gap:60px;}
        .chambre-body{grid-template-columns:1fr;} 
        .booking-box{background:white;border-radius:32px;padding:40px;box-shadow:0 30px 100px rgba(0,0,0,0.15);position:sticky;top:110px;}
        .price{font-size:52px;font-weight:800;color:var(--orange);text-align:center;margin-bottom:20px;}
        .reserve-btn{width:100%;background:var(--orange);color:white;border:none;padding:22px;font-size:24px;border-radius:16px;cursor:pointer;font-weight:600;transition:0.3s;}
        .reserve-btn:hover{background:#e55a2b;transform:translateY(-4px);}
        .success-box{background:#d4edda;color:#155724;padding:120px 40px;border-radius:32px;text-align:center;max-width:700px;margin:100px auto;box-shadow:0 30px 80px rgba(0,0,0,0.1);}
        .success-box i{font-size:90px;color:#28a745;margin-bottom:20px;display:block;}
        .btn-back{padding:18px 60px;background:var(--orange);color:white;border-radius:50px;text-decoration:none;font-weight:600;font-size:18px;display:inline-block;margin-top:20px;}
        
        /* Styles pour les messages d'alerte */
        .alert {
            padding: 20px 30px;
            border-radius: 16px;
            margin: 30px auto;
            max-width: 800px;
            text-align: center;
            font-size: 18px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* STYLES POUR L'AFFICHAGE EN GRILLE DES CHAMBRES */
        .chambre-list{
            margin-top:50px;
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
            gap: 40px; 
        }
        .chambre-card{ 
            background: white;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 70px rgba(0,0,0,0.15);
            transition: 0.4s;
        }
        .chambre-card:hover{
            transform: translateY(-5px); 
            box-shadow: 0 30px 80px rgba(0,0,0,0.2);
        }
        .chambre-card-img{ 
            height: 200px; 
            background: center/cover no-repeat;
            position: relative;
        }
        .chambre-card-price{
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--orange);
            color: white;
            padding: 10px 18px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .chambre-card-info{
            padding: 24px;
            text-align: left;
        }
        .chambre-card-info h4{
            font-size: 24px;
            color: var(--bleu);
            margin-bottom: 8px;
            font-weight: 700;
        }
        .chambre-card-stars{
            color: var(--orange);
            font-size: 16px;
            margin-bottom: 15px;
        }
        .chambre-card-icon{
            margin-right: 15px;
            font-size: 16px;
            color: #666;
            display: inline-block;
        }
        .chambre-card-description{
            font-size: 16px;
            color: #444;
            line-height: 1.5;
            margin-top: 15px;
        }
        .chambre-card-button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .chambre-card-button-group a{
            flex: 1;
            padding: 12px 0;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
            cursor: pointer; 
        }
        .btn-view-detail{
            background: white;
            color: var(--bleu);
            border: 2px solid var(--bleu);
        }
        .btn-view-detail:hover{
            background: var(--bleu);
            color: white;
        }
        .btn-book-now{
            background: var(--bleu);
            color: white;
            border: 2px solid var(--bleu); 
        }
        .btn-book-now:hover{
            background: var(--orange);
            border-color: var(--orange);
        }

        /* Style pour la page de détail d'une chambre */
        .chambre-detail-content{max-width:1200px;margin:140px auto;padding:0 5%;}
             /* FOOTER */
        footer{background:var(--bleu);color:white;padding:100px 5% 50px;margin-top:150px;}
        .footer-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:50px;}
        .footer-col h3{font-family:'Playfair Display',serif;font-size:32px;margin-bottom:30px;}
        .footer-col ul{list-style:none;}
        .footer-col ul li{margin-bottom:14px;}
        .footer-col ul li a{color:#ccc;text-decoration:none;transition:0.3s;}
        .footer-col ul li a:hover{color:var(--orange);padding-left:8px;}
        .social-links{margin-top:30px;display:flex;gap:16px;}
        .social-links a{width:50px;height:50px;background:rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;transition:0.3s;}
        .social-links a:hover{background:var(--orange);transform:translateY(-5px);}
        .copyright{text-align:center;padding-top:60px;border-top:1px solid rgba(255,255,255,0.1);margin-top:60px;font-size:15px;color:#aaa;}
        
        /* Responsive pour le menu déroulant */
        @media (max-width: 768px) {
            .user-profile-btn span {
                display: none;
            }
            
            .user-profile-btn {
                padding: 8px;
            }
            
            .dropdown-menu {
                position: fixed;
                top: 70px;
                left: 0;
                right: 0;
                margin: 0;
                border-radius: 0 0 16px 16px;
                min-width: auto;
            }
            
            /* Responsive existant */
            .header {
                height: 70px;
                padding: 0 4%;
            }
            .logo-header {
                font-size: 32px;
            }
            .hero {
                min-height: 80vh;
            }
            .logo {
                font-size: 80px;
            }
            .search-box {
                border-radius: 60px;
                margin: 30px auto;
                width: 95%;
            }
            .search-input {
                padding: 20px 30px;
                font-size: 18px;
            }
            .search-btn {
                width: 60px;
                height: 60px;
                font-size: 22px;
            }
            .grid, .chambre-list {
                grid-template-columns: 1fr;
                gap: 30px;
                padding: 0 4%; 
            }
            .offer-card:hover, .card:hover {
                transform: none; 
            }
            .back-btn {
                top: 85px;
                left: 20px;
                width: 50px;
                height: 50px;
                font-size: 24px;
            }
            .gallery {
                grid-template-columns: 1fr; 
                padding: 0 4%;
            }
            .main-photo {
                height: 300px; 
            }
            .side-photos {
                grid-template-columns: repeat(2, 1fr);
            }
            .side-photos div {
                height: 100px;
            }
            .hotel-body, .chambre-detail-content {
                grid-template-columns: 1fr; 
                padding: 0 4%;
                margin-top: 40px;
                gap: 30px;
            }
            .booking-box {
                position: static;
                margin-top: 30px; 
            }
            .price {
                font-size: 42px;
            }
            footer {
                padding: 60px 5% 30px;
                margin-top: 80px;
            }
            .footer-col h3 {
                font-size: 24px;
                margin-bottom: 20px;
            }
        }
        
        /* Adaptation écrans moyens */
        @media (max-width: 1200px) {
            .gallery {
                padding: 0 3%;
                gap: 15px;
            }
            .main-photo {
                height: 500px;
            }
            .side-photos div {
                height: 110px;
            }
            .hotel-body {
                padding: 0 3%;
                grid-template-columns: 1fr 380px;
                gap: 40px;
            }
            .chambre-detail-content {
                margin-top: 110px;
            }
            .chambre-card-info h4 {
                font-size: 20px;
            }
            .chambre-card-price {
                font-size: 16px;
                padding: 8px 14px;
            }
        }

        /* FIX RESPONSIVE PAGE DÉTAIL CHAMBRE */
.chambre-body-responsive {
    grid-template-columns: 1fr !important;
    gap: 40px;
    margin-top: 60px;
}

@media (min-width: 992px) {
    .chambre-body-responsive {
        grid-template-columns: 1fr 420px !important;
    }
}

/* Le formulaire des dates qui prend trop de place sur mobile */
.chambre-body-responsive .booking-box form > div[style*="grid-template-columns"] {
    grid-template-columns: 1fr !important;
}

/* Le bouton qui déborde */
.reserve-btn {
    white-space: nowrap;
}

/* Optionnel : centre mieux le tout sur mobile */
@media (max-width: 768px) {
    .chambre-detail-content {
        padding: 0 5% !important;
    }
    .booking-box {
        padding: 30px 25px;
    }
    .price {
        font-size: 42px;
    }
}
    </style>
</head>
<body>

<div class="header">
    <a href="." class="logo-header">soutra</a>
    <div style="display:flex;align-items:center;gap:20px;">
        <?php if ($user_connected): ?>
            <div class="user-menu-container">
                <button class="user-profile-btn" onclick="toggleDropdown()">
                    <img src="<?= $user_photo ?>" alt="Photo de profil" class="user-avatar">
                    <span><?= htmlspecialchars($user_name) ?></span>
                    <i class="fas fa-chevron-down arrow" style="font-size:14px;"></i>
                </button>
                
                <div class="dropdown-menu" id="userDropdown">
                    <div class="dropdown-header">
                        <img src="<?= $user_photo ?>" alt="Photo de profil" class="user-avatar">
                        <div class="user-info">
                            <h4><?= htmlspecialchars($user_name) ?></h4>
                            <p>Membre depuis 2024</p>
                        </div>
                    </div>
                    
                    <div class="dropdown-links">
                        <a href="profil.php" class="dropdown-link">
                            <i class="fas fa-user-circle"></i>
                            <span>Mon profil</span>
                        </a>
                        
                        <a href="mes-annonces.php" class="dropdown-link">
                            <i class="fas fa-list-alt"></i>
                            <span>Mes annonces</span>
                        </a>
                        
                        <a href="mes-reservations.php" class="dropdown-link">
                            <i class="fas fa-calendar-check"></i>
                            <span>Mes réservations</span>
                        </a>
                        
                        <a href="messages.php" class="dropdown-link">
                            <i class="fas fa-envelope"></i>
                            <span>Messages</span>
                            <span style="margin-left:auto;background:#ff4757;color:white;padding:2px 8px;border-radius:10px;font-size:12px;">3</span>
                        </a>
                        
                        <a href="favoris.php" class="dropdown-link">
                            <i class="fas fa-heart"></i>
                            <span>Favoris</span>
                        </a>
                        
                        <div class="dropdown-divider"></div>
                        
                        <a href="parametres.php" class="dropdown-link">
                            <i class="fas fa-cog"></i>
                            <span>Paramètres</span>
                        </a>
                        
                        <a href="aide.php" class="dropdown-link">
                            <i class="fas fa-question-circle"></i>
                            <span>Aide & Support</span>
                        </a>
                    </div>
                    
                    <div class="dropdown-footer">
                        <button class="logout-btn" onclick="window.location.href='deconnexion.php'">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Déconnexion</span>
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="connexion.php" style="padding:12px 32px;border:2px solid white;border-radius:50px;color:white;text-decoration:none;font-weight:600;transition:0.3s;">Connexion</a>
            <a href="inscription.php" style="padding:12px 32px;background:var(--orange);color:white;border-radius:50px;text-decoration:none;font-weight:600;transition:0.3s;">Inscription</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($message): ?>
    <?= $message ?>

<?php elseif (isset($chambre)): // Page de détail d'une chambre ?>
    <a href="?hotel=<?=htmlspecialchars($hotel['code_hotel'])?>" class="back-btn"><i class="fas fa-arrow-left"></i></a>

    <div class="chambre-detail-content">
        <h1 style="font-size:48px;color:var(--bleu);margin-bottom:10px;"><?=htmlspecialchars($chambre['type_chambre'])?></h1>
        <p style="font-size:22px;color:#555;margin-bottom:40px;">
            Hôtel: <a href="?hotel=<?=htmlspecialchars($hotel['code_hotel'])?>" style="color:var(--orange);text-decoration:none;font-weight:600;"><?=htmlspecialchars($hotel['nom_hotel'])?></a>, <?=htmlspecialchars($hotel['ville_hotel'])?>
        </p>

        <div class="gallery" style="grid-template-columns:1fr;">
            <div class="main-photo" style="height:500px;background-image:url('<?=$chambrePhotos[0]?>')"></div>
        </div>

        <div class="hotel-body chambre-body-responsive">
            <div>
                <h2 style="font-size:32px;margin-bottom:20px;">Détails de la Chambre</h2>
                <p style="font-size:18px;line-height:1.8;color:#444;margin-bottom:30px;">
                    <?=nl2br(htmlspecialchars($chambre['description_chambre']))?>
                </p>
            </div>

            <div class="booking-box">
                <div class="price"><?=number_format($chambre['prix_chambre'])?> FCFA <small style="font-size:19px;color:#666;"></small></div>
                <form method="post">
                    <input type="hidden" name="code_hotel" value="<?=htmlspecialchars($hotel['code_hotel'])?>">
                    <input type="hidden" name="code_chambre" value="<?=htmlspecialchars($chambre['code_chambre'])?>">
                    <input type="hidden" name="type_chambre_reservee" value="<?=htmlspecialchars($chambre['type_chambre'])?>"> 
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin:25px 0;">
                        <div>
                            <label style="display:block;margin-bottom:8px;font-weight:600;">Arrivée</label>
                            <input type="date" name="date_debut" required 
                                   min="<?=date('Y-m-d')?>" 
                                   value="<?=date('Y-m-d', strtotime('+1 day'))?>" 
                                   style="width:100%;padding:16px;border-radius:12px;border:1px solid #ddd;font-size:16px;">
                        </div>
                        <div>
                            <label style="display:block;margin-bottom:8px;font-weight:600;">Départ</label>
                            <input type="date" name="date_fin" required 
                                   min="<?=date('Y-m-d', strtotime('+2 days'))?>" 
                                   value="<?=date('Y-m-d', strtotime('+3 days'))?>" 
                                   style="width:100%;padding:16px;border-radius:12px;border:1px solid #ddd;font-size:16px;">
                        </div>
                    </div>
                    <button type="submit" name="reserver" class="reserve-btn">Réserver cette Chambre</button>
                </form>
            </div>
        </div>
    </div>

<?php elseif (isset($hotel)): // Page de détail d'un hôtel avec galerie corrigée et responsive ?>
    <div style="padding-top: 90px;"></div>
    <a href="." class="back-btn"><i class="fas fa-arrow-left"></i></a>

    <div class="gallery">
        <div class="main-photo" style="background-image:url('<?=$photos[0]?>')"></div>
        
        <div class="side-photos">
            <?php foreach (array_slice($photos, 1, 4) as $p): ?>
                <div style="background-image:url('<?=$p?>')"></div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="hotel-body" style="grid-template-columns:1fr;"> 
        <div>
            <h1><?=htmlspecialchars($hotel['nom_hotel'])?></h1>
            <p style="font-size:19px;color:#555;margin:12px 0;">
                <i class="fas fa-map-marker-alt" style="color:var(--orange);"></i>
                <?=htmlspecialchars($hotel['quartier_hotel'].', '.$hotel['ville_hotel'])?>
            </p>
            <h2 style="font-family:'Playfair Display',serif;font-size:42px;color:var(--bleu);margin-top:60px;border-bottom:3px solid var(--orange);display:inline-block;padding-bottom:10px;">Chambres disponibles</h2>

            <div class="chambre-list">
                <?php 
                if (empty($chambres)): ?>
                    <p style="font-size:20px;color:#666;padding:50px 0;">Aucune chambre n'est disponible pour cet hôtel.</p>
                <?php else: ?>
                    <?php foreach ($chambres as $c): $chambreImg = $hotelPhotos[array_rand($hotelPhotos)]; ?>
                        <div class="chambre-card">
                            <div class="chambre-card-img" style="background-image:url('<?=$chambreImg?>')">
                                <div class="chambre-card-price"><?=number_format($c['prix_chambre'])?> FCFA</div>
                            </div>
                            <div class="chambre-card-info">
                                <h4><?=htmlspecialchars($c['type_chambre'])?></h4>
                                <div class="chambre-card-stars">
                                    <?php for($i=0; $i<5; $i++): ?>
                                       
                                    <?php endfor; ?>
                                </div>
                                
                             
                                <p class="chambre-card-description">
                                    <?=substr(htmlspecialchars($c['description_chambre']), 0, 80)?>...
                                </p>
                                
                                <div class="chambre-card-button-group">
                                    <a href="?code_chambre=<?=htmlspecialchars($c['code_chambre'])?>" class="btn-view-detail">VOIR DÉTAIL</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>

<?php elseif ($isSearching): ?>
    <div style="padding:140px 5% 100px;background:#fff;">
        <h2 style="font-family:'Playfair Display',serif;font-size:52px;color:var(--bleu);text-align:center;margin-bottom:60px;">
            Résultats pour "<?=htmlspecialchars($_POST['query'])?>"
        </h2>
        <?php if (empty($searchResults)): ?>
            <p style="text-align:center;font-size:24px;color:#666;padding:100px;">Aucun hôtel trouvé.</p>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($searchResults as $h): $img = $hotelPhotos[array_rand($hotelPhotos)]; ?>
                    <div class="offer-card" onclick="location.href='?hotel=<?=htmlspecialchars($h['code_hotel'])?>'">
                        <div class="offer-img" style="background-image:url('<?=$img?>')">
                            <div class="heart"><i class="far fa-heart"></i></div>
                        </div>
                        <div class="offer-info">
                            <h3><?=htmlspecialchars($h['nom_hotel'])?></h3>
                            <p style="color:#666;margin:8px 0;"><?=htmlspecialchars($h['ville_hotel'])?></p>
                            <span style="background:#007bff;color:white;padding:8px 16px;border-radius:10px;font-weight:600;"><?=number_format(8.2 + mt_rand(0,16)/10, 1)?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <div class="hero">
        <div style="text-align:center;">
            <div class="logo">soutra</div>
            <form method="post" class="search-box">
                <input type="text" name="query" class="search-input" placeholder="Rechercher..." required autofocus>
                <button type="submit" name="search" class="search-btn"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>


    <div style="padding:140px 5% 100px;background:#fff;max-width:1600px;margin:auto;">
        <h2 style="font-family:'Playfair Display',serif;font-size:56px;color:var(--bleu);text-align:center;margin-bottom:80px;position:relative;">
            Offres exceptionnelles
            <div style="width:140px;height:6px;background:var(--orange);position:absolute;bottom:-20px;left:50%;transform:translateX(-50%);border-radius:3px;"></div>
        </h2>
        <div class="grid">
            <?php foreach($offres as $h): $img = $hotelPhotos[array_rand($hotelPhotos)]; ?>
                <div class="offer-card" onclick="location.href='?hotel=<?=htmlspecialchars($h['code_hotel'])?>'">
                    <div class="offer-img" style="background-image:url('<?=$img?>')">

                    </div>
                    <div class="offer-info">
                        <h3><?=htmlspecialchars($h['nom_hotel'])?></h3>
                        <p style="color:#666;"><?=htmlspecialchars($h['ville_hotel'])?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
<footer>
    <div class="footer-grid">
        <div class="footer-col">
            <h3>soutra</h3>
            <p>Réservez les meilleurs hôtels en un clic. Luxe, confort et simplicité.</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h3>Liens rapides</h3>
            <ul>
                <li><a href="#">À propos</a></li>
                <li><a href="#">Contact</a></li>
                <li><a href="#">Conditions d'utilisation</a></li>
                <li><a href="#">Politique de confidentialité</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Support</h3>
            <ul>
                <li><a href="#">Centre d'aide</a></li>
                <li><a href="#">Nous contacter</a></li>
                <li><a href="#">FAQ</a></li>
                <li><a href="#">Annulation</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Contact</h3>
            <ul>
                <li><a href="#">+225 07 87 43 71 19</a></li>
                <li><a href="#">+225 01 71 83 98 33</a></li>
                <li><a href="#">Soutra.pro@gmail.com</a></li>
                <li><a href="#">Abidjan, Côte d'Ivoire</a></li>
            </ul>
        </div>
    </div>
    <div class="copyright">
        © 2025 soutra. Tous droits réservés. Créé avec ❤️ en Côte d'Ivoire
    </div>
</footer>

<script>
    // Gestion du menu déroulant
    function toggleDropdown() {
        const dropdown = document.getElementById('userDropdown');
        const button = document.querySelector('.user-profile-btn');
        
        dropdown.classList.toggle('active');
        button.classList.toggle('active');
    }
    
    // Fermer le menu déroulant quand on clique ailleurs
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        const button = document.querySelector('.user-profile-btn');
        const isClickInside = dropdown.contains(event.target) || button.contains(event.target);
        
        if (!isClickInside && dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
            button.classList.remove('active');
        }
    });
    
    // Empêcher la fermeture quand on clique dans le menu
    document.getElementById('userDropdown').addEventListener('click', function(event) {
        event.stopPropagation();
    });
</script>
</body>
</html>