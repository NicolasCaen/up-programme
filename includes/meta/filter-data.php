<?php

function format_price($value) {
    if (empty($value)) {return '';}
    $price = abs(floatval($value));
    $formatted_price = number_format($price, 2, ',', ' ') . ' €';
    return $formatted_price;
}

function format_surface($value) {
    return $value ? (int)$value . ' m²' : '0 m²';
} 
function format_lot_number($value) {
    return $value ?  'Lot n°'.$value : 'Numéro de lot non défini';
} 


add_filter('format_up_price', function($value, $raw_value, $object_id) {
    // Si la valeur est vide, retourner une chaîne vide
    if (empty($value)) {
        return '';
    }
    
    // Si le signe € est déjà présent, retourner la valeur telle quelle
    if (strpos($value, '€') !== false) {
        return $value;
    }

    // Convertir en nombre et s'assurer qu'il est positif
    $price = abs(floatval($value));
    
    // Formater le prix avec 2 décimales et le symbole €
    $formatted_price = number_format($price, 2, ',', ' ') . ' €';
    
    return $formatted_price;
}, 10, 3);
add_filter('format_up_surface','format_surface', 10, 1);
add_filter('format_up_lot_number', 'format_lot_number', 10, 1);
