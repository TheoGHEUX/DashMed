<?php
// On inclut juste le header (qui contient le <head> et la navbar)
include __DIR__ . '/partials/head.php';
?>
<body>
<?php include __DIR__ . '/partials/headerPublic.php'; ?>

<main>
    <section class="hero">
        <h1><?= htmlspecialchars($pageTitle ?? 'Accueil') ?></h1>
        <div class="cadre-hero">
            <!-- Vérifie que cette image existe bien dans public/assets/images -->
            <img src="/assets/images/dashboard.webp" alt="Tableau de bord" class="hero-bg" />

            <div class="hero-content">
                <p class="lead">
                    DashMed centralise les données de santé de vos patients.
                </p>
                <div class="cta">
                    <!-- Les liens doivent être relatifs à la racine du site -->
                    <a class="btn btn-primary" href="register">S'inscrire</a>
                    <a class="btn btn-ghost" href="login">Se connecter</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>