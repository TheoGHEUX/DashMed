<?php
/**
 * Fichier : change-password.php
 * Page de changement de mot de passe (utilisateur connecté) pour DashMed.
 *
 * Variables:
 * - $errors  (array)
 * - $success (string)
 */

use Core\Csrf;
$csrf_token = Csrf::token();

$pageTitle = "Changer mon mot de passe";
$pageDescription = "Modifiez votre mot de passe en toute sécurité";
$pageStyles = ["/assets/style/authentication.css"]; // on réutilise le style d'auth
$pageScripts = ["/assets/script/header_responsive.js"];
?>
<!doctype html>
<html lang="fr">
<?php include __DIR__ . '/../partials/head.php'; ?>
<body>
<?php include __DIR__ . '/../partials/headerPrivate.php'; ?>

<main class="main">
    <section class="hero">
        <h1>Changer mon mot de passe</h1>
        <p class="subtitle">Saisissez votre ancien mot de passe puis le nouveau.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul class="errors">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= nl2br(htmlspecialchars($success, ENT_QUOTES, 'UTF-8')) ?></div>
        <?php endif; ?>

        <form class="form" action="/change-password" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>" />
            <div class="field">
                <input type="password" name="old_password" placeholder="Ancien mot de passe" required />
            </div>
            <div class="field">
                <input type="password" name="password" placeholder="Nouveau mot de passe" required />
            </div>
            <div class="field">
                <input type="password" name="password_confirm" placeholder="Confirmez le nouveau mot de passe" required />
            </div>
            <button class="btn" type="submit">Mettre à jour</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
