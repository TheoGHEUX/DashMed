<?php

namespace App\Models\Doctor\Enums;

final class Specialite
{
    public const LISTE = [
        'Addictologie',
        'Algologie',
        'Allergologie',
        'Anesthésie-Réanimation',
        'Cancérologie',
        'Cardio-vasculaire HTA',
        'Chirurgie',
        'Dermatologie',
        'Diabétologie-Endocrinologie',
        'Génétique',
        'Gériatrie',
        'Gynécologie-Obstétrique',
        'Hématologie',
        'Hépato-gastro-entérologie',
        'Imagerie médicale',
        'Immunologie',
        'Infectiologie',
        'Médecine du sport',
        'Médecine du travail',
        'Médecine générale',
        'Médecine légale',
        'Médecine physique et de réadaptation',
        'Néphrologie',
        'Neurologie',
        'Nutrition',
        'Ophtalmologie',
        'ORL',
        'Pédiatrie',
        'Pneumologie',
        'Psychiatrie',
        'Radiologie',
        'Rhumatologie',
        'Sexologie',
        'Toxicologie',
        'Urologie'
    ];

    public static function all()
    {
        return self::LISTE;
    }
}