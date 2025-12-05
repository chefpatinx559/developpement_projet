<?php
// DÉMARRAGE DE LA SESSION (OBLIGATOIRE !)
// session_start();
require "database/database.php";

// ==================== VÉRIFICATION AUTHENTIFICATION ====================
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: login.php");
    exit;
}

// ==================== FONCTION PHOTO UTILISATEUR (centralisée) ====================
// function userPhoto() {
//     if (!empty($_SESSION['photo']) && !empty($_SESSION['type_photo'])) {
//         return 'data:' . $_SESSION['type_photo'] . ';base64,' . base64_encode($_SESSION['photo']);
//     }
//     return 'assets/images/user.png'; // Ta belle icône par défaut
// }

// ==================== VARIABLES DASHBOARD ====================
$user_name = $_SESSION['nom_prenom'] ?? "Utilisateur Anonyme";
$user_role = $_SESSION['role'] ?? "Réception";

// ==================== REQUÊTES SQL (inchangées) ====================
$stmt = $pdo->query("
    SELECT
        COUNT(*) AS total_chambres,
        SUM(CASE WHEN r.statut_reservation = 'occupé' AND r.etat_reservation IN ('actif','en cours') THEN 1 ELSE 0 END) AS chambres_occupees
    FROM chambres c
    LEFT JOIN reservations r ON c.code_chambre = r.code_chambre ");
$kpi_chambres = $stmt->fetch();
$total_chambres = $kpi_chambres['total_chambres'] ?? 0;
$chambres_occupees = $kpi_chambres['chambres_occupees'] ?? 0;

$stmt = $pdo->query("SELECT COALESCE(SUM(montant_ttc), 0) AS revenu_mois FROM factures WHERE YEAR(date_facture) = YEAR(CURDATE()) AND MONTH(date_facture) = MONTH(CURDATE()) AND etat_facture = 'payée' ");
$revenu_mois = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) AS reservations_actives FROM reservations WHERE etat_reservation IN ('actif', 'en cours') AND (statut_reservation = 'occupé' OR statut_reservation = 'réservé') ");
$reservations_actives = $stmt->fetchColumn();

$total_clients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();

$stmt = $pdo->query("SELECT type_chambre, COUNT(*) AS total, SUM(CASE WHEN r.statut_reservation = 'occupé' AND r.etat_reservation IN ('actif','en cours') THEN 1 ELSE 0 END) AS occupe FROM chambres c LEFT JOIN reservations r ON c.code_chambre = r.code_chambre GROUP BY type_chambre ");
$occupation_data = $stmt->fetchAll();

$revenus_labels = []; 
$revenus_values = [];
$months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
for ($i = 5; $i >= 0; $i--) {
    $date = new DateTime("first day of -$i month");
    $mois = $date->format('Y-m');
    $label = $months[$date->format('n') - 1];
    $revenus_labels[] = $label;
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant_ttc), 0) FROM factures WHERE DATE_FORMAT(date_facture, '%Y-%m') = ? AND etat_facture = 'payée'");
    $stmt->execute([$mois]);
    $revenus_values[] = (float)$stmt->fetchColumn();
}

$stmt = $pdo->query("SELECT statut_reservation, COUNT(*) AS count FROM reservations WHERE etat_reservation IN ('actif', 'en cours') GROUP BY statut_reservation ");
$statuts = ['libre' => 0, 'occupé' => 0, 'réservé' => 0];
foreach ($stmt as $row) { 
    $statuts[$row['statut_reservation']] = (int)$row['count']; 
}

$stmt = $pdo->query("SELECT nationalite_client, COUNT(*) AS count FROM clients GROUP BY nationalite_client ORDER BY count DESC LIMIT 5 ");
$nationalites = $stmt->fetchAll();

$stmt = $pdo->query("SELECT r.numero_reservation, cl.nom_prenom_client, ch.nom_chambre, r.date_debut, r.date_fin, r.montant_reservation, r.statut_reservation FROM reservations r JOIN clients cl ON r.code_client = cl.code_client JOIN chambres ch ON r.code_chambre = ch.code_chambre WHERE r.etat_reservation IN ('actif', 'en cours') ORDER BY r.date_reservation DESC LIMIT 10 ");
$reservations_recentes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Tableau de Bord</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .small-box { border-radius: 0.75rem; }
        .small-box .icon { font-size: 2.5rem; opacity: 0.3; }
        .chart-container { height: 300px; }
        .badge { font-size: 0.85em; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1>Tableau de Bord</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- KPI Cards -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner"><h3><?= $chambres_occupees ?></h3><p>Chambres Occupées</p></div>
                            <div class="icon"><i class="fas fa-bed"></i></div>
                            <a href="#" class="small-box-footer">sur <?= $total_chambres ?> totales <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner"><h3><?= number_format($revenu_mois, 0, ',', ' ') ?></h3><p>Revenus du Mois</p></div>
                            <div class="icon"><i class="fas fa-euro-sign"></i></div>
                            <a href="#" class="small-box-footer">FCFA <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner"><h3><?= $reservations_actives ?></h3><p>Réservations Actives</p></div>
                            <div class="icon"><i class="fas fa-calendar-check"></i></div>
                            <a href="#" class="small-box-footer">En cours <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner"><h3><?= $total_clients ?></h3><p>Clients Total</p></div>
                            <div class="icon"><i class="fas fa-users"></i></div>
                            <a href="#" class="small-box-footer">Inscrits <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Graphiques -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Taux d'Occupation par Type</h3></div>
                            <div class="card-body"><canvas id="occupancyChart" class="chart-container"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Revenus Mensuels (6 mois)</h3></div>
                            <div class="card-body"><canvas id="revenueChart" class="chart-container"></canvas></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Statut des Réservations</h3></div>
                            <div class="card-body"><canvas id="statusChart" class="chart-container"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Top 5 Nationalités</h3></div>
                            <div class="card-body"><canvas id="nationalityChart" class="chart-container"></canvas></div>
                        </div>
                    </div>
                </div>

                <!-- Dernières réservations -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Dernières Réservations (10)</h3></div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>N° Réservation</th><th>Client</th><th>Chambre</th><th>Début</th><th>Fin</th><th>Montant</th><th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reservations_recentes as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['numero_reservation']) ?></td>
                                            <td><?= htmlspecialchars($r['nom_prenom_client']) ?></td>
                                            <td><?= htmlspecialchars($r['nom_chambre']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($r['date_debut'])) ?></td>
                                            <td><?= date('d/m/Y', strtotime($r['date_fin'])) ?></td>
                                            <td><?= number_format($r['montant_reservation'], 0, ',', ' ') ?> FCFA</td>
                                            <td>
                                                <span class="badge bg-<?= $r['statut_reservation']==='occupé' ? 'danger' : ($r['statut_reservation']==='réservé' ? 'warning' : 'success') ?>">
                                                    <?= ucfirst($r['statut_reservation']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
// Graphiques Chart.js
const occupationData = <?= json_encode(array_map(fn($row) => ['type' => $row['type_chambre'], 'taux' => $row['total'] > 0 ? round($row['occupe'] / $row['total'] * 100, 1) : 0 ], $occupation_data)) ?>;
const revenusData = <?= json_encode($revenus_values) ?>;
const moisLabels = <?= json_encode($revenus_labels) ?>;
const statutsData = <?= json_encode([$statuts['libre'], $statuts['occupé'], $statuts['réservé']]) ?>;
const nationalitesData = <?= json_encode(['labels' => array_column($nationalites, 'nationalite_client'), 'values' => array_column($nationalites, 'count') ]) ?>;

new Chart(document.getElementById('occupancyChart'), { type: 'doughnut', data: { labels: occupationData.map(d => d.type), datasets: [{ data: occupationData.map(d => d.taux), backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF'] }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
new Chart(document.getElementById('revenueChart'), { type: 'line', data: { labels: moisLabels, datasets: [{ label: 'Revenus TTC (FCFA)', data: revenusData, borderColor: '#28a745', backgroundColor: 'rgba(40,167,69,0.1)', fill: true, tension: 0.4 }] }, options: { responsive: true } });
new Chart(document.getElementById('statusChart'), { type: 'pie', data: { labels: ['Libre','Occupé','Réservé'], datasets: [{ data: statutsData, backgroundColor: ['#17a2b8','#dc3545','#ffc107'] }] }, options: { responsive: true } });
new Chart(document.getElementById('nationalityChart'), { type: 'bar', data: { labels: nationalitesData.labels, datasets: [{ label: 'Clients', data: nationalitesData.values, backgroundColor: '#6f42c1' }] }, options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } } });
</script>

<!-- MODALE PROFIL -->
<div class="modal fade" id="profilModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Mon Profil</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="profil" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <img src="<?= userPhoto() ?>"
                                 id="photoPreview" class="rounded-circle shadow-lg border border-4 border-white"
                                 style="width: 130px; height: 130px; object-fit: cover;" alt="Photo de profil">
                            <label for="photo" class="btn btn-success rounded-circle position-absolute bottom-0 end-0 d-flex align-items-center justify-content-center"
                                   style="width: 44px; height: 44px; cursor: pointer;">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" name="photo" id="photo" accept="image/*" class="d-none" onchange="previewImage(this)">
                        </div>
                        <h5 class="mt-3 mb-1 fw-bold"><?= htmlspecialchars($_SESSION['nom_prenom'] ?? '') ?></h5>
                        <span class="badge bg-success fs-6"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></span>
                    </div>
                    <div class="row g-3">
                        <?php
                        $nomComplet = $_SESSION['nom_prenom'] ?? '';
                        $parts = explode(' ', $nomComplet, 2);
                        $prenom = $parts[0] ?? '';
                        $nom = $parts[1] ?? '';
                        ?>
                        <div class="col-md-6"><label class="form-label fw-semibold">Prénom</label><input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($prenom) ?>" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Nom</label><input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($nom) ?>" required></div>
                        
                        <div class="col-md-6"><label class="form-label fw-semibold">Téléphone</label><input type="tel" name="telephone" class="form-control" value="<?= htmlspecialchars($_SESSION['telephone'] ?? '') ?>"></div>
                    </div>
                    <hr class="my-4">
                    <a href="javascript:void(0)" onclick="document.getElementById('pwdSection').classList.toggle('d-none')" class="text-primary fw-medium">
                        Changer le mot de passe
                    </a>
                    <div id="pwdSection" class="d-none mt-3">
                        <div class="row g-3">
                            <div class="col-md-6"><input type="password" name="password" class="form-control" placeholder="Nouveau mot de passe"></div>
                            <div class="col-md-6"><input type="password" name="password_confirm" class="form-control" placeholder="Confirmer"></div>
                        </div>
                        <small class="text-muted">Laissez vide pour conserver l'ancien mot de passe</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Messages -->
<?php if (isset($_SESSION['success'])): ?>
<div class="position-fixed top-50 start-50 translate-middle" style="z-index:9999;">
    <div class="card border-0 shadow-lg animate__animated animate__zoomIn animate__faster">
        <div class="card-body bg-success text-white text-center py-5 px-5 rounded-4">
            <i class="fas fa-check-circle fa-4x mb-3 animate__animated animate__bounce"></i>
            <h3 class="fw-bold mb-2">Félicitations !</h3>
            <p class="fs-5 mb-0"><?= htmlspecialchars($_SESSION['success']) ?></p>
        </div>
    </div>
</div>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="position-fixed top-0 end-0 p-4" style="z-index:9999;">
    <div class="alert alert-danger alert-dismissible fade show shadow-lg border-0">
        <strong>Erreur :</strong> <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php unset($_SESSION['error']); endif; ?>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('photoPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
setTimeout(() => {
    const successCard = document.querySelector('.card.border-0.shadow-lg');
    if (successCard) {
        successCard.classList.remove('animate__zoomIn');
        successCard.classList.add('animate__zoomOut');
        setTimeout(() => successCard.closest('.position-fixed')?.remove(), 600);
    }
}, 3000);
</script>

<script>
setTimeout(() => location.reload(), 120000);
</script>
</body>
</html>