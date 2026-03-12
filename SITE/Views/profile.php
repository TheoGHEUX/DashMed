<?php
/**
 * Vue : Page de profil utilisateur (Profile)
 * 
 * Cette page affiche les informations personnelles et professionnelles de l'utilisateur
 * connecté à l'application DashMed. Elle constitue l'espace personnel où l'utilisateur
 * peut consulter ses données de compte et accéder aux fonctionnalités de modification
 * (email, mot de passe). L'accès est strictement réservé aux utilisateurs authentifiés.
 * 
 * Fonctionnalités :
 * - Affichage des informations personnelles de l'utilisateur connecté
 * - Avatar symbolique (icône utilisateur)
 * - Tableau récapitulatif des données de profil
 * - Liens d'action pour modifier l'email et le mot de passe
 * - Sécurisation de l'accès par contrôle de session
 * - Redirection automatique vers /login si non authentifié
 * - Protection XSS via htmlspecialchars() sur toutes les données affichées
 * - Design responsive et accessible
 * 
 * Sections de la page :
 * 1. En-tête :
 *    - Titre principal "Profil"
 * 
 * 2. Carte de profil (profile-card) :
 *    a) Avatar :
 *       - Cercle avec icône utilisateur (👤)
 *       - Représentation visuelle symbolique
 * 
 *    b) Tableau d'informations (info-table) :
 *       - Prénom (name)
 *       - Nom de famille (last_name)
 *       - Sexe (M = Homme / F = Femme)
 *       - Spécialité médicale (specialite)
 *       - Adresse email (email) avec bouton "Changer"
 *       - Mot de passe masqué (••••••••) avec bouton "Changer"
 * 
 * Structure visuelle :
 * - Header privé (navigation pour utilisateurs authentifiés)
 * - Container principal avec carte de profil centrée
 * - Avatar en haut de la carte
 * - Tableau d'informations à deux colonnes (label / valeur)
 * - Boutons d'action pour email et mot de passe
 * - Footer commun avec liens complémentaires
 * 
 * Public cible :
 * - Utilisateurs authentifiés uniquement
 * - Professionnels de santé inscrits sur DashMed
 * - Médecins, infirmiers, personnel médical
 * - Administrateurs de compte
 * 
 * Parcours utilisateur :
 * - Connexion réussie → Accès au dashboard → Clic sur "Profil"
 * - Consultation des informations personnelles
 * - Clic sur "Changer" (email) → Redirection vers /change-mail
 * - Clic sur "Changer" (mot de passe) → Redirection vers /change-password
 * 
 * @package    DashMed
 * @subpackage Views
 * @category   Frontend
 * @version    2.0.0
 * @since      1.0.0
 * @author     FABRE Alexis
 * @author     GHEUX Théo
 * @author     JACOB Alexandre
 * @author     TAHA CHAOUI Amir
 * @author     UYSUN Ali
 * 
 * @see        \SITE\Controllers\ProfileController.php Contrôleur gérant cette vue
 * @see        \SITE\Views\auth\change-mail.php Page de changement d'email
 * @see        \SITE\Views\auth\change-password.php Page de changement de mot de passe
 * @see        \SITE\Views\partials\headerPrivate.php Header pour utilisateurs authentifiés
 * @see        \SITE\Views\auth\login.php Page de connexion (redirection si non authentifié)
 * 
 * @requires   PHP >= 7.4
 * @requires   Session PHP active avec données utilisateur
 * 
 * Dépendances CSS :
 * @uses /Public/assets/style/profile.css Styles de la page de profil (carte, avatar, tableau)
 * 
 * Dépendances JavaScript :
 * Aucune dépendance JavaScript spécifique pour cette page
 * 
 * Variables de template :
 * @var string $pageTitle       Titre de la page, par défaut "Profil"
 * @var string $pageDescription Meta description SEO pour la page de profil
 * @var array  $pageStyles      Chemins des CSS : ["/assets/style/profile.css"]
 * @var array  $pageScripts     Chemins des scripts JS (vide pour cette page)
 * 
 * Variables de session :
 * @var array $_SESSION['user'] Données de l'utilisateur connecté (obligatoire)
 * @var string $_SESSION['user']['name'] Prénom de l'utilisateur
 * @var string $_SESSION['user']['last_name'] Nom de famille de l'utilisateur
 * @var string $_SESSION['user']['sexe'] Sexe de l'utilisateur ('M' ou 'F')
 * @var string $_SESSION['user']['specialite'] Spécialité médicale de l'utilisateur
 * @var string $_SESSION['user']['email'] Adresse email de l'utilisateur
 * 
 * Variables locales :
 * @var array  $user  Référence vers $_SESSION['user'] pour simplifier l'accès
 * @var string $first Prénom de l'utilisateur (extrait de $user['name'])
 * @var string $last  Nom de famille de l'utilisateur (extrait de $user['last_name'])
 * 
 * Sécurité :
 * Vue : Page de profil utilisateur.
 * Affiche les informations personnelles et professionnelles de l'utilisateur connecté,
 * avec options de modification (email, mot de passe). Accès réservé aux utilisateurs authentifiés.
 *
 * @package DashMed
 * @version 2.0.0
 */
                <tbody>
                <tr>
                    <th scope="row">Prénom</th>
                    <td><?= htmlspecialchars($first) ?></td>
                </tr>
                <tr>
                    <th scope="row">Nom</th>
                    <td><?= htmlspecialchars($last) ?></td>
                </tr>
                <tr>
                    <th scope="row">Sexe</th>
                    <td><?= htmlspecialchars($user['sexe'] === 'M' ? 'Homme' : 'Femme') ?></td>
                </tr>
                <tr>
                    <th scope="row">Spécialité</th>
                    <td><?= htmlspecialchars($user['specialite'] ?? '') ?></td>
                </tr>
                <tr>
                    <th scope="row">Adresse email</th>
                    <td class="email-cell">
                        <span><?= htmlspecialchars($user['email'] ?? '') ?></span>
                        <a class="btn-edit" href="/change-mail" title="Changer votre adresse email (connexion requise)">Changer</a>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Mot de passe</th>
                    <td class="email-cell">
                        <span aria-label="Mot de passe masqué">••••••••</span>
                        <a class="btn-edit" href="/change-password" title="Changer votre mot de passe (connexion requise)">Changer</a>
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