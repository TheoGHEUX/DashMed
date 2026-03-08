<?php

/**
 * Changement d'adresse email
 *
 * Permet aux utilisateurs connectés de modifier leur adresse email.
 *
 * Affiche le formulaire sécurisé par CSRF et les messages d'état.
 *
 * Variables attendues :
 *  - $errors  (array)   Erreurs de validation à afficher
 *  - $success (string)  Message de succès
 *
 * @package Views
 */

$csrf_token = (class_exists('\Core\Csrf')) ? \Core\Csrf::token() : '';
$pageTitle = "Changer mon adresse email";
$pageStyles = ["/assets/style/authentication.css"];

include __DIR__ . '/../partials/head.php';
?>
<body>
<?php include __DIR__ . '/../partials/headerPrivate.php'; ?>

<main class="main">
    <section class="hero">
        <h1>Changer mon adresse email</h1>

        <?php if (!empty($errors)) : ?>
            <div class="alert alert-error"><ul><?php foreach ($errors as $err) : ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="alert alert-success"><?= nl2br(htmlspecialchars($success)) ?></div>
        <?php endif; ?>

        <form class="form" action="/change-email" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>" />
            <div class="field"><input type="password" name="current_password" placeholder="Mot de passe actuel" required /></div>
            <div class="field"><input type="email" name="new_email" placeholder="Nouvelle adresse email" required /></div>
            <div class="field"><input type="email" name="new_email_confirm" placeholder="Confirmez la nouvelle adresse" required /></div>
            <button class="btn" type="submit">Mettre à jour</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
