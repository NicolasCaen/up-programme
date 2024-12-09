<?php
namespace UpProgramme\Admin\FilterColumns;
use WP_Query;
abstract class BaseFilter {
    protected $post_type;
    protected $taxonomies = [];
    private static $initialized_filters = [];

    public function __construct() {
        if (isset(self::$initialized_filters[$this->post_type])) {
            return;
        }

        self::$initialized_filters[$this->post_type] = true;

        add_action('restrict_manage_posts', [$this, 'add_admin_filters']);
        add_filter('parse_query', [$this, 'filter_posts_by_taxonomies']);

        // Ajout des actions AJAX
        // add_action('admin_enqueue_scripts', [$this, 'enqueue_filter_scripts']);
        // add_action('wp_ajax_update_admin_filter', [$this, 'handle_ajax_filter']);
    }

    /**
     * Ajoute les filtres dans l'interface d'administration
     */
    public function add_admin_filters() {
        global $typenow;

        if ($typenow != $this->post_type) {
            return;
        }

        foreach ($this->taxonomies as $taxonomy) {
            $taxonomy_obj = get_taxonomy($taxonomy);
            $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';

            wp_dropdown_categories([
                'show_option_all' => sprintf(__('Tous les %s', 'up-programme'), $taxonomy_obj->labels->name),
                'taxonomy' => $taxonomy,
                'name' => $taxonomy,
                'orderby' => 'name',
                'selected' => $selected,
                'hierarchical' => true,
                'depth' => 3,
                'show_count' => true,
                'hide_empty' => false,
                'class' => 'taxonomy-filter'
            ]);
        }
    }

    /**
     * Filtre les posts en fonction des taxonomies sélectionnées
     */
    public function filter_posts_by_taxonomies($query) {
        global $pagenow, $wpdb;

        if (!is_admin() ||
            $pagenow !== 'edit.php' ||
            !isset($query->query['post_type']) ||
            $query->query['post_type'] !== $this->post_type ||
            !$query->is_main_query()) {
            return $query;
        }

        // Construire la requête tax_query
        $tax_query = [];
        foreach ($this->taxonomies as $taxonomy) {
            if (isset($_GET[$taxonomy]) && !empty($_GET[$taxonomy]) && $_GET[$taxonomy] != '-1') {
                $term_id = (int)$_GET[$taxonomy];
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term_id,
                    'operator' => 'IN'
                ];
            }
        }

        // Si des filtres de taxonomie sont appliqués, construire la requête SQL
        if (!empty($tax_query)) {
            $tax_query_sql = [];
            foreach ($tax_query as $tax) {
                $tax_query_sql[] = $wpdb->prepare(
                    "EXISTS (
                        SELECT 1 FROM {$wpdb->term_relationships} tr
                        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                        WHERE tr.object_id = {$wpdb->posts}.ID
                        AND tt.taxonomy = %s
                        AND tt.term_id = %d
                    )",
                    $tax['taxonomy'],
                    $tax['terms']
                );
            }

            $tax_query_sql = implode(' AND ', $tax_query_sql);

            // Ajouter notre propre filtre pour modifier la requête SQL
            add_filter('posts_request', function($sql) use ($wpdb, $tax_query_sql) {
                $posts_sql = $wpdb->prepare(
                    "SELECT SQL_CALC_FOUND_ROWS wp_posts.* 
                    FROM {$wpdb->posts} 
                    WHERE 1=1 
                    AND {$wpdb->posts}.post_type = %s
                    AND ({$wpdb->posts}.post_status = 'publish' 
                        OR {$wpdb->posts}.post_status = 'future' 
                        OR {$wpdb->posts}.post_status = 'draft' 
                        OR {$wpdb->posts}.post_status = 'pending' 
                        OR {$wpdb->posts}.post_status = 'private')
                    AND $tax_query_sql
                    GROUP BY {$wpdb->posts}.ID
                    ORDER BY {$wpdb->posts}.post_date DESC",
                    $this->post_type
                );
                return $posts_sql;
            });
        }

        // Debug
        add_action('all_admin_notices', function() use ($query) {
            echo '<div class="notice notice-info">';
            echo '<p>Requête SQL finale : ' . $query->request . '</p>';
            echo '<p>Nombre de résultats : ' . $query->found_posts . '</p>';
            echo '</div>';
        });

        return $query;
    }

    /**
     * Récupère les résultats filtrés
     */
    private function get_filtered_results($tax_params) {
        $args = [
            'post_type' => $this->post_type,
            'posts_per_page' => 20,
            'tax_query' => []
        ];

        foreach ($tax_params as $taxonomy => $term_id) {
            if (!empty($term_id) && $term_id != '-1') {
                $args['tax_query'][] = [
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term_id
                ];
            }
        }

        return new \WP_Query($args);
    }
}