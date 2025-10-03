<?php

if (!function_exists('getBoxColor')) {
    function getBoxColor($statut) {
        return match($statut) {
            'libre' => '#28a745',
            'occupe' => '#dc3545',
            'reserve' => '#ffc107',
            'maintenance' => '#fd7e14',
            'hors_service' => '#6c757d',
            default => '#6c757d'
        };
    }
}

if (!function_exists('getStatusIcon')) {
    function getStatusIcon($statut) {
        return match($statut) {
            'libre' => 'fa-unlock',
            'occupe' => 'fa-lock',
            'reserve' => 'fa-clock',
            'maintenance' => 'fa-tools',
            'hors_service' => 'fa-ban',
            default => 'fa-question'
        };
    }
}
