<?php

/**
 * Page connectée : Profil utilisateur
 *
 * Affiche les informations du compte et propose les actions de modification.
 *
 * Nécessite une session utilisateur active.
 *
 * Variables attendues :
 *  - $user             (array)   Données utilisateur
 *  - $pageTitle        (string)  Titre de la page
 *  - $pageDescription  (string)  Meta description
 *  - $pageStyles       (array)   Styles spécifiques
 *  - $pageScripts      (array)   Scripts spécifiques
 *
 * @package Views
 */

// RÉCUPÉRATION DES DONNÉES UTILISATEUR
$first = $user['name'] ?? '';
$last  = $user['last_name'] ?? '';

// CONFIGURATION : Variables du template
$pageTitle       = $pageTitle ?? "Profil";
$pageDescription = $pageDescription ?? "Consultez votre profil DashMed une fois connecté";
$pageStyles      = $pageStyles ?? ["/assets/style/profile.css"];
$pageScripts     = $pageScripts ?? [];

include __DIR__ . '/../partials/head.php';
?>
<body>
<?php include __DIR__ . '/../partials/headerPrivate.php'; ?>
<main>
    <div class="container">
        <h1 class="profile-title">Profil</h1>

        <div class="profile-card">
            <!-- Avatar symbolique de l'utilisateur -->
            <div class="avatar">
                <div class="avatar-circle" aria-hidden="true">👤</div>
            </div>

            <table class="info-table" aria-describedby="profil-infos">
                <tbody>
                <tr>
                    <th scope="row">Prénom</th>
                    <td><?= htmlspecialchars($first, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                    <th scope="row">Nom</th>
                    <td><?= htmlspecialchars($last, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                    <th scope="row">Sexe</th>
                    <td><?= htmlspecialchars(
                        ($user['sexe'] ?? '') === 'M' ? 'Homme' : 'Femme',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></td>
                </tr>
                <tr>
                    <th scope="row">Spécialité</th>
                    <td><?= htmlspecialchars($user['specialite'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                    <th scope="row">Adresse email</th>
                    <td class="email-cell">
                        <span><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                        <a class="btn-edit"
                           href="/change-email"
                           title="Changer votre adresse email (connexion requise)">Changer</a>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Mot de passe</th>
                    <td class="email-cell">
                        <span aria-label="Mot de passe masqué">••••••••</span>
                        <a class="btn-edit"
                           href="/change-password"
                           title="Changer votre mot de passe (connexion requise)">Changer</a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
