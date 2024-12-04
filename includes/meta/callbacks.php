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

// Dans votre thème ou plugin
