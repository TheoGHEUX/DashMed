<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Alerte Sécurité DashMed</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 5px;">

    <h2 style="color: #e53e3e;">Changement d'adresse email</h2>

    <p>Bonjour <?= htmlspecialchars($name) ?>,</p>

    <p>Votre adresse email de connexion DashMed vient d'être modifiée.</p>

    <p style="background-color: #fff5f5; border-left: 4px solid #e53e3e; padding: 10px;">
        <strong>Si vous n'êtes pas à l'origine de cette action</strong>, contactez immédiatement notre support car votre compte est peut-être compromis.
    </p>

    <p>Si vous avez effectué ce changement, vous pouvez ignorer cet email.</p>

    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="color: #999; font-size: 12px;">L'équipe DashMed</p>
</div>
</body>
</html>