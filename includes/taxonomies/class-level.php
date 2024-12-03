<?php
namespace UpProgramme\Taxonomies;

class Level {
    private $default_terms = [
        'rdc' => 'RDC',
        'plein-pied' => 'Plein pied',
        'r1' => 'R+1',
        'r2' => 'R+2',
        'r3' => 'R+3',
        'r4' => 'R+4',
        'r5' => 'R+5',
        'r6' => 'R+6',
        'r7' => 'R+7'
    ];

    public function register() {
        add_action('init', [$this, 'register_taxonomy']);
        add_action('init', [$this, 'register_default_terms']);
    }

    public function register_taxonomy() {
        $args = [
            'hierarchical' => true,
            'labels' => [
                'name' => 'Étage',
                'singular_name' => 'Étage',
                'search_items' => 'Rechercher des étages',
                'all_items' => 'Tous les étages',
                'parent_item' => 'Étage parent',
                'parent_item_colon' => 'Étage parent:',
                'edit_item' => 'Modifier l\'étage',
                'update_item' => 'Mettre à jour l\'étage',
                'add_new_item' => 'Ajouter un nouvel étage',
                'new_item_name' => 'Nom du nouvel étage',
                'menu_name' => 'Étages'
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'etage'],
            'show_in_rest' => true
        ];

        register_taxonomy('up_program_taxonomy_level', ['up_program_program', 'up_program_lot'], $args);
    }

    public function register_default_terms() {
        // Vérifie si les termes ont déjà été créés
        $option_name = 'up_program_default_levels_created';
        if (get_option($option_name)) {
            return;
        }

        // Crée chaque terme par défaut
        foreach ($this->default_terms as $slug => $name) {
            if (!term_exists($slug, 'up_program_taxonomy_level')) {
                wp_insert_term(
                    $name,
                    'up_program_taxonomy_level',
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