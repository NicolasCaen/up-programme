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
               'name' => 'Villes', // Nom général de la taxonomie
                'singular_name' => 'Ville', // Nom au singulier
                'menu_name' => 'Villes', // Nom dans le menu d'administration
                'all_items' => 'Toutes les villes', // Texte pour afficher tous les termes
                'edit_item' => 'Modifier la ville', // Texte pour modifier un terme
                'view_item' => 'Voir la ville', // Texte pour voir un terme
                'update_item' => 'Mettre à jour la ville', // Texte pour mettre à jour un terme
                'add_new_item' => 'Ajouter une nouvelle ville', // Texte pour ajouter un nouveau terme
                'new_item_name' => 'Nom de la nouvelle ville', // Texte pour le nom du nouveau terme
                'parent_item' => 'Ville parente', // Texte pour le terme parent (si hiérarchique)
                'parent_item_colon' => 'Ville parente :', // Texte pour le terme parent avec deux-points
                'search_items' => 'Rechercher des villes', // Texte pour rechercher des termes
                'popular_items' => 'Villes populaires', // Texte pour les termes populaires
                'separate_items_with_commas' => 'Séparer les villes par des virgules', // Texte pour les taxonomies non hiérarchiques
                'add_or_remove_items' => 'Ajouter ou supprimer des villes', // Texte pour ajouter ou supprimer des termes
                'choose_from_most_used' => 'Choisir parmi les villes les plus utilisées', // Texte pour choisir parmi les termes les plus utilisés
                'not_found' => 'Aucune ville trouvée', // Texte si aucun terme n'est trouvé
                'back_to_items' => '← Retour aux villes', // Texte pour revenir à la liste des termes
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