<?php
/*
Plugin Name: Citizim Immobilier
Description: Plugin pour gérer les programmes immobiliers et terrains.
Version: 1.0
Author: GEHIN nicolas
*/

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Enregistrer les types de contenu personnalisés
function up_register_custom_post_types() {
    // Programme Immobilier
    $labels_program = array(
        'name' => 'Programmes',
        'singular_name' => 'Programme Immobilier',
        'add_new' => 'Ajouter Nouveau',
        'add_new_item' => 'Ajouter Nouveau Programme',
        'edit_item' => 'Modifier Programme',
        'new_item' => 'Nouveau Programme',
        'view_item' => 'Voir Programme',
        'search_items' => 'Rechercher Programmes',
        'not_found' => 'Aucun programme trouvé',
        'not_found_in_trash' => 'Aucun programme trouvé dans la corbeille',
    );

    $args_program = array(
        'labels' => $labels_program,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail','editor'),
        'show_in_rest' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-admin-home',
        'rewrite' => array('slug' => 'neuf'),
    );

    register_post_type('real_estate_program', $args_program);

    // Terrain
    $labels_land = array(
        'name' => 'Terrains',
        'singular_name' => 'Terrain',
        'add_new' => 'Ajouter Nouveau',
        'add_new_item' => 'Ajouter Nouveau Terrain',
        'edit_item' => 'Modifier Terrain',
        'new_item' => 'Nouveau Terrain',
        'view_item' => 'Voir Terrain',
        'search_items' => 'Rechercher Terrains',
        'not_found' => 'Aucun terrain trouvé',
        'not_found_in_trash' => 'Aucun terrain trouvé dans la corbeille',
    );

    $args_land = array(
        'labels' => $labels_land,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_position' => 6,
        'menu_icon' => 'dashicons-admin-site',
    );

    register_post_type('land', $args_land);
}
add_action('init', 'up_register_custom_post_types');

// Ajouter des champs personnalisés pour Programme Immobilier
function up_add_program_meta_boxes() {
    add_meta_box(
        'up_program_details',
        'Détails du Programme',
        'up_render_program_details_box',
        'real_estate_program',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'up_add_program_meta_boxes');

function up_render_program_details_box($post) {
    $location = get_post_meta($post->ID, '_up_location', true);
    $price = get_post_meta($post->ID, '_up_price', true);

    ?>
    <label for="up_location">Emplacement :</label>
    <input type="text" id="up_location" name="up_location" value="<?php echo esc_attr($location); ?>" />

    <label for="up_price">Prix :</label>
    <input type="text" id="up_price" name="up_price" value="<?php echo esc_attr($price); ?>" />
    <?php
}

// Ajouter des champs personnalisés pour Terrain
function up_add_land_meta_boxes() {
    add_meta_box(
        'up_land_details',
        'Détails du Terrain',
        'up_render_land_details_box',
        'land',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'up_add_land_meta_boxes');

function up_render_land_details_box($post) {
    $size = get_post_meta($post->ID, '_up_size', true);
    $price = get_post_meta($post->ID, '_up_price', true);

    ?>
    <label for="up_size">Taille :</label>
    <input type="text" id="up_size" name="up_size" value="<?php echo esc_attr($size); ?>" />

    <label for="up_price">Prix :</label>
    <input type="text" id="up_price" name="up_price" value="<?php echo esc_attr($price); ?>" />
    <?php
}

// Sauvegarder les champs personnalisés
function up_save_custom_meta($post_id) {
    if (array_key_exists('up_location', $_POST)) {
        update_post_meta($post_id, '_up_location', sanitize_text_field($_POST['up_location']));
    }
    if (array_key_exists('up_price', $_POST)) {
        update_post_meta($post_id, '_up_price', sanitize_text_field($_POST['up_price']));
    }
    if (array_key_exists('up_size', $_POST)) {
        update_post_meta($post_id, '_up_size', sanitize_text_field($_POST['up_size']));
    }
}
add_action('save_post', 'up_save_custom_meta');

// Ajouter la taxonomie Ville
function up_register_city_taxonomy() {
    $labels = array(
        'name' => 'Villes',
        'singular_name' => 'Ville',
        'search_items' => 'Rechercher des villes',
        'all_items' => 'Toutes les villes',
        'parent_item' => 'Ville parente',
        'parent_item_colon' => 'Ville parente:',
        'edit_item' => 'Modifier la ville',
        'update_item' => 'Mettre à jour la ville',
        'add_new_item' => 'Ajouter une nouvelle ville',
        'new_item_name' => 'Nom de la nouvelle ville',
        'menu_name' => 'Villes'
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'ville'),
        'show_in_rest' => true, // Pour l'éditeur Gutenberg
    );

    // Enregistrer la taxonomie pour les deux types de contenu
    register_taxonomy(
        'city',
        array('real_estate_program', 'land'),
        $args
    );
}
add_action('init', 'up_register_city_taxonomy');

// Ajouter le CPT Lot Programme
function up_register_lot_post_type() {
    $labels = array(
        'name' => 'Lots',
        'singular_name' => 'Lot',
        'add_new' => 'Ajouter Nouveau',
        'add_new_item' => 'Ajouter Nouveau Lot',
        'edit_item' => 'Modifier Lot',
        'new_item' => 'Nouveau Lot',
        'view_item' => 'Voir Lot',
        'search_items' => 'Rechercher Lots',
        'not_found' => 'Aucun lot trouvé',
        'not_found_in_trash' => 'Aucun lot trouvé dans la corbeille',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_position' => 7,
        'menu_icon' => 'dashicons-admin-multisite',
        'show_in_rest' => true,
    );

    register_post_type('lot_program', $args);
}
add_action('init', 'up_register_lot_post_type');

// Ajouter les champs personnalisés pour Lot
function up_add_lot_meta_boxes() {
    add_meta_box(
        'up_lot_details',
        'Détails du Lot',
        'up_render_lot_details_box',
        'lot_program',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'up_add_lot_meta_boxes');

function up_render_lot_details_box($post) {
    $surface = get_post_meta($post->ID, '_up_surface', true);
    $price = get_post_meta($post->ID, '_up_price', true);
    $rooms = get_post_meta($post->ID, '_up_rooms', true);
    $program = get_post_meta($post->ID, '_up_program_id', true);
    $pdf_url = get_post_meta($post->ID, '_up_pdf_file', true);

    // Récupérer tous les programmes immobiliers
    $programs = get_posts(array(
        'post_type' => 'real_estate_program',
        'posts_per_page' => -1,
    ));

    // Ajouter l'attribut enctype pour permettre l'upload de fichiers
    wp_nonce_field(basename(__FILE__), 'up_lot_nonce');
    ?>
    <p>
        <label for="up_surface">Surface (m²) :</label>
        <input type="number" id="up_surface" name="up_surface" value="<?php echo esc_attr($surface); ?>" />
    </p>

    <p>
        <label for="up_price">Prix :</label>
        <input type="number" id="up_price" name="up_price" value="<?php echo esc_attr($price); ?>" />
    </p>

    <p>
        <label for="up_rooms">Nombre de pièces :</label>
        <input type="number" id="up_rooms" name="up_rooms" value="<?php echo esc_attr($rooms); ?>" />
    </p>

    <p>
        <label for="up_program_id">Programme immobilier :</label>
        <select id="up_program_id" name="up_program_id">
            <option value="">Sélectionner un programme</option>
            <?php foreach ($programs as $prog) : ?>
                <option value="<?php echo $prog->ID; ?>" <?php selected($program, $prog->ID); ?>>
                    <?php echo esc_html($prog->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="up_pdf_file">Fichier PDF :</label>
        <input type="file" id="up_pdf_file" name="up_pdf_file" accept=".pdf" />
        <?php if ($pdf_url) : ?>
            <br>
            <a href="<?php echo esc_url($pdf_url); ?>" target="_blank">Voir le PDF actuel</a>
            <label>
                <input type="checkbox" name="delete_pdf" value="1"> Supprimer le PDF
            </label>
        <?php endif; ?>
    </p>
    <?php
}

// Modifier la fonction de sauvegarde pour gérer le PDF
function up_save_lot_meta($post_id) {
    // Vérifier le nonce
    if (!isset($_POST['up_lot_nonce']) || !wp_verify_nonce($_POST['up_lot_nonce'], basename(__FILE__))) {
        return;
    }

    // Sauvegarder les champs standards
    $fields = array('surface', 'price', 'rooms', 'program_id');
    foreach ($fields as $field) {
        if (array_key_exists('up_' . $field, $_POST)) {
            update_post_meta(
                $post_id,
                '_up_' . $field,
                sanitize_text_field($_POST['up_' . $field])
            );
        }
    }

    // Gérer la suppression du PDF
    if (isset($_POST['delete_pdf']) && $_POST['delete_pdf'] == '1') {
        $old_pdf_url = get_post_meta($post_id, '_up_pdf_file', true);
        if ($old_pdf_url) {
            $upload_dir = wp_upload_dir();
            $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $old_pdf_url);
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            delete_post_meta($post_id, '_up_pdf_file');
        }
    }

    // Gérer l'upload du PDF
    if (!empty($_FILES['up_pdf_file']['name'])) {
        // Vérifier le type de fichier
        $file_type = wp_check_filetype($_FILES['up_pdf_file']['name']);
        if ($file_type['type'] != 'application/pdf') {
            return;
        }

        // Configurer l'upload
        require_once(ABSPATH . 'wp-admin/includes/file.php');

        // Définir le chemin d'upload personnalisé
        add_filter('upload_dir', 'up_custom_upload_dir');
        $upload = wp_handle_upload(
            $_FILES['up_pdf_file'],
            array('test_form' => false)
        );
        remove_filter('upload_dir', 'up_custom_upload_dir');

        if (!empty($upload['error'])) {
            return;
        }

        // Sauvegarder l'URL du fichier
        update_post_meta($post_id, '_up_pdf_file', $upload['url']);
    }
}
add_action('save_post_lot_program', 'up_save_lot_meta');

// Ajouter le support pour les uploads de fichiers dans le formulaire
function up_lot_form_enctype() {
    global $post;
    if ($post && get_post_type($post) === 'lot_program') {
        echo ' enctype="multipart/form-data"';
    }
}
add_action('post_edit_form_tag', 'up_lot_form_enctype');

// Fonction pour définir le chemin d'upload personnalisé
function up_custom_upload_dir($dirs) {
    $dirs['subdir'] = '/lots';
    $dirs['path'] = $dirs['basedir'] . '/lots';
    $dirs['url'] = $dirs['baseurl'] . '/lots';
    return $dirs;
}
