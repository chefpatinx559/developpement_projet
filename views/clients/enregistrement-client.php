<?php
//session_start();
require "database/database.php";

// ==================== FONCTION GÉNÉRATION CODE CLIENT ====================
function genererCodeClient($pdo) {
    $count = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
    return 'CL' . ($count + 1);
}

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

    if ($action === 'add' && empty($code)) {
        $code = genererCodeClient($pdo);
    }

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
                    $_SESSION['message'] = "Client ajouté avec succès. Code généré : <strong>$code</strong>";
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
}

// ==================== RECHERCHE + PAGINATION ====================
$recherche = $_GET['recherche'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;
$where = $recherche ? "WHERE nom_prenom_client LIKE :search OR code_client LIKE :search OR telephone_client LIKE :search" : "";
$search = "%$recherche%";
$countSql = "SELECT COUNT(*) FROM clients $where";
$countStmt = $pdo->prepare($countSql);
if ($recherche) $countStmt->bindParam(':search', $search);
$countStmt->execute();
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

// ==================== LISTE CLIENTS ====================
$sql = "SELECT c.*, COUNT(r.numero_reservation) as nb_reservations
        FROM clients c
        LEFT JOIN reservations r ON c.code_client = r.code_client
        $where
        GROUP BY c.code_client
        ORDER BY c.nom_prenom_client
        LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
if ($recherche) $stmt->bindParam(':search', $search);
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$clients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Gestion des Clients</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .nav-treeview .nav-link { padding-left: 2.5rem; }
        .badge { font-size: 0.85em; padding: 0.4em 0.8em; }
        .top-client-card {
            border-left: 5px solid #ffc107;
            background: #fff8e1;
            border-radius: 0.375rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1>Gestion des Clients</h1></div>
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
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Liste des clients</h3>
                        <button class="btn btn-light" id="addBtn">Ajouter un client</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom & Prénom</th>
                                        <th>Téléphone</th>
                                        <th>Email</th>
                                        <th>Nationalité</th>
                                        <th>Réservations</th>
                                        <th>État</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($clients)): ?>
                                        <tr><td colspan="8" class="text-center text-muted py-4">Aucun client trouvé.</td></tr>
                                    <?php else: foreach ($clients as $c): ?>
                                        <tr>
                                            <td><strong><code><?= htmlspecialchars($c['code_client']) ?></code></strong></td>
                                            <td><?= htmlspecialchars($c['nom_prenom_client']) ?></td>
                                            <td><?= htmlspecialchars($c['telephone_client']) ?></td>
                                            <td><?= htmlspecialchars($c['email_client']) ?></td>
                                            <td><?= htmlspecialchars($c['nationalite_client']) ?></td>
                                            <td>
                                                <span class="badge <?= $c['nb_reservations'] > 0 ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $c['nb_reservations'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $c['etat_client'] === 'Actif' ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= ucfirst($c['etat_client']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-warning btn-sm edit-btn"
                                                    data-code="<?= htmlspecialchars($c['code_client']) ?>"
                                                    data-nom="<?= htmlspecialchars($c['nom_prenom_client']) ?>"
                                                    data-date="<?= $c['date_naissance_client'] ?>"
                                                    data-lieu="<?= htmlspecialchars($c['lieu_naissance_client']) ?>"
                                                    data-sexe="<?= htmlspecialchars($c['sexe_client']) ?>"
                                                    data-nationalite="<?= htmlspecialchars($c['nationalite_client']) ?>"
                                                    data-situation="<?= htmlspecialchars($c['situation_matrimoniale_client']) ?>"
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
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav class="d-flex justify-content-center mt-3">
                                <ul class="pagination">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&recherche=<?= urlencode($recherche) ?>">Précédent</a>
                                    </li>
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&recherche=<?= urlencode($recherche) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&recherche=<?= urlencode($recherche) ?>">Suivant</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- ==================== MODAL CLIENT ==================== -->
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
                            <div class="col-md-6">
                                <label class="form-label">Code Client <span class="text-muted"></span></label>
                                <input type="text" name="code_client" id="code_client" class="form-control" readonly placeholder="Ex: CL128">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom & Prénom <span class="text-danger">*</span></label>
                                <input type="text" name="nom_prenom_client" id="nom_prenom_client" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date de naissance <span class="text-danger">*</span></label>
                                <input type="date" name="date_naissance_client" id="date_naissance_client" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lieu de naissance <span class="text-danger">*</span></label>
                                <input type="text" name="lieu_naissance_client" id="lieu_naissance_client" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sexe <span class="text-danger">*</span></label>
                                <select name="sexe_client" id="sexe_client" class="form-select" required>
                                    <option value="Masculin">Masculin</option>
                                    <option value="Féminin">Féminin</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nationalité <span class="text-danger">*</span></label>
                                <input type="text" name="nationalite_client" id="nationalite_client" class="form-control" value="Ivoirienne" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Situation matrimoniale</label>
                                <select name="situation_matrimoniale_client" id="situation_matrimoniale_client" class="form-select">
                                    <option value="Célibataire">Célibataire</option>
                                    <option value="Marié(e)">Marié(e)</option>
                                    <option value="Divorcé(e)">Divorcé(e)</option>
                                    <option value="Veuf(ve)">Veuf(ve)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre d'enfants</label>
                                <input type="number" name="nombre_enfant_client" id="nombre_enfant_client" class="form-control" min="0" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                                <input type="text" name="telephone_client" id="telephone_client" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email_client" id="email_client" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pays <span class="text-danger">*</span></label>
                                <input type="text" name="pays_client" id="pays_client" class="form-control" value="Côte d'Ivoire" required>
                            </div>

                            <!-- LISTE COMPLÈTE DES VILLES DE CÔTE D'IVOIRE -->
                            <div class="col-md-6">
                                <label class="form-label">Ville <span class="text-danger">*</span></label>
                                <select name="ville_client" id="ville_client" class="form-select" required>
                                    <option value="">-- Sélectionner une ville --</option>
                                    <option value="Abidjan" selected>Abidjan</option>
                                    <option value="Abengourou">Abengourou</option>
                                    <option value="Aboisso">Aboisso</option>
                                    <option value="Adiaké">Adiaké</option>
                                    <option value="Adzopé">Adzopé</option>
                                    <option value="Agboville">Agboville</option>
                                    <option value="Agnibilékrou">Agnibilékrou</option>
                                    <option value="Alépé">Alépé</option>
                                    <option value="Anyama">Anyama</option>
                                    <option value="Arrah">Arrah</option>
                                    <option value="Attécoubé">Attécoubé</option>
                                    <option value="Bangolo">Bangolo</option>
                                    <option value="Béoumi">Béoumi</option>
                                    <option value="Biankouma">Biankouma</option>
                                    <option value="Bingerville">Bingerville</option>
                                    <option value="Bloléquin">Bloléquin</option>
                                    <option value="Bondoukou">Bondoukou</option>
                                    <option value="Bongouanou">Bongouanou</option>
                                    <option value="Bouaké">Bouaké</option>
                                    <option value="Bouna">Bouna</option>
                                    <option value="Boundiali">Boundiali</option>
                                    <option value="Dabakala">Dabakala</option>
                                    <option value="Dabou">Dabou</option>
                                    <option value="Daloa">Daloa</option>
                                    <option value="Danane">Danane</option>
                                    <option value="Daoukro">Daoukro</option>
                                    <option value="Dimbokro">Dimbokro</option>
                                    <option value="Divô">Divô</option>
                                    <option value="Duekoué">Duekoué</option>
                                    <option value="Ferkessédougou">Ferkessédougou</option>
                                    <option value="Gagnoa">Gagnoa</option>
                                    <option value="Grand-Bassam">Grand-Bassam</option>
                                    <option value="Grand-Lahou">Grand-Lahou</option>
                                    <option value="Guiglo">Guiglo</option>
                                    <option value="Issia">Issia</option>
                                    <option value="Jacqueville">Jacqueville</option>
                                    <option value="Kani">Kani</option>
                                    <option value="Katiola">Katiola</option>
                                    <option value="Korhogo">Korhogo</option>
                                    <option value="Lakota">Lakota</option>
                                    <option value="Man">Man</option>
                                    <option value="Mankono">Mankono</option>
                                    <option value="Mbahiakro">Mbahiakro</option>
                                    <option value="Minignan">Minignan</option>
                                    <option value="Nafana">Nafana</option>
                                    <option value="Odienné">Odienné</option>
                                    <option value="Oumé">Oumé</option>
                                    <option value="Sakassou">Sakassou</option>
                                    <option value="San-Pédro">San-Pédro</option>
                                    <option value="Sassandra">Sassandra</option>
                                    <option value="Séguéla">Séguéla</option>
                                    <option value="Sinfra">Sinfra</option>
                                    <option value="Soubré">Soubré</option>
                                    <option value="Tabou">Tabou</option>
                                    <option value="Tanda">Tanda</option>
                                    <option value="Tiebissou">Tiebissou</option>
                                    <option value="Tiassalé">Tiassalé</option>
                                    <option value="Tingréla">Tingréla</option>
                                    <option value="Touba">Touba</option>
                                    <option value="Toumodi">Toumodi</option>
                                    <option value="Vavoua">Vavoua</option>
                                    <option value="Yamoussoukro">Yamoussoukro</option>
                                    <option value="Zouan-Hounien">Zouan-Hounien</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Adresse <span class="text-danger">*</span></label>
                                <input type="text" name="adresse_client" id="adresse_client" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Quartier <span class="text-danger">*</span></label>
                                <input type="text" name="quartier_client" id="quartier_client" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type de client</label>
                                <select name="type_client" id="type_client" class="form-select">
                                    <option value="Particulier">Particulier</option>
                                    <option value="Entreprise">Entreprise</option>
                                    <option value="VIP">VIP</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">État</label>
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

    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
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
        document.getElementById('code_client').readOnly = true;
        document.getElementById('code_client').value = '<?= genererCodeClient($pdo) ?>';
        document.getElementById('ville_client').value = 'Abidjan';
        document.getElementById('pays_client').value = "Côte d'Ivoire";
        document.getElementById('nationalite_client').value = "Ivoirienne";
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