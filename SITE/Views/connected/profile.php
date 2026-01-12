<?php
/**
 * Vue : Page Profil utilisateur
 *
 * Affiche les informations du compte pour l'utilisateur authentifié
 * et propose les actions de modification (email, mot de passe).
 *
 * Variables attendues :
 * @var array  $_SESSION['user']         Données utilisateur (name, last_name, sexe, specialite, email)
 * @var string $pageTitle               Titre de la page ("Profil")
 * @var string $pageDescription         Meta description
 * @var array<int,string> $pageStyles   Styles spécifiques (["/assets/style/profile.css"])
 * @var array<int,string> $pageScripts  Scripts spécifiques
 *
 * @package Views
 */

// SÉCURITÉ : Contrôle d'authentification
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

// RÉCUPÉRATION DES DONNÉES UTILISATEUR
$user  = $_SESSION['user'];
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
