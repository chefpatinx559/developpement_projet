<?php
<<<<<<< HEAD
require "database/database.php";

// Inclusion FPDF seulement si on exporte en PDF
if (isset($_POST['export']) && $_POST['export'] === 'pdf') {
    require_once 'librairiesfpdf/fpdf/fpdf.php'; 
}

// ==================== EXPORT EXCEL / CSV / PDF ====================
if (isset($_POST['export']) && in_array($_POST['export'], ['excel', 'csv', 'pdf'])) {

    $stmt = $pdo->query("
        SELECT h.*, 
               COUNT(c.code_chambre) as nb_chambres
        FROM hotels h
        LEFT JOIN chambres c ON h.code_hotel = c.code_hotel
        GROUP BY h.code_hotel
        ORDER BY h.nom_hotel ASC
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (ob_get_level()) ob_end_clean();

    // ====================== CSV ======================
    if ($_POST['export'] === 'csv') {
        $filename = 'Hotels_' . date('d-m-Y_H-i') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Nom Hôtel','Code','Type','Ville','Pays','Quartier','Adresse','Téléphone','Email','Nb Chambres','État'], ';');

        foreach ($data as $row) {
            fputcsv($output, [
                $row['nom_hotel'],
                $row['code_hotel'],
                $row['type_hotel'],
                $row['ville_hotel'],
                $row['pays_hotel'],
                $row['quartier_hotel'],
                $row['adresse_hotel'],
                $row['telephone_hotel'],
                $row['email_hotel'] ?? '',
                $row['nb_chambres'],
                ucfirst($row['etat_hotel'] ?? 'inconnu')
            ], ';');
        }
        exit;
    }

    // ====================== EXCEL ======================
    if ($_POST['export'] === 'excel') {
        $filename = 'Hotels_' . date('d-m-Y_H-i') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        ?>
        <table border="1">
            <tr style="background:#007bff;color:white;font-weight:bold;">
                <th>Nom Hôtel</th><th>Code</th><th>Type</th><th>Ville</th><th>Pays</th><th>Quartier</th>
                <th>Adresse</th><th>Téléphone</th><th>Email</th><th>Chambres</th><th>État</th>
            </tr>
            <?php foreach ($data as $row): ?>
            <tr align="center">
                <td><?= htmlspecialchars($row['nom_hotel']) ?></td>
                <td><?= htmlspecialchars($row['code_hotel']) ?></td>
                <td><?= htmlspecialchars($row['type_hotel']) ?></td>
                <td><?= htmlspecialchars($row['ville_hotel']) ?></td>
                <td><?= htmlspecialchars($row['pays_hotel']) ?></td>
                <td><?= htmlspecialchars($row['quartier_hotel']) ?></td>
                <td><?= htmlspecialchars($row['adresse_hotel']) ?></td>
                <td><?= htmlspecialchars($row['telephone_hotel']) ?></td>
                <td><?= htmlspecialchars($row['email_hotel'] ?? '') ?></td>
                <td><?= $row['nb_chambres'] ?></td>
                <td><?= ucfirst($row['etat_hotel'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
        exit;
    }

    // ====================== PDF ======================
    if ($_POST['export'] === 'pdf') {
        $pdf = new FPDF('L', 'mm', 'A3');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetFillColor(0,123,255);
        $pdf->SetTextColor(255,255,255);
        $pdf->Cell(0,15, ('Liste des Hotels'), 0,1,'C',true);
        $pdf->Ln(5);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(0,8, ('Genere le ') . date('d/m/Y à H:i'),0,1,'R');
        $pdf->Ln(8);

        // En-tête
        $pdf->SetFont('Arial','B',10);
        $pdf->SetFillColor(230,230,230);
        $pdf->Cell(10,10,'N°',1,0,'C',true);
        $pdf->Cell(55,10,('Nom Hotel'),1,0,'C',true);
        $pdf->Cell(25,10,'Code',1,0,'C',true);
        $pdf->Cell(35,10,'Type',1,0,'C',true);
        $pdf->Cell(40,10,'Ville / Pays',1,0,'C',true);
        $pdf->Cell(55,10,'Adresse',1,0,'C',true);
        $pdf->Cell(30,10,'Telephone',1,0,'C',true);
        $pdf->Cell(20,10,'Chambres',1,0,'C',true);
        $pdf->Cell(25,10,'Etat',1,1,'C',true);

        $pdf->SetFont('Arial','',9);
        $i = 1;
        foreach ($data as $row) {
            $villePays = $row['ville_hotel'] . ' / ' . $row['pays_hotel'];
            $pdf->Cell(10,9,$i++,1,0,'C');
            $pdf->Cell(55,9,($row['nom_hotel']),1,0,'L');
            $pdf->Cell(25,9,$row['code_hotel'],1,0,'C');
            $pdf->Cell(35,9,($row['type_hotel']),1,0,'L');
            $pdf->Cell(40,9,($villePays),1,0,'L');
            $pdf->Cell(55,9,($row['adresse_hotel']),1,0,'L');
            $pdf->Cell(30,9,$row['telephone_hotel'],1,0,'C');
            $pdf->Cell(20,9,$row['nb_chambres'],1,0,'C');
            $pdf->Cell(25,9,ucfirst($row['etat_hotel'] ?? ''),1,1,'C');
        }

        $pdf->Output('D', 'Hotels_' . date('d-m-Y_H-i') . '.pdf');
        exit;
    }
}

// ==================== AFFICHAGE NORMAL ====================
$stmt = $pdo->query("
    SELECT h.*, COUNT(c.code_chambre) as nb_chambres
    FROM hotels h
    LEFT JOIN chambres c ON h.code_hotel = c.code_hotel
    GROUP BY h.code_hotel
    ORDER BY h.nom_hotel ASC
");
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
=======
// session_start();
require_once __DIR__ . '/../../database/database.php';

// ==================== SUPPRESSION ====================
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM hotels WHERE code_hotel = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Hôtel supprimé avec succès.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur lors de la suppression : impossible de supprimer un hôtel avec des chambres réservées.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==================== AJOUT / MODIFICATION ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $code   = trim($_POST['code_hotel']);
    $nom    = trim($_POST['nom_hotel']);
    $type   = trim($_POST['type_hotel']);
    $latitude   = trim($_POST['latitude_hotel']);
    $longitude  = trim($_POST['longitude_hotel']);
    $pays       = trim($_POST['pays_hotel']);
    $ville      = trim($_POST['ville_hotel']);
    $quartier   = trim($_POST['quartier_hotel']);
    $adresse    = trim($_POST['adresse_hotel']);
    $telephone  = trim($_POST['telephone_hotel']);
    $email      = trim($_POST['email_hotel']);
    $observation= $_POST['observation_hotel'];
    $etat       = trim($_POST['etat_hotel']);

    // Génération automatique du code si vide (uniquement en ajout)
    if ($action === 'add' && empty($code) && !empty($nom)) {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $nom), 0, 4)); // 4 premières lettres sans espaces/caractères spéciaux
        $code = $prefix . mt_rand(1000, 9999);
        // Garantir l'unicité
        $i = 1;
        $base = $code;
        while (true) {
            $check = $pdo->prepare("SELECT COUNT(*) FROM hotels WHERE code_hotel = ?");
            $check->execute([$code]);
            if ($check->fetchColumn() == 0) break;
            $code = $prefix . mt_rand(1000, 9999);
            if ($i++ > 20) { // sécurité anti-boucle infinie
                $code = $base . $i;
                break;
            }
        }
    }

    try {
        if ($action === 'add') {
            $sql = "INSERT INTO hotels (code_hotel, nom_hotel, type_hotel, latitude_hotel, longitude_hotel,
                     pays_hotel, ville_hotel, quartier_hotel, adresse_hotel, telephone_hotel, email_hotel,
                     observation_hotel, etat_hotel)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code, $nom, $type, $latitude, $longitude, $pays, $ville, $quartier, $adresse, $telephone, $email, $observation, $etat]);
            $_SESSION['message'] = "Hôtel ajouté avec succès. Code généré : <strong>$code</strong>";
        }
        if ($action === 'update') {
            $sql = "UPDATE hotels SET nom_hotel=?, type_hotel=?, latitude_hotel=?, longitude_hotel=?,
                    pays_hotel=?, ville_hotel=?, quartier_hotel=?, adresse_hotel=?,
                    telephone_hotel=?, email_hotel=?, observation_hotel=?, etat_hotel=?
                    WHERE code_hotel=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $type, $latitude, $longitude, $pays, $ville, $quartier, $adresse, $telephone, $email, $observation, $etat, $code]);
            $_SESSION['message'] = "Hôtel modifié avec succès.";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }
   
}

// ==================== LISTE HÔTELS ====================
$stmt = $pdo->query("SELECT * FROM hotels ORDER BY nom_hotel");
$hotels = $stmt->fetchAll();

// ==================== MESSAGE FLASH ====================
$message = $_SESSION['message'] ?? '';
$alert_type = str_starts_with($message, 'Erreur') ? 'danger' : 'success';
if ($message) unset($_SESSION['message']);

// ==================== LISTE DES VILLES DE CÔTE D'IVOIRE ====================
$villes_cote_divoire = [
    "Abidjan", "Yamoussoukro", "Bouaké", "Daloa", "San-Pédro", "Gagnoa", "Korhogo", "Man", "Divo", "Anyama",
    "Abengourou", "Agboville", "Grand-Bassam", "Dabou", "Bouafle", "Sinfra", "Bondoukou", "Ferkessédougou", "Katiola", "Oumé",
    "Boundiali", "Duekoue", "Touba", "Aboisso", "Adzopé", "Bingerville", "Tiassalé", "Daoukro", "Vavoua", "Guiglo",
    "Issia", "Sassandra", "Toumodi", "Lakota", "Zuénoula", "Bouna", "Mankono", "Séguéla", "Odienné", "Tanda",
    "Bonoua", "Arrah", "Jacqueville", "Afféry", "Bangolo", "Béoumi", "Bloléquin", "Botro", "Dimbokro", "Grand-Lahou"
];
sort($villes_cote_divoire);
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<<<<<<< HEAD
    <title>Hotelio | Hôtels</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include './config/dashboard.php'; ?>
=======
    <title>Soutra+ | Gestion des Hôtels</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .nav-treeview .nav-link { padding-left: 2.5rem; }
        .badge { font-size: 0.85em; padding: 0.4em 0.8em; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include __DIR__ . '/../../config/dashboard.php'; ?>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
<<<<<<< HEAD
                <h1 class="mb-0">Hôtels (Triés par ordre alphabétique)</h1>
=======
                <div class="row mb-2">
                    <div class="col-sm-6"><h1>Gestion des Hôtels</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Hôtels</li>
                        </ol>
                    </div>
                </div>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
<<<<<<< HEAD

                <!-- Boutons Export -->
                <form method="post" class="d-flex justify-content-end mb-4 gap-2 flex-wrap">
                    <button type="submit" name="export" value="excel" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>&nbsp &nbsp
                    <button type="submit" name="export" value="csv" class="btn btn-info text-white">
                        <i class="fas fa-file-csv"></i> CSV
                    </button>&nbsp &nbsp
                    <button type="submit" name="export" value="pdf" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>&nbsp &nbsp
                </form>

                <!-- Liste des hôtels (même style que les chambres) -->
                <?php foreach ($hotels as $h): ?>
                <div class="card mb-4 shadow-sm border-start border-primary border-5">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <?= htmlspecialchars($h['nom_hotel']) ?> 
                            <small>(<?= htmlspecialchars($h['code_hotel']) ?>)</small>
                        </h5>
                        <span class="badge bg-light text-dark">
                            <?= $h['nb_chambres'] ?> chambre<?= $h['nb_chambres'] > 1 ? 's' : '' ?>
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
=======
<<<<<<< HEAD
                
                <!-- Message Flash -->
=======
>>>>>>> 5cf037d595c4416fe2eed56b7720130cf8344b85
                <?php if ($message): ?>
<<<<<<< HEAD
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
=======
                        <!-- <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div> -->
>>>>>>> 9ecb113a2e5352327ff75a3e20f37459a2a5e2b8
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Liste des hôtels</h3>
                        <button class="btn btn-light" id="addBtn">
                            Ajouter un hôtel
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom</th>
                                        <th>Type</th>
<<<<<<< HEAD
                                        <th>Ville / Pays</th>
                                        <th>Adresse Complète</th>
                                        <th>Téléphone</th>
                                        <th>Email</th>
                                        <th>Chambres</th>
                                        <th>État</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <td><strong><?= htmlspecialchars($h['code_hotel']) ?></strong></td>
                                        <td><?= htmlspecialchars($h['nom_hotel']) ?></td>
                                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($h['type_hotel']) ?></span></td>
                                        <td>
                                            <?= htmlspecialchars($h['ville_hotel']) ?><br>
                                            <small class="text-muted"><?= htmlspecialchars($h['pays_hotel']) ?></small>
                                        </td>
                                        <td class="text-start">
                                            <?= nl2br(htmlspecialchars($h['adresse_hotel'])) ?><br>
                                            <small class="text-muted">Quartier : <?= htmlspecialchars($h['quartier_hotel']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($h['telephone_hotel']) ?></td>
                                        <td><?= htmlspecialchars($h['email_hotel'] ?? '-') ?></td>
                                        <td><strong class="text-primary"><?= $h['nb_chambres'] ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?= ($h['etat_hotel'] ?? '') == 'actif' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($h['etat_hotel'] ?? 'inconnu') ?>
                                            </span>
                                        </td>
                                    </tr>
=======
                                        <th>Ville</th>
                                        <th>Téléphone</th>
                                        <th>État</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($hotels)): ?>
                                        <tr><td colspan="7" class="text-center text-muted py-4">Aucun hôtel enregistré.</td></tr>
                                    <?php else: foreach ($hotels as $h): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($h['code_hotel']) ?></strong></td>
                                            <td><?= htmlspecialchars($h['nom_hotel']) ?></td>
                                            <td><span class="badge bg-info"><?= ucfirst($h['type_hotel'] ?? 'Standard') ?></span></td>
                                            <td><?= htmlspecialchars($h['ville_hotel']) ?></td>
                                            <td><?= htmlspecialchars($h['telephone_hotel']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $h['etat_hotel'] === 'actif' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($h['etat_hotel']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-warning btn-sm edit-btn"
                                                    data-code="<?= htmlspecialchars($h['code_hotel']) ?>"
                                                    data-nom="<?= htmlspecialchars($h['nom_hotel']) ?>"
                                                    data-type="<?= htmlspecialchars($h['type_hotel']) ?>"
                                                    data-lat="<?= htmlspecialchars($h['latitude_hotel']) ?>"
                                                    data-lng="<?= htmlspecialchars($h['longitude_hotel']) ?>"
                                                    data-pays="<?= htmlspecialchars($h['pays_hotel']) ?>"
                                                    data-ville="<?= htmlspecialchars($h['ville_hotel']) ?>"
                                                    data-quartier="<?= htmlspecialchars($h['quartier_hotel']) ?>"
                                                    data-adresse="<?= htmlspecialchars($h['adresse_hotel']) ?>"
                                                    data-tel="<?= htmlspecialchars($h['telephone_hotel']) ?>"
                                                    data-email="<?= htmlspecialchars($h['email_hotel']) ?>"
                                                    data-obs="<?= htmlspecialchars($h['observation_hotel']) ?>"
                                                    data-etat="<?= htmlspecialchars($h['etat_hotel']) ?>">
                                                    Modifier
                                                </button>
                                                <a href="?delete=<?= urlencode($h['code_hotel']) ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Supprimer définitivement cet hôtel ?');">
                                                    Supprimer
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
<<<<<<< HEAD
                </div>
                <?php endforeach; ?>

                <?php if (empty($hotels)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-2x mb-3"></i><br>
                        Aucun hôtel enregistré pour le moment.
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

    <!-- ==================== MODAL HÔTEL ==================== -->
    <div class="modal fade" id="hotelModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Ajouter un hôtel</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="hotelForm" method="post">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>Code hôtel <span class="text-muted"></span></label>
                                <input type="text" name="code_hotel" id="code_hotel" class="form-control" readonly >
                            </div>
                            <div class="col-md-8">
                                <label>Nom de l'hôtel <span class="text-danger">*</span></label>
                                <input type="text" name="nom_hotel" id="nom_hotel" class="form-control" required onkeyup="generateCode()">
                            </div>
                            <div class="col-md-6">
                                <label>Type / Catégorie</label>
                                <input type="text" name="type_hotel" id="type_hotel" class="form-control" placeholder="Ex: 4 étoiles, Résidence...">
                            </div>
                            <div class="col-md-6">
                                <label>État</label>
                                <select name="etat_hotel" id="etat_hotel" class="form-select">
                                    <option value="actif">Actif</option>
                                    <option value="inactif">Inactif</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Pays <span class="text-danger">*</span></label>
                                <input type="text" name="pays_hotel" id="pays_hotel" class="form-control" value="Côte d'Ivoire" readonly>
                            </div>
                            <div class="col-md-6">
                                <label>Ville <span class="text-danger">*</span></label>
                                <select name="ville_hotel" id="ville_hotel" class="form-select" required>
                                    <option value="">-- Choisir une ville --</option>
                                    <?php foreach ($villes_cote_divoire as $ville): ?>
                                        <option value="<?= htmlspecialchars($ville) ?>"><?= htmlspecialchars($ville) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Quartier</label>
                                <input type="text" name="quartier_hotel" id="quartier_hotel" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Adresse complète</label>
                                <input type="text" name="adresse_hotel" id="adresse_hotel" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Téléphone <span class="text-danger">*</span></label>
                                <input type="text" name="telephone_hotel" id="telephone_hotel" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" name="email_hotel" id="email_hotel" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>Latitude</label>
                                <input type="text" name="latitude_hotel" id="latitude_hotel" class="form-control" placeholder="Ex: 5.360000">
                            </div>
                            <div class="col-md-6">
                                <label>Longitude</label>
                                <input type="text" name="longitude_hotel" id="longitude_hotel" class="form-control" placeholder="Ex: -4.008300">
                            </div>
                            <div class="col-12">
                                <label>Observations / Services</label>
                                <textarea name="observation_hotel" id="observation_hotel" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success">Sauvegarder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    // Génération automatique du code en temps réel quand on tape le nom
    function generateCode() {
        const nom = document.getElementById('nom_hotel').value.trim();
        if (nom.length === 0) {
            document.getElementById('code_hotel').value = '';
            return;
        }
        const prefix = nom.replace(/[^A-Za-z]/g, '').substring(0, 4).toUpperCase();
        const random = Math.floor(1000 + Math.random() * 9000);
        document.getElementById('code_hotel').value = prefix + random;
    }

    const modal = new bootstrap.Modal('#hotelModal');
    document.getElementById('addBtn').addEventListener('click', () => {
        document.getElementById('hotelForm').reset();
        document.getElementById('modalTitle').innerText = 'Ajouter un hôtel';
        document.getElementById('formAction').value = 'add';
        document.getElementById('code_hotel').readOnly = true;
        document.getElementById('code_hotel').value = '';
        document.getElementById('pays_hotel').value = 'Côte d\'Ivoire';
        generateCode(); // pré-remplir au cas où
        modal.show();
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').innerText = 'Modifier un hôtel';
            document.getElementById('formAction').value = 'update';
            document.getElementById('code_hotel').value = this.dataset.code;
            document.getElementById('code_hotel').readOnly = true;
            document.getElementById('nom_hotel').value = this.dataset.nom;
            document.getElementById('type_hotel').value = this.dataset.type;
            document.getElementById('latitude_hotel').value = this.dataset.lat;
            document.getElementById('longitude_hotel').value = this.dataset.lng;
            document.getElementById('pays_hotel').value = this.dataset.pays;
            document.getElementById('ville_hotel').value = this.dataset.ville;
            document.getElementById('quartier_hotel').value = this.dataset.quartier;
            document.getElementById('adresse_hotel').value = this.dataset.adresse;
            document.getElementById('telephone_hotel').value = this.dataset.tel;
            document.getElementById('email_hotel').value = this.dataset.email;
            document.getElementById('observation_hotel').value = this.dataset.obs;
            document.getElementById('etat_hotel').value = this.dataset.etat;
            modal.show();
        });
    });
</script>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
</body>
</html>