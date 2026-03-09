<?php

/**
 * Inscription
 *
 * @package Views
 *
 * Variables attendues :
 *  - $csrf_token (string)  Jeton CSRF
 *  - $old        (array)   Valeurs précédentes (pour re-remplir)
 *  - $errors     (array)   Liste d'erreurs de validation
 *  - $success    (string)  Message de succès
 *  - $specialites (array)  Liste des spécialités transmise par le contrôleur
 */

$csrf_token = $csrf_token ?? '';
$old = $old ?? [];
$errors = $errors ?? [];
$success = $success ?? '';
$specialites = $specialites ?? [];

$pageTitle = $pageTitle ?? "Inscription";
$pageDescription = $pageDescription ?? "Créez votre compte DashMed !";
$pageStyles = $pageStyles ?? ["/assets/style/authentication.css"];
$pageScripts = $pageScripts ?? [];

include __DIR__ . '/../partials/head.php';
?>
<body>
<?php include __DIR__ . '/../partials/headerPublic.php'; ?>

<main class="main">
    <section class="hero">
        <h1>Bienvenue dans DashMed</h1>
        <p class="subtitle">Créez votre compte</p>

        <?php if (!empty($success)) : ?>
            <div class="alert alert-success" role="status">
                <?= nl2br(htmlspecialchars($success, ENT_QUOTES, 'UTF-8')) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)) : ?>
            <div class="alert alert-error" role="alert">
                <ul class="errors" style="margin:0; padding-left:20px;">
                    <?php foreach ((array)$errors as $err) : ?>
                        <li><?= htmlspecialchars($err ?? '', ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="form" action="/register" method="post" novalidate>
            <input type="hidden"
                   name="csrf_token"
                   value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>"/>

            <div class="field">
                <input
                        type="text"
                        name="prenom"
                        placeholder="Prénom"
                        required
                        value="<?= htmlspecialchars($old['prenom'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                />
            </div>

            <div class="field">
                <input
                        type="text"
                        name="nom"
                        placeholder="Nom"
                        required
                        value="<?= htmlspecialchars($old['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                />
            </div>

            <div class="field">
                <select id="sexe" name="sexe" required>
                    <option value="" disabled <?= empty($old['sexe']) ? 'selected' : '' ?>>Sexe</option>
                    <option value="M" <?= ($old['sexe'] ?? '') === 'M' ? 'selected' : '' ?>>Homme</option>
                    <option value="F" <?= ($old['sexe'] ?? '') === 'F' ? 'selected' : '' ?>>Femme</option>
                </select>
            </div>

            <div class="field">
                <select id="specialite" name="specialite" required>
                    <option value="" disabled <?= empty($old['specialite']) ? 'selected' : '' ?>>Spécialité</option>
                    <?php foreach ($specialites as $sp) : ?>
                        <option value="<?= htmlspecialchars($sp, ENT_QUOTES, 'UTF-8') ?>"
                                <?= ($old['specialite'] ?? '') === $sp ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sp, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <input
                        type="email"
                        name="email"
                        placeholder="Adresse email"
                        required
                        value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="email"
                />
            </div>

            <div class="field">
                <input type="password"
                       name="password"
                       placeholder="Mot de passe"
                       required
                       autocomplete="new-password" />
            </div>

            <div class="field">
                <input type="password"
                       name="password_confirm"
                       placeholder="Confirmer le mot de passe"
                       required
                       autocomplete="new-password" />
            </div>

            <button class="btn" type="submit">S'inscrire</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>