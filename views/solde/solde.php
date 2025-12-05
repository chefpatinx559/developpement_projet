<?php
session_start();
require "database/database.php";


$user_id = $_SESSION['utilisateur_id'];

$stmt = $pdo->prepare("SELECT solde FROM utilisateurs WHERE utilisateur_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$solde = $user['solde'] ?? 0;

$message = $_SESSION['message_success'] ?? '';
unset($_SESSION['message_success']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mon Solde</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --wave-blue: #1E90FF;
      --wave-dark: #1873CC;
      --text: #ffffff;
      --card: rgba(255,255,255,0.95);
      --shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background: linear-gradient(135deg, var(--wave-blue) 0%, var(--wave-dark) 100%);
      min-height: 100vh;
      color: white;
      padding: 20px 0;
    }
    .container {
      max-width: 420px;
      margin: 0 auto;
      background: var(--card);
      border-radius: 28px;
      overflow: hidden;
      box-shadow: var(--shadow);
      backdrop-filter: blur(10px);
    }
    .header {
      background: transparent;
      padding: 40px 20px 20px;
      text-align: center;
    }
    .header h1 {
      font-size: 26px;
      font-weight: 700;
      color: var(--wave-dark);
    }

    .success-alert {
      margin: 20px;
      background: #d4edda;
      color: #155724;
      padding: 16px;
      border-radius: 16px;
      text-align: center;
      font-weight: 600;
      font-size: 15px;
      border-left: 6px solid #28a745;
    }

    .balance-card {
      margin: 0 20px 30px;
      background: white;
      border-radius: 20px;
      padding: 28px 20px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(30,144,255,0.2);
      border: 1px solid rgba(30,144,255,0.15);
    }
    .balance-card p {
      color: #666;
      font-size: 14px;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 1.5px;
    }
    .balance-card h2 {
      font-size: 44px;
      font-weight: 700;
      color: var(--wave-dark);
      margin: 0;
    }
    .balance-card .currency {
      font-size: 20px;
      color: #888;
      margin-left: 6px;
    }

    .actions {
      padding: 20px;
      display: flex;
      gap: 18px;
      justify-content: center;
    }
    .btn {
      flex: 1;
      max-width: 155px;
      background: var(--wave-blue);
      color: white;
      border: none;
      padding: 16px;
      border-radius: 16px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      box-shadow: 0 6px 20px rgba(30,144,255,0.3);
    }
    .btn:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 30px rgba(30,144,255,0.4);
    }
    .btn.withdraw {
      background: #ff5722;
      box-shadow: 0 6px 20px rgba(255,87,34,0.3);
    }
    .emoji { font-size: 24px; }

    .footer-btn {
      margin: 20px;
      padding: 16px;
      background: rgba(0,0,0,0.08);
      color: #333;
      border: none;
      border-radius: 16px;
      width: calc(100% - 40px);
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
    }
    .footer-btn:hover { background: rgba(0,0,0,0.15); }
  </style>
</head>
<body>

  <div class="container">

    <div class="header">
      <h1>Mon Compte</h1>
    </div>

    <?php if ($message): ?>
      <div class="success-alert">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <div class="balance-card">
      <p>Solde Principal</p>
      <h2><?= number_format($solde, 0, '', ' ') ?><span class="currency">FCFA</span></h2>
    </div>

    <!-- ACTIONS : Dépôt + Retrait uniquement -->
    <div class="actions">
      <a href="paiement.php">
        <button class="btn">
          <span class="emoji">Deposit</span>
          <span>Dépôt</span>
        </button>
      </a>
      <button class="btn withdraw">
        <span class="emoji">Withdrawal</span>
        <span>Retrait</span>
      </button>
    </div>

    <button class="footer-btn" onclick="window.location.href='#'">
      ← Retour à l'accueil
    </button>

  </div>
</body>
</html>