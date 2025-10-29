<?php
/**
 * Fichier : inscription.php
 * Page d'inscription utilisateur de l'application DashMed.
 *
 * Permet à l'utilisateur de créer un compte en saisissant son prénom, nom, email et mot de passe.
 * Le formulaire est sécurisé via un token CSRF. Affiche également les messages d'erreur ou de succès.
 *
 * Variables :
 * - $csrf_token (string)  Token CSRF pour sécuriser le formulaire
 * - $errors     (array)   Liste des erreurs de saisie
 * - $success    (string)  Message de succès
 * - $old        (array)   Valeurs précédemment saisies (prenom, nom, email, sexe, specialite)
 *
 * @package DashMed
 * @version 1.0
 * @author FABRE Alexis, GHEUX Théo, JACOB Alexandre, TAHA CHAOUI Amir, UYSUN Ali
 */

use Core\Csrf;
$csrf_token = Csrf::token();

$pageTitle = "Inscription";
$pageDescription = "Créez votre compte DashMed !";
$pageStyles = ["/assets/style/authentication.css"];
$pageScripts = [];

// Liste des spécialités médicales
$specialites = [
        'Addictologie', 'Algologie', 'Allergologie', 'Anesthésie-Réanimation',
        'Cancérologie', 'Cardio-vasculaire HTA', 'Chirurgie', 'Dermatologie',
        'Diabétologie-Endocrinologie', 'Génétique', 'Gériatrie',
        'Gynécologie-Obstétrique', 'Hématologie', 'Hépato-gastro-entérologie',
        'Imagerie médicale', 'Immunologie', 'Infectiologie', 'Médecine du sport',
        'Médecine du travail', 'Médecine générale', 'Médecine légale',
        'Médecine physique et de réadaptation', 'Néphrologie', 'Neurologie',
        'Nutrition', 'Ophtalmologie', 'ORL', 'Pédiatrie', 'Pneumologie',
        'Psychiatrie', 'Radiologie', 'Rhumatologie', 'Sexologie',
        'Toxicologie', 'Urologie'
];
?>
<!doctype html>
<html lang="fr">
<?php include __DIR__ . '/../partials/head.php'; ?>
<body>
<?php include __DIR__ . '/../partials/headerPublic.php'; ?>
<main class="main">
    <section class="hero">
        <h1>Bienvenue dans DashMed</h1>
        <p class="subtitle">Créez votre compte médecin</p>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul class="errors">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="form" action="/inscription" method="post" autocomplete="on" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>"/>

            <div class="field">
                <input type="text" name="prenom" placeholder="Prénom" required
                       value="<?= htmlspecialchars($old['prenom'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       maxlength="50" />
            </div>

            <div class="field">
                <input type="text" name="nom" placeholder="Nom" required
                       value="<?= htmlspecialchars($old['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       maxlength="100" />
            </div>

            <div class="field">
                <input type="email" name="email" placeholder="Adresse email" required
                       value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       maxlength="150" />
            </div>

            <div class="field">
                <select name="sexe" required>
                    <option value="" disabled selected>Sexe *</option>
                    <option value="M" <?= ($old['sexe'] ?? '') === 'M' ? 'selected' : '' ?>>Homme</option>
                    <option value="F" <?= ($old['sexe'] ?? '') === 'F' ? 'selected' : '' ?>>Femme</option>
                </select>
            </div>

            <div class="field">
                <select name="specialite" required>
                    <option value="" disabled selected>Spécialité médicale *</option>
                    <?php foreach ($specialites as $spec): ?>
                        <option value="<?= htmlspecialchars($spec, ENT_QUOTES, 'UTF-8') ?>"
                                <?= ($old['specialite'] ?? '') === $spec ? 'selected' : '' ?>>
                            <?= htmlspecialchars($spec, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <input type="password" name="password" placeholder="Mot de passe (min 12 caractères)" required />
            </div>

            <div class="field">
                <input type="password" name="password_confirm" placeholder="Confirmer le mot de passe" required />
            </div>

            <button class="btn" type="submit">S'inscrire</button>

            <p class="muted small mt-16">
                Vous avez déjà un compte ? <a href="/login" class="link-strong">Connectez-vous</a>
            </p>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>