<?php
//session_start();
require "database/database.php";

// ==================== SUPPRESSION ====================
if (isset($_GET['delete'])) {
    $code = $_GET['delete'];
    try {
        $check = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE code_chambre = ?");
        $check->execute([$code]);
        if ($check->fetchColumn() > 0) {
            $_SESSION['message'] = "Erreur : Cette chambre est liée à une réservation.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM chambres WHERE code_chambre = ?");
            $stmt->execute([$code]);
            $_SESSION['message'] = "Chambre supprimée avec succès.";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// ==================== AJOUT / MODIFICATION ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $code   = trim($_POST['code_chambre']);
    $nom    = trim($_POST['nom_chambre']);
    $type   = $_POST['type_chambre'];
    $description = $_POST['description_chambre'] ?? '';
    $prix   = trim($_POST['prix_chambre']);
    $etat   = trim($_POST['etat_chambre'] ?? 'disponible');
    $hotel  = trim($_POST['code_hotel']);

    // Génération automatique du code à l'ajout
    if ($action === 'add' && empty($code) && !empty($nom)) {
        // Extraire les 2 premières lettres du nom (sans espaces ni chiffres)
        preg_match('/^[A-Za-z]*/', str_replace([' ', '-'], '', $nom), $letters);
        $prefix = strtoupper(substr($letters[0], 0, 2));
        if (strlen($prefix) < 2) $prefix = 'CH'; // sécurité

        // Extraire le numéro (tout ce qui est numérique à la fin)
        preg_match('/\d+$/', $nom, $num);
        $number = $num[0] ?? mt_rand(100, 999);

        $code = $prefix . $number;

        // Garantir l'unicité
        $i = 1;
        $base = $code;
        while (true) {
            $check = $pdo->prepare("SELECT COUNT(*) FROM chambres WHERE code_chambre = ?");
            $check->execute([$code]);
            if ($check->fetchColumn() == 0) break;
            $code = $prefix . ($number + $i++);
            if ($i > 50) { $code = $base . $i; break; }
        }
    }

    if (empty($code) || empty($nom) || empty($type) || empty($prix) || empty($hotel)) {
        $_SESSION['message'] = "Erreur : Tous les champs obligatoires doivent être remplis.";
    } else {
        try {
            if ($action === 'add') {
                $check = $pdo->prepare("SELECT COUNT(*) FROM chambres WHERE code_chambre = ?");
                $check->execute([$code]);
                if ($check->fetchColumn() > 0) {
                    $_SESSION['message'] = "Erreur : Ce code chambre existe déjà.";
                } else {
                    $sql = "INSERT INTO chambres
                            (code_chambre, nom_chambre, type_chambre, description_chambre, prix_chambre, etat_chambre, code_hotel)
                            VALUES (?,?,?,?,?,?,?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$code, $nom, $type, $description, $prix, $etat, $hotel]);
                    $_SESSION['message'] = "Chambre ajoutée avec succès. Code généré : <strong>$code</strong>";
                }
            }
            if ($action === 'update') {
                $sql = "UPDATE chambres SET
                        nom_chambre = ?, type_chambre = ?, description_chambre = ?,
                        prix_chambre = ?, etat_chambre = ?, code_hotel = ?
                        WHERE code_chambre = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $type, $description, $prix, $etat, $hotel, $code]);
                $_SESSION['message'] = "Chambre modifiée avec succès.";
            }
        } catch (Exception $e) {
            $_SESSION['message'] = "Erreur : " . $e->getMessage();
        }
    }
   
}

// ==================== LISTE ====================
$stmt = $pdo->query("
    SELECT c.*, h.nom_hotel
    FROM chambres c
    LEFT JOIN hotels h ON c.code_hotel = h.code_hotel
    ORDER BY h.nom_hotel, c.nom_chambre
");
$chambres = $stmt->fetchAll();
$hotels = $pdo->query("SELECT code_hotel, nom_hotel FROM hotels ORDER BY nom_hotel")->fetchAll();

// Message
$message = $_SESSION['message'] ?? '';
$alert_type = str_starts_with($message, 'Erreur') ? 'danger' : 'success';
if ($message) unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Gestion des Chambres</title>
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
    <?php include 'config/dashboard.php'; ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1>Gestion des Chambres</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Chambres</li>
                        </ol>
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

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Liste des chambres</h3>
                        <button class="btn btn-light" id="addBtn">
                            Ajouter une chambre
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Prix</th>
                                        <th>Hôtel</th>
                                        <th>État</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($chambres)): ?>
                                        <tr><td colspan="7" class="text-center text-muted py-4">Aucune chambre enregistrée.</td></tr>
                                    <?php else: foreach ($chambres as $c): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($c['code_chambre']) ?></strong></td>
                                            <td><?= htmlspecialchars($c['nom_chambre']) ?></td>
                                            <td><span class="badge bg-info text-dark"><?= ucwords(str_replace('chambre ', '', $c['type_chambre'])) ?></span></td>
                                            <td><strong><?= number_format($c['prix_chambre']) ?> FCFA</strong></td>
                                            <td><?= htmlspecialchars($c['nom_hotel'] ?? '<em class="text-muted">Non assigné</em>') ?></td>
                                            <td>
                                                <?php
                                                $etat = $c['etat_chambre'];
                                                $badge = match($etat) {
                                                    'disponible' => 'success', 'occupée' => 'danger',
                                                    'réservée' => 'warning', 'maintenance' => 'secondary',
                                                    default => 'dark'
                                                };
                                                ?>
                                                <span class="badge bg-<?= $badge ?>"><?= ucfirst($etat) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-warning btn-sm edit-btn"
                                                    data-code="<?= htmlspecialchars($c['code_chambre']) ?>"
                                                    data-nom="<?= htmlspecialchars($c['nom_chambre']) ?>"
                                                    data-type="<?= htmlspecialchars($c['type_chambre']) ?>"
                                                    data-desc="<?= htmlspecialchars($c['description_chambre'] ?? '') ?>"
                                                    data-prix="<?= htmlspecialchars($c['prix_chambre']) ?>"
                                                    data-etat="<?= htmlspecialchars($c['etat_chambre']) ?>"
                                                    data-hotel="<?= htmlspecialchars($c['code_hotel']) ?>">
                                                    Modifier
                                                </button>
                                                <a href="?delete=<?= urlencode($c['code_chambre']) ?>" 
                                                   onclick="return confirm('Supprimer cette chambre ?');"
                                                   class="btn btn-danger btn-sm">
                                                    Supprimer
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- ==================== MODAL CHAMBRE ==================== -->
    <div class="modal fade" id="chambreModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Ajouter une chambre</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="chambreForm" method="post">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Code chambre <span class="text-muted"></span></label>
                                <input type="text" name="code_chambre" id="code_chambre" class="form-control" readonly >
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom chambre <span class="text-danger">*</span></label>
                                <input type="text" name="nom_chambre" id="nom_chambre" class="form-control" 
                                       placeholder="Ex: Chambre 101, Suite 305, VIP 12" required onkeyup="generateCodeFromName()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type chambre <span class="text-danger">*</span></label>
                                <select name="type_chambre" id="type_chambre" class="form-select" required>
                                    <option value="chambre simple">Chambre Simple</option>
                                    <option value="chambre double">Chambre Double</option>
                                    <option value="chambre vip">Chambre VIP</option>
                                    <option value="chambre suite">Chambre Suite</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                                <input type="text" name="prix_chambre" id="prix_chambre" class="form-control" placeholder="25000" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">État</label>
                                <select name="etat_chambre" id="etat_chambre" class="form-select">
                                    <option value="disponible">Disponible</option>
                                    <option value="occupée">Occupée</option>
                                    <option value="réservée">Réservée</option>
                                    <option value="maintenance">En maintenance</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hôtel <span class="text-danger">*</span></label>
                                <select name="code_hotel" id="code_hotel" class="form-select" required>
                                    <option value="">-- Sélectionner un hôtel --</option>
                                    <?php foreach ($hotels as $h): ?>
                                        <option value="<?= $h['code_hotel'] ?>"><?= htmlspecialchars($h['nom_hotel']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description_chambre" id="description_chambre" class="form-control" rows="4"></textarea>
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
    function generateCodeFromName() {
        const nom = document.getElementById('nom_chambre').value.trim();
        if (!nom) {
            document.getElementById('code_chambre').value = '';
            return;
        }
        // 2 premières lettres (sans espaces)
        let prefix = nom.replace(/[^A-Za-z]/g, '').substring(0, 2).toUpperCase();
        if (prefix.length < 2) prefix = 'CH';

        // Extraire le numéro à la fin
        const numMatch = nom.match(/\d+$/);
        const number = numMatch ? numMatch[0] : '';

        document.getElementById('code_chambre').value = prefix + number;
    }

    const modal = new bootstrap.Modal('#chambreModal');
    document.getElementById('addBtn').addEventListener('click', () => {
        document.getElementById('chambreForm').reset();
        document.getElementById('modalTitle').innerText = 'Ajouter une chambre';
        document.getElementById('formAction').value = 'add';
        document.getElementById('code_chambre').value = '';
        generateCodeFromName();
        modal.show();
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').innerText = 'Modifier une chambre';
            document.getElementById('formAction').value = 'update';
            document.getElementById('code_chambre').value = this.dataset.code;
            document.getElementById('code_chambre').readOnly = true;
            document.getElementById('nom_chambre').value = this.dataset.nom;
            document.getElementById('type_chambre').value = this.dataset.type;
            document.getElementById('description_chambre').value = this.dataset.desc;
            document.getElementById('prix_chambre').value = this.dataset.prix;
            document.getElementById('etat_chambre').value = this.dataset.etat;
            document.getElementById('code_hotel').value = this.dataset.hotel;
            modal.show();
        });
    });
</script>
</body>
</html>