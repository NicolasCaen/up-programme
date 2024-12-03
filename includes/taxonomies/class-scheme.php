<?php
namespace UpProgramme\Taxonomies;

class Scheme {
    private $default_terms = [
        'demembrement' => 'Démembrement',
        'location-accession' => 'Location / Accession',
        'anru' => 'ANRU',
        'residence-principale' => 'Résidence principale ou Investissement locatif',
        'aucun' => 'Aucun',
        'pls' => 'Pls',
        'pinel' => 'Pinel',
        'lancement-commercial' => 'Lancement Commercial',
        'achat-libre' => 'Achat libre',
    ];

    public function register() {
        add_action('init', [$this, 'register_taxonomy']);
        add_action('init', [$this, 'register_default_terms']);
    }

    public function register_taxonomy() {
        $args = [
            'hierarchical' => true,
            'labels' => [
                'name' => 'Dispositif',
                'singular_name' => 'Dispositif',
                'search_items' => 'Rechercher des dispositifs',
                'all_items' => 'Tous les dispositifs',
                'parent_item' => 'Dispositif parent',
                'parent_item_colon' => 'Dispositif parent:',
                'edit_item' => 'Modifier le dispositif',
                'update_item' => 'Mettre à jour le dispositif',
                'add_new_item' => 'Ajouter un nouveau dispositif',
                'new_item_name' => 'Nom du nouveau dispositif',
                'menu_name' => 'Dispositifs'
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'dispositif'],
            'show_in_rest' => true
        ];

        register_taxonomy('up_program_taxonomy_scheme', ['up_program_program', 'up_program_lot'], $args);
    }

    public function register_default_terms() {
        // Vérifie si les termes ont déjà été créés
        $option_name = 'up_program_default_schemes_created';
        if (get_option($option_name)) {
            return;
        }

        // Crée chaque terme par défaut
        foreach ($this->default_terms as $slug => $name) {
            if (!term_exists($slug, 'up_program_taxonomy_scheme')) {
                wp_insert_term(
                    $name,
                    'up_program_taxonomy_scheme',
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