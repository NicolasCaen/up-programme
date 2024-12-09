<?php
namespace UpProgramme\Admin\FilterColumns;

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
            ]);
        }
    }

    /**
     * Filtre les posts en fonction des taxonomies sélectionnées
     */
    public function filter_posts_by_taxonomies($query) {
        global $pagenow;
        
        if (!is_admin() || 
            $pagenow !== 'edit.php' || 
            !isset($query->query['post_type']) || 
            $query->query['post_type'] !== $this->post_type) {
            return $query;
        }

        $tax_query = [];

        foreach ($this->taxonomies as $taxonomy) {
            if (isset($_GET[$taxonomy]) && !empty($_GET[$taxonomy]) && $_GET[$taxonomy] != '-1') {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $_GET[$taxonomy],
                ];
            }
        }

        if (!empty($tax_query)) {
            if (count($tax_query) > 1) {
                $tax_query['relation'] = 'AND';
            }
            $query->set('tax_query', $tax_query);
        }

        return $query;
    }
}
