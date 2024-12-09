<?php
namespace UpProgramme\Taxonomies;

class City {
    public function register() {
        add_action('init', [$this, 'register_taxonomy']);
        
        // Ajouter les colonnes personnalisées
        add_filter('manage_edit-up_program_taxonomy_city_columns', [$this, 'add_ville_columns']);
        add_filter('manage_up_program_taxonomy_city_custom_column', [$this, 'add_ville_column_content'], 10, 3);
    }

    public function register_taxonomy() {
        $args = [
            'hierarchical' => true,
            'labels' => [
                'name' => 'Villes',
                // ... autres labels
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'ville'],
            'show_in_rest' => true,
        ];

        register_taxonomy('up_program_taxonomy_city', ['up_program_program', 'up_program_land'], $args);
    }

    // Ajouter les colonnes personnalisées
    public function add_ville_columns($columns) {
        $new_columns = array();
        
        // Conserver la colonne du nom
        if (isset($columns['name'])) {
            $new_columns['name'] = $columns['name'];
        }
        
        // Ajouter nos colonnes personnalisées
        $new_columns['ville_id'] = 'ID';
        $new_columns['ville_identifiant'] = 'Identifiant';
        
        // Conserver les autres colonnes
        if (isset($columns['description'])) {
            $new_columns['description'] = $columns['description'];
        }
        if (isset($columns['slug'])) {
            $new_columns['slug'] = $columns['slug'];
        }
        if (isset($columns['posts'])) {
            $new_columns['posts'] = $columns['posts'];
        }

        return $new_columns;
    }

    // Ajouter le contenu des colonnes personnalisées
    public function add_ville_column_content($content, $column_name, $term_id) {
        switch ($column_name) {
            case 'ville_id':
                $ville_id = get_term_meta($term_id, 'id', true);
                return $ville_id ? $ville_id : '-';
            
            case 'ville_identifiant':
                $ville_identifiant = get_term_meta($term_id, 'identifiant', true);
                return $ville_identifiant ? $ville_identifiant : '-';
        }
        return $content;
    }
} 