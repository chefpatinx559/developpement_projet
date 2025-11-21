<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Hotels - Bienvenue</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Segoe UI', Arial, sans-serif; color: white; overflow: hidden; }

        .slider {
            position: relative;
            height: 100vh;
            overflow: hidden;
        }
        .slide {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
        }
        .slide.active { opacity: 1; }

        .overlay {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.45);
        }

        .content {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 10;
            width: 90%;
            max-width: 1000px;
        }
        .content h1 {
            font-size: 4.5rem;
            margin-bottom: 1rem;
            text-shadow: 0 3px 10px rgba(0,0,0,0.6);
        }
        .content p {
            font-size: 1.5rem;
            margin-bottom: 2.5rem;
            text-shadow: 0 2px 8px rgba(0,0,0,0.6);
        }

        .btn {
            display: inline-block;
            padding: 16px 48px;
            background: #c9a96e;
            color: white;
            text-decoration: none;
            font-size: 1.3rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        .btn:hover {
            background: #d4b67a;
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.4);
        }

        @media (max-width: 768px) {
            .content h1 { font-size: 3rem; }
            .content p { font-size: 1.2rem; }
            .btn { padding: 14px 36px; font-size: 1.1rem; }
        }
    </style>
</head>
<body>

<div class="slider">
    <div class="slide active" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&q=85');"></div>
    <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-4.0.3&auto=format&fit=crop&q=85');"></div>
    <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1571896349842-33c0aa41f940?ixlib=rb-4.0.3&auto=format&fit=crop&q=85');"></div>
    <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1582714636030-71b00e1a9e3?ixlib=rb-4.0.3&auto=format&fit=crop&q=85');"></div>
    <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1568084680786-a84f91d1153c?ixlib=rb-4.0.3&auto=format&fit=crop&q=85');"></div>

    <div class="overlay"></div>

    <div class="content">
        <h1>Bienvenue dans le Luxe Absolu</h1>
        <p>Découvrez les plus beaux hôtels de Côte d'Ivoire</p>
        <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/index.php" class="btn">
            Réserver maintenant
        </a>
    </div>
</div>

<script>
    const PAGE_CONNEXION = "<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/index.php";
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');

    // Fonction pour passer à la slide suivante
    function nextSlide() {
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
    }

    // Change d'image toutes les 5 secondes
    const slideInterval = setInterval(nextSlide, 5000);

    // Redirection automatique après 15 secondes = 3 photos complètes (0s → 5s → 10s → 15s)
    const autoRedirect = setTimeout(() => {
        clearInterval(slideInterval); // arrête le slider
        window.location.href = PAGE_CONNEXION;
    }, 15000); // 15 secondes exactement

    // Si l'utilisateur clique sur le bouton → annule tout et redirige tout de suite
    document.querySelector('.btn').addEventListener('click', function(e) {
        clearInterval(slideInterval);
        clearTimeout(autoRedirect);
        // Le href du lien fait déjà la redirection
    });
</script>

</body>
</html>