<?php
// session_start();
require "database/database.php";

// ==================== DONNÉES COMMUNES ====================
$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";

// Récupération des listes pour les filtres
$hotels   = $pdo->query("SELECT code_hotel, nom_hotel FROM hotels ORDER BY nom_hotel")->fetchAll();
$clients  = $pdo->query("SELECT code_client, nom_prenom_client FROM clients ORDER BY nom_prenom_client")->fetchAll();
$chambres = $pdo->query("SELECT code_chambre, nom_chambre FROM chambres ORDER BY nom_chambre")->fetchAll();

// ==================== PARAMÈTRES GET ====================
$view         = $_GET['view'] ?? 'hotel'; // hotel | client | chambre | top
$code_hotel   = $_GET['code_hotel'] ?? '';
$code_client  = $_GET['code_client'] ?? '';
$code_chambre = $_GET['code_chambre'] ?? '';
$date_debut   = $_GET['date_debut'] ?? '';
$date_fin     = $_GET['date_fin'] ?? '';

// Construction dynamique de la requête
$where  = "WHERE 1=1";
$params = [];

if ($view === 'hotel' && $code_hotel) {
    $where .= " AND ch.code_hotel = ?";
    $params[] = $code_hotel;
}
if ($view === 'client' && $code_client) {
    $where .= " AND r.code_client = ?";
    $params[] = $code_client;
}
if ($view === 'chambre' && $code_chambre) {
    $where .= " AND r.code_chambre = ?";
    $params[] = $code_chambre;
}
if ($date_debut && $date_fin) {
    $where .= " AND r.date_reservation BETWEEN ? AND ?";
    $params[] = $date_debut;
    $params[] = $date_fin;
}

// Ordre selon la vue
$order = match($view) {
    'top'   => "ORDER BY CAST(r.montant_reservation AS DECIMAL(10,2)) ASC",
    default => "ORDER BY r.date_reservation DESC"
};

$sql = "
    SELECT r.*, cl.nom_prenom_client, ch.nom_chambre, h.nom_hotel, h.code_hotel
    FROM reservations r
    LEFT JOIN clients cl   ON r.code_client = cl.code_client
    LEFT JOIN chambres ch  ON r.code_chambre = ch.code_chambre
    LEFT JOIN hotels h     ON ch.code_hotel = h.code_hotel
    $where $order
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all_reservations = $stmt->fetchAll();

// Regroupement par hôtel pour la vue "hotel" et "top"
$reservations_par_hotel = [];
foreach ($all_reservations as $r) {
    $code = $r['code_hotel'] ?? 'INCONNU';
    $nom  = $r['nom_hotel'] ?? 'Hôtel non défini';
    if (!isset($reservations_par_hotel[$code])) {
        $reservations_par_hotel[$code] = [
            'nom' => $nom,
            'reservations' => []
        ];
    }
    $reservations_par_hotel[$code]['reservations'][] = $r;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Réservations</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
        .hotel-card { border-left: 4px solid #007bff; }
        .badge-rank { width: 28px; height: 28px; font-size: 0.75rem; }
        .top-resa { background-color: #f8f9fa; }
        .tab-content { min-height: 500px; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php include 'config/dashboard.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Réservations - Vue détaillée</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Réservations</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <!-- Onglets de navigation -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?= $view==='hotel' ? 'active' : '' ?>" href="?view=hotel">Par Hôtel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view==='client' ? 'active' : '' ?>" href="?view=client">Par Client</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view==='chambre' ? 'active' : '' ?>" href="?view=chambre">Par Chambre</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view==='top' ? 'active' : '' ?>" href="?view=top">Top Montants (croissant)</a>
                    </li>
                </ul>

                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3 align-items-end">
                            <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">

                            <?php if ($view === 'hotel'): ?>
                                <div class="col-md-4">
                                    <label>Hôtel</label>
                                    <select name="code_hotel" class="form-control">
                                        <option value="">Tous les hôtels</option>
                                        <?php foreach ($hotels as $h): ?>
                                            <option value="<?= $h['code_hotel'] ?>" <?= $code_hotel==$h['code_hotel']?'selected':'' ?>>
                                                <?= htmlspecialchars($h['nom_hotel']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <?php if ($view === 'client'): ?>
                                <div class="col-md-4">
                                    <label>Client</label>
                                    <select name="code_client" class="form-control">
                                        <option value="">Tous les clients</option>
                                        <?php foreach ($clients as $cl): ?>
                                            <option value="<?= $cl['code_client'] ?>" <?= $code_client==$cl['code_client']?'selected':'' ?>>
                                                <?= htmlspecialchars($cl['nom_prenom_client']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <?php if ($view === 'chambre'): ?>
                                <div class="col-md-4">
                                    <label>Chambre</label>
                                    <select name="code_chambre" class="form-control">
                                        <option value="">Toutes les chambres</option>
                                        <?php foreach ($chambres as $ch): ?>
                                            <option value="<?= $ch['code_chambre'] ?>" <?= $code_chambre==$ch['code_chambre']?'selected':'' ?>>
                                                <?= htmlspecialchars($ch['nom_chambre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="col-md-3">
                                <label>Date début</label>
                                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Date fin</label>
                                <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Contenu selon la vue -->
                <?php if ($view === 'hotel' || $view === 'top'): ?>
                    <?php if (empty($reservations_par_hotel)): ?>
                        <div class="alert alert-info text-center">Aucune réservation trouvée.</div>
                    <?php else: ?>
                        <?php foreach ($reservations_par_hotel as $code => $data): ?>
                            <div class="card hotel-card shadow-sm mb-4">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-hotel"></i> <?= htmlspecialchars($data['nom']) ?>
                                        <small class="text-light ms-2">(<?= $code ?>)</small>
                                    </h5>
                                    <span class="badge bg-light text-dark">
                                        <?= count($data['reservations']) ?> réservation<?= count($data['reservations'])>1?'s':'' ?>
                                    </span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Numéro</th>
                                                    <th>Date</th>
                                                    <th>Client</th>
                                                    <th>Chambre</th>
                                                    <th>Statut</th>
                                                    <th class="text-end">Montant</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($data['reservations'] as $index => $r): ?>
                                                    <?php
                                                    $isTop = $view==='top' && $index < 3;
                                                    $rankClass = match($index){
                                                        0 => 'bg-warning text-dark',
                                                        1 => 'bg-secondary text-white',
                                                        2 => 'bg-danger text-white',
                                                        default => 'bg-light text-dark'
                                                    };
                                                    $badgeStatut = match($r['statut_reservation']){
                                                        'libre' => 'success',
                                                        'occupé','occupée' => 'danger',
                                                        default => 'warning'
                                                    };
                                                    ?>
                                                    <tr <?= $isTop ? 'class="top-resa"' : '' ?>>
                                                        <td>
                                                            <span class="badge rounded-pill <?= $rankClass ?> badge-rank d-flex align-items-center justify-content-center">
                                                                <?= $index + 1 ?>
                                                            </span>
                                                        </td>
                                                        <td><strong><?= htmlspecialchars($r['numero_reservation']) ?></strong></td>
                                                        <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                                                        <td><?= htmlspecialchars($r['nom_prenom_client'] ?? '—') ?></td>
                                                        <td><?= htmlspecialchars($r['nom_chambre'] ?? '—') ?></td>
                                                        <td><span class="badge bg-<?= $badgeStatut ?>"><?= ucfirst($r['statut_reservation']) ?></span></td>
                                                        <td class="text-end text-success fw-bold">
                                                            <?= number_format((float)$r['montant_reservation']) ?> FCFA
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                <?php else: // vues client ou chambre (liste plate) ?>
                    <?php if (empty($all_reservations)): ?>
                        <div class="alert alert-info text-center">Aucune réservation trouvée.</div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Numéro</th>
                                                <th>Date Rés.</th>
                                                <th>Client</th>
                                                <th>Chambre</th>
                                                <th>Hôtel</th>
                                                <th>Statut</th>
                                                <th class="text-end">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($all_reservations as $r):
                                                $badgeStatut = match($r['statut_reservation']){
                                                    'libre' => 'success',
                                                    'occupé','occupée' => 'danger',
                                                    default => 'warning'
                                                };
                                            ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($r['numero_reservation']) ?></strong></td>
                                                    <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                                                    <td><?= htmlspecialchars($r['nom_prenom_client'] ?? '—') ?></td>
                                                    <td><?= htmlspecialchars($r['nom_chambre'] ?? '—') ?></td>
                                                    <td><?= htmlspecialchars($r['nom_hotel'] ?? '—') ?></td>
                                                    <td><span class="badge bg-<?= $badgeStatut ?>"><?= ucfirst($r['statut_reservation']) ?></span></td>
                                                    <td class="text-end text-success fw-bold">
                                                        <?= number_format((float)$r['montant_reservation']) ?> FCFA
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>