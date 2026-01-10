<?php

/**
 * Vue : Vérification d'email
 *
 * Affiche le résultat de la vérification d'adresse email (succès, erreurs ou en cours).
 *
 * Variables attendues :
 *  - $pageTitle (string)       Titre de la page
 *  - $pageDescription (string) Meta description
 *  - $pageStyles (array)       Styles spécifiques
 *  - $pageScripts (array)      Scripts spécifiques
 *  - $success (string|null)    Message de succès (optionnel)
 *  - $errors (array|null)      Liste d'erreurs (optionnel)
 *
 * @package Views
 */

$pageTitle = $pageTitle ?? "Vérification d'email";
$pageDescription = $pageDescription ?? "Vérification de votre adresse email";
$pageStyles = $pageStyles ?? ["/assets/style/authentication.css"];
$pageScripts = $pageScripts ?? [];

include __DIR__ . '/../partials/head.php';
?>
<body>
<?php include __DIR__ . '/../partials/headerPublic.php'; ?>

<main class="body-main-container">
    <section class="auth-section">
        <div class="auth-container">
            <h1 class="auth-title">Vérification d'email</h1>

            <?php if (!empty($success)) : ?>
                <div class="alert alert-success" role="status" aria-live="polite">
                    <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                </div>

                <div class="auth-links">
                    <a href="/login"
                       class="btn-primary"
                       style="display: inline-block; padding: 12px 24px;
                              text-decoration: none; border-radius: 5px;">
                        Se connecter
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)) : ?>
                <div class="alert alert-error" role="alert">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error) : ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="auth-links" style="margin-top: 20px;">
                    <p>Vous n'avez pas reçu l'email de vérification ?</p>
                    <a href="/resend-verification" class="link-strong">Renvoyer l'email de vérification</a>
                </div>
            <?php endif; ?>

            <?php if (empty($success) && empty($errors)) : ?>
                <div class="alert alert-info" role="status" aria-live="polite">
                    <p>Vérification en cours...</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>