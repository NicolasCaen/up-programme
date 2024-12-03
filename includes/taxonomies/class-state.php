<?php
namespace UpProgramme\Taxonomies;

class State {
    private $default_terms = [
        'vendu' => 'Vendu',
        'disponible' => 'Disponible',
        'optionne' => 'Optionné',
        'reserve' => 'Réservé',
        'autre' => 'Autre'
    ];

    public function register() {
        add_action('init', [$this, 'register_taxonomy']);
        add_action('init', [$this, 'register_default_terms']);
    }

    public function register_taxonomy() {
        $args = [
            'hierarchical' => true,
            'labels' => [
                'name' => 'État',
                'singular_name' => 'État',
                'search_items' => 'Rechercher des états',
                'all_items' => 'Tous les états',
                'parent_item' => 'État parent',
                'parent_item_colon' => 'État parent:',
                'edit_item' => 'Modifier l\'état',
                'update_item' => 'Mettre à jour l\'état',
                'add_new_item' => 'Ajouter un nouvel état',
                'new_item_name' => 'Nom du nouvel état',
                'menu_name' => 'États'
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'etat'],
            'show_in_rest' => true
        ];

        register_taxonomy('up_program_taxonomy_state', ['up_program_lot'], $args);
    }

    public function register_default_terms() {
        // Vérifie si les termes ont déjà été créés
        $option_name = 'up_program_default_states_created';
        if (get_option($option_name)) {
            return;
        }

        // Crée chaque terme par défaut
        foreach ($this->default_terms as $slug => $name) {
            if (!term_exists($slug, 'up_program_taxonomy_state')) {
                wp_insert_term(
                    $name,
                    'up_program_taxonomy_state',
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