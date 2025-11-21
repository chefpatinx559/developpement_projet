<?php
<<<<<<< HEAD
//session_start();
require "database/database.php";

// ==================== FILTRE ====================
$code_hotel = $_GET['code_hotel'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$where = "WHERE r.statut_reservation = 'occupé'";
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

// ==================== LISTE ====================
$stmt = $pdo->prepare("
    SELECT ch.*, h.nom_hotel, r.numero_reservation 
    FROM chambres ch 
    JOIN reservations r ON ch.code_chambre = r.code_chambre 
    LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel 
    $where 
    ORDER BY ch.nom_chambre
");
$stmt->execute($params);
$chambres = $stmt->fetchAll();

$hotels = $pdo->query("SELECT code_hotel, nom_hotel FROM hotels ORDER BY nom_hotel")->fetchAll();

// ==================== UTILISATEUR CONNECTÉ ====================
$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";
?>
=======
// session_start();
require "database/database.php";

// ==================== DONNÉES COMMUNES ====================
$user_name = "Jean Dupont";
$user_role  = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";

// Récupération des hôtels pour le filtre
$hotels = $pdo->query("SELECT code_hotel, nom_hotel FROM hotels ORDER BY nom_hotel")->fetchAll(PDO::FETCH_ASSOC);

// Paramètres GET
$tab          = $_GET['tab'] ?? 'occupées'; // occupées | réservées | par-hotel
$date_debut   = $_GET['date_debut'] ?? '';
$date_fin     = $_GET['date_fin'] ?? '';
$code_hotel   = $_GET['code_hotel'] ?? '';

// ==================== REQUÊTES SELON L'ONGLET ====================
$chambres = [];

if ($tab === 'occupées' || $tab === 'réservées' || $tab === 'par-hotel') {
    $statut = ($tab === 'réservées') ? 'réservé' : 'occupé';

    $where  = "WHERE r.statut_reservation = ?";
    $params = [$statut];

    // Filtre par hôtel (uniquement pour l'onglet par-hotel ou si sélectionné)
    if (($tab === 'par-hotel' || $tab === 'occupées') && !empty($code_hotel)) {
        $where .= " AND ch.code_hotel = ?";
        $params[] = $code_hotel;
    }

    // Filtre par période (chevauchement)
    if (!empty($date_debut) && !empty($date_fin)) {
        $where .= " AND r.date_fin_entre >= ? AND r.date_debut_entre <= ?";
        $params[] = $date_debut;
        $params[] = $date_fin;
    }

    $sql = "
        SELECT DISTINCT
            ch.code_chambre, ch.nom_chambre, ch.type_chambre, ch.prix_chambre,
            h.nom_hotel, h.code_hotel,
            r.numero_reservation
        FROM chambres ch
        JOIN reservations r ON ch.code_chambre = r.code_chambre
        LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
        $where
        ORDER BY h.nom_hotel, ch.nom_chambre
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $chambres = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<<<<<<< HEAD
    <title>Soutra+ | Chambres Occupées par Hôtel sur Période</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>
=======
    <title>Soutra+ | État des Chambres</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .nav-tabs .nav-link.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .badge-type { font-size: 0.8rem; }
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
        .hotel-card { border-left: 5px solid #007bff; }
        .price-rank { width: 32px; height: 32px; font-size: 0.85rem; }
        .top-chambre { background-color: #f8f9fa; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php include 'config/dashboard.php'; ?>

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
<<<<<<< HEAD
                        <h1>Chambres Occupées par Hôtel sur Période</h1>
=======
                        <h1><i class="fas fa-bed"></i> État des Chambres</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">État Chambres</li>
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
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Prix</th>
                                        <th>Hôtel</th>
                                        <th>Réservation</th>
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
                                            <td><?= htmlspecialchars($ch['numero_reservation'] ?? '—') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
    </footer>
</div>
=======

        <section class="content">
            <div class="container-fluid">

                <!-- Onglets -->
                <ul class="nav nav-tabs mb-4" id="stateTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'occupées' ? 'active' : '' ?>" href="?tab=occupées&date_debut=<?= htmlspecialchars($date_debut) ?>&date_fin=<?= htmlspecialchars($date_fin) ?>">
                            <i class="fas fa-user-check text-danger"></i> Chambres Occupées
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'réservées' ? 'active' : '' ?>" href="?tab=réservées&date_debut=<?= htmlspecialchars($date_debut) ?>&date_fin=<?= htmlspecialchars($date_fin) ?>">
                            <i class="fas fa-calendar-check text-warning"></i> Chambres Réservées
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'par-hotel' ? 'active' : '' ?>" href="?tab=par-hotel&code_hotel=<?= htmlspecialchars($code_hotel) ?>&date_debut=<?= htmlspecialchars($date_debut) ?>&date_fin=<?= htmlspecialchars($date_fin) ?>">
                            <i class="fas fa-hotel text-primary"></i> Par Hôtel
                        </a>
                    </li>
                </ul>

                <!-- Formulaire de filtre commun -->
                <div class="card mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-filter"></i> Filtres</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3 align-items-end">
                            <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">

                            <?php if ($tab === 'par-hotel'): ?>
                                <div class="col-md-4">
                                    <label class="form-label">Hôtel</label>
                                    <select name="code_hotel" class="form-select">
                                        <option value="">-- Tous les hôtels --</option>
                                        <?php foreach ($hotels as $h): ?>
                                            <option value="<?= $h['code_hotel'] ?>" <?= $code_hotel === $h['code_hotel'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($h['nom_hotel']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="col-md-3">
                                <label class="form-label">Du</label>
                                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Au</label>
                                <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin) ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Filtrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Résultats -->
                <?php if (empty($chambres)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i>
                        Aucune chambre <?= $tab === 'réservées' ? 'réservée' : 'occupée' ?> trouvée avec les critères actuels.
                    </div>
                <?php else: ?>
                    <?php
                    // Regroupement par hôtel
                    $chambres_par_hotel = [];
                    foreach ($chambres as $c) {
                        $code = $c['code_hotel'] ?? 'INCONNU';
                        $nom  = $c['nom_hotel'] ?? 'Hôtel non défini';
                        if (!isset($chambres_par_hotel[$code])) {
                            $chambres_par_hotel[$code] = ['nom' => $nom, 'chambres' => []];
                        }
                        $chambres_par_hotel[$code]['chambres'][] = $c;
                    }
                    ?>

                    <?php foreach ($chambres_par_hotel as $code_hotel => $data): ?>
                        <div class="card hotel-card shadow-sm mb-4">
                            <div class="card-header <?= $tab === 'réservées' ? 'bg-warning' : 'bg-danger' ?> text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-hotel"></i> <?= htmlspecialchars($data['nom']) ?>
                                    <small class="text-light ms-2">(<?= $code_hotel ?>)</small>
                                </h5>
                                <span class="badge bg-light text-dark">
                                    <?= count($data['chambres']) ?> chambre<?= count($data['chambres']) > 1 ? 's' : '' ?>
                                    <?= $tab === 'réservées' ? 'réservée(s)' : 'occupée(s)' ?>
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
                                                <th>Prix/jour</th>
                                                <th>N° Réservation</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['chambres'] as $index => $c): ?>
                                                <?php
                                                $rankBadge = match($index) {
                                                    0 => 'bg-warning text-dark',
                                                    1 => 'bg-secondary text-white',
                                                    2 => 'bg-danger text-white',
                                                    default => 'bg-light text-dark'
                                                };
                                                ?>
                                                <tr <?= $index < 3 ? 'class="top-chambre"' : '' ?>>
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
                                                            <?= is_numeric($c['prix_chambre']) ? number_format((float)$c['prix_chambre']) . ' FCFA' : htmlspecialchars($c['prix_chambre']) ?>
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $tab === 'réservées' ? 'warning' : 'danger' ?>">
                                                            <?= htmlspecialchars($c['numero_reservation']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="mt-3 text-center">
                        <strong class="h4 text-primary">
                            <?= count($chambres) ?> chambre<?= count($chambres) > 1 ? 's' : '' ?>
                            <?= $tab === 'réservées' ? 'réservée(s)' : 'occupée(s)' ?>
                        </strong>
                    </div>
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