<?php
<<<<<<< HEAD
require "database/database.php";

// Inclusion FPDF seulement si export PDF
if (isset($_POST['export']) && $_POST['export'] === 'pdf') {
    require_once 'librairiesfpdf/fpdf/fpdf.php';
}

// ==================== EXPORT EXCEL / CSV / PDF ====================
if (isset($_POST['export']) && in_array($_POST['export'], ['excel', 'csv', 'pdf'])) {
    $stmt = $pdo->query("
        SELECT 
            r.*,
            cl.nom_prenom_client,
            ch.nom_chambre,
            ch.code_chambre,
            h.nom_hotel,
            h.code_hotel
        FROM reservations r
        LEFT JOIN chambres ch ON r.code_chambre = ch.code_chambre
        LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
        LEFT JOIN clients cl ON r.code_client = cl.code_client
        ORDER BY h.code_hotel, r.date_reservation DESC
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nettoyage buffer
    if (ob_get_level()) ob_end_clean();

    // ====================== EXPORT CSV ======================
    if ($_POST['export'] === 'csv') {
        $filename = 'Reservations_' . date('d-m-Y_H-i') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF"; // BOM UTF-8
        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'Hôtel','Code Hôtel','N° Réservation','Date Réservation','Client','Chambre',
            'Début','Fin','Durée (jours)','Prix/jour','Montant','Statut','Observation'
        ], ';');

        foreach ($data as $row) {
            fputcsv($output, [
                $row['nom_hotel'] ?? 'Inconnu',
                $row['code_hotel'] ?? '',
                $row['numero_reservation'],
                date('d/m/Y', strtotime($row['date_reservation'])),
                $row['nom_prenom_client'] ?? '—',
                $row['nom_chambre'] ?? $row['code_chambre'],
                date('d/m/Y', strtotime($row['date_debut'])),
                date('d/m/Y', strtotime($row['date_fin'])),
                $row['duree_jours'],
                number_format($row['prix_chambre']) . ' FCFA',
                number_format($row['montant_reservation']) . ' FCFA',
                ucfirst($row['statut_reservation'] ?? ''),
                $row['observation_reservation'] ?? ''
            ], ';');
        }
        exit;
    }

    // ====================== EXPORT EXCEL ======================
    if ($_POST['export'] === 'excel') {
        $filename = 'Reservations_' . date('d-m-Y_H-i') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        ?>
        <table border="1">
            <tr style="background:#007bff;color:white;font-weight:bold;">
                <th>Hôtel</th><th>Code Hôtel</th><th>N° Réserv.</th><th>Date Réserv.</th>
                <th>Client</th><th>Chambre</th><th>Début</th><th>Fin</th>
                <th>Durée</th><th>Prix/jour</th><th>Montant</th><th>Statut</th>
            </tr>
            <?php foreach ($data as $row): ?>
            <tr align="center">
                <td><?= htmlspecialchars($row['nom_hotel'] ?? 'Inconnu') ?></td>
                <td><?= htmlspecialchars($row['code_hotel'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['numero_reservation']) ?></td>
                <td><?= date('d/m/Y', strtotime($row['date_reservation'])) ?></td>
                <td><?= htmlspecialchars($row['nom_prenom_client'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['nom_chambre'] ?? $row['code_chambre']) ?></td>
                <td><?= date('d/m/Y', strtotime($row['date_debut'])) ?></td>
                <td><?= date('d/m/Y', strtotime($row['date_fin'])) ?></td>
                <td><?= $row['duree_jours'] ?></td>
                <td><?= number_format($row['prix_chambre']) ?> FCFA</td>
                <td><?= number_format($row['montant_reservation']) ?> FCFA</td>
                <td><?= ucfirst($row['statut_reservation'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
        exit;
    }

    // ====================== EXPORT PDF ======================
    if ($_POST['export'] === 'pdf') {
        $pdf = new FPDF('L', 'mm', 'A4'); // Landscape pour plus de colonnes
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetFillColor(0, 123, 255);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 12, 'Liste des Reservations par Hotel', 0, 1, 'C', true);
        $pdf->Ln(5);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, 'Genere le ' . date('d/m/Y à H:i'), 0, 1, 'R');
        $pdf->Ln(5);

        $currentHotel = '';
        foreach ($data as $row) {
            $hotel = ($row['nom_hotel'] ?? 'Hôtel inconnu') . ' (' . ($row['code_hotel'] ?? 'INCONNU') . ')';

            if ($currentHotel !== $hotel) {
                if ($currentHotel !== '') $pdf->Ln(8);
                $currentHotel = $hotel;

                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetFillColor(0, 123, 255);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(0, 10, $hotel, 0, 1, 'L', true);
                $pdf->Ln(3);

                // En-tête tableau
                $pdf->SetFillColor(220, 220, 220);
                $pdf->SetTextColor(0);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(15, 8, 'N°', 1, 0, 'C', true);
                $pdf->Cell(25, 8, 'Réserv.', 1, 0, 'C', true);
                $pdf->Cell(25, 8, 'Date', 1, 0, 'C', true);
                $pdf->Cell(50, 8, 'Client', 1, 0, 'C', true);
                $pdf->Cell(35, 8, 'Chambre', 1, 0, 'C', true);
                $pdf->Cell(25, 8, 'Début', 1, 0, 'C', true);
                $pdf->Cell(25, 8, 'Fin', 1, 0, 'C', true);
                $pdf->Cell(15, 8, 'Jours', 1, 0, 'C', true);
                $pdf->Cell(25, 8, 'Montant', 1, 0, 'C', true);
                $pdf->Cell(25, 8, 'Statut', 1, 1, 'C', true);
            }

            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(15, 8, '', 1, 0, 'C');
            $pdf->Cell(25, 8, $row['numero_reservation'], 1, 0, 'C');
            $pdf->Cell(25, 8, date('d/m/Y', strtotime($row['date_reservation'])), 1, 0, 'C');
            $pdf->Cell(50, 8, substr($row['nom_prenom_client'] ?? '—', 0, 25), 1, 0, 'L');
            $pdf->Cell(35, 8, $row['nom_chambre'] ?? $row['code_chambre'], 1, 0, 'L');
            $pdf->Cell(25, 8, date('d/m', strtotime($row['date_debut'])), 1, 0, 'C');
            $pdf->Cell(25, 8, date('d/m/Y', strtotime($row['date_fin'])), 1, 0, 'C');
            $pdf->Cell(15, 8, $row['duree_jours'], 1, 0, 'C');
            $pdf->Cell(25, 8, number_format($row['montant_reservation']) . ' FCFA', 1, 0, 'C');
            $pdf->Cell(25, 8, ucfirst($row['statut_reservation'] ?? ''), 1, 1, 'C');
        }
        $pdf->Output('D', 'Reservations_' . date('d-m-Y_H-i') . '.pdf');
        exit;
    }
}

// ==================== AFFICHAGE NORMAL ====================
$stmt = $pdo->query(" SELECT 
        r.*,
        cl.nom_prenom_client,
        ch.nom_chambre,
        ch.code_chambre,
        h.nom_hotel,
        h.code_hotel
    FROM reservations r
    LEFT JOIN chambres ch ON r.code_chambre = ch.code_chambre
    LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
    LEFT JOIN clients cl ON r.code_client = cl.code_client
    ORDER BY h.code_hotel, r.date_reservation DESC
");
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regroupement par hôtel
$group = [];
foreach ($all as $r) {
    $code = $r['code_hotel'] ?? 'INCONNU';
    $group[$code]['nom'] = $r['nom_hotel'] ?? 'Hôtel inconnu';
    $group[$code]['list'][] = $r;
}
?>
=======
//session_start();
require_once "database/database.php";

function formatMoney($amount) {
    return number_format(floatval($amount ?? 0), 0, ',', ' ') . ' FCFA';
}

// === GÉNÉRATION AUTO DU NUMÉRO DE RÉSERVATION ===
function genererNumeroReservation($pdo) {
    $count = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
    return 'RES' . ($count + 1);
}

// === SUPPRESSION ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE numero_reservation = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Réservation supprimée avec succès.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }
   
}

// === AJOUT / MODIFICATION ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $numero = trim($_POST['numero_reservation'] ?? '');
    $date_res = $_POST['date_reservation'] ?? '';
    $heure_res = $_POST['heure_reservation'] ?? '';
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';
    $type_res = trim($_POST['type_reservation'] ?? '');
    $code_chambre = $_POST['code_chambre'] ?? '';
    $code_client = $_POST['code_client'] ?? '';
    $code_facture = $_POST['code_facture'] ?? '';
    $duree_jours = (int)($_POST['duree_jours'] ?? 0);
    $prix_chambre = floatval($_POST['prix_chambre'] ?? 0);
    $montant_res = $duree_jours * $prix_chambre;
    $statut_res = $_POST['statut_reservation'] ?? 'réservé';
    $observation = trim($_POST['observation_reservation'] ?? '');
    $etat_res = $_POST['etat_reservation'] ?? 'actif';

    // Si ajout et numéro vide → générer automatiquement
    if ($action === 'add' && empty($numero)) {
        $numero = genererNumeroReservation($pdo);
    }

    try {
        if ($action === 'add') {
            $sql = "INSERT INTO reservations
                    (numero_reservation, date_reservation, heure_reservation, date_debut, date_fin,
                     type_reservation, code_chambre, code_client, code_facture,
                     duree_jours, prix_chambre, montant_reservation, statut_reservation,
                     observation_reservation, etat_reservation)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $pdo->prepare($sql)->execute([
                $numero, $date_res, $heure_res, $date_debut, $date_fin,
                $type_res, $code_chambre, $code_client, $code_facture,
                $duree_jours, $prix_chambre, $montant_res, $statut_res,
                $observation, $etat_res
            ]);
            $_SESSION['message'] = "Réservation ajoutée avec succès. Numéro : <strong>$numero</strong>";
        }
        if ($action === 'update') {
            $sql = "UPDATE reservations SET
                    date_reservation=?, heure_reservation=?, date_debut=?, date_fin=?,
                    type_reservation=?, code_chambre=?, code_client=?, code_facture=?,
                    duree_jours=?, prix_chambre=?, montant_reservation=?, statut_reservation=?,
                    observation_reservation=?, etat_reservation=?
                    WHERE numero_reservation=?";
            $pdo->prepare($sql)->execute([
                $date_res, $heure_res, $date_debut, $date_fin,
                $type_res, $code_chambre, $code_client, $code_facture,
                $duree_jours, $prix_chambre, $montant_res, $statut_res,
                $observation, $etat_res, $numero
            ]);
            $_SESSION['message'] = "Réservation modifiée avec succès.";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $_SESSION['message'] = "Erreur : Ce numéro de réservation existe déjà !";
        } else {
            $_SESSION['message'] = "Erreur : " . $e->getMessage();
        }
    }
   
}

// === CHARGEMENT DONNÉES ===
$reservations = $pdo->query("
    SELECT r.*, cl.nom_prenom_client, ch.nom_chambre, h.nom_hotel, f.titre_facture
    FROM reservations r
    LEFT JOIN clients cl ON r.code_client = cl.code_client
    LEFT JOIN chambres ch ON r.code_chambre = ch.code_chambre
    LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
    LEFT JOIN factures f ON r.code_facture = f.code_facture
    ORDER BY r.date_reservation DESC
")->fetchAll();

$clients = $pdo->query("SELECT code_client, nom_prenom_client FROM clients ORDER BY nom_prenom_client")->fetchAll();
$factures = $pdo->query("SELECT code_facture, titre_facture FROM factures ORDER BY titre_facture")->fetchAll();

$message = $_SESSION['message'] ?? '';
$alert_type = str_starts_with($message, 'Erreur') ? 'danger' : 'success';
if ($message) unset($_SESSION['message']);
?>

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<<<<<<< HEAD
    <title>Hotelio | Réservations par Hôtel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css">
=======
    <title>Soutra+ | Réservations</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    <style>
        .badge-reservé { background:#ffc107; color:#000; }
        .badge-occupé { background:#dc3545; color:#fff; }
        .badge-libre { background:#28a745; color:#fff; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>
<<<<<<< HEAD

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="mb-0">Réservations par Hôtel</h1>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <!-- Boutons d'export -->
                <form method="post" class="d-flex justify-content-end mb-4 gap-2 flex-wrap">
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

                <!-- Liste groupée par hôtel -->
                <?php foreach ($group as $code => $h): ?>
                <div class="card mb-4 shadow-sm border-start border-primary border-5">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= htmlspecialchars($h['nom']) ?> <small>(<?= $code ?>)</small></h5>
                        <span class="badge bg-light text-dark"><?= count($h['list']) ?> réservation<?= count($h['list']) > 1 ? 's' : '' ?></span>
=======
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-6"><h1>Gestion des Réservations</h1></div>
                    <div class="col-sm-6 text-end">
                        <button class="btn btn-primary btn-lg" id="addBtn">Nouvelle réservation</button>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Liste des réservations (<?= count($reservations) ?>)</h3>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
<<<<<<< HEAD
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>N° Réserv.</th>
=======
                                <thead class="table-dark">
                                    <tr>
                                        <th>N°</th>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Chambre</th>
                                        <th>Période</th>
                                        <th>Durée</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
<<<<<<< HEAD
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($h['list'] as $i => $r): ?>
                                    <tr>
                                        <td><span class="badge rounded-pill <?= $i==0?'bg-warning':($i==1?'bg-secondary':'bg-dark') ?> text-white"><?= $i+1 ?></span></td>
                                        <td><strong><?= htmlspecialchars($r['numero_reservation']) ?></strong></td>
                                        <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                                        <td><?= htmlspecialchars($r['nom_prenom_client'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($r['nom_chambre'] ?? $r['code_chambre']) ?></td>
                                        <td><?= date('d/m', strtotime($r['date_debut'])) ?> → <?= date('d/m/Y', strtotime($r['date_fin'])) ?></td>
                                        <td><span class="badge bg-info"><?= $r['duree_jours'] ?> j</span></td>
                                        <td class="text-success fw-bold"><?= number_format($r['montant_reservation']) ?> FCFA</td>
                                        <td>
                                            <span class="badge <?= $r['statut_reservation']==='libre' ? 'bg-success' : ($r['statut_reservation']==='occupé' ? 'bg-danger' : 'bg-warning') ?>">
                                                <?= ucfirst($r['statut_reservation'] ?? '') ?>
                                            </span>
                                        </td>
=======
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $r): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($r['numero_reservation']) ?></strong></td>
                                        <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                                        <td><?= htmlspecialchars($r['nom_prenom_client'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($r['nom_chambre'] ?? '—') ?></td>
                                        <td><?= date('d/m', strtotime($r['date_debut'])) ?> → <?= date('d/m/Y', strtotime($r['date_fin'])) ?></td>
                                        <td><span class="badge bg-info"><?= $r['duree_jours'] ?> j</span></td>
                                        <td class="text-success fw-bold"><?= formatMoney($r['montant_reservation']) ?></td>
                                        <td>
                                            <span class="badge <?= $r['statut_reservation']==='libre'?'bg-success':($r['statut_reservation']==='occupé'?'bg-danger':'bg-warning') ?>">
                                                <?= ucfirst($r['statut_reservation']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-warning btn-sm edit-btn"
                                                data-bs-numero="<?= htmlspecialchars($r['numero_reservation']) ?>"
                                                data-bs-date_res="<?= $r['date_reservation'] ?>"
                                                data-bs-heure_res="<?= $r['heure_reservation'] ?>"
                                                data-bs-date_debut="<?= $r['date_debut'] ?>"
                                                data-bs-date_fin="<?= $r['date_fin'] ?>"
                                                data-bs-type_res="<?= htmlspecialchars($r['type_reservation']) ?>"
                                                data-bs-code_chambre="<?= $r['code_chambre'] ?>"
                                                data-bs-code_client="<?= $r['code_client'] ?>"
                                                data-bs-code_facture="<?= $r['code_facture'] ?>"
                                                data-bs-duree="<?= $r['duree_jours'] ?>"
                                                data-bs-prix="<?= $r['prix_chambre'] ?>"
                                                data-bs-statut="<?= $r['statut_reservation'] ?>"
                                                data-bs-etat="<?= $r['etat_reservation'] ?>"
                                                data-bs-obs="<?= htmlspecialchars($r['observation_reservation']) ?>">
                                                Modifier
                                            </button>
                                            <a href="?delete=<?= urlencode($r['numero_reservation']) ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Supprimer cette réservation ?');">Supprimer</a>


                                               <a href="imprimer_recu_reservation?numero=<?= urlencode($r['numero_reservation']) ?>" class="btn btn-success btn-sm" target="_blank">
                                                    Reçu
                                                </a>
                                        </td>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
<<<<<<< HEAD
                <?php endforeach; ?>

                <?php if (empty($group)): ?>
                <div class="alert alert-info text-center">
                    Aucune réservation enregistrée pour le moment.
                </div>
                <?php endif; ?>

=======
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
            </div>
        </section>
    </div>
</div>
<<<<<<< HEAD
=======

<!-- MODAL -->
<div class="modal fade" id="reservationModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Nouvelle réservation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="reservationForm" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>N° Réservation <span class="text-muted"></span></label>
                            <input type="text" name="numero_reservation" id="numero_reservation" class="form-control" readonly placeholder="Ex: RES49">
                        </div>
                        <div class="col-md-4"><label>Date réservation</label><input type="date" name="date_reservation" id="date_reservation" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                        <div class="col-md-4"><label>Heure</label><input type="time" name="heure_reservation" id="heure_reservation" class="form-control" value="<?= date('H:i') ?>" required></div>
                       
                        <div class="col-md-6"><label>Date début *</label><input type="date" name="date_debut" id="date_debut" class="form-control" required></div>
                        <div class="col-md-6"><label>Date fin *</label><input type="date" name="date_fin" id="date_fin" class="form-control" required></div>
                       
                        <div class="col-md-6"><label>Client *</label>
                            <select name="code_client" id="code_client" class="form-select" required>
                                <option value="">-- Client --</option>
                                <?php foreach ($clients as $c): ?>
                                    <option value="<?= $c['code_client'] ?>"><?= htmlspecialchars($c['nom_prenom_client']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                       
                        <div class="col-md-6"><label>Chambre *</label>
                            <select name="code_chambre" id="code_chambre" class="form-select" required>
                                <option value="">-- Sélectionner une chambre --</option>
                                <?php
                                $stmt = $pdo->query("SELECT code_chambre, nom_chambre, prix_chambre FROM chambres ORDER BY nom_chambre");
                                while ($ch = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?= $ch['code_chambre'] ?>" data-prix="<?= $ch['prix_chambre'] ?>">
                                        <?= htmlspecialchars($ch['nom_chambre']) ?> (<?= formatMoney($ch['prix_chambre']) ?>/jour)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4"><label>Prix journalier</label><input type="number" step="1000" name="prix_chambre" id="prix_chambre" class="form-control" readonly></div>
                        <div class="col-md-4"><label>Durée (jours)</label><input type="number" min="1" name="duree_jours" id="duree_jours" class="form-control bg-light" readonly></div>
                        <div class="col-md-4"><label>Montant total</label><input type="text" id="montant_reservation" class="form-control bg-light" readonly></div>
                        <div class="col-md-6"><label>Facture</label>
                            <select name="code_facture" id="code_facture" class="form-select">
                                <option value="">Aucune</option>
                                <?php foreach ($factures as $f): ?>
                                    <option value="<?= $f['code_facture'] ?>"><?= htmlspecialchars($f['titre_facture']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6"><label>Type réservation</label><input type="text" name="type_reservation" id="type_reservation" class="form-control"></div>
                        <div class="col-md-6"><label>Statut</label>
                            <select name="statut_reservation" id="statut_reservation" class="form-select">
                                <option value="réservé">Réservé</option>
                                <option value="occupé">Occupé</option>
                                <option value="libre">Libre</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label>État</label>
                            <select name="etat_reservation" id="etat_reservation" class="form-select">
                                <option value="actif">Actif</option>
                                <option value="en cours">En cours</option>
                                <option value="inactif">Inactif</option>
                            </select>
                        </div>
                        <div class="col-12"><label>Observation</label><textarea name="observation_reservation" id="observation_reservation" class="form-control" rows="3"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-lg">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    const modal = new bootstrap.Modal('#reservationModal');

    function updateCalculs() {
        const debut = document.getElementById('date_debut').value;
        const fin = document.getElementById('date_fin').value;
        const prixInput = document.getElementById('prix_chambre');
        const dureeInput = document.getElementById('duree_jours');
        const montantInput = document.getElementById('montant_reservation');
        let jours = 0;
        if (debut && fin) {
            const d1 = new Date(debut);
            const d2 = new Date(fin);
            jours = Math.round((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
            if (jours < 1) jours = 1;
        }
        dureeInput.value = jours;
        const prix = parseFloat(prixInput.value) || 0;
        const total = prix * jours;
        montantInput.value = new Intl.NumberFormat('fr-FR').format(total) + ' FCFA';
    }

    document.getElementById('code_chambre').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const prix = selected ? selected.dataset.prix : 0;
        document.getElementById('prix_chambre').value = prix;
        updateCalculs();
    });

    document.getElementById('date_debut').addEventListener('change', updateCalculs);
    document.getElementById('date_fin').addEventListener('change', updateCalculs);

    // Bouton Nouvelle réservation → génère le numéro auto
    document.getElementById('addBtn').onclick = () => {
        document.getElementById('reservationForm').reset();
        document.getElementById('modalTitle').textContent = 'Nouvelle réservation';
        document.getElementById('formAction').value = 'add';
        document.getElementById('numero_reservation').readOnly = true;
        document.getElementById('numero_reservation').value = '<?= genererNumeroReservation($pdo) ?>';
        document.getElementById('date_reservation').value = '<?= date('Y-m-d') ?>';
        document.getElementById('heure_reservation').value = '<?= date('H:i') ?>';
        document.getElementById('prix_chambre').value = '';
        document.getElementById('duree_jours').value = '';
        document.getElementById('montant_reservation').value = '';
        modal.show();
    };

    // Bouton Modifier
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.onclick = () => {
            document.getElementById('modalTitle').textContent = 'Modifier la réservation';
            document.getElementById('formAction').value = 'update';
            document.getElementById('numero_reservation').value = btn.dataset.bsNumero;
            document.getElementById('numero_reservation').readOnly = true;
            document.getElementById('date_reservation').value = btn.dataset.bsDateRes;
            document.getElementById('heure_reservation').value = btn.dataset.bsHeureRes;
            document.getElementById('date_debut').value = btn.dataset.bsDateDebut;
            document.getElementById('date_fin').value = btn.dataset.bsDateFin;
            document.getElementById('type_reservation').value = btn.dataset.bsTypeRes;
            document.getElementById('code_chambre').value = btn.dataset.bsCodeChambre;
            document.getElementById('code_client').value = btn.dataset.bsCodeClient;
            document.getElementById('code_facture').value = btn.dataset.bsCodeFacture;
            document.getElementById('duree_jours').value = btn.dataset.bsDuree;
            document.getElementById('prix_chambre').value = btn.dataset.bsPrix;
            document.getElementById('statut_reservation').value = btn.dataset.bsStatut;
            document.getElementById('etat_reservation').value = btn.dataset.bsEtat;
            document.getElementById('observation_reservation').value = btn.dataset.bsObs;
            updateCalculs();
            modal.show();
        };
    });
</script>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
</body>
</html>