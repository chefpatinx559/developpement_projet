<?php
if(isset($_POST['btn_envoyer']))
{

# Specify your API KEY
$api_key = "YOUR-API-KEY";

$checkout_params = [
    "amount" => $_POST['sai_montant'],
    "currency" => "XOF",
    "error_url" => "https://soutra.pro/api/echec",
    "success_url" => "https://soutra.pro/api/succes",
];

# Define the request options
$curlOptions = [
  CURLOPT_URL => "https://api.wave.com/v1/checkout/sessions",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 5,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => json_encode($checkout_params),
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer {$api_key}",
    "Content-Type: application/json"
  ],
];

# Execute the request and get a response
$curl = curl_init();
curl_setopt_array($curl, $curlOptions);
$response = curl_exec($curl);



    $checkout_session = json_decode($response);

    $wave_launch_url = $checkout_session->wave_launch_url;
    ?><script type='text/javascript'>document.location.replace('<?php echo $wave_launch_url; ?>');</script><?php

//$err = curl_error($curl);
/*

if (empty($response["wave_launch_url"])) {
  echo "cURL Error #:";
} else {
    # You can now decode the response and use the checkout session. Happy coding ;)
    $checkout_session = json_decode($response);

    # You can redirect the user by using the 'wave_launch_url' field.
    $wave_launch_url = $checkout_session["wave_launch_url"];
    //header('Location: $wave_launch_url');
    ?><script type='text/javascript'>document.location.replace('<?php echo $wave_launch_url; ?>');</script><?php
    
}
*/


curl_close($curl);


}
 ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Transfert d'argent - Version Claire</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      min-height: 100vh;
      font-family: 'Segoe UI', system-ui, sans-serif;
    }

    .card-transfer {
      background: white;
      border-radius: 1.5rem;
      box-shadow: 0 20px 40px rgba(0,0,0,0.08);
      border: none;
      overflow: hidden;
    }

    .card-header-custom {
      background: linear-gradient(90deg, #4361ee, #2dd4bf);
      color: white;
      padding: 2rem;
      text-align: center;
    }

    .form-control, .form-select {
      height: 58px;
      border-radius: 1rem;
      border: 1.5px solid #e2e8f0;
    }

    .form-control:focus, .form-select:focus {
      border-color: #4361ee;
      box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
    }

    .btn-send {
      background: linear-gradient(90deg, #4361ee, #2dd4bf);
      border: none;
      height: 62px;
      border-radius: 1.5rem;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s;
    }

    .btn-send:hover {
      transform: translateY(-4px);
      box-shadow: 0 15px 30px rgba(67, 97, 238, 0.3);
    }

    /* Style du sélecteur avec recherche */
    .iti { width: 100%; }
    .iti__country-list {
      max-height: 300px;
      overflow-y: auto;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      border-radius: 1rem;
    }
  </style>

  <!-- Bibliothèque légère pour le sélecteur de pays avec recherche (drapeaux + indicatifs) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/css/intlTelInput.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/js/intlTelInput.min.js"></script>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">

  <div class="card-transfer w-100" style="max-width: 440px;">
    <!-- En-tête colorée -->
    <div class="card-header-custom">
      <h2 class="mb-0 fw-bold">
        <i class="bi bi-send-fill me-2"></i>
        Envoyer de l'argent
      </h2>
      <p class="mb-0 opacity-90 small mt-2">Instantané • Sans frais cachés • 100% sécurisé</p>
    </div>

    <div class="card-body p-5">
      <form id="transferForm" action="" method="POST">
        <!-- Montant -->
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Montant à envoyer</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0 fs-4">XOF</span>
            <input
              type="number"
              step="0.01"
              class="form-control form-control-lg text-end fs-3 fw-bold border-start-0"
              placeholder="0.00"
              id="amount"
              required
              name="sai_montant"
            >
          </div>
        </div>

        <!-- Numéro de téléphone avec sélecteur complet -->
        <div class="mb-5">
          <label class="form-label fw-semibold text-dark">Numéro du destinataire</label>
          <input
            type="tel"
            id="phone"
            class="form-control form-control-lg"
            placeholder="Ex: 6 12 34 56 78"
            
          >
        </div>

        <!-- Bouton -->
        <button name="btn_envoyer" class="btn btn-send text-white w-100 shadow-lg">
          <i class="bi bi-send-fill me-2"></i>
          Envoyer l'argent maintenant
        </button>
      </form>

      <div class="text-center mt-4">
        <small class="text-muted">
          <i class="bi bi-shield-lock-fill text-success"></i>
          Paiements protégés • Chiffrement bancaire • RGPD
        </small>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Initialisation du sélecteur de pays complet avec recherche et drapeaux
    const input = document.querySelector("#phone");
    const iti = window.intlTelInput(input, {
      initialCountry: "ci",
      preferredCountries: ["fr", "be", "ch", "ma", "ci", "cm", "us", "gb"],
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/js/utils.js",
      separateDialCode: true,
      nationalMode: false,
      dropdownContainer: document.body,
      formatOnDisplay: true,
    });

    
  </script>
</body>
</html>