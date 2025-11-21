<?php
//session_start();
require "database/database.php";

// ==================== SUPPRESSION ====================
if (isset($_GET['delete'])) {
    $code = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE code_client = ?");
        $stmt->execute([$code]);
        $_SESSION['message'] = "Client supprimé avec succès.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==================== AJOUT / MODIFICATION ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $code = trim($_POST['code_client']);
    $nom = trim($_POST['nom_prenom_client']);
    $date_naiss = $_POST['date_naissance_client'];
    $lieu = trim($_POST['lieu_naissance_client']);
    $sexe = $_POST['sexe_client'];
    $nationalite = trim($_POST['nationalite_client']);
    $situation = trim($_POST['situation_matrimoniale_client'] ?? '');
    $enfants = (int)($_POST['nombre_enfant_client'] ?? 0);
    $tel = trim($_POST['telephone_client']);
    $email = trim($_POST['email_client']);
    $pays = trim($_POST['pays_client']);
    $ville = trim($_POST['ville_client']);
    $adresse = trim($_POST['adresse_client']);
    $quartier = trim($_POST['quartier_client']);
    $type = $_POST['type_client'] ?? 'Particulier';
    $etat = $_POST['etat_client'] ?? 'Actif';

    if (empty($code) || empty($nom) || empty($tel) || empty($email)) {
        $_SESSION['message'] = "Erreur : Les champs obligatoires doivent être remplis.";
    } else {
        try {
            if ($action === 'add') {
                $check = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE code_client = ?");
                $check->execute([$code]);
                if ($check->fetchColumn() > 0) {
                    $_SESSION['message'] = "Erreur : Ce code client existe déjà.";
                } else {
                    $sql = "INSERT INTO clients VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$code, $nom, $date_naiss, $lieu, $sexe, $nationalite, $situation, $enfants, $tel, $email, $pays, $ville, $adresse, $quartier, $type, $etat]);
                    $_SESSION['message'] = "Client ajouté avec succès.";
                }
            }
            if ($action === 'update') {
                $sql = "UPDATE clients SET
                    nom_prenom_client=?, date_naissance_client=?, lieu_naissance_client=?,
                    sexe_client=?, nationalite_client=?, situation_matrimoniale_client=?,
                    nombre_enfant_client=?, telephone_client=?, email_client=?,
                    pays_client=?, ville_client=?, adresse_client=?, quartier_client=?,
                    type_client=?, etat_client=?
                    WHERE code_client=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $date_naiss, $lieu, $sexe, $nationalite, $situation, $enfants, $tel, $email, $pays, $ville, $adresse, $quartier, $type, $etat, $code]);
                $_SESSION['message'] = "Client modifié avec succès.";
            }
        } catch (Exception $e) {
            $_SESSION['message'] = "Erreur : " . $e->getMessage();
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==================== RÉCUPÉRER TOUS LES CLIENTS AVEC NB RÉSERVATIONS ====================
$stmt = $pdo->query("
    SELECT c.*, COUNT(r.numero_reservation) as nb_reservations
    FROM clients c
    LEFT JOIN reservations r ON c.code_client = r.code_client
    GROUP BY c.code_client
    ORDER BY nb_reservations DESC, c.nom_prenom_client
");
$all_clients = $stmt->fetchAll();

// Regrouper par type de client
$clients_par_type = [];
foreach ($all_clients as $c) {
    $type = $c['type_client'] ?? 'Particulier';
    if (!isset($clients_par_type[$type])) {
        $clients_par_type[$type] = [];
    }
    $clients_par_type[$type][] = $c;
}

// Ordre d'affichage des types
$ordre_types = ['VIP', 'Entreprise', 'Particulier'];
$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Clients par Fidélité</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-3px); }
        .badge-type { font-size: 0.8rem; }
        .client-card { border-left: 5px solid #007bff; }
        .rank-badge { width: 32px; height: 32px; font-size: 0.9rem; }
        .top-client { background-color: #fff8e1; border-left: 6px solid #ffc107; }
        .medal-1 { background: linear-gradient(45deg, #f39c12, #e67e22); color: white; }
        .medal-2 { background: #95a5a6; color: white; }
        .medal-3 { background: #cd7f32; color: white; }
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
                        <h1>Clients classés par fidélité</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Clients</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?= str_starts_with($_SESSION['message'], 'Erreur') ? 'danger' : 'success' ?> alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <div class="mb-3">
                     <a href="enregistrement" class="btn btn-secondary">
                        Gestion des clients
                     </a>
                </div>

                <?php if (empty($all_clients)): ?>
                    <div class="alert alert-info text-center">
                        Aucun client enregistré pour le moment.
                    </div>
                <?php else: ?>
                    <?php foreach ($ordre_types as $type): ?>
                        <?php if (!isset($clients_par_type[$type]) || empty($clients_par_type[$type])) continue; ?>
                        <?php $clients = $clients_par_type[$type]; ?>
                        <div class="card client-card shadow-sm mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center
                                <?= $type === 'VIP' ? 'bg-danger text-white' : ($type === 'Entreprise' ? 'bg-info text-white' : 'bg-primary text-White') ?>">
                                <h5 class="mb-0">
                                    <?= $type === 'VIP' ? 'Clients VIP' : ($type === 'Entreprise' ? 'Clients Entreprise' : 'Clients Particuliers') ?>
                                </h5>
                                <span class="badge bg-light text-dark">
                                    <?= count($clients) ?> client<?= count($clients) > 1 ? 's' : '' ?>
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Code</th>
                                                <th>Nom & Prénom</th>
                                                <th>Téléphone</th>
                                                <th>Ville</th>
                                                <th>Réservations</th>
                                                <th>État</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($clients as $index => $c): ?>
                                                <?php
                                                $globalRank = 0;
                                                foreach ($all_clients as $i => $client) {
                                                    if ($client['code_client'] === $c['code_client']) {
                                                        $globalRank = $i + 1;
                                                        break;
                                                    }
                                                }
                                                $isTop3 = $globalRank <= 3;
                                                $rankClass = $globalRank == 1 ? 'medal-1' : ($globalRank == 2 ? 'medal-2' : ($globalRank == 3 ? 'medal-3' : 'bg-secondary'));
                                                ?>
                                                <tr <?= $isTop3 ? 'class="top-client fw-bold"' : '' ?>>
                                                    <td>
                                                        <?php if ($isTop3): ?>
                                                            <span class="badge rounded-pill <?= $rankClass ?> rank-badge d-flex align-items-center justify-content-center">
                                                                <?= $globalRank ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted"><?= $globalRank ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><strong><code><?= htmlspecialchars($c['code_client']) ?></code></strong></td>
                                                    <td><?= htmlspecialchars($c['nom_prenom_client']) ?></td>
                                                    <td><?= htmlspecialchars($c['telephone_client']) ?></td>
                                                    <td><?= htmlspecialchars($c['ville_client']) ?></td>
                                                    <td>
                                                        <span class="badge bg-success fs-6">
                                                            <?= $c['nb_reservations'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $c['etat_client'] === 'Actif' ? 'success' : 'danger' ?>">
                                                            <?= ucfirst($c['etat_client']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-warning btn-sm edit-btn"
                                                            data-code="<?= htmlspecialchars($c['code_client']) ?>"
                                                            data-nom="<?= htmlspecialchars($c['nom_prenom_client']) ?>"
                                                            data-date="<?= $c['date_naissance_client'] ?>"
                                                            data-lieu="<?= htmlspecialchars($c['lieu_naissance_client']) ?>"
                                                            data-sexe="<?= htmlspecialchars($c['sexe_client']) ?>"
                                                            data-nationalite="<?= htmlspecialchars($c['nationalite_client']) ?>"
                                                            data-situation="<?= htmlspecialchars($c['situation_matrimoniale_client'] ?? '') ?>"
                                                            data-enfants="<?= $c['nombre_enfant_client'] ?>"
                                                            data-tel="<?= htmlspecialchars($c['telephone_client']) ?>"
                                                            data-email="<?= htmlspecialchars($c['email_client']) ?>"
                                                            data-pays="<?= htmlspecialchars($c['pays_client']) ?>"
                                                            data-ville="<?= htmlspecialchars($c['ville_client']) ?>"
                                                            data-adresse="<?= htmlspecialchars($c['adresse_client']) ?>"
                                                            data-quartier="<?= htmlspecialchars($c['quartier_client']) ?>"
                                                            data-type="<?= htmlspecialchars($c['type_client']) ?>"
                                                            data-etat="<?= htmlspecialchars($c['etat_client']) ?>">
                                                            Modifier
                                                        </button>
                                                        <a href="?delete=<?= urlencode($c['code_client']) ?>"
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Supprimer ce client ?');">
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
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
</div>

<!-- MODAL CLIENT (inchangé) -->
<div class="modal fade" id="clientModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Ajouter un client</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="clientForm" method="post">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <div class="row g-3">
                        <div class="col-md-6"><label>Code Client *</label><input type="text" name="code_client" id="code_client" class="form-control" required></div>
                        <div class="col-md-6"><label>Nom & Prénom *</label><input type="text" name="nom_prenom_client" id="nom_prenom_client" class="form-control" required></div>
                        <div class="col-md-6"><label>Date de naissance *</label><input type="date" name="date_naissance_client" id="date_naissance_client" class="form-control" required></div>
                        <div class="col-md-6"><label>Lieu de naissance *</label><input type="text" name="lieu_naissance_client" id="lieu_naissance_client" class="form-control" required></div>
                        <div class="col-md-4"><label>Sexe *</label>
                            <select name="sexe_client" id="sexe_client" class="form-select" required>
                                <option value="Masculin">Masculin</option>
                                <option value="Féminin">Féminin</option>
                            </select>
                        </div>
                        <div class="col-md-4"><label>Nationalité *</label><input type="text" name="nationalite_client" id="nationalite_client" value="Ivoirienne" class="form-control" required></div>
                        <div class="col-md-4"><label>Situation matrimoniale</label>
                            <select name="situation_matrimoniale_client" id="situation_matrimoniale_client" class="form-select">
                                <option value="Célibataire">Célibataire</option>
                                <option value="Marié(e)">Marié(e)</option>
                                <option value="Divorcé(e)">Divorcé(e)</option>
                                <option value="Veuf(ve)">Veuf(ve)</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label>Nombre d'enfants</label><input type="number" name="nombre_enfant_client" id="nombre_enfant_client" class="form-control" min="0" value="0"></div>
                        <div class="col-md-6"><label>Téléphone *</label><input type="text" name="telephone_client" id="telephone_client" class="form-control" required></div>
                        <div class="col-md-6"><label>Email *</label><input type="email" name="email_client" id="email_client" class="form-control" required></div>
                        <div class="col-md-6"><label>Pays *</label><input type="text" name="pays_client" id="pays_client" value="Côte d'Ivoire" class="form-control" required></div>
                        <div class="col-md-6"><label>Ville *</label>
                            <select name="ville_client" id="ville_client" class="form-select" required>
                                <option value="">-- Sélectionner une ville --</option>
                                <option value="Abidjan">Abidjan</option>
                                <option value="Yamoussoukro">Yamoussoukro</option>
                                <option value="Bouaké">Bouaké</option>
                                <option value="Daloa">Daloa</option>
                                <option value="San-Pédro">San-Pédro</option>
                                <!-- (toutes les villes de Côte d'Ivoire comme avant) -->
                                <option value="Korhogo">Korhogo</option>
                                <option value="Gagnoa">Gagnoa</option>
                                <!-- ... tu peux garder la liste complète ici ... -->
                            </select>
                        </div>
                        <div class="col-md-6"><label>Adresse *</label><input type="text" name="adresse_client" id="adresse_client" class="form-control" required></div>
                        <div class="col-md-6"><label>Quartier *</label><input type="text" name="quartier_client" id="quartier_client" class="form-control" required></div>
                        <div class="col-md-6"><label>Type de client</label>
                            <select name="type_client" id="type_client" class="form-select">
                                <option value="Particulier">Particulier</option>
                                <option value="Entreprise">Entreprise</option>
                                <option value="VIP">VIP</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label>État</label>
                            <select name="etat_client" id="etat_client" class="form-select">
                                <option value="Actif">Actif</option>
                                <option value="Inactif">Inactif</option>
                            </select>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    const modal = new bootstrap.Modal('#clientModal');
    document.getElementById('addBtn').addEventListener('click', () => {
        document.getElementById('clientForm').reset();
        document.getElementById('modalTitle').innerText = 'Ajouter un client';
        document.getElementById('formAction').value = 'add';
        document.getElementById('code_client').readOnly = false;
        document.getElementById('ville_client').value = 'Abidjan';
        modal.show();
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').innerText = 'Modifier un client';
            document.getElementById('formAction').value = 'update';
            document.getElementById('code_client').value = this.dataset.code;
            document.getElementById('code_client').readOnly = true;
            document.getElementById('nom_prenom_client').value = this.dataset.nom;
            document.getElementById('date_naissance_client').value = this.dataset.date;
            document.getElementById('lieu_naissance_client').value = this.dataset.lieu;
            document.getElementById('sexe_client').value = this.dataset.sexe;
            document.getElementById('nationalite_client').value = this.dataset.nationalite;
            document.getElementById('situation_matrimoniale_client').value = this.dataset.situation;
            document.getElementById('nombre_enfant_client').value = this.dataset.enfants;
            document.getElementById('telephone_client').value = this.dataset.tel;
            document.getElementById('email_client').value = this.dataset.email;
            document.getElementById('pays_client').value = this.dataset.pays;
            document.getElementById('ville_client').value = this.dataset.ville;
            document.getElementById('adresse_client').value = this.dataset.adresse;
            document.getElementById('quartier_client').value = this.dataset.quartier;
            document.getElementById('type_client').value = this.dataset.type;
            document.getElementById('etat_client').value = this.dataset.etat;
            modal.show();
        });
    });
</script>
</body>
</html>