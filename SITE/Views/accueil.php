<?php
/**
 * Fichier : accueil.php
 *
 * Page d'accueil utilisateur pour l'application DashMed.
 * Affiche les statistiques, les activités récentes et propose des actions rapides.
 * Sécurise l'accès via session utilisateur et token CSRF.
 * Utilise les partials pour le head et le footer.
 *
 * @package DashMed
 * @version 1.1
 * @author FABRE Alexis, GHEUX Théo, JACOB Alexandre, TAHA CHAOUI Amir, UYSUN Ali
 */

/**
 * Génère le token CSRF pour la sécurité des formulaires.
 * @var string $csrf_token
 */
$csrf_token = \Core\Csrf::token();

/**
 * Vérifie la présence de la session utilisateur.
 * Redirige vers la page de connexion si l'utilisateur n'est pas authentifié.
 */
if (empty($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

/**
 * Variables dynamiques pour le template de la page.
 *
 * @var string $pageTitle       Titre de la page (balise <title>)
 * @var string $pageDescription Description pour la balise <meta name="description">
 * @var array  $pageStyles      Liste des feuilles de style spécifiques à la page
 * @var array  $pageScripts     Liste des scripts spécifiques à la page
 */
$pageTitle = "Accueil";
$pageDescription = "Page d'accueil accessible une fois connecté, espace pour voir l'activité et les informations des médecins";
$pageStyles = [
        "/assets/style/accueil.css"
];
$pageScripts = [
        "/assets/script/header_responsive.js"
];

/**
 * Activités récentes affichées sur l'accueil.
 *
 * @var array $activites Chaque élément est un tableau associatif avec les clés :
 *                       - 'label' : string, description de l'activité
 *                       - 'date'  : string, date de l'activité au format JJ/MM/AAAA
 */
$activites = [
        ["label" => "Rdv avec Dr. Smith", "date" => "03/12/2025"],
        ["label" => "Résultats prise de sang", "date" => "02/12/2025"],
        ["label" => "Prescription médicaments", "date" => "01/12/2025"]
];
?>
<!doctype html>
<html lang="fr">
<?php include __DIR__ . '/partials/head.php'; ?>

<body>
<?php include __DIR__ . '/partials/headerPrivate.php'; ?>
<main>
    <div class="accueil-container">
        <!-- Phrase d'accroche et Dashboard -->
        <section class="dashboard-banner">
            <div class="banner-content">
                <h1>Bienvenue sur DashMed</h1>
                <p>Votre plateforme médicale pour une gestion hospitalière efficace et sécurisée</p>
                <a href="/dashboard" class="dashboard-card">
                    <div class="card-icon">📊</div>
                    <div class="card-text">
                        <h3>Tableau de bord</h3>
                        <span>Voir toutes mes données</span>
                    </div>
                    <div class="card-arrow">→</div>
                </a>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>