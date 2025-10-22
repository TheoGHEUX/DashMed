<?php
namespace Controllers;

final class MapController
{
    public function show(): void
    {
        \View::render('map');
    }
}
