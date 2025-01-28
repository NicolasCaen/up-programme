<?php
namespace UpProgramme\Taxonomies;

class TaxLand {
    public function register() {
        add_action('init', [$this, 'register_taxonomy']);
        add_action('save_post_up_program_land', [$this, 'sync_land_to_taxonomy'], 10, 3);
        add_action('before_delete_post', [$this, 'maybe_delete_land_term']);
    }

    public function register_taxonomy() {
        $args = [
            'hierarchical' => true,
            'labels' => [
                'name' => 'Lands',
                'singular_name' => 'Land',
                'search_items' => 'Rechercher des lands',
                'all_items' => 'Tous les lands',
                'parent_item' => 'Land parent',
                'parent_item_colon' => 'Land parent:',
                'edit_item' => 'Modifier le land',
                'update_item' => 'Mettre à jour le land',
                'add_new_item' => 'Ajouter un nouveau land',
                'new_item_name' => 'Nom du nouveau land',
                'menu_name' => 'Terrains'
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'land'],
            'show_in_rest' => true
        ];

        register_taxonomy('up_program_taxonomy_land', ['up_program_parcel'], $args);
    }

    /**
     * Synchronise le land avec la taxonomie
     */
    public function sync_land_to_taxonomy($post_id, $post, $update) {
        // Vérifie si c'est une révision ou un auto-save
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        // Récupère le titre de la parcelle
        $land_title = get_the_title($post_id);
        
        // Crée ou met à jour le terme correspondant
        $term = term_exists($land_title, 'up_program_taxonomy_land');
        
        if (!$term) {
            // Crée un nouveau terme si n'existe pas
            $result = wp_insert_term(
                $land_title,
                'up_program_taxonomy_land',
                [
                    'slug' => sanitize_title($land_title)
                ]
            );
            
            if (!is_wp_error($result)) {
                // Stocke l'ID du terme dans une meta du post pour référence future
                update_post_meta($post_id, '_up_land_term_id', $result['term_id']);
            }
        } else {
            // Met à jour uniquement le nom du terme, pas le slug
            wp_update_term(
                $term['term_id'],
                'up_program_taxonomy_land',
                [
                    'name' => $land_title
                ]
            );
        }
    }

    /**
     * Supprime le terme correspondant quand une parcelle est supprimée
     */
    public function maybe_delete_land_term($post_id) {
        // Vérifie si c'est une parcelle
        if (get_post_type($post_id) !== 'up_program_parcel') {
            return;
        }

        // Récupère l'ID du terme associé depuis les meta
        $term_id = get_post_meta($post_id, '_up_land_term_id', true);
        
        // Si pas de term_id dans les meta, essaie de trouver par le titre
        if (!$term_id) {
            $land_title = get_the_title($post_id);
            $term = term_exists($land_title, 'up_program_taxonomy_land');
            if ($term) {
                $term_id = $term['term_id'];
            }
        }
        
        // Supprime le terme s'il existe
        if ($term_id) {
            wp_delete_term($term_id, 'up_program_taxonomy_land');
        }
    }
}
