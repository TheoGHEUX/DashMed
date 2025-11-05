<?php
/**
 * Page de connexion (public).
 * Formulaire sécurisé par token CSRF. Affiche les messages d'erreur/succès et conserve les valeurs saisies.
 *
 * Variables disponibles :
 * @var string       $csrf_token Token CSRF pour sécuriser le formulaire
 * @var array|null    $errors     Liste des erreurs à afficher (optionnel)
 * @var string|null   $success    Message de succès (optionnel)
 * @var array|null    $old        Valeurs précédemment saisies (optionnel)
 */

$csrf_token = \Core\Csrf::token();


$pageTitle = "Connexion";
$pageDescription = "Connectez-vous à DashMed !";
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
        <h1>Bienvenue dans DashMed</h1>
        <p class="subtitle">Connectez-vous pour continuer</p>

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

        <form class="form" action="/login" method="post" autocomplete="on" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>" />
            <div class="field">
                <input type="email" name="email" placeholder="Adresse email" required value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
            </div>
            <div class="field">
                <input type="password" name="password" placeholder="Mot de passe" required />
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
