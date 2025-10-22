<?php
/**
 * Fichier : login.php
 * Page de connexion utilisateur de l'application DashMed.
 *
 * Permet à l'utilisateur de se connecter en saisissant son email et son mot de passe.
 * Le formulaire est sécurisé via un token CSRF. Affiche également les messages d'erreur ou de succès.
 *
 * Variables :
 * - $csrf_token (string)  Token CSRF pour sécuriser le formulaire
 * - $errors     (array)   Liste des erreurs de saisie
 * - $success    (string)  Message de succès
 * - $old        (array)   Valeurs précédemment saisies (ex: email)
 *
 * @package DashMed
 * @version 1.0
 * @author FABRE Alexis, GHEUX Théo, JACOB Alexandre, TAHA CHAOUI Amir, UYSUN Ali
 */

use Core\Csrf;
$csrf_token = Csrf::token();

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
