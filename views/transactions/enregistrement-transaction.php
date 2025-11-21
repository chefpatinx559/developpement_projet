<?php
//session_start();
require_once __DIR__ . '/../../database/database.php';

function formatMoney($amount) {
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}

// === SUPPRESSION ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE numero_transaction = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Transaction supprimée avec succès.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
   
}

// === AJOUT / MODIFICATION ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action       = $_POST['action'] ?? '';
    $numero       = trim($_POST['numero_transaction'] ?? '');
    $date         = $_POST['date_transaction'] ?? '';
    $heure        = $_POST['heure_transaction'] ?? '';
    $montant      = floatval($_POST['montant_transaction'] ?? 0);
    $frais        = floatval($_POST['frais_transaction'] ?? 0);
    $total        = $montant + $frais;
    $expediteur   = trim($_POST['expediteur'] ?? '');
    $destinataire = trim($_POST['destinataire'] ?? '');
    $type         = trim($_POST['type_transaction'] ?? '');
    $objet        = trim($_POST['objet_transaction'] ?? '');
    $mode         = trim($_POST['mode_reglement'] ?? '');
    $num_reg      = trim($_POST['numero_reglement'] ?? '');
    $ref_reg      = trim($_POST['reference_reglement'] ?? '');
    $user         = $_POST['utilisateur_id'] ?? '';
    $facture      = trim($_POST['code_facture'] ?? '');
    $etat         = $_POST['etat_transaction'] ?? '';

    try {
        if ($action === 'add') {
            $sql = "INSERT INTO transactions 
                    (numero_transaction, date_transaction, heure_transaction, montant_transaction, frais_transaction, montant_total,
                     expediteur, destinataire, type_transaction, objet_transaction, mode_reglement, numero_reglement,
                     reference_reglement, utilisateur_id, code_facture, etat_transaction)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$numero, $date, $heure, $montant, $frais, $total, $expediteur, $destinataire, $type, $objet, $mode, $num_reg, $ref_reg, $user, $facture, $etat]);
            $_SESSION['message'] = "Transaction ajoutée avec succès.";
        } elseif ($action === 'update') {
            $sql = "UPDATE transactions SET 
                    date_transaction=?, heure_transaction=?, montant_transaction=?, frais_transaction=?, montant_total=?,
                    expediteur=?, destinataire=?, type_transaction=?, objet_transaction=?, mode_reglement=?, 
                    numero_reglement=?, reference_reglement=?, utilisateur_id=?, code_facture=?, etat_transaction=?
                    WHERE numero_transaction=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date, $heure, $montant, $frais, $total, $expediteur, $destinataire, $type, $objet, $mode, $num_reg, $ref_reg, $user, $facture, $etat, $numero]);
            $_SESSION['message'] = "Transaction modifiée avec succès.";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }
   
}

// === LISTE + DONNÉES POUR SELECT ===
$stmt = $pdo->query("
    SELECT t.*, c.nom_prenom_client, f.titre_facture, u.nom_prenom as nom_utilisateur
    FROM transactions t
    LEFT JOIN clients c ON t.destinataire = c.code_client
    LEFT JOIN factures f ON t.code_facture = f.code_facture
    LEFT JOIN utilisateurs u ON t.utilisateur_id = u.utilisateur_id
    ORDER BY t.date_transaction DESC, t.heure_transaction DESC
");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$clients      = $pdo->query("SELECT code_client, nom_prenom_client FROM clients ORDER BY nom_prenom_client")->fetchAll(PDO::FETCH_ASSOC);
$factures     = $pdo->query("SELECT code_facture, titre_facture FROM factures ORDER BY titre_facture")->fetchAll(PDO::FETCH_ASSOC);
$utilisateurs = $pdo->query("SELECT utilisateur_id, nom_prenom FROM utilisateurs ORDER BY nom_prenom")->fetchAll(PDO::FETCH_ASSOC);

$message = $_SESSION['message'] ?? '';
$alert_type = str_starts_with($message, 'Erreur') ? 'danger' : 'success';
if ($message) unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Gestion des Transactions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .action-group { display: flex; gap: 8px; flex-wrap: nowrap; justify-content: center; }
        .action-group .btn { padding: 0.4rem 0.8rem; font-size: 0.875rem; min-width: 80px; }
        @media (max-width: 768px) { .action-group { flex-direction: column; } }
        .badge-success { background-color: #28a745 !important; }
        .badge-danger { background-color: #dc3545 !important; }
        .badge-warning { background-color: #ffc107 !important; color: #212529; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include __DIR__ . '/../../config/dashboard.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6"><h1>Gestion des Transactions</h1></div>
                    <div class="col-sm-6 text-end">
                        <button class="btn btn-primary" id="addBtn">Ajouter une transaction</button>
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

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Liste des transactions (<?= count($transactions) ?>)</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>N°</th>
                                        <th>Date & Heure</th>
                                        <th>Montant Total</th>
                                        <th>Type</th>
                                        <th>Client</th>
                                        <th>Facture</th>
                                        <th>État</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $t): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($t['numero_transaction']) ?></strong></td>
                                        <td><?= date('d/m/Y H:i', strtotime($t['date_transaction'] . ' ' . $t['heure_transaction'])) ?></td>
                                        <td class="text-success fw-bold"><?= formatMoney($t['montant_total']) ?></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($t['type_transaction'] ?? '') ?></span></td>
                                        <td><?= htmlspecialchars($t['nom_prenom_client'] ?? $t['destinataire'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($t['titre_facture'] ?? $t['code_facture'] ?? '—') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $t['etat_transaction'] === 'Succès' ? 'success' : ($t['etat_transaction'] === 'Echec' ? 'danger' : 'warning') ?>">
                                                <?= htmlspecialchars($t['etat_transaction'] ?? 'En attente') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <button class="btn btn-warning btn-sm edit-btn"
                                                    data-bs-numero="<?= htmlspecialchars($t['numero_transaction']) ?>"
                                                    data-bs-date="<?= $t['date_transaction'] ?>"
                                                    data-bs-heure="<?= $t['heure_transaction'] ?>"
                                                    data-bs-montant="<?= $t['montant_transaction'] ?>"
                                                    data-bs-frais="<?= $t['frais_transaction'] ?? 0 ?>"
                                                    data-bs-expediteur="<?= htmlspecialchars($t['expediteur'] ?? '') ?>"
                                                    data-bs-destinataire="<?= htmlspecialchars($t['destinataire'] ?? '') ?>"
                                                    data-bs-type="<?= htmlspecialchars($t['type_transaction'] ?? '') ?>"
                                                    data-bs-objet="<?= htmlspecialchars($t['objet_transaction'] ?? '') ?>"
                                                    data-bs-mode="<?= htmlspecialchars($t['mode_reglement'] ?? '') ?>"
                                                    data-bs-numreg="<?= htmlspecialchars($t['numero_reglement'] ?? '') ?>"
                                                    data-bs-refreg="<?= htmlspecialchars($t['reference_reglement'] ?? '') ?>"
                                                    data-bs-user="<?= $t['utilisateur_id'] ?? '' ?>"
                                                    data-bs-facture="<?= htmlspecialchars($t['code_facture'] ?? '') ?>"
                                                    data-bs-etat="<?= htmlspecialchars($t['etat_transaction'] ?? '') ?>">
                                                    Modifier
                                                </button>
                                                <a href="impression?numero=<?= urlencode($t['numero_transaction']) ?>" class="btn btn-success btn-sm" target="_blank">
                                                    Reçu
                                                </a>
                                                <a href="?delete=<?= urlencode($t['numero_transaction']) ?>" class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Supprimer cette transaction ?');">
                                                    Supprimer
                                                </a>
                                            </div>
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

<!-- MODAL COMPLET -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Ajouter une transaction</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="transactionForm" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <div class="row g-3">
                        <div class="col-md-4"><label>N° Transaction <span class="text-danger">*</span></label><input type="text" name="numero_transaction" id="numero_transaction" class="form-control" required></div>
                        <div class="col-md-4"><label>Date <span class="text-danger">*</span></label><input type="date" name="date_transaction" id="date_transaction" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
                        <div class="col-md-4"><label>Heure <span class="text-danger">*</span></label><input type="time" name="heure_transaction" id="heure_transaction" class="form-control" required value="<?= date('H:i') ?>"></div>

                        <div class="col-md-4"><label>Montant <span class="text-danger">*</span></label><input type="number" step="1" name="montant_transaction" id="montant_transaction" class="form-control" required></div>
                        <div class="col-md-4"><label>Frais</label><input type="number" step="1" name="frais_transaction" id="frais_transaction" class="form-control" value="0"></div>
                        <div class="col-md-4"><label>Total</label><input type="text" class="form-control bg-light" id="total_display" readonly value="0 FCFA"></div>

                        <div class="col-md-6"><label>Expéditeur</label><input type="text" name="expediteur" id="expediteur" class="form-control"></div>
                        <div class="col-md-6"><label>Destinataire (Client)</label>
                            <select name="destinataire" id="destinataire" class="form-select">
                                <option value="">-- Choisir --</option>
                                <?php foreach ($clients as $c): ?>
                                    <option value="<?= $c['code_client'] ?>"><?= htmlspecialchars($c['nom_prenom_client']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4"><label>Type transaction</label>
                            <select name="type_transaction" id="type_transaction" class="form-select">
                                <option value="Dépôt">Dépôt</option>
                                <option value="Retrait">Retrait</option>
                                <option value="Transfert">Transfert</option>
                                <option value="Paiement facture">Paiement facture</option>
                            </select>
                        </div>
                        <div class="col-md-8"><label>Objet</label><input type="text" name="objet_transaction" id="objet_transaction" class="form-control"></div>

                        <div class="col-md-4"><label>Mode règlement</label>
                            <select name="mode_reglement" id="mode_reglement" class="form-select">
                                <option value="Espèces">Espèces</option>
                                <option value="Mobile Money">Mobile Money</option>
                                <option value="Virement">Virement</option>
                                <option value="Chèque">Chèque</option>
                            </select>
                        </div>
                        <div class="col-md-4"><label>N° règlement</label><input type="text" name="numero_reglement" id="numero_reglement" class="form-control"></div>
                        <div class="col-md-4"><label>Référence règlement</label><input type="text" name="reference_reglement" id="reference_reglement" class="form-control"></div>

                        <div class="col-md-6"><label>Utilisateur</label>
                            <select name="utilisateur_id" id="utilisateur_id" class="form-select">
                                <?php foreach ($utilisateurs as $u): ?>
                                    <option value="<?= $u['utilisateur_id'] ?>"><?= htmlspecialchars($u['nom_prenom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6"><label>Facture liée</label>
                            <select name="code_facture" id="code_facture" class="form-select">
                                <option value="">Aucune</option>
                                <?php foreach ($factures as $f): ?>
                                    <option value="<?= $f['code_facture'] ?>"><?= htmlspecialchars($f['titre_facture']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4"><label>État</label>
                            <select name="etat_transaction" id="etat_transaction" class="form-select">
                                <option value="Succès">Succès</option>
                                <option value="En attente">En attente</option>
                                <option value="Echec">Echec</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    const modal = new bootstrap.Modal('#transactionModal');

    document.getElementById('addBtn').onclick = () => {
        document.getElementById('transactionForm').reset();
        document.getElementById('modalTitle').textContent = 'Ajouter une transaction';
        document.getElementById('formAction').value = 'add';
        document.getElementById('numero_transaction').readOnly = false;
        document.getElementById('date_transaction').value = '<?= date('Y-m-d') ?>';
        document.getElementById('heure_transaction').value = '<?= date('H:i') ?>';
        updateTotal();
        modal.show();
    };

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.onclick = () => {
            document.getElementById('modalTitle').textContent = 'Modifier la transaction';
            document.getElementById('formAction').value = 'update';
            document.getElementById('numero_transaction').value = btn.dataset.bsNumero;
            document.getElementById('numero_transaction').readOnly = true;
            document.getElementById('date_transaction').value = btn.dataset.bsDate;
            document.getElementById('heure_transaction').value = btn.dataset.bsHeure;
            document.getElementById('montant_transaction').value = btn.dataset.bsMontant;
            document.getElementById('frais_transaction').value = btn.dataset.bsFrais;
            document.getElementById('expediteur').value = btn.dataset.bsExpediteur;
            document.getElementById('destinataire').value = btn.dataset.bsDestinataire;
            document.getElementById('type_transaction').value = btn.dataset.bsType;
            document.getElementById('objet_transaction').value = btn.dataset.bsObjet;
            document.getElementById('mode_reglement').value = btn.dataset.bsMode;
            document.getElementById('numero_reglement').value = btn.dataset.bsNumreg;
            document.getElementById('reference_reglement').value = btn.dataset.bsRefreg;
            document.getElementById('utilisateur_id').value = btn.dataset.bsUser;
            document.getElementById('code_facture').value = btn.dataset.bsFacture;
            document.getElementById('etat_transaction').value = btn.dataset.bsEtat;
            updateTotal();
            modal.show();
        };
    });

    function updateTotal() {
        const montant = parseFloat(document.getElementById('montant_transaction').value) || 0;
        const frais = parseFloat(document.getElementById('frais_transaction').value) || 0;
        document.getElementById('total_display').value = new Intl.NumberFormat('fr-FR').format(montant + frais) + ' FCFA';
    }
    document.getElementById('montant_transaction')?.addEventListener('input', updateTotal);
    document.getElementById('frais_transaction')?.addEventListener('input', updateTotal);
</script>
</body>
</html>