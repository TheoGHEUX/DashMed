<?php
/**
 * Fichier : activation.php
 * Page d'activation de compte pour DashMed.
 *
 * Variables:
 * - $errors  (array)  Liste des erreurs
 * - $success (string) Message de succès
 */

use Core\Csrf;

$pageTitle = "Activation de compte";
$pageDescription = "Activez votre compte DashMed";
$pageStyles = ["/assets/style/authentication.css"];
$pageScripts = [];
?>
<!doctype html>
<html lang="fr">
<?php include __DIR__ . '/../partials/head.php'; ?>
<body>
<?php include __DIR__ . '/../partials/headerPublic.php'; ?>

<main class="main">
    <section class="hero">
        <h1>Activation de votre compte</h1>
        <p class="subtitle">Finalisation de votre inscription sur DashMed</p>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="margin: 20px auto; max-width: 600px;">
                <?= $success ?>
            </div>
            <div style="margin-top: 30px; text-align: center;">
                <a href="/login" class="btn" style="background: #12c9d4; color: #fff; padding: 14px 32px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 500;">
                    Se connecter maintenant
                </a>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" style="margin: 20px auto; max-width: 600px;">
                <ul class="errors">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div style="margin-top: 30px; text-align: center;">
                <a href="/inscription" class="btn" style="background: #12c9d4; color: #fff; padding: 14px 32px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 500; margin-right: 10px;">
                    Créer un nouveau compte
                </a>
                <a href="/login" class="btn" style="background: #6c757d; color: #fff; padding: 14px 32px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 500;">
                    Se connecter
                </a>
            </div>
        <?php endif; ?>

        <?php if (empty($success) && empty($errors)): ?>
            <div style="margin: 30px auto; text-align: center;">
                <p>⏳ Activation en cours...</p>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>