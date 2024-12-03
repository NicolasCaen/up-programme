<?php
namespace UpProgramme\Taxonomies;

class PropertyType {
    private $default_terms = [
        'appartement' => 'Appartement',
        'maison' => 'Maison',
        'studio' => 'Studio',
        'duplex' => 'Duplex',
        'triplex' => 'Triplex',
        'loft' => 'Loft',
        'terrain' => 'Terrain',
        'local-commercial' => 'Local Commercial',
        'bureau' => 'Bureau',
        'parking' => 'Parking'
    ];

    public function register() {
        add_action('init', [$this, 'register_taxonomy']);
        add_action('init', [$this, 'register_default_terms']);
    }

    public function register_taxonomy() {
        $args = [
            'hierarchical' => true,
            'labels' => [
                'name' => 'Type de propriété',
                'singular_name' => 'Type de propriété',
                'search_items' => 'Rechercher des types de propriété',
                'all_items' => 'Tous les types de propriété',
                'parent_item' => 'Type parent',
                'parent_item_colon' => 'Type parent:',
                'edit_item' => 'Modifier le type',
                'update_item' => 'Mettre à jour le type',
                'add_new_item' => 'Ajouter un nouveau type',
                'new_item_name' => 'Nom du nouveau type',
                'menu_name' => 'Types de propriété'
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'type-propriete'],
            'show_in_rest' => true
        ];

        register_taxonomy('up_program_property_type', ['up_program_program', 'up_program_lot'], $args);
    }

    public function register_default_terms() {
        // Vérifie si les termes ont déjà été créés
        $option_name = 'up_program_default_property_types_created';
        if (get_option($option_name)) {
            return;
        }

        // Crée chaque terme par défaut
        foreach ($this->default_terms as $slug => $name) {
            if (!term_exists($slug, 'up_program_property_type')) {
                wp_insert_term(
                    $name,
                    'up_program_property_type',
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