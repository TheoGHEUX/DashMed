<?php
/**
 * Fichier : inscription.php
 * Page d'inscription utilisateur de l'application DashMed.
 *
 * @package DashMed
 * @version 2.0
 * @author FABRE Alexis, GHEUX Théo, JACOB Alexandre, TAHA CHAOUI Amir, UYSUN Ali
 */

use Core\Csrf;

$csrf_token = Csrf::token();
$pageTitle = "Inscription";
$pageDescription = "Créez votre compte DashMed !";
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
        <p class="subtitle">Créez votre compte</p>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= nl2br(htmlspecialchars($success, ENT_QUOTES, 'UTF-8')) ?></div>
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

        <form class="form" action="/inscription" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>"/>

            <div class="field">
                <input type="text" name="name" placeholder="Prénom" required value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
            </div>

            <div class="field">
                <input type="text" name="last_name" placeholder="Nom" required value="<?= htmlspecialchars($old['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
            </div>

            <div class="field">
                <select name="sexe" required>
                    <option value="" disabled selected>Sexe</option>
                    <option value="M" <?= ($old['sexe'] ?? '') === 'M' ? 'selected' : '' ?>>Homme</option>
                    <option value="F" <?= ($old['sexe'] ?? '') === 'F' ? 'selected' : '' ?>>Femme</option>
                </select>
            </div>

            <div class="field">
                <select name="specialite" required>
                    <option value="" disabled selected>Spécialité</option>
                    <option value="Addictologie" <?= ($old['specialite'] ?? '') === 'Addictologie' ? 'selected' : '' ?>>Addictologie</option>
                    <option value="Algologie" <?= ($old['specialite'] ?? '') === 'Algologie' ? 'selected' : '' ?>>Algologie</option>
                    <option value="Allergologie" <?= ($old['specialite'] ?? '') === 'Allergologie' ? 'selected' : '' ?>>Allergologie</option>
                    <option value="Anesthésie-Réanimation" <?= ($old['specialite'] ?? '') === 'Anesthésie-Réanimation' ? 'selected' : '' ?>>Anesthésie-Réanimation</option>
                    <option value="Cancérologie" <?= ($old['specialite'] ?? '') === 'Cancérologie' ? 'selected' : '' ?>>Cancérologie</option>
                    <option value="Cardio-vasculaire HTA" <?= ($old['specialite'] ?? '') === 'Cardio-vasculaire HTA' ? 'selected' : '' ?>>Cardio-vasculaire HTA</option>
                    <option value="Chirurgie" <?= ($old['specialite'] ?? '') === 'Chirurgie' ? 'selected' : '' ?>>Chirurgie</option>
                    <option value="Dermatologie" <?= ($old['specialite'] ?? '') === 'Dermatologie' ? 'selected' : '' ?>>Dermatologie</option>
                    <option value="Diabétologie-Endocrinologie" <?= ($old['specialite'] ?? '') === 'Diabétologie-Endocrinologie' ? 'selected' : '' ?>>Diabétologie-Endocrinologie</option>
                    <option value="Génétique" <?= ($old['specialite'] ?? '') === 'Génétique' ? 'selected' : '' ?>>Génétique</option>
                    <option value="Gériatrie" <?= ($old['specialite'] ?? '') === 'Gériatrie' ? 'selected' : '' ?>>Gériatrie</option>
                    <option value="Gynécologie-Obstétrique" <?= ($old['specialite'] ?? '') === 'Gynécologie-Obstétrique' ? 'selected' : '' ?>>Gynécologie-Obstétrique</option>
                    <option value="Hématologie" <?= ($old['specialite'] ?? '') === 'Hématologie' ? 'selected' : '' ?>>Hématologie</option>
                    <option value="Hépato-gastro-entérologie" <?= ($old['specialite'] ?? '') === 'Hépato-gastro-entérologie' ? 'selected' : '' ?>>Hépato-gastro-entérologie</option>
                    <option value="Imagerie médicale" <?= ($old['specialite'] ?? '') === 'Imagerie médicale' ? 'selected' : '' ?>>Imagerie médicale</option>
                    <option value="Immunologie" <?= ($old['specialite'] ?? '') === 'Immunologie' ? 'selected' : '' ?>>Immunologie</option>
                    <option value="Infectiologie" <?= ($old['specialite'] ?? '') === 'Infectiologie' ? 'selected' : '' ?>>Infectiologie</option>
                    <option value="Médecine du sport" <?= ($old['specialite'] ?? '') === 'Médecine du sport' ? 'selected' : '' ?>>Médecine du sport</option>
                    <option value="Médecine du travail" <?= ($old['specialite'] ?? '') === 'Médecine du travail' ? 'selected' : '' ?>>Médecine du travail</option>
                    <option value="Médecine générale" <?= ($old['specialite'] ?? '') === 'Médecine générale' ? 'selected' : '' ?>>Médecine générale</option>
                    <option value="Médecine légale" <?= ($old['specialite'] ?? '') === 'Médecine légale' ? 'selected' : '' ?>>Médecine légale</option>
                    <option value="Médecine physique et de réadaptation" <?= ($old['specialite'] ?? '') === 'Médecine physique et de réadaptation' ? 'selected' : '' ?>>Médecine physique et de réadaptation</option>
                    <option value="Néphrologie" <?= ($old['specialite'] ?? '') === 'Néphrologie' ? 'selected' : '' ?>>Néphrologie</option>
                    <option value="Neurologie" <?= ($old['specialite'] ?? '') === 'Neurologie' ? 'selected' : '' ?>>Neurologie</option>
                    <option value="Nutrition" <?= ($old['specialite'] ?? '') === 'Nutrition' ? 'selected' : '' ?>>Nutrition</option>
                    <option value="Ophtalmologie" <?= ($old['specialite'] ?? '') === 'Ophtalmologie' ? 'selected' : '' ?>>Ophtalmologie</option>
                    <option value="ORL" <?= ($old['specialite'] ?? '') === 'ORL' ? 'selected' : '' ?>>ORL</option>
                    <option value="Pédiatrie" <?= ($old['specialite'] ?? '') === 'Pédiatrie' ? 'selected' : '' ?>>Pédiatrie</option>
                    <option value="Pneumologie" <?= ($old['specialite'] ?? '') === 'Pneumologie' ? 'selected' : '' ?>>Pneumologie</option>
                    <option value="Psychiatrie" <?= ($old['specialite'] ?? '') === 'Psychiatrie' ? 'selected' : '' ?>>Psychiatrie</option>
                    <option value="Radiologie" <?= ($old['specialite'] ?? '') === 'Radiologie' ? 'selected' : '' ?>>Radiologie</option>
                    <option value="Rhumatologie" <?= ($old['specialite'] ?? '') === 'Rhumatologie' ? 'selected' : '' ?>>Rhumatologie</option>
                    <option value="Sexologie" <?= ($old['specialite'] ?? '') === 'Sexologie' ? 'selected' : '' ?>>Sexologie</option>
                    <option value="Toxicologie" <?= ($old['specialite'] ?? '') === 'Toxicologie' ? 'selected' : '' ?>>Toxicologie</option>
                    <option value="Urologie" <?= ($old['specialite'] ?? '') === 'Urologie' ? 'selected' : '' ?>>Urologie</option>
                </select>
            </div>

            <div class="field">
                <input type="email" name="email" placeholder="Adresse email" required value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
            </div>

            <div class="field">
                <input type="password" name="password" placeholder="Mot de passe" required />
            </div>

            <div class="field">
                <input type="password" name="password_confirm" placeholder="Confirmer le mot de passe" required />
            </div>

            <button class="btn" type="submit">S'inscrire</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>