<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vérification Email</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">

<!-- Conteneur principal -->
<div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border: 1px solid #dddddd; border-radius: 5px;">

    <h2 style="color: #2c5282; margin-top: 0;">Bienvenue sur DashMed !</h2>

    <p style="color: #333333; line-height: 1.6;">Bonjour <?= htmlspecialchars($name) ?>,</p>

    <p style="color: #333333; line-height: 1.6;">
        Merci de vous être inscrit. Pour activer votre compte, cliquez ci-dessous :
    </p>

    <!-- Bouton (Tableau pour compatibilité Outlook) -->
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <a href="<?= htmlspecialchars($url) ?>"
                   style="display: inline-block; padding: 12px 30px; background-color: #2c5282; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    Vérifier mon adresse email
                </a>
            </td>
        </tr>
    </table>

    <p style="color: #666666; font-size: 12px;">Ou copiez ce lien : <br>
        <a href="<?= htmlspecialchars($url) ?>" style="color: #2c5282;"><?= htmlspecialchars($url) ?></a>
    </p>

    <p style="color: #e53e3e; font-size: 13px;"><strong>⚠️ Ce lien expire dans 24 heures.</strong></p>

    <hr style="border: none; border-top: 1px solid #dddddd; margin: 30px 0;">

    <p style="color: #999999; font-size: 11px; text-align: center;">L'équipe DashMed</p>
</div>

</body>
</html>