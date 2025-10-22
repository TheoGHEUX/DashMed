<!--
    Fichier : map.php

    Page du plan du site de l'application DashMed.
    Liste l'ensemble des pages accessibles pour faciliter la navigation.
    La structure inclut le header (dupliqué ici), la liste des liens, et le pied de page inclus via partial.

    @package DashMed
    @author  FABRE Alexis, GHEUX Théo, JACOB Alexandre, TAHA CHAOUI Amir, UYSUN Ali
    @version 1.0
-->
<!doctype html>
<html lang="fr">
<?php
$pageTitle = "Plan du site";
$pageDescription = "Plan du site de DashMed";
$pageStyles = ["/assets/style/map.css"];
$pageScripts = ["/assets/script/header_responsive.js"];
include __DIR__ . '/partials/head.php';
?>
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
                        <li><a href="/legal-notices">Mentions légales</a></li>
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
