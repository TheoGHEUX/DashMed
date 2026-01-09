<?php
/**
 * Vue : Page d'accueil authentifiée
 *
 * Page d'accueil pour utilisateurs connectés avec bannière de bienvenue et
 * lien vers le tableau de bord.
 *
 * @package Views
 */

// SÉCURITÉ : session & CSRF
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// génère le token CSRF si la classe existe (silencieux sinon)
$csrf_token = function_exists('\\Core\\Csrf::token') ? \Core\Csrf::token() : '';

// contrôle d'accès : utilisateur requis
if (empty($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

// CONFIGURATION : variables du template
$pageTitle       = $pageTitle ?? "Accueil";
$pageDescription = $pageDescription ?? "Page d'accueil accessible une fois connecté";
$pageStyles      = $pageStyles ?? ["/assets/style/accueil.css"];
$pageScripts     = $pageScripts ?? ["/assets/script/header_responsive.js"];

include __DIR__ . '/partials/head.php';
?>
<body>
<?php include __DIR__ . '/partials/headerPrivate.php'; ?>

<main>
    <div class="accueil-container">
        <section class="dashboard-banner">
            <div class="banner-content">
                <h1 class="welcome-title" style="color: #0fb0c0 !important;">
                    <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
                </h1>
                <p>Votre plateforme médicale pour une gestion hospitalière efficace et sécurisée</p>

                <a href="/dashboard" class="dashboard-card" role="link" aria-label="Accéder au tableau de bord">
                    <div class="card-icon" aria-hidden="true">📊</div>
                    <div class="card-text">
                        <h3>Tableau de bord</h3>
                        <span>Voir toutes mes données</span>
                    </div>
                    <div class="card-arrow" aria-hidden="true">→</div>
                </a>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>