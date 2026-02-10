<?php

/**
 * Page connectée : Profil utilisateur
 *
 * @package Views
 */

use Core\Domain\Services\AuthenticationService;

AuthenticationService::ensureUserIsAuthenticated();

$user  = AuthenticationService::getCurrentUser();
$first = $user['name'] ?? '';
$last  = $user['last_name'] ?? '';

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
            <div class="avatar">
                <div class="avatar-circle" aria-hidden="true">👤</div>
            </div>

            <table class="info-table">
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
                        <a class="btn-edit" href="/change-email">Changer</a>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Mot de passe</th>
                    <td class="email-cell">
                        <span aria-label="Mot de passe masqué">••••••••</span>
                        <a class="btn-edit" href="/change-password">Changer</a>
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
