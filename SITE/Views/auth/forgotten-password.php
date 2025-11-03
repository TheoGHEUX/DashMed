<?php
/**
 * Fichier : forgotten_password.php
 * Page de réinitialisation du mot de passe utilisateur (DashMed).
 *
 * Permet à l’utilisateur de demander un lien de réinitialisation par e-mail.
 * Utilise la structure dynamique (head, header, footer) et un token CSRF pour sécuriser le formulaire.
 *
 * Variables :
 * - $success (string|null)  Message de confirmation.
 * - $errors  (array|null)   Liste des erreurs de validation.
 * - $old     (array|null)   Anciennes valeurs du formulaire.
 *
 * @package DashMed
 * @version 1.1
 * @author FABRE Alexis, GHEUX Théo, JACOB Alexandre, TAHA CHAOUI Amir, UYSUN Ali
 */

$csrf_token = \Core\Csrf::token();

$pageTitle = "Mot de passe oublié";
$pageDescription = "Page de mot de passe oublié, envoie un lien par mail pour le changer";
$pageStyles = ["/assets/style/forgotten_password.css"];
$pageScripts = [];
?>
<!doctype html>
<html lang="fr">
<?php include __DIR__ . '/../partials/head.php'; ?>
<body>
<?php include __DIR__ . '/../partials/headerPublic.php'; ?>

<main class="main">
    <section class="hero">
        <h1>Réinitialisez votre mot de passe</h1>
        <p class="subtitle">Entrez votre adresse email ci-dessous et vous recevrez un lien pour changer de mot de passe.</p>

        <form class="form" action="/forgotten-password" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            <div class="field">
                <input type="email" name="email" placeholder="Adresse email" required value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
            </div>
            <button class="btn" type="submit">Envoyer le lien</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
