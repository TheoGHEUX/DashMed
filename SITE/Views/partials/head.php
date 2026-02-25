<?php

/**
 * Partial : En-tête HTML commun
 *
 * Génère la balise <head> avec meta, titre, styles et scripts.
 *
 * Charge automatiquement les styles communs et le dark mode.
 *
 * Variables attendues :
 *  - $pageTitle       (string)  Titre de la page
 *  - $pageDescription (string)  Meta description
 *  - $pageStyles      (array)   Styles spécifiques
 *  - $pageScripts     (array)   Scripts spécifiques
 *
 * @package Views
 */

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? '', ENT_QUOTES) ?>">

    <title><?= htmlspecialchars($pageTitle ?? 'DashMed', ENT_QUOTES) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/assets/style/body_main_container.css">
    <link rel="stylesheet" href="/assets/style/header.css">
    <link rel="stylesheet" href="/assets/style/footer.css">
    <link rel="stylesheet" href="/assets/style/dark-mode.css">

    <?php
    // Styles spécifiques
    foreach ($pageStyles ?? [] as $href) {
        echo '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES) . '">' . PHP_EOL;
    }
    ?>

    <link rel="icon" href="/assets/images/logo.png">

    <?php
    // Scripts spécifiques
    foreach ($pageScripts ?? [] as $src) {
        echo '<script src="' . htmlspecialchars($src, ENT_QUOTES) . '" defer></script>' . PHP_EOL;
    }

    // dark mode (sans defer)
    echo '<script src="/assets/script/dark-mode.js"></script>' . PHP_EOL;

    // header responsive
    echo '<script src="/assets/script/header_responsive.js" defer></script>' . PHP_EOL;
    ?>
</head>
