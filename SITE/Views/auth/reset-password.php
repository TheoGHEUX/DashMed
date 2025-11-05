<?php
/**
 * Page de réinitialisation du mot de passe (public).
 * Permet à l'utilisateur de définir un nouveau mot de passe via un lien sécurisé.
 *
 * Variables disponibles :
 * @var string       $csrf_token Token CSRF pour sécuriser le formulaire
 * @var string|null  $email      Email lié à la demande (optionnel)
 * @var string|null  $token      Token unique de réinitialisation (optionnel)
 * @var array|null   $errors     Liste des erreurs à afficher (optionnel)
 * @var string|null  $success    Message de succès (optionnel)
 */

$csrf_token = \Core\Csrf::token();

$pageTitle = "Réinitialisation";
$pageDescription = "Page pour réinitialiser le mot de passe oublié et définir un nouveau";
$pageStyles = ["/assets/style/forgotten_password.css", "/assets/style/authentication.css"];
$pageScripts = [];
?>
<!doctype html>
<html lang="fr">
<?php include __DIR__ . '/../partials/head.php'; ?>
<body>
<?php include __DIR__ . '/../partials/headerPublic.php'; ?>

<main class="main">
    <section class="hero">
        <h1>Définissez un nouveau mot de passe</h1>

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

        <form class="form" action="/reset-password" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="field">
                <input type="password" name="password" placeholder="Nouveau mot de passe" required>
            </div>
            <div class="field">
                <input type="password" name="password_confirm" placeholder="Confirmer le mot de passe" required>
            </div>
            <button class="btn" type="submit">Changer mon mot de passe</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
