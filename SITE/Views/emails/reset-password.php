<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation Mot de Passe DashMed</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">

<div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border: 1px solid #dddddd; border-radius: 5px;">

    <h2 style="color: #2c5282; margin-top: 0;">Réinitialisation de votre mot de passe</h2>

    <p style="color: #333333; line-height: 1.6;">Bonjour <?= htmlspecialchars($name) ?>,</p>

    <p style="color: #333333; line-height: 1.6;">
        Vous avez demandé la réinitialisation de votre mot de passe DashMed. Pour définir un nouveau mot de passe, cliquez sur le bouton ci-dessous :
    </p>

    <!-- Bouton centré -->
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <a href="<?= htmlspecialchars($url) ?>"
                   style="display: inline-block; padding: 12px 30px; background-color: #2c5282; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    Réinitialiser mon mot de passe
                </a>
            </td>
        </tr>
    </table>

    <p style="color: #666666; font-size: 12px;">Ou copiez ce lien dans votre navigateur : <br>
        <span style="word-break: break-all; color: #666;"><?= htmlspecialchars($url) ?></span>
    </p>

    <p style="color: #e53e3e; font-size: 13px;"><strong>⚠️ Ce lien expire dans 60 minutes.</strong></p>

    <p style="color: #666666; font-size: 12px; margin-top: 30px;">
        Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email en toute sécurité. Votre mot de passe actuel reste inchangé.
    </p>

    <hr style="border: none; border-top: 1px solid #dddddd; margin: 30px 0;">

    <p style="color: #999999; font-size: 11px; text-align: center;">L'équipe DashMed</p>
</div>

</body>
</html>