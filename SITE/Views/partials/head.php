<?php
// Variables dynamiques attendues : $pageTitle, $pageDescription, $pageStyles (array), $pageScripts (array)
?>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? '', ENT_QUOTES) ?>">
    <title><?= htmlspecialchars($pageTitle ?? 'DashMed', ENT_QUOTES) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Styles principaux -->
    <link rel="stylesheet" href="/assets/style/body_main_container.css">
    <link rel="stylesheet" href="/assets/style/header.css">
    <link rel="stylesheet" href="/assets/style/footer.css">
    <?php
    // Styles spécifiques à la page
    if (!empty($pageStyles)) {
        foreach ($pageStyles as $href) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES) . '">' . PHP_EOL;
        }
    }
    ?>
    <link rel="icon" href="/assets/images/logo.png">
    <?php
    // Scripts spécifiques à la page
    if (!empty($pageScripts)) {
        foreach ($pageScripts as $src) {
            echo '<script src="' . htmlspecialchars($src, ENT_QUOTES) . '" defer></script>' . PHP_EOL;
        }
    } else {
        // Script global par défaut
        echo '<script src="/assets/script/header_responsive.js" defer></script>' . PHP_EOL;
    }
    ?>
</head>
