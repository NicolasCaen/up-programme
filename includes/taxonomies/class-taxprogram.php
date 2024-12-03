<?php
namespace UpProgramme\Taxonomies;

class TaxProgram {
    public function register() {

        add_action('init', [$this, 'register_taxonomy']);
        add_action('save_post_up_program_program', [$this, 'sync_program_to_taxonomy'], 10, 3);
        add_action('before_delete_post', [$this, 'maybe_delete_program_term']);
    }

    public function register_taxonomy() {
        $args = [
            'hierarchical' => true,
            'labels' => [
                'name' => 'Programme',
                'singular_name' => 'Programme',
                'search_items' => 'Rechercher des programmes',
                'all_items' => 'Tous les programmes',
                'parent_item' => 'Programme parent',
                'parent_item_colon' => 'Programme parent:',
                'edit_item' => 'Modifier le programme',
                'update_item' => 'Mettre à jour le programme',
                'add_new_item' => 'Ajouter un nouveau programme',
                'new_item_name' => 'Nom du nouveau programme',
                'menu_name' => 'Programmes'
            ],
            'show_ui' =>true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'program'],
            'show_in_rest' => true
        ];

        register_taxonomy('up_program_taxonomy_program', ['up_program_lot'], $args);
    }

    /**
     * Synchronise le programme avec la taxonomie
     */
    public function sync_program_to_taxonomy($post_id, $post, $update) {
        // Vérifie si c'est une révision ou un auto-save
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        // Récupère le titre du programme
        $program_title = get_the_title($post_id);
        
        // Crée ou met à jour le terme correspondant
        $term = term_exists($program_title, 'up_program_taxonomy_program');
        
        if (!$term) {
            // Crée un nouveau terme si n'existe pas
            $result = wp_insert_term(
                $program_title,
                'up_program_taxonomy_program',
                [
                    'slug' => sanitize_title($program_title)
                ]
            );
            
            if (!is_wp_error($result)) {
                // Stocke l'ID du terme dans une meta du post pour référence future
                update_post_meta($post_id, '_up_program_term_id', $result['term_id']);
            }
        } else {
            // Met à jour uniquement le nom du terme, pas le slug
            wp_update_term(
                $term['term_id'],
                'up_program_taxonomy_program',
                [
                    'name' => $program_title
                ]
            );
        }
    }

    /**
     * Supprime le terme correspondant quand un programme est supprimé
     */
    public function maybe_delete_program_term($post_id) {
        // Vérifie si c'est un programme
        if (get_post_type($post_id) !== 'up_program_program') {
            return;
        }

        // Récupère l'ID du terme associé depuis les meta
        $term_id = get_post_meta($post_id, '_up_program_term_id', true);
        
        // Si pas de term_id dans les meta, essaie de trouver par le titre
        if (!$term_id) {
            $program_title = get_the_title($post_id);
            $term = term_exists($program_title, 'up_program_taxonomy_program');
            if ($term) {
                $term_id = $term['term_id'];
            }
        }
        
        // Supprime le terme s'il existe
        if ($term_id) {
            wp_delete_term($term_id, 'up_program_taxonomy_program');
        }
    }
} 