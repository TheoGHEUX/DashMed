<?php
/**
 * Vue : Renvoyer l'email de vérification
 *
 * Page publique pour demander le renvoi d'un email de vérification.
 * Variables optionnelles : $success (string), $errors (array), $email (string)
 *
 * @package DashMed
 */

$csrf_token = '';
if (class_exists('\Core\Csrf')) {
    if (method_exists('\Core\Csrf', 'generate')) {
        $csrf_token = \Core\Csrf::generate();
    } elseif (method_exists('\Core\Csrf', 'token')) {
        $csrf_token = \Core\Csrf::token();
    }
}

$pageTitle       = $pageTitle ?? "Renvoyer l'email de vérification";
$pageDescription = $pageDescription ?? "Renvoyer l'email de vérification";
$pageStyles      = $pageStyles ?? ["/assets/style/authentication.css"];
$pageScripts     = $pageScripts ?? [];

include __DIR__ . '/../partials/head.php';
?>
<body>
<?php include __DIR__ . '/../partials/headerPublic.php'; ?>

<main class="body-main-container">
    <section class="auth-section">
        <div class="auth-container">
            <h1 class="auth-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="status" aria-live="polite">
                    <span aria-hidden="true">✅</span>
                    <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" role="alert">
                    <span aria-hidden="true">❌</span>
                    <ul style="margin:0; padding-left:20px;">
                        <?php foreach ((array)$errors as $error): ?>
                            <li><?= htmlspecialchars($error ?? '', ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <p class="auth-description">
                Entrez l'adresse email associée à votre compte pour recevoir un nouvel email de vérification.
            </p>

            <form class="form" action="/resend-verification" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>" />

                <div class="form-group">
                    <label for="email" class="sr-only">Adresse email</label>
                    <input
                            id="email"
                            type="email"
                            name="email"
                            placeholder="Adresse email"
                            required
                            value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="email"
                    />
                </div>

                <button type="submit" class="btn-primary">Renvoyer l'email</button>
            </form>

            <div class="auth-links" style="margin-top:16px;">
                <a href="/login" class="link-strong">← Retour à la connexion</a>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>