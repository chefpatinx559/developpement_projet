<?php
class document
{
    public function enregistrement()
    {
        // === PROTECTION : Session requise ===
        /*if (!isset($_SESSION['login']) || !isset($_SESSION['mdp'])) {
            $this->redirectToDeconnexion();
            return;
        }

        // === PROTECTION : Rôle Superviseur ou Administrateur uniquement ===
        if (!empty($_SESSION['role']) && !in_array($_SESSION['role'], ['Superviseur', 'Administrateur'])) {
            session_destroy();
            $this->redirectToDeconnexion();
            return;
         }
         */

        // === Inclusion de la vue CRUD ===
        include "views/documents/enregistrement-document.php";
    }

    public function liste()
    {
        /* === PROTECTION : Session requise ===
        if (!isset($_SESSION['login']) || !isset($_SESSION['mdp'])) {
            $this->redirectToDeconnexion();
            return;
        }

        // === PROTECTION : Rôle Superviseur ou Administrateur uniquement ===
        if (!empty($_SESSION['role']) && !in_array($_SESSION['role'], ['Superviseur', 'Administrateur'])) {
            session_destroy();
            $this->redirectToDeconnexion();
            return;
        } */

        // === Inclusion de la vue Liste (optionnelle) ===
        include "views/documents/liste-document.php";
    }

    // === Redirection sécurisée vers déconnexion ===
    private function redirectToDeconnexion()
    {
        $base_url = $this->getBaseUrl();
        ?>
        <script type="text/javascript">
            document.location.replace('<?= $base_url ?>/utilisateur/deconnexion');
        </script>
        <?php
        exit;
    }

    // === Construction dynamique de l'URL de base (HTTPS/HTTP + dossier) ===
    private function getBaseUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $dir = dirname($_SERVER["PHP_SELF"]);

        // Supprime le dernier slash si présent
        $dir = ($dir === '/' || $dir === '\\') ? '' : rtrim($dir, '/\\');

        return "$protocol://$host$dir";
    }
}
<<<<<<< HEAD
?>
=======
?>
>>>>>>> 24653d20902f480a272f396807e06cb4679ae919
