<?php
/**
 * Page publique d'accueil de l'application DashMed.
 * Présente le service, invite à l'inscription ou à la connexion.
 * Utilise les partials `head`, `header` et `footer` pour le rendu.
 *
 * Variables dynamiques attendues :
 * @var string $pageTitle       Titre de la page (optionnel)
 * @var string $pageDescription Description pour la balise meta (optionnel)
 * @var array  $pageStyles      URLs des feuilles de style spécifiques (optionnel)
 * @var array  $pageScripts     URLs des scripts JS spécifiques (optionnel)
 *
 * @package DashMed
 * @author FABRE Alexis, GHEUX Théo, JACOB Alexandre, TAHA CHAOUI Amir, UYSUN Ali
 */
?>
<!doctype html>
<html lang="fr">
<?php
// Variables dynamiques transmises depuis le contrôleur
$pageTitle = $pageTitle ?? "Accueil";
$pageDescription = $pageDescription ?? "Page d'accueil de DashMed : votre tableau de bord santé simple et moderne pour la médecine";
$pageStyles = $pageStyles ?? ["/assets/style/index.css"];
$pageScripts = ["/assets/script/header_responsive.js"];
include __DIR__ . '/partials/head.php';
?>
<body>
<?php include __DIR__ . '/partials/headerPublic.php'; ?>
<main>
    <section class="hero">
        <h1>Votre tableau de bord santé, simple et moderne</h1>
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
        <h1>Pourquoi choisir DashMed ?</h1>
        <div class="cadre-features">
            <img src="/assets/images/suivi.webp" alt="image representant un graphique de suivi" />
            <div class="suivi-content">
                <h2>Suivi clair</h2>
                <p>Des indicateurs lisibles et des graphiques pour comprendre vos mesures en un coup d'œil.</p>
            </div>
        </div>

        <div class="cadre-features">
            <img src="/assets/images/securite.webp" alt="image representant un cadenas sur un serveur" />
            <div class="securite-content">
                <h2>Sécurité</h2>
                <p>Vos données sont chiffrées et hébergées sur des serveurs conformes aux standards.</p>
            </div>
        </div>

        <div class="cadre-features">
            <img src="/assets/images/personnalisable.webp" alt="image de personnalisation" />
            <div class="personnalisable-content">
                <h2>Personnalisable</h2>
                <p>Adaptez vos tableaux, vos unités et vos objectifs selon votre pratique.</p>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>