<?php
/**
 * Partial : head commun
 *
 * Section <head> partagée pour les vues. Gère titre, meta description,
 * styles communs, styles/scripts spécifiques, Google Fonts et scripts globaux.
 *
 * Variables attendues (optionnelles) :
 * @var string $pageTitle
 * @var string $pageDescription
 * @var array  $pageStyles
 * @var array  $pageScripts
 */
?>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? '', ENT_QUOTES) ?>">

    <title><?= htmlspecialchars($pageTitle ?? 'DashMed', ENT_QUOTES) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles communs -->
    <link rel="stylesheet" href="/assets/style/body_main_container.css">
    <link rel="stylesheet" href="/assets/style/header.css">
    <link rel="stylesheet" href="/assets/style/footer.css">
    <link rel="stylesheet" href="/assets/style/dark-mode.css">

    <?php
    // Styles spécifiques
    $pageStyles = $pageStyles ?? [];
    if (!empty($pageStyles) && is_array($pageStyles)) {
        foreach ($pageStyles as $href) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES) . '">' . PHP_EOL;
        }
    }
    ?>

    <link rel="icon" href="/assets/images/logo.png">

    <?php
    // Scripts spécifiques (defer)
    $pageScripts = $pageScripts ?? [];
    if (!empty($pageScripts) && is_array($pageScripts)) {
        foreach ($pageScripts as $src) {
            echo '<script src="' . htmlspecialchars($src, ENT_QUOTES) . '" defer></script>' . PHP_EOL;
        }
    }

    // dark-mode.js prioritaire (sans defer) pour éviter FOUC
    echo '<script src="/assets/script/dark-mode.js"></script>' . PHP_EOL;

    // Script global pour le header responsive (defer)
    echo '<script src="/assets/script/header_responsive.js" defer></script>' . PHP_EOL;
    ?>
</head>