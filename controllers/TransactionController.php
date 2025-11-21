<?php 

class transaction
{

public function enregistrement() {
  

        /* Protection
        if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }

if(!empty($_SESSION['role'])){ if ($_SESSION['role'] != 'Superviseur'  and $_SESSION['role'] != 'Administrateur') {                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        }
        */

        include "views/transactions/enregistrement-transaction.php";
    }



    

    public function transaction_facture() {
  

        /* Protection
        if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }

if(!empty($_SESSION['role'])){ if ($_SESSION['role'] != 'Superviseur'  and $_SESSION['role'] != 'Administrateur') {                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        }
        */

        include "views/transactions/transaction-par-facture.php";
    }

     public function transaction_client() {
  

        /* Protection
        if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }

if(!empty($_SESSION['role'])){ if ($_SESSION['role'] != 'Superviseur'  and $_SESSION['role'] != 'Administrateur') {                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        }
        */

        include "views/transactions/transaction-par-client.php";
    }

    public function transaction_type() {
  

        /* Protection
        if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }

if(!empty($_SESSION['role'])){ if ($_SESSION['role'] != 'Superviseur'  and $_SESSION['role'] != 'Administrateur') {                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        }
        */

        include "views/transactions/transaction-par-type.php";
    }

    public function solde_client() {
  

        /* Protection
        if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }

if(!empty($_SESSION['role'])){ if ($_SESSION['role'] != 'Superviseur'  and $_SESSION['role'] != 'Administrateur') {                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        }
        */

        include "views/transactions/solde-client.php";
    }

     public function solde_caisse() {
  

        /* Protection
        if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }

if(!empty($_SESSION['role'])){ if ($_SESSION['role'] != 'Superviseur'  and $_SESSION['role'] != 'Administrateur') {                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        }
        */

        include "views/transactions/solde-caisse.php";
    }

public function impression() {
  

        /* Protection
        if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }

if(!empty($_SESSION['role'])){ if ($_SESSION['role'] != 'Superviseur'  and $_SESSION['role'] != 'Administrateur') {                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        }
        */

        include "views/transactions/imprimer_recu.php";
    }


    public function rapport() {
  

        /* Protection
        if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }

if(!empty($_SESSION['role'])){ if ($_SESSION['role'] != 'Superviseur'  and $_SESSION['role'] != 'Administrateur') {                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        }
        */

        include "views/transactions/rapport_transaction.php";
    }


public function liste() {
  

        /* Protection
        if (!isset($_SESSION['login']) and ! isset($_SESSION['mdp'])) {
            ?>
            <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
            <?php
        }

if(!empty($_SESSION['role'])){ if ($_SESSION['role'] != 'Superviseur'  and $_SESSION['role'] != 'Administrateur') {                session_destroy();
                ?>
                <script type='text/javascript'>document.location.replace('<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/deconnexion');</script>";
                <?php
            }
        } */

       include "views/transactions/liste-transaction.php";
    }


}





 ?>