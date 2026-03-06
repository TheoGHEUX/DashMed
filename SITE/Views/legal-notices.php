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
                <h3>Politique de confidentialité</h3>
                <p>
                    DashMed s'engage à protéger les données personnelles et de santé
                    conformément au RGPD et aux réglementations applicables.
                    Nos traitements sont limités aux finalités de suivi médical
                    et d'amélioration des soins.
                </p>
                <p style="margin-top: 12px;">
                    <strong>Vos droits :</strong> accès, rectification, effacement,
                    portabilité et opposition. Contactez notre DPO pour toute demande.
                </p>
            </div>

            <div class="panel">
                <h3>Sécurité & Hébergement</h3>
                <p>
                    La plateforme DashMed respecte les normes de sécurité
                    les plus strictes pour les données de santé :
                </p>
                <ul>
                    <li>
                        <strong>Hébergement HDS</strong> — certifié Hébergeur de Données de Santé
                    </li>
                    <li>
                        <strong>Chiffrement</strong> — TLS/SSL en transit, chiffrement au repos
                    </li>
                    <li>
                        <strong>Authentification</strong> — politique de mots de passe renforcée,
                        limitation des tentatives
                    </li>
                    <li>
                        <strong>Traçabilité</strong> — journalisation complète des accès
                        et actions utilisateur
                    </li>
                </ul>
            </div>

            <div class="panel full long">
                <h3>Conditions d'utilisation</h3>
                <p>
                    L'accès à DashMed est réservé aux professionnels de santé
                    dûment habilités. Chaque utilisateur est responsable de
                    la confidentialité de ses identifiants.
                </p>
                <ul>
                    <li>
                        <strong>Cookies :</strong> PHPSESSID (session), theme_preference
                        et dashboard_layout (préférences utilisateur, 1 an). Aucun cookie
                        publicitaire ou de tracking tiers n'est utilisé.
                    </li>
                    <li>
                        <strong>Responsabilités :</strong> Les décisions médicales restent
                        sous la responsabilité exclusive du praticien. DashMed fournit
                        un outil de visualisation et ne se substitue pas au jugement clinique.
                    </li>
                    <li>
                        <strong>Disponibilité :</strong> Objectif de 99,5% hors maintenance.
                        Les interventions planifiées sont annoncées 48h à l'avance.
                    </li>
                    <li>
                        <strong>Contact :</strong> Pour toute question, contactez notre
                        équipe support ou notre Délégué à la Protection des Données (DPO).
                    </li>
                </ul>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
