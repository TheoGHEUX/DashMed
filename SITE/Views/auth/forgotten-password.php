<?php
/**
 * Vue : Mot de passe oublié
 *
 * Page publique pour demander l'envoi d'un lien de réinitialisation par email.
 * Affiche le formulaire sécurisé par CSRF et les messages d'état (success / errors).
 *
 * Variables attendues (optionnelles) :
 * @var string|null $csrf_token Token CSRF
 * @var array|null  $errors     Liste d'erreurs de validation
 * @var string|null $success    Message de confirmation
 * @var array|null  $old        Anciennes valeurs du formulaire
 *
 * @package Views
 */

$csrf_token = '';
if (class_exists('\Core\Csrf')) {
    if (method_exists('\Core\Csrf', 'token')) {
        $csrf_token = \Core\Csrf::token();
    } elseif (method_exists('\Core\Csrf', 'generate')) {
        $csrf_token = \Core\Csrf::generate();
    }
}

$old     = $old ?? [];
$errors  = $errors ?? [];
$success = $success ?? '';

$pageTitle       = $pageTitle ?? "Mot de passe oublié";
$pageDescription = $pageDescription ?? "Recevez un lien par email pour réinitialiser votre mot de passe";
$pageStyles     = $pageStyles ?? ["/assets/style/forgotten_password.css"];
$pageScripts    = $pageScripts ?? [];

include __DIR__ . '/../partials/head.php';
?>
<body>
<?php include __DIR__ . '/../partials/headerPublic.php'; ?>

<main class="main">
    <section class="hero" aria-labelledby="forgotten-title">
        <h1 id="forgotten-title">Réinitialisez votre mot de passe</h1>
        <p class="subtitle">
            Entrez votre adresse email ci-dessous et vous recevrez
            un lien pour changer de mot de passe.
        </p>

        <?php if (!empty($success)) : ?>
            <div class="alert alert-success" role="status" aria-live="polite">
                <?= nl2br(htmlspecialchars($success, ENT_QUOTES, 'UTF-8')) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)) : ?>
            <div class="alert alert-error" role="alert" aria-live="assertive">
                <ul style="margin:0; padding-left:20px;">
                    <?php foreach ((array)$errors as $err) : ?>
                        <li><?= htmlspecialchars($err ?? '', ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="form" action="/forgotten-password" method="post" novalidate>
            <input type="hidden"
                   name="csrf_token"
                   value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="field">
                <label for="email" class="sr-only">Adresse email</label>
                <input
                        id="email"
                        type="email"
                        name="email"
                        placeholder="Adresse email"
                        required
                        value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="email"
                />
            </div>
            <button class="btn" type="submit">Envoyer le lien</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>