<?php

/**
 * Vue : Connexion utilisateur
 *
 * Page de connexion publique. Affiche le formulaire sécurisé par CSRF,
 * messages d'erreur/succès et liens utiles (inscription, mot de passe oublié).
 *
 * Variables optionnelles :
 *  - $csrf_token (string)
 *  - $errors     (array)
 *  - $success    (string)
 *  - $old        (array)  Valeurs précédentes
 *
 * @package DashMed
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

$pageTitle       = $pageTitle ?? "Connexion";
$pageDescription = $pageDescription ?? "Connectez-vous à DashMed";
$pageStyles      = $pageStyles ?? ["/assets/style/authentication.css"];
$pageScripts     = $pageScripts ?? [];

include __DIR__ . '/../partials/head.php';
?>
<body>
<?php include __DIR__ . '/../partials/headerPublic.php'; ?>

<main class="main">
    <section class="hero" aria-labelledby="login-title">
        <h1 id="login-title">Bienvenue dans DashMed</h1>
        <p class="subtitle">Connectez-vous pour continuer</p>

        <?php if (!empty($errors)) : ?>
            <div class="alert alert-error" role="alert" aria-live="assertive">
                <ul class="errors" style="margin:0; padding-left:20px;">
                    <?php foreach ((array)$errors as $err) : ?>
                        <li><?= htmlspecialchars($err ?? '', ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="alert alert-success" role="status" aria-live="polite">
                <?= nl2br(htmlspecialchars($success, ENT_QUOTES, 'UTF-8')) ?>
            </div>
        <?php endif; ?>

        <form class="form" action="/login" method="post" autocomplete="on" novalidate>
            <input type="hidden"
                   name="csrf_token"
                   value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>" />

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

            <div class="field">
                <label for="password" class="sr-only">Mot de passe</label>
                <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Mot de passe"
                        required
                        autocomplete="current-password"
                />
            </div>

            <button class="btn" type="submit">Se connecter</button>

            <p class="muted small mt-16">
                Pas de compte ? <a href="/inscription" class="link-strong">Inscrivez-vous</a>
            </p>

            <p class="muted small">ou</p>

            <p class="small">
                <a href="/forgotten-password" class="link-strong">Mot de passe oublié ?</a>
            </p>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>