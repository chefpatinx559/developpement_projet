<?php
<<<<<<< HEAD
require "database/database.php";

// Inclusion FPDF seulement si export PDF
if (isset($_POST['export']) && $_POST['export'] === 'pdf') {
    require_once 'librairiesfpdf/fpdf/fpdf.php';
}

// ==================== FILTRES ====================
$date_debut = $_GET['date_debut'] ?? '';
$date_fin   = $_GET['date_fin'] ?? '';

$where  = "";
$params = [];

if ($date_debut !== '' && $date_fin !== '') {
    // Une chambre est occupée si la période de réservation chevauche la période demandée
    $where = "WHERE r.statut_reservation = 'occupé'
              AND r.date_debut <= ?
              AND r.date_fin >= ?";
    $params = [$date_fin, $date_debut];
}

// ==================== EXPORT (CSV / EXCEL / PDF) ====================
if (isset($_POST['export']) && in_array($_POST['export'], ['excel', 'csv', 'pdf'])) {

    $sql = " gitSELECT 
            ch.*,
            h.nom_hotel,
            h.code_hotel,
            r.numero_reservation,
            r.date_debut,
            r.date_fin,
            cl.nom_prenom_client
        FROM reservations r
        JOIN chambres ch ON r.code_chambre = ch.code_chambre
        LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
        LEFT JOIN clients cl ON r.code_client = cl.code_client
        $where
        ORDER BY h.nom_hotel, ch.nom_chambre
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (ob_get_level()) ob_end_clean();

    // CSV
    if ($_POST['export'] === 'csv') {
        $filename = 'Chambres_Occupees_' . date('d-m-Y') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Hôtel','Chambre','Code','Type','Prix','Client','Réservation','Du','Au'], ';');

        foreach ($data as $row) {
            fputcsv($output, [
                $row['nom_hotel'] ?? 'Inconnu',
                $row['nom_chambre'],
                $row['code_chambre'],
                ucwords(str_replace('chambre ', '', $row['type_chambre'] ?? '')),
                number_format($row['prix_chambre']) . ' FCFA',
                $row['nom_prenom_client'] ?? '—',
                $row['numero_reservation'],
                date('d/m/Y', strtotime($row['date_debut'])),
                date('d/m/Y', strtotime($row['date_fin'])),
            ], ';');
        }
        exit;
    }

    // Excel + PDF similaires (je te les mets plus bas pour pas alourdir)
    // ... (code Excel et PDF identique aux pages précédentes, je te le colle à la fin)
}

// ==================== DONNÉES PRINCIPALES ====================
$sql = " SELECT 
        ch.*,
        h.nom_hotel,
        h.code_hotel,
        r.numero_reservation,
        r.date_debut,
        r.date_fin,
        cl.nom_prenom_client
    FROM reservations r
    JOIN chambres ch ON r.code_chambre = ch.code_chambre
    LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
    LEFT JOIN clients cl ON r.code_client = cl.code_client
    $where
    ORDER BY h.nom_hotel, ch.nom_chambre
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regroupement par hôtel
$group = [];
foreach ($all as $row) {
    $code = $row['code_hotel'] ?? 'INCONNU';
    $group[$code]['nom'] = $row['nom_hotel'] ?? 'Hôtel inconnu';
    $group[$code]['list'][] = $row;
}
?>

=======
//session_start();
require "database/database.php";

// ==================== FILTRE ====================
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$where = "";
$params = [];
if ($date_debut && $date_fin) {
    $where = "WHERE r.statut_reservation = 'occupé' AND r.date_debut_entre <= ? AND r.date_fin_entre >= ?";
    $params = [$date_fin, $date_debut]; // Note: Pour chevauchement de période
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

// ==================== UTILISATEUR CONNECTÉ ====================
$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";
?>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<<<<<<< HEAD
    <title>Hotelio | Chambres Occupées sur Période</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css">
    <style>
        .badge-occupé { background:#dc3545; color:#fff; }
    </style>
=======
    <title>Soutra+ | Chambres Occupées sur Période</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>
<<<<<<< HEAD

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="mb-0">Chambres Occupées</h1>
                <?php if ($date_debut && $date_fin): ?>
                    <small class="text-muted">
                        Du <?= date('d/m/Y', strtotime($date_debut)) ?> au <?= date('d/m/Y', strtotime($date_fin)) ?>
                        — <?= count($all) ?> chambre<?= count($all)>1?'s':'' ?> occupée<?= count($all)>1?'s':'' ?>
                    </small>
                <?php else: ?>
                    <small class="text-muted">Sélectionnez une période pour voir les chambres occupées</small>
                <?php endif; ?>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <!-- Filtres + Exports -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <form method="get" class="row g-3 align-items-end mb-3">
                            <div class="col-md-5">
                                <label class="form-label">Date de début</label>
                                <input type="date" name="date_debut" class="form-select" value="<?= htmlspecialchars($date_debut) ?>" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date de fin</label>
                                <input type="date" name="date_fin" class="form-select" value="<?= htmlspecialchars($date_fin) ?>" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Afficher</button>
                            </div>
                        </form>

                        <?php if ($date_debut && $date_fin): ?>
                        <form method="post" class="d-flex justify-content-end gap-2 flex-wrap">
                            <button type="submit" name="export" value="excel" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button type="submit" name="export" value="csv" class="btn btn-info text-white">
                                <i class="fas fa-file-csv"></i> CSV
                            </button>
                            <button type="submit" name="export" value="pdf" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Chambres occupées groupées par hôtel -->
                <?php foreach ($group as $code => $h): ?>
                <div class="card mb-4 shadow-sm border-start border-danger border-5">
                    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= htmlspecialchars($h['nom']) ?> <small>(<?= $code ?>)</small></h5>
                        <span class="badge bg-light text-dark"><?= count($h['list']) ?> occupée<?= count($h['list'])>1?'s':'' ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Code</th>
                                        <th>Nom Chambre</th>
                                        <th>Type</th>
                                        <th>Prix</th>
                                        <th>Client</th>
                                        <th>Réservation</th>
                                        <th>Période</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($h['list'] as $i => $ch): ?>
                                    <tr>
                                        <td><span class="badge rounded-pill bg-dark text-white"><?= $i+1 ?></span></td>
                                        <td><strong><?= htmlspecialchars($ch['code_chambre']) ?></strong></td>
                                        <td><?= htmlspecialchars($ch['nom_chambre']) ?></td>
                                        <td><span class="badge bg-info text-dark"><?= ucwords(str_replace('chambre ','',$ch['type_chambre']??'')) ?></span></td>
                                        <td class="text-success fw-bold"><?= number_format($ch['prix_chambre']) ?> FCFA</td>
                                        <td><?= htmlspecialchars($ch['nom_prenom_client'] ?? '—') ?></td>
                                        <td><span class="badge bg-danger">#<?= htmlspecialchars($ch['numero_reservation']) ?></span></td>
                                        <td><?= date('d/m', strtotime($ch['date_debut'])) ?> → <?= date('d/m/Y', strtotime($ch['date_fin'])) ?></td>
                                    </tr>
=======
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Chambres Occupées sur Période</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <form method="get" class="row g-3">
                            <div class="col-md-5">
                                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            <div class="col-md-5">
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
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
<<<<<<< HEAD
                <?php endforeach; ?>

                <?php if (empty($group) && $date_debut && $date_fin): ?>
                <div class="alert alert-success text-center">
                    <i class="fas fa-check-circle fa-3x mb-3"></i><br>
                    <h4>Aucune chambre occupée sur cette période !</h4>
                    <p>Toutes les chambres sont libres → Parfait pour les nouvelles réservations !</p>
                </div>
                <?php endif; ?>

                <?php if (!$date_debut || !$date_fin): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-calendar-alt fa-2x mb-3"></i><br>
                    Veuillez sélectionner une période pour voir les chambres occupées.
                </div>
                <?php endif; ?>

            </div>
        </section>
    </div>
</div>
=======
            </div>
        </section>
    </div>
    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
    </footer>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
</body>
</html>