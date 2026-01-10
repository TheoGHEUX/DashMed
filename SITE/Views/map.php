<?php
/**
 * Vue : Plan du site (Sitemap)
 *
 * Page listant les pages publiques de l'application DashMed de façon hiérarchique
 * pour faciliter la navigation et l'indexation.
 *
 * Variables attendues :
 * @var string $pageTitle               Titre de la page ( "Plan du site")
 * @var string $pageDescription         Meta description
 * @var array<int,string> $pageStyles   Styles spécifiques (["/assets/style/map.css"])
 * @var array<int,string> $pageScripts  Scripts spécifiques ( ["/assets/script/header_responsive.js"])
 *
 * @see \SITE\Views\partials\head.php
 * @see \SITE\Views\partials\headerPublic.php
 * @see \SITE\Views\partials\footer.php
 *
 * @package Views
 */

// Configuration des variables de template (valeurs par défaut)
$pageTitle       = $pageTitle ?? "Plan du site";
$pageDescription = $pageDescription ?? "Plan du site de DashMed";
$pageStyles      = $pageStyles ?? ["/assets/style/map.css"];
$pageScripts     = $pageScripts ?? ["/assets/script/header_responsive.js"];

include __DIR__ . '/partials/head.php';
?>
<!doctype html>
<html lang="fr">
<body>

<?php include __DIR__ . '/partials/headerPublic.php'; ?>

<main class="content">
    <div class="container">
        <h1>Plan du site</h1>
        <p class="muted">Toutes les pages disponibles sur DashMed.</p>

        <nav class="sitemap" aria-label="Plan du site">
            <ul class="level-1">
                <li>
                    <a href="/">Accueil</a>
                </li>

                <li>
                    <span>Espace utilisateur</span>
                    <ul class="level-2">
                        <li><a href="/register">Inscription</a></li>
                        <li><a href="/login">Connexion</a></li>
                        <li><a href="/forgotten-password">Mot de passe oublié</a></li>
                    </ul>
                </li>

                <li>
                    <span>Informations</span>
                    <ul class="level-2">
                        <li><a href="/mentions-legales">Mentions légales</a></li>
                        <li><a href="/map">Plan du site</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <div class="tips">
            Trouvez toutes les pages du site depuis ce tableau pour naviguer plus facilement !
        </div>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>