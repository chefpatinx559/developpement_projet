<?php
//session_start();
require "database/database.php";

// Protection
//if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Administrateur', 'Superviseur'])) {
   // header("Location: login.php");
   // exit;
//}


$user_name = $_SESSION['nom_prenom'] ?? "Utilisateur";

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

// Récupération des logs
$sql = "SELECT * 
        FROM audits ";
        

$stmt = $pdo->prepare($sql);
$stmt->execute();
$audits = $stmt->fetchAll();

// Total pour pagination
$total = $pdo->query("SELECT COUNT(*) FROM audits")->fetchColumn();
$totalPages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Journal d'Audit</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .badge-etat { font-size: 0.85em; padding: 0.4em 0.8em; border-radius: 0.375rem; }
        .highlight-user { background-color: #fff8e1 !important; }
        .detail-cell { max-width: 320px; word-wrap: break-word; }
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
                        <h1>Journal d'Audit</h1>
                        <p class="text-muted mb-0">Suivi complet des actions système</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">
                            Historique des actions (<?= $total ?> enregistrements)
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Utilisateur</th>
                                        <th>Module</th>
                                        <th>Action</th>
                                        <th>Détail</th>
                                        <th>État</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($audits)): ?>
                                        <tr><td colspan="7" class="text-center py-5 text-muted">Aucune action enregistrée</td></tr>
                                    <?php else: foreach ($audits as $a): ?>
                                        <tr <?= ($a['module'] === 'Utilisateurs') ? 'class="highlight-user"' : '' ?>>
                                            <td><?= date('d/m/Y', strtotime($a['date'])) ?></td>
                                            <td><?= date('H:i:s', strtotime($a['heure'])) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($a['nom_prenom'] ?? 'Inconnu') ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($a['utilisateur_id']) ?></small>
                                            </td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($a['module']) ?></span></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $a['action'] === 'Création' ? 'success' : 
                                                    ($a['action'] === 'Modification' ? 'warning' : 
                                                    ($a['action'] === 'Suppression' ? 'danger' : 'secondary')) 
                                                ?>">
                                                    <?= htmlspecialchars($a['action']) ?>
                                                </span>
                                            </td>
                                            <td class="detail-cell"><small><?= htmlspecialchars($a['detail']) ?></small></td>
                                            <td>
                                                <span class="badge bg-<?= $a['etat'] === 'Succès' ? 'success' : 'danger' ?>">
                                                    <?= $a['etat'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if ($totalPages > 1): ?>
                    <div class="card-footer clearfix">
                        <ul class="pagination pagination-sm m-0 float-right">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page-1 ?>">«</a>
                            </li>
                            <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page+1 ?>">»</a>
                            </li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>
</body>
</html>