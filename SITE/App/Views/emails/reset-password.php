<?php
/**
 * @var string $url  // Lien pour réinitialiser le mot de passe (contient &email=...)
 * @var string $name // Prénom Nom pour l'affichage
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation Mot de Passe DashMed</title>
</head>
<body>
<div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 20px;">
    <h2>Réinitialisation de votre mot de passe</h2>

    <p>Bonjour <?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8') ?>,</p>

    <p>Pour définir un nouveau mot de passe, cliquez sur le bouton ci-dessous :</p>

    <div style="margin: 20px 0;">
        <a href="<?= $url ?>" style="background:#2c5282;color:#fff;padding:12px 30px;text-decoration:none;border-radius:5px;font-weight:bold;">Réinitialiser mon mot de passe</a>
    </div>

    <p style="color:#e53e3e;"><strong>⚠️ Le lien expire dans 60 minutes.</strong></p>

    <p style="font-size:13px;color:#888;">
        Si le bouton ne fonctionne pas, copie son adresse dans votre navigateur.
    </p>
</div>
</body>
</html>