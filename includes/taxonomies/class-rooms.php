<?php
namespace UpProgramme\Taxonomies;

class Rooms {
    private $default_terms = [
        "1" => "1",
        "2" => "2",
        "3" => "3",
        "4" => "4",
        "5" => "5",
        "6" => "6",
        "7" => "7",
        "8" => "8",
        "9" => "9",
        "10" => "10"
    ];

    public function register() {
        add_action('init', [$this, 'register_taxonomy']);
        add_action('init', [$this, 'register_default_terms']);
    }

    public function register_taxonomy() {
        $args = [
            'hierarchical' => true,
            'labels' => [
                'name' => 'Pièces',
                'singular_name' => 'Pièces',
                'search_items' => 'Rechercher des pièces',
                'all_items' => 'Toutes les pièces',
                'parent_item' => 'Pièce parente',
                'parent_item_colon' => 'Pièce parente:',
                'edit_item' => 'Modifier la pièce',
                'update_item' => 'Mettre à jour la pièce',
                'add_new_item' => 'Ajouter une nouvelle pièce',
                'new_item_name' => 'Nom de la nouvelle pièce',
                'menu_name' => 'Pièces'
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'piece'],
            'show_in_rest' => true
        ];

        register_taxonomy('up_program_taxonomy_rooms', ['up_program_program', 'up_program_lot'], $args);
    }

    public function register_default_terms() {
        // Vérifie si les termes ont déjà été créés
        $option_name = 'up_program_default_rooms_created';
        if (get_option($option_name)) {
            return;
        }

        // Crée chaque terme par défaut
        foreach ($this->default_terms as $slug => $name) {
            if (!term_exists($slug, 'up_program_taxonomy_rooms')) {
                wp_insert_term(
                    $name,
                    'up_program_taxonomy_rooms',
                    [
                        'slug' => $slug
                    ]
                );
            }
        }

        // Marque les termes comme créés
        update_option($option_name, true);
    }
} 