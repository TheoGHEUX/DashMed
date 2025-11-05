<?php
namespace Controllers;

/**
 * Contrôleur des mentions légales.
 */
final class LegalNoticesController
{
    /**
     * Affiche la page des mentions légales.
     *
     * @return void
     */
    public function show(): void
    {
        \View::render('legal-notices');
    }
}
