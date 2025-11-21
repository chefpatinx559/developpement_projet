<?php
<<<<<<< HEAD
//session_start();
require "database/database.php";

// ==================== FILTRE ====================
$code_hotel = $_GET['code_hotel'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$where = "WHERE 1=1";
$params = [];
if ($code_hotel) {
    $where .= " AND ch.code_hotel = ?";
    $params[] = $code_hotel;
}
$subquery = "";
if ($date_debut && $date_fin) {
    $subquery = "AND ch.code_chambre NOT IN (
        SELECT code_chambre FROM reservations 
        WHERE statut_reservation IN ('occupé', 'réservé') 
        AND date_debut_entre <= ? AND date_fin_entre >= ?
    )";
    $params[] = $date_fin;
    $params[] = $date_debut;
}

// ==================== LISTE ====================
$stmt = $pdo->prepare("
    SELECT ch.*, h.nom_hotel 
    FROM chambres ch 
    LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel 
    $where $subquery 
    ORDER BY ch.nom_chambre
");
$stmt->execute($params);
$chambres = $stmt->fetchAll();

$hotels = $pdo->query("SELECT code_hotel, nom_hotel FROM hotels ORDER BY nom_hotel")->fetchAll();

=======
// session_start();
require "database/database.php";

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
// ==================== UTILISATEUR CONNECTÉ ====================
$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";
<<<<<<< HEAD
?>
=======

// ==================== DÉTECTION DE LA PAGE ====================
$page = $_GET['page'] ?? 'toutes';

// ==================== DONNÉES COMMUNES ====================
$hotels = $pdo->query("SELECT code_hotel, nom_hotel FROM hotels ORDER BY nom_hotel")->fetchAll();
$factures = $pdo->query("SELECT code_facture, titre_facture FROM factures ORDER BY titre_facture")->fetchAll();

// ==================== PAGE 1 : TOUTES LES CHAMBRES TRIÉES PAR PRIX ====================
if ($page === 'toutes') {
    $stmt = $pdo->query("
        SELECT c.*, h.nom_hotel, h.code_hotel
        FROM chambres c
        LEFT JOIN hotels h ON c.code_hotel = h.code_hotel
        ORDER BY h.code_hotel, c.prix_chambre + 0
    ");
    $all_chambres = $stmt->fetchAll();

    // Regroupement par hôtel
    $chambres_par_hotel = [];
    foreach ($all_chambres as $c) {
        $code = $c['code_hotel'] ?? 'INCONNU';
        $nom  = $c['nom_hotel'] ?? 'Hôtel non défini';
        if (!isset($chambres_par_hotel[$code])) {
            $chambres_par_hotel[$code] = ['nom' => $nom, 'chambres' => []];
        }
        $chambres_par_hotel[$code]['chambres'][] = $c;
    }

    // Tri par prix croissant dans chaque hôtel
    foreach ($chambres_par_hotel as &$data) {
        usort($data['chambres'], function($a, $b) {
            $pa = is_numeric($a['prix_chambre']) ? (float)$a['prix_chambre'] : PHP_INT_MAX;
            $pb = is_numeric($b['prix_chambre']) ? (float)$b['prix_chambre'] : PHP_INT_MAX;
            return $pa <=> $pb;
        });
    }
    unset($data);
}

// ==================== PAGE 2 : CHAMBRES LIBRES PAR HÔTEL + PÉRIODE ====================
elseif ($page === 'libres_hotel') {
    $code_hotel  = $_GET['code_hotel'] ?? '';
    $date_debut  = $_GET['date_debut'] ?? '';
    $date_fin    = $_GET['date_fin'] ?? '';

    $where = "WHERE 1=1";
    $params = [];

    if ($code_hotel) {
        $where .= " AND ch.code_hotel = ?";
        $params[] = $code_hotel;
    }

    $subquery = "";
    if ($date_debut && $date_fin) {
        $subquery = " AND ch.code_chambre NOT IN (
            SELECT code_chambre FROM reservations
            WHERE statut_reservation IN ('occupé', 'réservé')
            AND date_debut_entre <= ? AND date_fin_entre >= ?
        )";
        $params[] = $date_fin;
        $params[] = $date_debut;
    }

    $stmt = $pdo->prepare("
        SELECT ch.*, h.nom_hotel, h.code_hotel
        FROM chambres ch
        LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
        $where $subquery
        ORDER BY h.code_hotel, ch.prix_chambre + 0
    ");
    $stmt->execute($params);
    $all_chambres = $stmt->fetchAll();

    // Même regroupement que page 1
    $chambres_par_hotel = [];
    foreach ($all_chambres as $c) {
        $code = $c['code_hotel'] ?? 'INCONNU';
        $nom  = $c['nom_hotel'] ?? 'Hôtel non défini';
        if (!isset($chambres_par_hotel[$code])) {
            $chambres_par_hotel[$code] = ['nom' => $nom, 'chambres' => []];
        }
        $chambres_par_hotel[$code]['chambres'][] = $c;
    }
    foreach ($chambres_par_hotel as &$data) {
        usort($data['chambres'], function($a, $b) {
            $pa = is_numeric($a['prix_chambre']) ? (float)$a['prix_chambre'] : PHP_INT_MAX;
            $pb = is_numeric($b['prix_chambre']) ? (float)$b['prix_chambre'] : PHP_INT_MAX;
            return $pa <=> $pb;
        });
    }
    unset($data);
}

// ==================== PAGE 3 : CHAMBRES LIBRES (TOUS HÔTELS) SUR PÉRIODE ====================
elseif ($page === 'libres_periode') {
    $date_debut = $_GET['date_debut'] ?? '';
    $date_fin   = $_GET['date_fin'] ?? '';

    $subquery = "";
    $params = [];
    if ($date_debut && $date_fin) {
        $subquery = "WHERE ch.code_chambre NOT IN (
            SELECT code_chambre FROM reservations
            WHERE statut_reservation IN ('occupé', 'réservé')
            AND date_debut_entre <= ? AND date_fin_entre >= ?
        )";
        $params = [$date_fin, $date_debut];
    }

    $stmt = $pdo->prepare("
        SELECT ch.*, h.nom_hotel, h.code_hotel
        FROM chambres ch
        LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
        $subquery
        ORDER BY h.code_hotel, ch.prix_chambre + 0
    ");
    $stmt->execute($params);
    $all_chambres = $stmt->fetchAll();

    $chambres_par_hotel = [];
    foreach ($all_chambres as $c) {
        $code = $c['code_hotel'] ?? 'INCONNU';
        $nom  = $c['nom_hotel'] ?? 'Hôtel non défini';
        if (!isset($chambres_par_hotel[$code])) {
            $chambres_par_hotel[$code] = ['nom' => $nom, 'chambres' => []];
        }
        $chambres_par_hotel[$code]['chambres'][] = $c;
    }
    foreach ($chambres_par_hotel as &$data) {
        usort($data['chambres'], function($a, $b) {
            $pa = is_numeric($a['prix_chambre']) ? (float)$a['prix_chambre'] : PHP_INT_MAX;
            $pb = is_numeric($b['prix_chambre']) ? (float)$b['prix_chambre'] : PHP_INT_MAX;
            return $pa <=> $pb;
        });
    }
    unset($data);
}

// ==================== PAGE 4 : CHAMBRES RÉSERVÉES PAR HÔTEL + PÉRIODE ====================
elseif ($page === 'reservees') {
    $code_hotel = $_GET['code_hotel'] ?? '';
    $date_debut = $_GET['date_debut'] ?? '';
    $date_fin   = $_GET['date_fin'] ?? '';

    $where = "WHERE r.statut_reservation = 'réservé'";
    $params = [];

    if ($code_hotel) {
        $where .= " AND ch.code_hotel = ?";
        $params[] = $code_hotel;
    }
    if ($date_debut && $date_fin) {
        $where .= " AND r.date_debut_entre <= ? AND r.date_fin_entre >= ?";
        $params[] = $date_fin;
        $params[] = $date_debut;
    }

    $stmt = $pdo->prepare("
        SELECT ch.*, h.nom_hotel, h.code_hotel, r.numero_reservation
        FROM chambres ch
        JOIN reservations r ON ch.code_chambre = r.code_chambre
        LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
        $where
        ORDER BY h.code_hotel, ch.prix_chambre + 0
    ");
    $stmt->execute($params);
    $all_chambres = $stmt->fetchAll();

    $chambres_par_hotel = [];
    foreach ($all_chambres as $c) {
        $code = $c['code_hotel'] ?? 'INCONNU';
        $nom  = $c['nom_hotel'] ?? 'Hôtel non défini';
        if (!isset($chambres_par_hotel[$code])) {
            $chambres_par_hotel[$code] = ['nom' => $nom, 'chambres' => []];
        }
        $chambres_par_hotel[$code]['chambres'][] = $c;
    }
    foreach ($chambres_par_hotel as &$data) {
        usort($data['chambres'], function($a, $b) {
            $pa = is_numeric($a['prix_chambre']) ? (float)$a['prix_chambre'] : PHP_INT_MAX;
            $pb = is_numeric($b['prix_chambre']) ? (float)$b['prix_chambre'] : PHP_INT_MAX;
            return $pa <=> $pb;
        });
    }
    unset($data);
}

// ==================== PAGE 5 : RÉSERVATIONS PAR FACTURE ====================
elseif ($page === 'facture') {
    $code_facture = $_GET['code_facture'] ?? '';
    $where = "WHERE 1=1";
    $params = [];
    if ($code_facture) {
        $where .= " AND r.code_facture = ?";
        $params[] = $code_facture;
    }

    $stmt = $pdo->prepare("
        SELECT r.*, cl.nom_prenom_client, ch.nom_chambre, h.nom_hotel, f.titre_facture
        FROM reservations r
        LEFT JOIN clients cl ON r.code_client = cl.code_client
        LEFT JOIN chambres ch ON r.code_chambre = ch.code_chambre
        LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
        LEFT JOIN factures f ON r.code_facture = f.code_facture
        $where
        ORDER BY r.date_reservation DESC
    ");
    $stmt->execute($params);
    $reservations = $stmt->fetchAll();
}
?>

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<<<<<<< HEAD
    <title>Soutra+ | Chambres Libres par Hôtel sur Période</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
=======
    <title>Soutra+ | Gestion Chambres & Réservations</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
        .badge-type { font-size: 0.8rem; }
        .hotel-card { border-left: 4px solid #007bff; }
        .price-rank { width: 28px; height: 28px; font-size: 0.75rem; }
        .top-chambre { background-color: #f8f9fa; }
    </style>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>
<<<<<<< HEAD
=======

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
<<<<<<< HEAD
                        <h1>Chambres Libres par Hôtel sur Période</h1>
=======
                        <?php
                        $titres = [
                            'toutes'        => 'Toutes les chambres (triées par prix)',
                            'libres_hotel'  => 'Chambres libres par hôtel sur période',
                            'libres_periode'=> 'Chambres libres sur période (tous hôtels)',
                            'reservees'     => 'Chambres réservées par hôtel sur période',
                            'facture'       => 'Réservations par facture'
                        ];
                        echo '<h1>' . $titres[$page] . '</h1>';
                        ?>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Chambres</li>
                        </ol>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                    </div>
                </div>
            </div>
        </section>
<<<<<<< HEAD
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <form method="get" class="row g-3">
                            <div class="col-md-4">
                                <select name="code_hotel" class="form-control">
                                    <option value="">-- Sélectionner un hôtel --</option>
                                    <?php foreach ($hotels as $h): ?>
                                        <option value="<?= $h['code_hotel'] ?>" <?= $code_hotel == $h['code_hotel'] ? 'selected' : '' ?>><?= htmlspecialchars($h['nom_hotel']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin) ?>">
=======

        <section class="content">
            <div class="container-fluid">

                <!-- FILTRES COMMUNS -->
                <?php if (in_array($page, ['libres_hotel', 'libres_periode', 'reservees'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-filter"></i> Filtres</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <input type="hidden" name="page" value="<?= $page ?>">
                            <?php if (in_array($page, ['libres_hotel', 'reservees'])): ?>
                            <div class="col-md-4">
                                <select name="code_hotel" class="form-control">
                                    <option value="">-- Tous les hôtels --</option>
                                    <?php foreach ($hotels as $h): ?>
                                    <option value="<?= $h['code_hotel'] ?>" <?= ($code_hotel??'') == $h['code_hotel'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($h['nom_hotel']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            <?php if (in_array($page, ['libres_hotel', 'libres_periode', 'reservees'])): ?>
                            <div class="col-md-3">
                                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut??'') ?>">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin??'') ?>">
                            </div>
                            <?php endif; ?>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrer</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($page === 'facture'): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-filter"></i> Filtre par facture</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <input type="hidden" name="page" value="facture">
                            <div class="col-md-6">
                                <select name="code_facture" class="form-control">
                                    <option value="">-- Toutes les factures --</option>
                                    <?php foreach ($factures as $f): ?>
                                    <option value="<?= $f['code_facture'] ?>" <?= $code_facture == $f['code_facture'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['titre_facture']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                            </div>
                        </form>
                    </div>
<<<<<<< HEAD
=======
                </div>
                <?php endif; ?>

                <!-- AFFICHAGE DES CHAMBRES PAR HÔTEL (pages 1 à 4) -->
                <?php if (isset($chambres_par_hotel)): ?>
                    <?php if (empty($chambres_par_hotel)): ?>
                        <div class="alert alert-info text-center">Aucune chambre trouvée selon les critères.</div>
                    <?php else: ?>
                        <?php foreach ($chambres_par_hotel as $code_hotel => $data): ?>
                        <?php $hotel = $data['nom']; $chambres = $data['chambres']; ?>
                        <div class="card hotel-card shadow-sm mb-4">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-hotel"></i> <?= htmlspecialchars($hotel) ?>
                                    <small class="text-light ms-2">(<?= $code_hotel ?>)</small>
                                </h5>
                                <span class="badge bg-light text-dark">
                                    <?= count($chambres) ?> chambre<?= count($chambres)>1?'s':'' ?>
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Code</th>
                                                <th>Nom</th>
                                                <th>Type</th>
                                                <th>Prix</th>
                                                <th>État</th>
                                                <?php if ($page === 'reservees'): ?><th>Réservation</th><?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($chambres as $index => $c): ?>
                                            <?php
                                            $isTop = $index < 3;
                                            $rankBadge = match($index) {
                                                0 => 'bg-warning text-dark',
                                                1 => 'bg-secondary text-white',
                                                2 => 'bg-danger text-white',
                                                default => 'bg-light text-dark'
                                            };
                                            ?>
                                            <tr <?= $isTop ? 'class="top-chambre"' : '' ?>>
                                                <td>
                                                    <span class="badge rounded-pill <?= $rankBadge ?> price-rank d-flex align-items-center justify-content-center">
                                                        <?= $index + 1 ?>
                                                    </span>
                                                </td>
                                                <td><strong><?= htmlspecialchars($c['code_chambre']) ?></strong></td>
                                                <td><?= htmlspecialchars($c['nom_chambre']) ?></td>
                                                <td>
                                                    <span class="badge bg-info text-dark badge-type">
                                                        <?= ucwords(str_replace('chambre ', '', $c['type_chambre'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong class="text-success">
                                                        <?php
                                                        $prix = $c['prix_chambre'];
                                                        echo is_numeric($prix) ? number_format((float)$prix) . ' FCFA' : htmlspecialchars($prix);
                                                        ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <?php
                                                    $etat = $c['etat_chambre'] ?? 'disponible';
                                                    $badge = match($etat) {
                                                        'disponible' => 'success',
                                                        'occupée' => 'danger',
                                                        'réservée' => 'warning',
                                                        'maintenance' => 'secondary',
                                                        default => 'dark'
                                                    };
                                                    ?>
                                                    <span class="badge bg-<?= $badge ?>"><?= ucfirst($etat) ?></span>
                                                </td>
                                                <?php if ($page === 'reservees'): ?>
                                                <td><?= htmlspecialchars($c['numero_reservation'] ?? '—') ?></td>
                                                <?php endif; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- PAGE FACTURE (tableau différent) -->
                <?php if ($page === 'facture'): ?>
                <div class="card">
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
<<<<<<< HEAD
                                        <th>Code</th>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Prix</th>
                                        <th>Hôtel</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($chambres as $ch): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($ch['code_chambre']) ?></td>
                                            <td><?= htmlspecialchars($ch['nom_chambre']) ?></td>
                                            <td><?= ucfirst(str_replace('chambre ', '', $ch['type_chambre'])) ?></td>
                                            <td><?= htmlspecialchars($ch['prix_chambre']) ?> FCFA</td>
                                            <td><?= htmlspecialchars($ch['nom_hotel'] ?? '—') ?></td>
                                        </tr>
=======
                                        <th>Numéro</th>
                                        <th>Date Rés.</th>
                                        <th>Client</th>
                                        <th>Chambre</th>
                                        <th>Hôtel</th>
                                        <th>Statut</th>
                                        <th>Montant</th>
                                        <th>Facture</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['numero_reservation']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                                        <td><?= htmlspecialchars($r['nom_prenom_client'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($r['nom_chambre'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($r['nom_hotel'] ?? '—') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $r['statut_reservation']==='libre'?'success':($r['statut_reservation']==='occupé'?'danger':'warning') ?>">
                                                <?= ucfirst($r['statut_reservation']) ?>
                                            </span>
                                        </td>
                                        <td><?= number_format($r['montant_reservation']) ?> FCFA</td>
                                        <td><?= htmlspecialchars($r['titre_facture'] ?? '—') ?></td>
                                    </tr>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
<<<<<<< HEAD
            </div>
        </section>
    </div>
    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
    </footer>
</div>
=======
                <?php endif; ?>

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
</div>

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>