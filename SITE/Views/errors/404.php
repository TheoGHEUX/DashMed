<?php
/**
 * Vue : Page d'erreur 404 (Page non trouvée)
 *
 * Page simple affichant le code 404, un message et deux actions (accueil, dashboard).
 *
 * Variables optionnelles (définies par le contrôleur) :
 * @var string $pageTitle
 * @var string $pageDescription
 * @var array  $pageStyles
 * @var array  $pageScripts
 */

http_response_code(404);

// Valeurs par défaut pour le partial head
$pageTitle       = $pageTitle ?? "Page non trouvée - Erreur 404";
$pageDescription = $pageDescription ?? "La page que vous recherchez n'existe pas.";
$pageStyles      = $pageStyles ?? ["/assets/style/404.css"];
$pageScripts     = $pageScripts ?? [];

include __DIR__ . '/../partials/head.php';
?>
<body>
<?php include __DIR__ . '/../partials/headerPublic.php'; ?>

<main>
    <div class="error-container">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1 class="error-title">Page non trouvée</h1>
            <p class="error-message">
                Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
            </p>
            <div class="error-actions">
                <a href="/" class="btn btn-primary">Retour à l'accueil</a>
                <a href="/dashboard" class="btn btn-secondary">Tableau de bord</a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>