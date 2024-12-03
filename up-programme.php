<?php
/**
 * Plugin Name: Citizim Immobilier
 * Description: Plugin pour gérer les programmes immobiliers et terrains.
 * Version: 1.0
 * Author: GEHIN Nicolas
 */

if (!defined('ABSPATH')) {
    exit;
}

// Définition des constantes
define('UP_PROGRAMME_VERSION', '1.0.0');
define('UP_PROGRAMME_FILE', __FILE__);
define('UP_PROGRAMME_PATH', plugin_dir_path(__FILE__));
define('UP_PROGRAMME_URL', plugin_dir_url(__FILE__));

// Fonction de débogage
function up_debug($message) {
    if (WP_DEBUG === true) {
        error_log(print_r($message, true));
    }
}

// Autoloader amélioré
spl_autoload_register(function ($class) {
    $prefix = 'UpProgramme\\';
    $base_dir = UP_PROGRAMME_PATH . 'includes/';

    // Vérifie si la classe utilise notre namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Récupère le nom relatif de la classe
    $relative_class = substr($class, $len);

    // Convertit le namespace en chemin de fichier
    $parts = explode('\\', $relative_class);
    $class_name = array_pop($parts);
    
    // Convertit le nom de la classe en nom de fichier
    $file_name = 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';
    
    // Construit le chemin complet
    $path_parts = array_map(function($part) {
        $part = strtolower($part);
        // Gestion spéciale pour certains dossiers
        switch ($part) {
            case 'posttypes':
                return 'post-types';
            case 'filters':
                return 'admin/filters';
            default:
                return $part;
        }
    }, $parts);
    
    $file = $base_dir . implode('/', $path_parts);
    
    if (!empty($path_parts)) {
        $file .= '/';
    }
    
    $file .= $file_name;

    up_debug('Trying to load: ' . $file);

    // Si le fichier existe, on le charge
    if (file_exists($file)) {
        require $file;
        up_debug('File loaded successfully: ' . $file);
        return true;
    }
    
    up_debug('File not found: ' . $file);
    return false;
});

// Chargement de l'initialisation
require_once UP_PROGRAMME_PATH . 'includes/class-init.php';

// Initialisation du plugin
if (class_exists('UpProgramme\\Init')) {
    UpProgramme\Init::register_services();
    up_debug('Plugin initialized');
}
