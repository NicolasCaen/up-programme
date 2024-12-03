<?php
namespace UpProgramme\Taxonomies;

class City {
    public function register() {
        add_action('init', [$this, 'register_taxonomy']);
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
        ];

        register_taxonomy('up_program_taxonomy_city', ['up_program_program', 'up_program_land'], $args);
    }
} 