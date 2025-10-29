<?php
/**
 * Fichier : profile.php
 * Page de profil utilisateur de l'application DashMed.
 *
 * Affiche les informations de l'utilisateur connecté (nom, prénom, email, sexe, spécialité) et propose
 * des actions sur le compte (modifier email, mot de passe, supprimer compte). Sécurisée via session utilisateur.
 *
 * Variables dynamiques :
 * - $pageTitle       (string)  Titre de la page
 * - $pageDescription (string)  Description pour les métadonnées
 * - $pageStyles      (array)   Styles CSS spécifiques
 * - $pageScripts     (array)   Scripts JS spécifiques
 *
 * @package DashMed
 * @version 1.0
 * @author FABRE Alexis, GHEUX Théo, JACOB Alexandre, TAHA CHAOUI Amir, UYSUN Ali
 */

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

$user = $_SESSION['user'];

// ✅ CORRECTION : Utilise directement prenom et nom depuis la session
$prenom = $user['prenom'] ?? '';
$nom = $user['nom'] ?? '';
$email = $user['email'] ?? '';
$sexe = $user['sexe'] ?? '';
$specialite = $user['specialite'] ?? '';

// Affichage lisible du sexe
$sexeLabel = $sexe === 'M' ? 'Homme' : ($sexe === 'F' ? 'Femme' : 'Non renseigné');

$pageTitle = "Profil";
$pageDescription = "Consultez votre profil DashMed une fois connecté";
$pageStyles = ["/assets/style/profile.css"];
$pageScripts = [];

include __DIR__ . '/partials/head.php';
?>
<body>
<?php include __DIR__ . '/partials/headerPrivate.php'; ?>
<main>
    <div class="container">
        <h1 class="profile-title">Mon Profil</h1>

        <div class="profile-card">
            <div class="avatar">
                <div class="avatar-circle" aria-hidden="true">👤</div>
            </div>
            <table class="info-table" aria-describedby="profil-infos">
                <tbody>
                <tr>
                    <th scope="row">Prénom</th>
                    <td><?= htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                    <th scope="row">Nom</th>
                    <td><?= htmlspecialchars($nom, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                    <th scope="row">Sexe</th>
                    <td><?= htmlspecialchars($sexeLabel, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                    <th scope="row">Spécialité</th>
                    <td><?= htmlspecialchars($specialite, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                    <th scope="row">Adresse email</th>
                    <td class="email-cell">
                        <span><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
                        <a class="btn-edit" href="/change-mail" title="Changer votre adresse email">Changer</a>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Mot de passe</th>
                    <td class="email-cell">
                        <span aria-label="Mot de passe masqué">••••••••</span>
                        <a class="btn-edit" href="/change-password" title="Changer votre mot de passe">Changer</a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>