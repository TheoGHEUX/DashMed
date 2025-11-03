<?php
$csrf_token = \Core\Csrf::generate();

$pageTitle = 'Renvoyer l\'email de vérification';
$pageDescription = 'Renvoyer l\'email de vérification';
$pageStyles = ["/assets/style/authentication.css"];
$pageScripts = [];
?>
<!doctype html>
<html lang="fr">
<?php include __DIR__ . '/../partials/head.php'; ?>
<body>
<?php include __DIR__ . '/../partials/headerPublic.php'; ?>

    <main class="body-main-container">
        <section class="auth-section">
            <div class="auth-container">
                <h1 class="auth-title">Renvoyer l'email de vérification</h1>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                        </svg>
                        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                        </svg>
                        <ul style="margin: 0; padding-left: 20px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <p class="auth-description">
                    Entrez l'adresse email associée à votre compte pour recevoir un nouvel email de vérification.
                </p>

                <form class="form" action="/resend-verification" method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>" />
                    
                    <div class="form-group">
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="Adresse email" 
                            required 
                            value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        />
                    </div>

                    <button type="submit" class="btn-primary">Renvoyer l'email</button>
                </form>

                <div class="auth-links">
                    <a href="/login" class="link-strong">← Retour à la connexion</a>
                </div>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
