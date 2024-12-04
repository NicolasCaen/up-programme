<?php
namespace UpProgramme\Meta;

abstract class MetaBase {
    protected $object_type = 'post'; // CEci est toujours post pour tous les cpt, sinon ca peut etre comment, user,...
    protected $meta_fields = [];
    protected $post_type = 'post'; 

    public function register() {
        add_action('init', [$this, 'register_meta_fields']);
        $this->register_meta_format_filters();
    }

    public function register_meta_fields() {
        foreach ($this->meta_fields as $meta_key => $args) {
            $default_args = [
                'single' => true,
                'type' => 'string',
                'default' => '',
            ];

            // Configuration du REST API et de l'affichage
            if (isset($args['get_callback']) || isset($args['update_callback'])) {
                $default_args['show_in_rest'] = [
                    'schema' => [
                        'type' => 'string'
                    ],
                    'prepare_callback' => $args['get_callback'] ?? null,
                    'get_callback' => $args['get_callback'] ?? null,
                ];
                
                if (isset($args['update_callback'])) {
                    $default_args['show_in_rest']['update_callback'] = $args['update_callback'];
                }
            } else {
                $default_args['show_in_rest'] = true;
            }

            register_meta(
                $this->object_type,
                $meta_key,
                array_merge($default_args, $args, [
                    'object_subtype' => $this->post_type
                ])
            );
        }
    }

    /**
     * Enregistre les filtres de formatage pour les metas
     */
    protected function register_meta_format_filters() {
        foreach ($this->meta_fields as $meta_key => $args) {
            if (isset($args['get_callback'])) {
                $this->add_meta_format_filter($meta_key, $args['get_callback']);
            }
        }
    }

    /**
     * Applique un callback sur une meta spécifique pour
     * 
     * @param string $meta_key La clé de la meta à formater
     * @param callable $callback La fonction de callback à appliquer
     */
    protected function add_meta_format_filter($meta_key, $callback) {
        add_filter('get_post_metadata', function($value, $object_id, $current_meta_key, $single) 
            use ($meta_key, $callback) {
            // Si la valeur est déjà définie ou si ce n'est pas la bonne meta_key, on retourne la valeur
            if ($value !== null || $current_meta_key !== $meta_key) {
                return $value;
            }
            
            // Vérifie si on n'est pas dans l'admin
            if (!is_admin()) {
                // Récupère la valeur brute directement depuis la base de données
                global $wpdb;
                $raw_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s",
                    $object_id,
                    $meta_key
                ));
                
                return $callback($raw_value, null, null);
            }
            
            return $value;
        }, 10, 4);
    }

    protected function set_meta_fields(array $fields): void {
        $this->meta_fields = $fields;
    }
}
