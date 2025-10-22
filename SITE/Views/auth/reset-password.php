<?php
/**
 * Fichier : reset-password.php
 * Page de réinitialisation du mot de passe utilisateur pour l'application DashMed.
 *
 * Permet à l'utilisateur de définir un nouveau mot de passe après avoir reçu le lien de réinitialisation.
 * Le formulaire est sécurisé via un token CSRF. Utilise la structure dynamique avec head, header et footer inclus.
 *
 * Variables dynamiques :
 * - $csrf_token (string)  Token CSRF pour sécuriser le formulaire
 * - $email      (string)  Email de l'utilisateur pour lequel le mot de passe est réinitialisé
 * - $token      (string)  Token unique de réinitialisation reçu par email
 * - $errors     (array)   Liste des erreurs de saisie
 * - $success    (string)  Message de succès après modification
 * - $pageTitle       (string)  Titre de la page
 * - $pageDescription (string)  Description pour les métadonnées
 * - $pageStyles      (array)   Styles CSS spécifiques
 * - $pageScripts     (array)   Scripts JS spécifiques
 *
 * @package DashMed
 * @version 1.0
 * @author FABRE Alexis, GHEUX Théo, JACOB Alexandre, TAHA CHAOUI Amir, UYSUN Ali
 */

$csrf_token = \Core\Csrf::token();

$pageTitle = "DashMed - Réinitialisation";
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
