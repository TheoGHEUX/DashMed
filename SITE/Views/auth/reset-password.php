<?php
/**
 * Vue : Réinitialisation du mot de passe
 *
 * Page publique permettant à un utilisateur de définir un nouveau mot de passe
 * après avoir cliqué sur le lien de réinitialisation reçu par email.
 *
 * Variables attendues :
 * @var string $csrf_token        Token CSRF (généré via \Core\Csrf::token())
 * @var string $email             Email de l'utilisateur (hidden)
 * @var string $token             Token de réinitialisation (hidden)
 * @var array  $errors            Tableau d'erreurs de validation (optionnel)
 * @var string $success           Message de succès (optionnel)
 * @var string $pageTitle         Titre de la page (optionnel)
 * @var string $pageDescription   Meta description (optionnel)
 * @var array  $pageStyles        Styles spécifiques (optionnel)
 * @var array  $pageScripts       Scripts spécifiques (optionnel)
 */

$csrf_token = (class_exists('\Core\Csrf') && method_exists('\Core\Csrf', 'token'))
        ? \Core\Csrf::token()
        : '';

$pageTitle = $pageTitle ?? "Réinitialisation";
$pageDescription = $pageDescription ?? "Réinitialisez votre mot de passe";
$pageStyles = $pageStyles ?? ["/assets/style/forgotten_password.css", "/assets/style/authentication.css"];
$pageScripts = $pageScripts ?? [];

include __DIR__ . '/../partials/head.php';
?>
<!doctype html>
<html lang="fr">
<body>
<?php include __DIR__ . '/../partials/headerPublic.php'; ?>

<main class="main">
    <section class="hero">
        <h1>Définissez un nouveau mot de passe</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <ul class="errors" style="margin:0; padding-left:20px;">
                    <?php foreach ((array)$errors as $err): ?>
                        <li><?= htmlspecialchars($err ?? '', ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="status">
                <?= nl2br(htmlspecialchars($success, ENT_QUOTES, 'UTF-8')) ?>
            </div>
        <?php endif; ?>

        <form class="form" action="/reset-password" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="field">
                <input type="password" name="password" placeholder="Nouveau mot de passe" required autocomplete="new-password">
            </div>
            <div class="field">
                <input type="password" name="password_confirm" placeholder="Confirmer le mot de passe" required autocomplete="new-password">
            </div>
            <button class="btn" type="submit">Changer mon mot de passe</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>