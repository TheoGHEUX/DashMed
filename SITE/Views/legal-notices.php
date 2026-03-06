<?php

/**
 * Mentions légales
 *
 * Affiche les informations sur la gestion des données (RGPD).
 *
 * Présente les conditions d'utilisation et droits des utilisateurs.
 *
 * Variables attendues :
 *  - $pageTitle       (string)  Titre de la page
 *  - $pageDescription (string)  Meta description
 *  - $pageStyles      (array)   Styles spécifiques
 *  - $pageScripts     (array)   Scripts spécifiques
 *
 * @package Views
 */

$pageTitle       = $pageTitle ?? "Mentions légales";
$pageDescription = $pageDescription ?? "Toutes les mentions légales de DashMed";
$pageStyles      = $pageStyles ?? ["/assets/style/legal_notices.css"];
$pageScripts     = $pageScripts ?? [];

include __DIR__ . '/partials/head.php';
?>
<body>
<?php include __DIR__ . '/partials/headerPublic.php'; ?>

<main class="content">
    <div class="container">
        <h1>Mentions légales</h1>
        <p class="muted">Dernière mise à jour : 6 mars 2026</p>

        <section class="legal-grid">
            <div class="panel short">
                <h3>À propos de DashMed</h3>
                <p>
                    DashMed est une plateforme de démonstration conçue pour illustrer
                    les bonnes pratiques de développement d'applications médicales.
                    Les données affichées sont <strong>exclusivement fictives</strong>
                    et générées pour des besoins de présentation.
                </p>
                <p style="margin-top: 12px; font-size: 11px; color: var(--text-secondary, #6f7580);">
                    Développé dans un cadre pédagogique — 2025-2026
                </p>
            </div>

            <div class="panel">
                <h3>Technologies & Sécurité</h3>
                <p>
                    DashMed est développé selon les standards de l'industrie
                    et implémente les meilleures pratiques de sécurité :
                </p>
                <ul>
                    <li>
                        <strong>Backend</strong> — PHP 8.1+ avec architecture MVC
                    </li>
                    <li>
                        <strong>Sécurité</strong> — Protection CSRF, requêtes préparées,
                        hashage bcrypt, limitation brute force
                    </li>
                    <li>
                        <strong>Frontend</strong> — Chart.js pour visualisation,
                        Leaflet.js pour cartographie
                    </li>
                    <li>
                        <strong>Hébergement</strong> — XAMPP local (dev uniquement)
                    </li>
                </ul>
            </div>

            <div class="panel full long">
                <h3>Cookies & Confidentialité</h3>
                <p>
                    Le site utilise uniquement des cookies techniques essentiels
                    au fonctionnement. Aucun tracking publicitaire ou analytique.
                </p>
                <ul>
                    <li>
                        <strong>PHPSESSID</strong> — Gestion de session (durée : session navigateur)
                    </li>
                    <li>
                        <strong>theme_preference</strong> — Mémorisation du mode sombre/clair (1 an)
                    </li>
                    <li>
                        <strong>dashboard_layout</strong> — Sauvegarde de l'agencement des graphiques (1 an)
                    </li>
                </ul>
                <p style="margin-top: 14px; font-size: 11px; color: var(--text-secondary, #6f7580);">
                    <strong>Note :</strong> Cette plateforme de démonstration utilise des données fictives.
                    En environnement de production avec données réelles, un hébergement certifié HDS
                    et la désignation d'un DPO seraient requis conformément au RGPD.
                </p>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
