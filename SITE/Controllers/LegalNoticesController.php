<?php
namespace Controllers;

final class LegalNoticesController
{
    public function show(): void
    {
        \View::render('legal-notices');
    }
}
