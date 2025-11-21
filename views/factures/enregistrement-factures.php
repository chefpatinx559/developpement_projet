<?php
//session_start();
require "database/database.php";

<<<<<<< HEAD
=======
// ==================== FONCTION GÉNÉRATION CODE FACTURE ====================
function genererCodeFacture($pdo) {
    $count = $pdo->query("SELECT COUNT(*) FROM factures")->fetchColumn();
    return 'FACT' . ($count + 1);
}

// RÉCUPÉRATION DE TOUS LES CLIENTS POUR LA LISTE DÉROULANTE
$stmt_clients = $pdo->query("SELECT code_client, nom_prenom_client FROM clients ORDER BY nom_prenom_client");
$clients = $stmt_clients->fetchAll(PDO::FETCH_ASSOC);

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
// ==================== SUPPRESSION ====================
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM factures WHERE code_facture = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Facture supprimée avec succès.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
<<<<<<< HEAD
    header("Location: http://localhost/soutra/facture/enregistrement");
    exit;
=======
   
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
}

// ==================== AJOUT / MODIFICATION ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['print'])) {
    $action = $_POST['action'] ?? '';
    $code_facture = trim($_POST['code_facture']);
    $titre_facture = trim($_POST['titre_facture']);
    $date_facture = $_POST['date_facture'];
    $montant_ht = $_POST['montant_ht'];
    $montant_ttc = $_POST['montant_ttc'];
    $taux_taxes = $_POST['taux_taxes'];
    $type_taxes = trim($_POST['type_taxes']);
    $etat_facture = $_POST['etat_facture'];
    $code_client = trim($_POST['code_client']);

<<<<<<< HEAD
=======
    // Génération automatique du code lors de l'ajout si vide
    if ($action === 'add' && empty($code_facture)) {
        $code_facture = genererCodeFacture($pdo);
    }

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    try {
        if ($action === 'add') {
            $sql = "INSERT INTO factures
                    (code_facture, titre_facture, date_facture, montant_ht, montant_ttc, taux_taxes, type_taxes, etat_facture, code_client)
                    VALUES (?,?,?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code_facture, $titre_facture, $date_facture, $montant_ht, $montant_ttc, $taux_taxes, $type_taxes, $etat_facture, $code_client]);
<<<<<<< HEAD
            $_SESSION['message'] = "Facture créée avec succès.";
=======
            $_SESSION['message'] = "Facture créée avec succès. Code généré : <strong>$code_facture</strong>";
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
        }
        if ($action === 'update') {
            $sql = "UPDATE factures SET
                    code_facture = ?, titre_facture = ?, date_facture = ?, montant_ht = ?, montant_ttc = ?,
                    taux_taxes = ?, type_taxes = ?, etat_facture = ?, code_client = ?
                    WHERE code_facture = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code_facture, $titre_facture, $date_facture, $montant_ht, $montant_ttc, $taux_taxes, $type_taxes, $etat_facture, $code_client, $_POST['old_code']]);
            $_SESSION['message'] = "Facture modifiée avec succès.";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }
<<<<<<< HEAD
    header("Location: http://localhost/soutra/facture/enregistrement");
    exit;
}

// ==================== GÉNÉRATION PDF (REÇU 80mm) ====================
if (isset($_GET['print'])) {
    $code = $_GET['print'];
    $stmt = $pdo->prepare("SELECT * FROM factures WHERE code_facture = ?");
    $stmt->execute([$code]);
    $f = $stmt->fetch();
=======
   
}
// ==================== GÉNÉRATION PDF (REÇU 80mm) - PARFAIT, COURT & ACCENTS 100% CORRIGÉS ====================
if (isset($_GET['print'])) {
    $code = $_GET['print'];

    // Récupération facture + nom client
    $stmt = $pdo->prepare("
        SELECT f.*, c.nom_prenom_client 
        FROM factures f
        LEFT JOIN clients c ON f.code_client = c.code_client
        WHERE f.code_facture = ?
    ");
    $stmt->execute([$code]);
    $f = $stmt->fetch(PDO::FETCH_ASSOC);
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    if (!$f) die("Facture non trouvée");

    require('libraries/fpdf/fpdf.php');

<<<<<<< HEAD
    // Classe PDF personnalisée
    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Courier','B',12);
            $this->Cell(0,6,'SOUTRA +',0,1,'C');
            $this->SetFont('Courier','',8);
            $this->Cell(0,4,'Hotel de luxe - Abidjan',0,1,'C');
            $this->Cell(0,4,'Tel: +225 07 00 00 00 00',0,1,'C');
            $this->Cell(0,4,'contact@soutraplus.com',0,1,'C');
            $this->Ln(2);

            // Ligne horizontale
            $this->Line(5, $this->GetY(), 75, $this->GetY());
=======
    // === FONCTION UTF-8 UNIVERSELLE (corrige TOUS les accents parfaitement) ===
    function utf8($text) {
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
    }

    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Courier','B',13);
            $this->Cell(0,6,utf8('SOUTRA +'),0,1,'C');
            $this->SetFont('Courier','',8);
            $this->Cell(0,4,utf8('Hôtel de luxe - Côte d’Ivoire '),0,1,'C');
            $this->Cell(0,4,utf8('Email : soutrapro531@gmail.com'),0,1,'C');
            $this->Ln(2);
            $this->SetLineWidth(0.5);
            $this->Line(6, $this->GetY(), 74, $this->GetY());
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
            $this->Ln(3);
        }

        function Footer() {
<<<<<<< HEAD
            $this->SetY(-15);
            $this->SetFont('Courier','I',7);
            $this->Cell(0,4,'Merci de votre visite !',0,0,'C');
        }
    }

    // Format 80mm
    $pdf = new PDF('P', 'mm', [80, 200]); // Largeur 80mm, hauteur max 200mm
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->SetMargins(5, 5, 5);

    // === EN-TÊTE REÇU ===
    $pdf->SetFont('Courier','B',10);
    $pdf->Cell(0,5,'RECU N: '.strtoupper($f['code_facture']),0,1,'C');
    $pdf->SetFont('Courier','',8);
    $pdf->Cell(0,4,'Date: '.date('d/m/Y H:i', strtotime($f['date_facture'])),0,1,'C');
    $pdf->Cell(0,4,'Client: '.strtoupper($f['code_client']),0,1,'C');
    $pdf->Ln(2);
    $pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY()); // CORRIGÉ : $pdf->GetY()
    $pdf->Ln(3);

    // === DÉTAIL ===
    $pdf->SetFont('Courier','',9);
    $pdf->Cell(50,5,'Prestation',0);
    $pdf->Cell(20,5,number_format($f['montant_ht'],0,',',' ').' FCFA',0,1,'R');

    $taxe = $f['montant_ttc'] - $f['montant_ht'];
    $pdf->SetFont('Courier','',8);
    $pdf->Cell(50,4,'TVA ('.$f['taux_taxes'].'% '.$f['type_taxes'].')',0);
    $pdf->Cell(20,4,number_format($taxe,0,',',' ').' FCFA',0,1,'R');

    $pdf->Ln(2);
    $pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY()); // CORRIGÉ
    $pdf->Ln(3);

    // === TOTAL ===
    $pdf->SetFont('Courier','B',11);
    $pdf->Cell(50,6,'TOTAL',0);
    $pdf->Cell(20,6,number_format($f['montant_ttc'],0,',',' ').' FCFA',0,1,'R');

    $pdf->Ln(3);
    $pdf->SetFont('Courier','',8);
    $pdf->Cell(0,4,'Etat: '.strtoupper($f['etat_facture']),0,1,'C');

    $pdf->Ln(5);
    $pdf->SetFont('Courier','I',7);
    $pdf->MultiCell(0,3,"Conditions: 30 jours nets\nPenalites: 1.5% / mois",0,'C');
=======
            $this->SetY(-12);
            $this->SetFont('Courier','I',7);
            $this->Cell(0,4,utf8('Merci de votre visite !'),0,0,'C');
        }

        // Cell avec UTF-8 automatique
        function C($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false) {
            $this->Cell($w, $h, utf8($txt), $border, $ln, $align, $fill);
        }
    }

    if (ob_get_length()) ob_clean();

    $pdf = new PDF('P', 'mm', [80, 140]); // Ultra court
    $pdf->AddPage();
    $pdf->SetMargins(6, 5, 6);
    $pdf->SetAutoPageBreak(true, 10);

    // N° Reçu + Impression temps réel
    $pdf->SetFont('Courier','B',11);
    $pdf->C(0,6,'REÇU N° '.strtoupper($f['code_facture']),0,1,'C');
    $pdf->SetFont('Courier','',8);
    $pdf->C(0,4,'Imprimé le '.date('d/m/Y à H:i'),0,1,'C');

    // Client (nom complet avec accents)
    $nom = $f['nom_prenom_client'] ?? $f['code_client'];
    $pdf->Ln(2);
    $pdf->SetFont('Courier','B',9);
    $pdf->C(0,5,'Client :',0,1,'L');
    $pdf->SetFont('Courier','',9);
    $pdf->C(0,5,$nom,0,1,'L');

    $pdf->Ln(2);
    $pdf->SetLineWidth(0.4);
    $pdf->Line(6, $pdf->GetY(), 74, $pdf->GetY());
    $pdf->Ln(3);

    // Désignation (tronquée proprement)
    $titre = $f['titre_facture'] ?: 'Prestation';
    $titre_court = mb_substr($titre, 0, 26, 'UTF-8');
    if (mb_strlen($titre, 'UTF-8') > 26) $titre_court .= '...';

    $pdf->SetFont('Courier','',9);
    $pdf->C(48,5,$titre_court,0,0,'L');
    $pdf->C(22,5,number_format($f['montant_ht'],0,',',' ').' FCFA',0,1,'R');

    // TVA
    $taxe = $f['montant_ttc'] - $f['montant_ht'];
    $pdf->SetFont('Courier','',8);
    $pdf->C(48,4,utf8($f['type_taxes'].' '.$f['taux_taxes'].'%'),0,0,'L');
    $pdf->C(22,4,number_format($taxe,0,',',' ').' FCFA',0,1,'R');

    // TOTAL
    $pdf->Ln(2);
    $pdf->SetLineWidth(0.7);
    $pdf->Line(6, $pdf->GetY(), 74, $pdf->GetY());
    $pdf->Ln(3);
    $pdf->SetFont('Courier','B',12);
    $pdf->C(48,7,'TOTAL',0,0,'L');
    $pdf->C(22,7,number_format($f['montant_ttc'],0,',',' ').' FCFA',0,1,'R');

    // État
    $pdf->Ln(3);
    $etat = mb_strtoupper($f['etat_facture'], 'UTF-8');
    $pdf->SetFont('Courier','B',9);
    $pdf->C(0,5,'État : '.$etat,0,1,'C');
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919

    $pdf->Output('I', 'Recu_'.$f['code_facture'].'.pdf');
    exit;
}

// ==================== LISTE TOUTES LES FACTURES ====================
$stmt = $pdo->query("SELECT * FROM factures ORDER BY date_facture DESC");
$factures = $stmt->fetchAll();

// ==================== MESSAGE FLASH ====================
$message = $_SESSION['message'] ?? '';
$alert_type = str_starts_with($message, 'Erreur') ? 'danger' : 'success';
if ($message) unset($_SESSION['message']);
<<<<<<< HEAD

// ==================== UTILISATEUR CONNECTÉ ====================
$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";
?>

=======
?>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Gestion des Factures</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .nav-treeview .nav-link { padding-left: 2.5rem; }
        .badge { font-size: 0.85em; }
<<<<<<< HEAD
=======
        #montant_ttc { background-color: #f8f9fa; }
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    </style>
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
                        <h1>Gestion des Factures</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Factures</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
<<<<<<< HEAD

=======
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
        <section class="content">
            <div class="container-fluid">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
<<<<<<< HEAD
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

=======
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title mb-0">
<<<<<<< HEAD
                                Liste des factures 
=======
                                Liste des factures
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                <span id="filter-info" class="badge bg-info ms-2" style="display:none;"></span>
                            </h3>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-danger me-2" id="showUnpaid">
<<<<<<< HEAD
                                Factures impayées 
=======
                                Factures impayées
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                <span class="badge bg-danger" id="unpaidCount">0</span>
                            </button>
                            <button type="button" class="btn btn-secondary me-2" id="showAll">
                                Toutes les factures
                            </button>
                            <button class="btn btn-primary" id="addBtn">
                                Ajouter une facture
                            </button>
                        </div>
                    </div>
<<<<<<< HEAD

=======
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Titre</th>
                                        <th>Date</th>
                                        <th>Montant HT</th>
                                        <th>Montant TTC</th>
                                        <th>État</th>
                                        <th>Client</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
<<<<<<< HEAD
                                    <?php foreach ($factures as $f): 
=======
                                    <?php foreach ($factures as $f):
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                                        $status = strtolower(trim($f['etat_facture']));
                                    ?>
                                    <tr data-status="<?= $status ?>">
                                        <td><strong><?= htmlspecialchars($f['code_facture']) ?></strong></td>
                                        <td><?= htmlspecialchars($f['titre_facture']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($f['date_facture'])) ?></td>
                                        <td><?= number_format($f['montant_ht'], 0, ',', ' ') ?> FCFA</td>
                                        <td><strong><?= number_format($f['montant_ttc'], 0, ',', ' ') ?> FCFA</strong></td>
                                        <td>
                                            <span class="badge bg-<?= $f['etat_facture']=='Payée'?'success':($f['etat_facture']=='Annulée'?'danger':'warning') ?>">
                                                <?= htmlspecialchars($f['etat_facture']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($f['code_client']) ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm edit-btn"
                                                data-bs-code="<?= htmlspecialchars($f['code_facture']) ?>"
                                                data-bs-titre="<?= htmlspecialchars($f['titre_facture']) ?>"
                                                data-bs-date="<?= $f['date_facture'] ?>"
                                                data-bs-ht="<?= $f['montant_ht'] ?>"
                                                data-bs-ttc="<?= $f['montant_ttc'] ?>"
                                                data-bs-taux="<?= $f['taux_taxes'] ?>"
                                                data-bs-type="<?= htmlspecialchars($f['type_taxes']) ?>"
                                                data-bs-etat="<?= $f['etat_facture'] ?>"
                                                data-bs-client="<?= htmlspecialchars($f['code_client']) ?>">
                                                Modifier
                                            </button>
                                            <a href="?print=<?= urlencode($f['code_facture']) ?>" target="_blank"
                                               class="btn btn-info btn-sm text-white">Imprimer PDF</a>
                                            <a href="?delete=<?= urlencode($f['code_facture']) ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Supprimer cette facture ?');">
                                                Supprimer
                                            </a>
                                        </td>
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
</div>

<!-- ==================== MODAL FACTURE ==================== -->
<div class="modal fade" id="factureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Ajouter une facture</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
            </div>
<<<<<<< HEAD
            <div class="modal-body">
=======
            <div class COO="modal-body">
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                <form id="factureForm" method="post">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="old_code" id="old_code">
                    <div class="row g-3">
                        <div class="col-md-4">
<<<<<<< HEAD
                            <label>Code Facture <span class="text-danger">*</span></label>
                            <input type="text" name="code_facture" id="code_facture" class="form-control" required>
=======
                            <label>Code Facture <span class="text-muted"></span></label>
                            <input type="text" name="code_facture" id="code_facture" class="form-control" readonly placeholder="Ex: FACT88">
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                        </div>
                        <div class="col-md-8">
                            <label>Titre facture</label>
                            <input type="text" name="titre_facture" id="titre_facture" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Date facture</label>
                            <input type="date" name="date_facture" id="date_facture" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label>Montant HT</label>
                            <input type="number" step="0.01" name="montant_ht" id="montant_ht" class="form-control" required>
                        </div>
                        <div class="col-md-4">
<<<<<<< HEAD
                            <label>Montant TTC</label>
                            <input type="number" step="0.01" name="montant_ttc" id="montant_ttc" class="form-control" required>
=======
                            <label>Montant TTC <small class="text-muted"></small></label>
                            <input type="number" step="0.01" name="montant_ttc" id="montant_ttc" class="form-control" required readonly>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                        </div>
                        <div class="col-md-3">
                            <label>Taux taxes (%)</label>
                            <input type="number" step="0.01" name="taux_taxes" id="taux_taxes" class="form-control" value="18" required>
                        </div>
                        <div class="col-md-3">
                            <label>Type taxes</label>
                            <input type="text" name="type_taxes" id="type_taxes" class="form-control" value="TVA" required>
                        </div>
                        <div class="col-md-3">
                            <label>État</label>
                            <select name="etat_facture" id="etat_facture" class="form-control">
                                <option value="en attente">en attente</option>
                                <option value="Payée">Payée</option>
                                <option value="non payer">non payer</option>
                            </select>
                        </div>
                        <div class="col-md-3">
<<<<<<< HEAD
                            <label>Code Client</label>
                            <input type="text" name="code_client" id="code_client" class="form-control" required>
=======
                            <label>Client <span class="text-danger">*</span></label>
                            <select name="code_client" id="code_client" class="form-control" required>
                                <option value="">-- Sélectionner un client --</option>
                                <?php foreach ($clients as $c): ?>
                                    <option value="<?= htmlspecialchars($c['code_client']) ?>">
                                        <?= htmlspecialchars($c['code_client'] . ' - ' . $c['nom_prenom_client']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success px-4">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<<<<<<< HEAD

<script>
    const modal = new bootstrap.Modal('#factureModal');

    // Compter les impayées au chargement
=======
<script>
    const modal = new bootstrap.Modal('#factureModal');

    // === CALCUL AUTOMATIQUE DU TTC ===
    function calculerTTC() {
        const ht = parseFloat(document.getElementById('montant_ht').value) || 0;
        const taux = parseFloat(document.getElementById('taux_taxes').value) || 0;
        const ttc = ht * (1 + taux / 100);
        document.getElementById('montant_ttc').value = ttc.toFixed(2);
    }
    document.getElementById('montant_ht').addEventListener('input', calculerTTC);
    document.getElementById('taux_taxes').addEventListener('input', calculerTTC);
    document.getElementById('factureModal').addEventListener('shown.bs.modal', calculerTTC);

>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    function updateUnpaidCount() {
        const count = document.querySelectorAll('tr[data-status="non payer"], tr[data-status="en attente"]').length;
        document.getElementById('unpaidCount').textContent = count;
    }
<<<<<<< HEAD

    // Initialisation
    updateUnpaidCount();

    // Bouton Ajouter
=======
    updateUnpaidCount();

    // === BOUTON AJOUTER UNE FACTURE → CODE AUTO ===
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    document.getElementById('addBtn').addEventListener('click', () => {
        document.getElementById('factureForm').reset();
        document.getElementById('modalTitle').innerText = 'Ajouter une facture';
        document.getElementById('formAction').value = 'add';
<<<<<<< HEAD
        document.getElementById('code_facture').readOnly = false;
        document.getElementById('date_facture').value = '<?= date('Y-m-d') ?>';
        modal.show();
    });

    // Bouton Modifier
=======
        document.getElementById('code_facture').readOnly = true;
        document.getElementById('code_facture').value = '<?= genererCodeFacture($pdo) ?>';
        document.getElementById('date_facture').value = '<?= date('Y-m-d') ?>';
        document.getElementById('taux_taxes').value = '18';
        document.getElementById('type_taxes').value = 'TVA';
        calculerTTC();
        modal.show();
    });

    // === BOUTON MODIFIER ===
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').innerText = 'Modifier une facture';
            document.getElementById('formAction').value = 'update';
            document.getElementById('old_code').value = this.dataset.bsCode;
            document.getElementById('code_facture').value = this.dataset.bsCode;
            document.getElementById('code_facture').readOnly = true;
            document.getElementById('titre_facture').value = this.dataset.bsTitre;
            document.getElementById('date_facture').value = this.dataset.bsDate;
            document.getElementById('montant_ht').value = this.dataset.bsHt;
<<<<<<< HEAD
            document.getElementById('montant_ttc').value = this.dataset.bsTtc;
=======
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
            document.getElementById('taux_taxes').value = this.dataset.bsTaux;
            document.getElementById('type_taxes').value = this.dataset.bsType;
            document.getElementById('etat_facture').value = this.dataset.bsEtat;
            document.getElementById('code_client').value = this.dataset.bsClient;
<<<<<<< HEAD
=======
            calculerTTC();
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
            modal.show();
        });
    });

<<<<<<< HEAD
    // Bouton : Afficher seulement les impayées
=======
    // Filtres impayées / toutes
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
    document.getElementById('showUnpaid').addEventListener('click', function() {
        document.querySelectorAll('tbody tr').forEach(row => {
            const status = row.dataset.status;
            row.style.display = (status === 'non payer' || status === 'en attente') ? '' : 'none';
        });
        document.getElementById('filter-info').textContent = 'Filtre : Factures impayées uniquement';
        document.getElementById('filter-info').style.display = 'inline';
    });
<<<<<<< HEAD

    // Bouton : Afficher toutes les factures
    document.getElementById('showAll').addEventListener('click', function() {
        document.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = '';
        });
=======
    document.getElementById('showAll').addEventListener('click', function() {
        document.querySelectorAll('tbody tr').forEach(row => row.style.display = '');
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
        document.getElementById('filter-info').style.display = 'none';
    });
</script>
</body>
</html>