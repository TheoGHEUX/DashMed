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
                <h3>Projet pédagogique</h3>
                <p>
                    DashMed est un projet étudiant développé dans un cadre éducatif.
                    Toutes les données affichées sont <strong>fictives et générées aléatoirement</strong>.
                    Ce site n'a aucune vocation médicale réelle et ne traite aucune donnée personnelle.
                </p>
                <p style="margin-top: 12px; font-size: 12px; font-style: italic;">
                    🎓 Réalisé par des étudiants — 2025-2026
                </p>
            </div>

            <div class="panel">
                <h3>Technologies & Sécurité</h3>
                <p>
                    Malgré son caractère pédagogique, DashMed implémente
                    les bonnes pratiques de développement web sécurisé :
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
                <p style="margin-top: 14px; font-size: 12px;">
                    <strong>Note importante :</strong> En production réelle avec de vraies données de santé,
                    un hébergement certifié HDS et un DPO seraient obligatoires (RGPD).
                    Ce projet respecte les bonnes pratiques mais reste dans un cadre uniquement éducatif.
                </p>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
