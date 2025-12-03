<?php
// config/dashboard.php ou ton fichier d'inclusion
$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";

// Fonction old_url améliorée et simplifiée
function old_url($path = '') {
    $base = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== 'off' ? 'https://' : 'http://') . 
            $_SERVER['HTTP_HOST'] . 
            rtrim(dirname($_SERVER["PHP_SELF"]), '/\\');
    return $base . '/' . ltrim($path, '/');
}

// Fonction pour détecter la page active
function isActive($path) {
    $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = '/' . trim($path, '/');
    $current = rtrim($current, '/') . '/';
    $path = rtrim($path, '/') . '/';
    return strpos($current, $path) !== false;
}

function activeClass($path, $class = 'active') {
    return isActive($path) ? $class : '';
}
?>

<!-- ==================== NAVBAR ==================== -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0 shadow-sm">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= old_url('index.php') ?>" class="nav-link fw-semibold">Accueil</a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                <img src="data:<?= $_SESSION['type_photo'] ?? 'image/jpeg'; ?>;base64,<?= base64_encode($_SESSION['photo'] ?? '') ?>"
                     class="user-image img-circle elevation-2 border border-white"
                     alt="User Image"
                     style="width: 38px; height: 38px; object-fit: cover;">
                <span class="d-none d-md-inline ms-2 fw-semibold"><?= htmlspecialchars($_SESSION['nom_prenom'] ?? 'Utilisateur') ?></span>
                <i class="fas fa-chevron-down ms-1 text-muted small d-none d-lg-inline"></i>
            </a>

            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow-lg border-0 mt-2" style="border-radius: 16px; min-width: 280px;">
                <li class="user-header bg-primary position-relative overflow-hidden" style="border-radius: 16px 16px 0 0; padding: 2.5rem 1.5rem;">
                    <div class="text-center">
                        <div class="position-relative d-inline-block">
                            <img src="data:<?= $_SESSION['type_photo'] ?? 'image/jpeg'; ?>;base64,<?= base64_encode($_SESSION['photo'] ?? '') ?>"
                                 class="img-circle elevation-4 border border-white border-4"
                                 alt="User Avatar"
                                 style="width: 90px; height: 90px; object-fit: cover;">
                            <div class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-white"
                                 style="width: 26px; height: 26px; box-shadow: 0 0 0 4px rgba(40,167,69,0.3);"></div>
                        </div>
                        <p class="text-white mt-3 mb-1 fw-bold fs-5"><?= htmlspecialchars($_SESSION['nom_prenom'] ?? 'Utilisateur') ?></p>
                        <p class="text-white opacity-90 mb-0"><small><?= htmlspecialchars($_SESSION['role'] ?? 'Membre') ?></small></p>
                    </div>
                </li>

                <li class="user-body bg-white" style="border-radius: 0 0 16px 16px;">
                    <div class="p-3">
                        <a href="#" class="d-block text-decoration-none py-3 px-3 rounded hover-bg-light text-dark">
                            <i class="fas fa-user-circle text-primary me-3"></i> Mon Profil
                        </a>
                        <a href="#" class="d-block text-decoration-none py-3 px-3 rounded hover-bg-light text-dark">
                            <i class="fas fa-cog text-muted me-3"></i> Paramètres
                        </a>
                        <hr class="my-2">
                        <a href="<?= old_url('/utilisateur/deconnexion') ?>" class="btn btn-danger btn-block text-white fw-bold mt-2">
                            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                        </a>
                    </div>
                    <div class="text-center py-2 bg-light small text-muted border-top">
                        Connecté le <?= date('d/m/Y à H:i') ?>
                    </div>
                </li>
            </ul>
        </li>
    </ul>
</nav>

<!-- ==================== SIDEBAR ==================== -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?= old_url('index.php') ?>" class="brand-link text-center py-3">
        <span class="brand-text font-weight-light fw-bold">Soutra+</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="data:<?= $_SESSION['type_photo'] ?? 'image/jpeg'; ?>;base64,<?= base64_encode($_SESSION['photo'] ?? '') ?>"
                     class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block text-white"><?= htmlspecialchars($_SESSION['nom_prenom'] ?? 'Utilisateur') ?></a>
                <span class="badge badge-success"><?= htmlspecialchars($_SESSION['role'] ?? 'Membre') ?></span>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <!-- TABLEAU DE BORD -->
                <li class="nav-item">
                    <a href="<?= old_url('/utilisateur/dashboard') ?>" class="nav-link <?= activeClass('/utilisateur/dashboard') ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Tableau de bord</p>
                    </a>
                </li>

                <!-- CHAMBRES -->
                <li class="nav-item <?= activeClass('/chambre/') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= activeClass('/chambre/') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-bed"></i>
                        <p>Chambres <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= old_url('/chambre/enregistrement') ?>" class="nav-link <?= activeClass('/chambre/enregistrement') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Enregistrement Chambres</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= old_url('/chambre/liste') ?>" class="nav-link <?= activeClass('/chambre/liste') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Liste des Chambres</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- RÉSERVATIONS -->
                <li class="nav-item <?= activeClass('/reservation/') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= activeClass('/reservation/') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-calendar-check"></i>
                        <p>Réservations <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= old_url('/reservation/enregistrement') ?>" class="nav-link <?= activeClass('/reservation/enregistrement') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Enregistrement Réservations</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= old_url('/reservation/reservation_par_hotel') ?>" class="nav-link <?= activeClass('/reservation/reservation_par_hotel') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Détails réservations</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= old_url('/reservation/chambre_occupee_hotel') ?>" class="nav-link <?= activeClass('/reservation/chambre_occupee_hotel') ?>">
                                <i class="far fa-circle nav-icon"></i><p>États des chambres</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= old_url('/reservation/liste_chambre_libre_hotel') ?>" class="nav-link <?= activeClass('/reservation/liste_chambre_libre_hotel') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Listes par type</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= old_url('/reservation/liste_reservation_facture') ?>" class="nav-link <?= activeClass('/reservation/liste_reservation_facture') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Factures réservations</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- CLIENTS -->
                <li class="nav-item <?= activeClass('/clients/') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= activeClass('/clients/') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-user"></i>
                        <p>Clients <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= old_url('/clients/enregistrement') ?>" class="nav-link <?= activeClass('/clients/enregistrement') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Enregistrement Clients</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= old_url('/clients/liste') ?>" class="nav-link <?= activeClass('/clients/liste') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Liste Clients</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- DOCUMENTS -->
                <li class="nav-item <?= activeClass('/document/') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= activeClass('/document/') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>Documents <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= old_url('/document/enregistrement') ?>" class="nav-link <?= activeClass('/document/enregistrement') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Enregistrement Documents</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- FACTURES -->
                <li class="nav-item <?= activeClass('/facture/') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= activeClass('/facture/') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                        <p>Factures <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= old_url('/facture/enregistrement') ?>" class="nav-link <?= activeClass('/facture/enregistrement') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Enregistrement Factures</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- UTILISATEURS -->
                <li class="nav-item <?= (activeClass('/utilisateur/') && !isActive('/utilisateur/dashboard') && !isActive('/utilisateur/deconnexion')) ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= (activeClass('/utilisateur/') && !isActive('/utilisateur/dashboard') && !isActive('/utilisateur/deconnexion')) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Utilisateurs <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= old_url('/utilisateur/enregistrement') ?>" class="nav-link <?= activeClass('/utilisateur/enregistrement') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Liste utilisateurs</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- TRANSACTIONS -->
                <li class="nav-item <?= activeClass('/transaction/') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= activeClass('/transaction/') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-exchange-alt"></i>
                        <p>Transactions <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= old_url('/transaction/enregistrement') ?>" class="nav-link <?= activeClass('/transaction/enregistrement') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Enregistrement Transactions</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= old_url('/transaction/transaction_facture') ?>" class="nav-link <?= activeClass('/transaction/transaction_facture') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Transactions globales</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= old_url('/transaction/solde_caisse') ?>" class="nav-link <?= activeClass('/transaction/solde_caisse') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Soldes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= old_url('/transaction/rapport') ?>" class="nav-link <?= activeClass('/transaction/rapport') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Rapports transactions</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- ALBUMS -->
                <li class="nav-item <?= activeClass('/album/') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= activeClass('/album/') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-images"></i>
                        <p>Albums <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= old_url('/album/enregistrement') ?>" class="nav-link <?= activeClass('/album/enregistrement') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Enregistrement Albums</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- HÔTELS -->
                <li class="nav-item <?= activeClass('/hotel/') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= activeClass('/hotel/') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-hotel"></i>
                        <p>Hôtels <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= old_url('/hotel/enregistrement') ?>" class="nav-link <?= activeClass('/hotel/enregistrement') ?>">
                                <i class="far fa-circle nav-icon"></i><p>Enregistrement Hôtels</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- PARAMÈTRES -->
                <li class="nav-header text-uppercase text-warning">Paramètres</li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>Configuration</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>