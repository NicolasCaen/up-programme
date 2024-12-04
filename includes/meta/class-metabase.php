<?php
namespace UpProgramme\Meta;

require_once 'filter-data.php';

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
                'show_in_rest' => [
                    'schema' => [
                        'type' => 'string'
                    ]
                ]
            ];

            // Assurer la cohérence entre le type et la valeur par défaut
            if (isset($args['type'])) {
                $default_args['type'] = $args['type'];
                $default_args['show_in_rest']['schema']['type'] = $args['type'];
                
                // Ajuster la valeur par défaut selon le type
                switch ($args['type']) {
                    case 'integer':
                        $default_args['default'] = 0;
                        break;
                    case 'number':
                        $default_args['default'] = 0.0;
                        break;
                    case 'boolean':
                        $default_args['default'] = false;
                        break;
                    case 'array':
                        $default_args['default'] = [];
                        break;
                    case 'object':
                        $default_args['default'] = new \stdClass();
                        break;
                    default: // string
                        $default_args['default'] = '';
                }
            }

            // Création d'une fonction de callback pour le formatage
            $format_callback = function($value) use ($meta_key) {
                if (empty($value)) {
                    return $value;
                }
                
                $filter_name = 'format_' . strtolower($meta_key);
                if (has_filter($filter_name)) {
                    return apply_filters($filter_name, $value, $value, get_the_ID());
                }
                return $value;
            };

            // Configuration du REST API
            $default_args['show_in_rest'] = [
                'schema' => [
                    'type' => $args['type'] ?? 'string'
                ],
                'prepare_callback' => $format_callback,
                'get_callback' => $format_callback
            ];

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
        // On enregistre un filtre pour chaque meta_field, qu'il ait un callback ou non
        foreach ($this->meta_fields as $meta_key => $args) {
            $callback = isset($args['get_callback']) ? $args['get_callback'] : null;
            $this->add_meta_format_filter($meta_key, $callback);
        }
    }

    /**
     * Applique un callback sur une meta spécifique pour
     * 
     * @param string $meta_key La clé de la meta à formater
     * @param callable $callback La fonction de callback à appliquer
     */
    protected function add_meta_format_filter($meta_key, $callback = null) {
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
                
                // Si un callback spécifique est défini, on l'applique d'abord
                $formatted_value = $callback ? $callback($raw_value, null, null) : $raw_value;
                
                // Applique ensuite le filtre spécifique s'il existe
                $filter_name = 'format_' . strtolower($meta_key);
                if (has_filter($filter_name)) {
                    return apply_filters($filter_name, $formatted_value, $raw_value, $object_id);
                }
                
                return $formatted_value;
            }
            
            return $value;
        }, 10, 4);
    }

    protected function set_meta_fields(array $fields): void {
        $this->meta_fields = $fields;
    }
}
