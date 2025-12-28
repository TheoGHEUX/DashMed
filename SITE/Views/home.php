<?php
/**
 * Vue : Page d'accueil publique (Home)
 *
 * Vitrine publique de l'application DashMed. Présente le hero, les CTA et les
 * principales fonctionnalités pour inviter à l'inscription ou la connexion.
 *
 * @package    DashMed
 * @subpackage Views
 * @category   Frontend
 * @version    1.1
 * @since      1.0
 *
 * Variables attendues :
 * @var string $pageTitle               Titre de la page (défaut : "Accueil")
 * @var string $pageDescription         Meta description
 * @var array<int,string> $pageStyles   Styles spécifiques (["/assets/style/index.css"])
 * @var array<int,string> $pageScripts  Scripts spécifiques (["/assets/script/header_responsive.js"])
 *
 * @see \SITE\Views\partials\head.php
 * @see \SITE\Views\partials\headerPublic.php
 * @see \SITE\Views\partials\footer.php
 */

$pageTitle       = $pageTitle ?? "Accueil";
$pageDescription = $pageDescription
    ?? "Page d'accueil de DashMed : votre tableau de bord santé simple et moderne pour la médecine";
$pageStyles      = $pageStyles ?? ["/assets/style/index.css"];
$pageScripts     = $pageScripts ?? ["/assets/script/header_responsive.js"];

include __DIR__ . '/partials/head.php';
?>
<body>
<?php include __DIR__ . '/partials/headerPublic.php'; ?>
<main>
    <section class="hero">
        <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="cadre-hero">
            <img src="/assets/images/dashboard.webp" alt="Tableau de bord médical moderne" class="hero-bg" />
            <div class="hero-content">
                <p class="lead">
                    DashMed centralise vos données de santé pour mieux suivre vos objectifs,
                    visualiser vos progrès et rester informé.
                </p>
                <div class="cta">
                    <a class="btn btn-primary" href="/register">S'inscrire</a>
                    <a class="btn btn-ghost" href="/login">Se connecter</a>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <h2>Pourquoi choisir DashMed ?</h2>

        <div class="cadre-features">
            <img src="/assets/images/suivi.webp" alt="Graphique de suivi" />
            <div class="feature-content">
                <h3>Suivi clair</h3>
                <p>Des indicateurs lisibles et des graphiques pour comprendre vos mesures en un coup d'œil.</p>
            </div>
        </div>

        <div class="cadre-features">
            <img src="/assets/images/securite.webp" alt="Cadenas sur un serveur" />
            <div class="feature-content">
                <h3>Sécurité</h3>
                <p>Vos données sont chiffrées et hébergées sur des serveurs conformes aux standards.</p>
            </div>
        </div>

        <div class="cadre-features">
            <img src="/assets/images/personnalisable.webp" alt="Personnalisation" />
            <div class="feature-content">
                <h3>Personnalisable</h3>
                <p>Adaptez vos tableaux, vos unités et vos objectifs selon votre pratique.</p>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>