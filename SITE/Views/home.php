<?php
/**
 * Fichier : home.php
 * Page d'accueil de l'application DashMed.
 *
 * Présente le service et ses avantages, invite à l'inscription ou à la connexion.
 * Utilise la structure dynamique avec head, header et footer inclus.
 *
 * Variables dynamiques attendues :
 * - $pageTitle       : string   - Titre de la page
 * - $pageDescription : string   - Description pour les métadonnées
 * - $pageStyles      : array    - Styles CSS spécifiques
 * - $pageScripts     : array    - Scripts JS spécifiques
 *
 * @package DashMed
 * @version 1.0
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