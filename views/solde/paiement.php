<?php 
session_start();
require "database/database.php";

$_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WAVE - Dépôt</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }

    body {
      background-color: #fff;
      padding-top: 60px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .container {
      max-width: 400px;
      width: 90%;
      margin: 0 auto;
      text-align: center;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 0;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }

    .header-title {
      font-size: 20px;
      font-weight: bold;
      color: #333;
    }

    .wave-logo {
      width: 100px;
      height: 100px;
      background-color: #1E90FF;
      border-radius: 10px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .wave-logo img {
      width: 70%;
      height: 70%;
      object-fit: contain;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .form-label {
      font-size: 16px;
      font-weight: bold;
      color: #333;
      text-align: center;
    }

    .form-input {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 16px;
      color: #333;
      outline: none;
      text-align: center;
    }

    .submit-button {
      width: 100%;
      padding: 15px;
      background-color: #1E90FF;
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 18px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .submit-button:hover {
      background-color: #1976D2;
    }

    .footer-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      height: 30px;
      background-color: black;
      z-index: 1000;
    }
  </style>
</head>
<body>

<div class="container">
  
  <div class="header">
    <div class="header-title">WAVE</div>
  </div>

  <div class="wave-logo">
    <img src="images.png" alt="Logo WAVE">
  </div>

  <form action="create_payment.php" method="POST">

    <div class="form-group">
      <label for="montant" class="form-label">Montant * :</label>
      <input name="montant" type="number" id="montant" class="form-input" placeholder="Entrez le montant" required />
    </div>

    <div class="form-group">
      <label for="phone" class="form-label">Téléphone * :</label>
      <input name="phone" type="tel" id="phone" class="form-input" placeholder="Entrez votre numéro" required />
    </div>

    <button type="submit" class="submit-button" name="valide_recharger">Valider</button>

  </form>
</div>

<div class="footer-bar"></div>

</body>
</html>
