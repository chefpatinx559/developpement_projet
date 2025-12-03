<?php
// session_start();
require_once "database/database.php";

/* ================================================================
   1. GESTION DES EXPORTS (TOUT EN HAUT → avant tout HTML)
   ================================================================ */
if (isset($_POST['export']) && in_array($_POST['export'], ['excel', 'csv', 'pdf'])) {

    $filter = $_POST['filter'] ?? '';
    $where  = $filter ? "WHERE a.code_hotel = ? OR a.code_chambre = ?" : "";
    $params = $filter ? [$filter, $filter] : [];

    $sql = "
        SELECT a.code_album, a.titre_album, a.etat_album,
               COALESCE(h.nom_hotel, '-') AS nom_hotel,
               COALESCE(h.ville_hotel, '-') AS ville_hotel,
               COALESCE(c.nom_chambre, '-') AS nom_chambre
        FROM albums a
        LEFT JOIN hotels h ON a.code_hotel = h.code_hotel
        LEFT JOIN chambres c ON a.code_chambre = c.code_chambre
        $where
        ORDER BY a.titre_album
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nettoyage complet des buffers (indispensable avec dashboard.php)
    while (ob_get_level()) ob_end_clean();

    // CSV
    if ($_POST['export'] === 'csv') {
        $filename = 'Albums_' . date('d-m-Y_H-i') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF"; // BOM UTF-8
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Code Album', 'Titre', 'Hôtel', 'Ville', 'Chambre', 'État'], ';');
        foreach ($data as $row) {
            fputcsv($out, [
                $row['code_album'],
                $row['titre_album'],
                $row['nom_hotel'],
                $row['ville_hotel'],
                $row['nom_chambre'],
                ucfirst($row['etat_album'])
            ], ';');
        }
        exit;
    }

    // Excel (.xls)
    if ($_POST['export'] === 'excel') {
        $filename = 'Albums_' . date('d-m-Y_H-i') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        echo '<table border="1"><tr style="background:#198754;color:white;font-weight:bold;">
                <th>Code Album</th><th>Titre</th><th>Hôtel</th><th>Ville</th><th>Chambre</th><th>État</th></tr>';
        foreach ($data as $row) {
            echo '<tr align="center">
                    <td>' . htmlspecialchars($row['code_album']) . '</td>
                    <td>' . htmlspecialchars($row['titre_album']) . '</td>
                    <td>' . htmlspecialchars($row['nom_hotel']) . '</td>
                    <td>' . htmlspecialchars($row['ville_hotel']) . '</td>
                    <td>' . htmlspecialchars($row['nom_chambre']) . '</td>
                    <td>' . ucfirst($row['etat_album']) . '</td>
                  </tr>';
        }
        echo '</table>';
        exit;
    }

    // PDF
    if ($_POST['export'] === 'pdf') {
        require_once 'librairiesfpdf/fpdf/fpdf.php';
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetFillColor(25, 135, 84);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 12, mb_convert_encoding('Liste des Albums Photo', 'Windows-1252', 'UTF-8'), 0, 1, 'C', true);
        $pdf->Ln(5);
        $pdf->SetTextColor(0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, mb_convert_encoding('Généré le ' . date('d/m/Y à H:i'), 'Windows-1252', 'UTF-8'), 0, 1, 'R');
        $pdf->Ln(8);

        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('Arial', 'B', 10);
        $widths = [25, 70, 50, 40, 50, 25];
        $header = ['Code', 'Titre', 'Hôtel', 'Ville', 'Chambre', 'État'];
        foreach ($header as $i => $h) {
            $pdf->Cell($widths[$i], 10, mb_convert_encoding($h, 'Windows-1252', 'UTF-8'), 1, 0, 'C', true);
        }
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 9);
        foreach ($data as $row) {
            $pdf->Cell($widths[0], 8, $row['code_album'], 1, 0, 'C');
            $pdf->Cell($widths[1], 8, mb_convert_encoding(mb_substr($row['titre_album'], 0, 40), 'Windows-1252', 'UTF-8'), 1, 0, 'L');
            $pdf->Cell($widths[2], 8, mb_convert_encoding(mb_substr($row['nom_hotel'], 0, 25), 'Windows-1252', 'UTF-8'), 1, 0, 'L');
            $pdf->Cell($widths[3], 8, mb_convert_encoding($row['ville_hotel'], 'Windows-1252', 'UTF-8'), 1, 0, 'L');
            $pdf->Cell($widths[4], 8, mb_convert_encoding(mb_substr($row['nom_chambre'], 0, 25), 'Windows-1252', 'UTF-8'), 1, 0, 'L');
            $pdf->Cell($widths[5], 8, mb_convert_encoding(ucfirst($row['etat_album']), 'Windows-1252', 'UTF-8'), 1, 1, 'C');
        }
        $pdf->Output('D', 'Albums_' . date('d-m-Y_H-i') . '.pdf');
        exit;
    }
}

/* ================================================================
   2. LE RESTE DE LA PAGE (après les exports)
   ================================================================ */
$filter = $_GET['filter'] ?? '';
$where  = $filter ? "WHERE a.code_album = ? OR a.code_chambre = ?" : "";
$params = $filter ? [$filter, $filter] : [];

// Suppression
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM albums WHERE code_album = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Album supprimé avec succès.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?filter=" . urlencode($filter));
    exit;
}

// Ajout / Modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['export'])) {
    $action       = $_POST['action'] ?? '';
    $code         = trim($_POST['code_album']);
    $titre        = trim($_POST['titre_album']);
    $code_hotel   = !empty($_POST['code_hotel']) ? $_POST['code_hotel'] : null;
    $code_chambre = !empty($_POST['code_chambre']) ? $_POST['code_chambre'] : null;
    $etat         = $_POST['etat_album'] ?? 'inactif';
    $photo = $type_photo = null;

    if (isset($_FILES['photo_album']) && $_FILES['photo_album']['error'] === UPLOAD_ERR_OK) {
        $photo      = file_get_contents($_FILES['photo_album']['tmp_name']);
        $type_photo = $_FILES['photo_album']['type'];
    }

    try {
        if ($action === 'add') {
            $sql = "INSERT INTO albums (code_album, titre_album, photo_album, type_photo, code_hotel, code_chambre, etat_album)
                    VALUES (?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code, $titre, $photo, $type_photo, $code_hotel, $code_chambre, $etat]);
            $_SESSION['message'] = "Album ajouté avec succès.";
        }
        if ($action === 'update') {
            if ($photo) {
                $sql = "UPDATE albums SET titre_album=?, photo_album=?, type_photo=?, code_hotel=?, code_chambre=?, etat_album=? WHERE code_album=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$titre, $photo, $type_photo, $code_hotel, $code_chambre, $etat, $code]);
            } else {
                $sql = "UPDATE albums SET titre_album=?, code_hotel=?, code_chambre=?, etat_album=? WHERE code_album=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$titre, $code_hotel, $code_chambre, $etat, $code]);
            }
            $_SESSION['message'] = "Album modifié avec succès.";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?filter=" . urlencode($filter));
    exit;
}

// Liste des albums + hôtels
$stmt = $pdo->prepare("
    SELECT a.*, h.nom_hotel, h.ville_hotel, c.nom_chambre, c.type_chambre
    FROM albums a
    LEFT JOIN hotels h ON a.code_hotel = h.code_hotel
    LEFT JOIN chambres c ON a.code_chambre = c.code_chambre
    $where
    ORDER BY a.titre_album
");
$stmt->execute($params);
$albums = $stmt->fetchAll();

$hotels = $pdo->query("SELECT code_hotel, nom_hotel, ville_hotel FROM hotels WHERE etat_hotel='actif' ORDER BY nom_hotel")->fetchAll();

$message = $_SESSION['message'] ?? '';
$alert_type = str_starts_with($message ?? '', 'Erreur') ? 'danger' : 'success';
if ($message) unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Gestion des Albums</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .nav-treeview .nav-link { padding-left: 2.5rem; }
        .badge { font-size: 0.85em; }
        .album-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .album-card-img { width: 100%; height: 180px; object-fit: cover; border-radius: 10px 10px 0 0; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php include 'config/dashboard.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1>Gestion des Albums (Hôtels & Chambres)</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Albums</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <?php if ($message): ?>
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- BOUTONS EXPORT + FILTRE + AJOUT -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <form method="post" class="d-flex gap-3">
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        <button type="submit" name="export" value="excel" class="btn btn-success shadow-sm px-4">
                            Excel
                        </button>
                        <button type="submit" name="export" value="csv" class="btn btn-info text-white shadow-sm px-4">
                            CSV
                        </button>
                        <button type="submit" name="export" value="pdf" class="btn btn-danger shadow-sm px-4">
                            PDF
                        </button>
                    </form>

                    <div class="d-flex gap-2">
                        <form method="get" class="d-flex gap-2">
                            <input type="text" name="filter" class="form-control" placeholder="Code hôtel ou chambre"
                                   value="<?= htmlspecialchars($filter) ?>" style="width:220px;">
                            <button type="submit" class="btn btn-outline-primary">Filtrer</button>
                        </form>
                        <button class="btn btn-primary shadow-sm" id="addBtn">Ajouter un album</button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            Liste des albums
                            <?php if ($filter): ?><span class="badge bg-info ms-2">Filtré : <?= htmlspecialchars($filter) ?></span><?php endif; ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($albums)): ?>
                            <div class="alert alert-info text-center">
                                <?= $filter ? "Aucun album trouvé." : "Aucun album enregistré." ?>
                            </div>
                        <?php elseif ($filter): ?>
                            <div class="row">
                                <?php foreach ($albums as $a): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 shadow-sm">
                                            <?php if ($a['photo_album']): ?>
                                                <img src="data:<?= htmlspecialchars($a['type_photo']) ?>;base64,<?= base64_encode($a['photo_album']) ?>" class="album-card-img" alt="<?= htmlspecialchars($a['titre_album']) ?>">
                                            <?php else: ?>
                                                <div class="bg-light border album-card-img d-flex align-items-center justify-content-center">
                                                    <span class="text-muted">Pas d’image</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($a['titre_album']) ?></h5>
                                                <p class="card-text small">
                                                    <strong>Code :</strong> <?= htmlspecialchars($a['code_album']) ?><br>
                                                    <strong>Hôtel :</strong> <?= htmlspecialchars($a['nom_hotel'] ?? '—') ?><br>
                                                    <strong>Chambre :</strong> <?= htmlspecialchars($a['nom_chambre'] ?? '—') ?><br>
                                                    <strong>État :</strong>
                                                    <span class="badge bg-<?= $a['etat_album']==='actif'?'success':'secondary' ?>">
                                                        <?= ucfirst($a['etat_album']) ?>
                                                    </span>
                                                </p>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-warning btn-sm edit-btn flex-fill"
                                                        data-bs-code="<?= htmlspecialchars($a['code_album']) ?>"
                                                        data-bs-titre="<?= htmlspecialchars($a['titre_album']) ?>"
                                                        data-bs-hotel="<?= htmlspecialchars($a['code_hotel'] ?? '') ?>"
                                                        data-bs-chambre="<?= htmlspecialchars($a['code_chambre'] ?? '') ?>"
                                                        data-bs-etat="<?= htmlspecialchars($a['etat_album']) ?>">
                                                        Modifier
                                                    </button>
                                                    <a href="?delete=<?= urlencode($a['code_album']) ?>&filter=<?= urlencode($filter) ?>"
                                                       class="btn btn-danger btn-sm flex-fill"
                                                       onclick="return confirm('Supprimer cet album ?');">Supprimer</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Code</th><th>Titre</th><th>Photo</th><th>Hôtel</th><th>Chambre</th><th>État</th><th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($albums as $a): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($a['code_album']) ?></strong></td>
                                            <td><?= htmlspecialchars($a['titre_album']) ?></td>
                                            <td>
                                                <?php if ($a['photo_album']): ?>
                                                    <img src="data:<?= htmlspecialchars($a['type_photo']) ?>;base64,<?= base64_encode($a['photo_album']) ?>" class="album-img" alt="Photo">
                                                <?php else: ?><span class="text-muted">Aucune</span><?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($a['nom_hotel'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($a['nom_chambre'] ?? '—') ?></td>
                                            <td><span class="badge bg-<?= $a['etat_album']==='actif'?'success':'secondary' ?>"><?= ucfirst($a['etat_album']) ?></span></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm edit-btn"
                                                    data-bs-code="<?= htmlspecialchars($a['code_album']) ?>"
                                                    data-bs-titre="<?= htmlspecialchars($a['titre_album']) ?>"
                                                    data-bs-hotel="<?= htmlspecialchars($a['code_hotel'] ?? '') ?>"
                                                    data-bs-chambre="<?= htmlspecialchars($a['code_chambre'] ?? '') ?>"
                                                    data-bs-etat="<?= htmlspecialchars($a['etat_album']) ?>">Modifier</button>
                                                <a href="?delete=<?= urlencode($a['code_album']) ?>" class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Supprimer ?');">Supprimer</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
</div>

<!-- MODAL -->
<div class="modal fade" id="albumModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Ajouter un album</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="albumForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Code album <span class="text-danger">*</span></label>
                            <input type="text" name="code_album" id="code_album" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Titre album <span class="text-danger">*</span></label>
                            <input type="text" name="titre_album" id="titre_album" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hôtel <span class="text-danger">*</span></label>
                            <select name="code_hotel" id="code_hotel" class="form-select" required>
                                <option value="">-- Sélectionner un hôtel --</option>
                                <?php foreach ($hotels as $h): ?>
                                    <option value="<?= $h['code_hotel'] ?>"><?= htmlspecialchars($h['nom_hotel']) ?> (<?= $h['ville_hotel'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chambre</label>
                            <select name="code_chambre" id="code_chambre" class="form-select" disabled>
                                <option value="">-- Sélectionner une chambre --</option>
                            </select>
                            <small class="text-muted">Choisissez d'abord un hôtel</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">État</label>
                            <select name="etat_album" id="etat_album" class="form-select">
                                <option value="actif">Actif</option>
                                <option value="inactif" selected>Inactif</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Photo album</label>
                            <input type="file" name="photo_album" id="photo_album" class="form-control" accept="image/*">
                            <small class="text-muted">Laissez vide pour conserver l’image actuelle</small>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    const modal = new bootstrap.Modal('#albumModal');
    const selectHotel = document.getElementById('code_hotel');
    const selectChambre = document.getElementById('code_chambre');

    function loadChambres(hotelCode, selected = '') {
        if (!hotelCode) {
            selectChambre.innerHTML = '<option value="">-- Sélectionner une chambre --</option>';
            selectChambre.disabled = true;
            return;
        }
        selectChambre.innerHTML = '<option>Chargement...</option>';
        selectChambre.disabled = true;
        fetch(`album/import?hotel=${encodeURIComponent(hotelCode)}`)
            .then(r => r.json())
            .then(data => {
                let opts = '<option value="">-- Sélectionner une chambre --</option>';
                data.forEach(ch => {
                    opts += `<option value="${ch.code_chambre}" ${ch.code_chambre===selected?'selected':''}>${ch.nom_chambre} (${ch.type_chambre})</option>`;
                });
                selectChambre.innerHTML = opts;
                selectChambre.disabled = false;
            });
    }

    selectHotel.addEventListener('change', () => loadChambres(selectHotel.value));

    document.getElementById('addBtn').addEventListener('click', () => {
        document.getElementById('modalTitle').innerText = 'Ajouter un album';
        document.getElementById('formAction').value = 'add';
        document.getElementById('albumForm').reset();
        document.getElementById('code_album').readOnly = false;
        selectChambre.disabled = true;
        modal.show();
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').innerText = 'Modifier un album';
            document.getElementById('formAction').value = 'update';
            document.getElementById('code_album').value = this.dataset.bsCode;
            document.getElementById('code_album').readOnly = true;
            document.getElementById('titre_album').value = this.dataset.bsTitre;
            document.getElementById('code_hotel').value = this.dataset.bsHotel || '';
            document.getElementById('etat_album').value = this.dataset.bsEtat;
            loadChambres(this.dataset.bsHotel || '', this.dataset.bsChambre || '');
            modal.show();
        });
    });
</script>
</body>
</html>