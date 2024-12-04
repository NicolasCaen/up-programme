<?php

function format_price($value, $request, $args) {
    return $value ? (int)$value . ' €' : '0 €';
}

function format_surface($value, $request, $args) {
    return $value ? (int)$value . ' m²' : '0 m²';
} 
function format_lot_number($value, $request, $args) {
    return $value ?  'Lot n°'.(int)$value : 'Numéro de lot non défini';
} 

/**
 * Filtre pour formater les prix
 * 
 * @param mixed $value La valeur à formater
 * @param mixed $raw_value La valeur brute depuis la base de données
 * @param int $object_id L'ID de l'objet (post, user, etc.)
 * @return string Le prix formaté
 */
add_filter('format_up_price', function($value, $raw_value, $object_id) {
    // Si la valeur est vide, retourner une chaîne vide
    if (empty($value)) {
        return '';
    }

    // Convertir en nombre et s'assurer qu'il est positif
    $price = abs(floatval($value));
    
    // Formater le prix avec 2 décimales et le symbole €
    $formatted_price = number_format($price, 2, ',', ' ') . ' €';
    
    return $formatted_price;
}, 10, 3);

add_filter('format_up_surface', function($value, $raw_value, $object_id) {
    // Si la valeur est vide, retourner une chaîne vide
    if (empty($value)) {
        return '';
    }

    // Convertir en nombre et s'assurer qu'il est positif
    $price = abs(floatval($value));
    
    // Formater le prix avec 2 décimales et le symbole €
    $formatted_price = number_format($price, 0, ',', ' ') . ' m²';
    
    return $formatted_price;
}, 10, 3);