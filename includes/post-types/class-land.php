<?php
namespace UpProgramme\PostTypes;

class Land {
    public function register() {
        add_action('init', [$this, 'register_post_type']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta']);
    }

    public function register_post_type() {
        $args = [
            'labels' => [
                'name' => 'Terrains',
                'singular_name' => 'Terrain',
                'add_new' => 'Ajouter Nouveau',
                'add_new_item' => 'Ajouter Nouveau Terrain',
                'edit_item' => 'Modifier Terrain',
                'new_item' => 'Nouveau Terrain',
                'view_item' => 'Voir Terrain',
                'search_items' => 'Rechercher Terrains',
                'not_found' => 'Aucun terrain trouvé',
                'not_found_in_trash' => 'Aucun terrain trouvé dans la corbeille',
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
            'menu_position' => 6,
            'menu_icon' => 'dashicons-palmtree',
        ];

        register_post_type('up_program_land', $args);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'land_details',
            'Détails du Terrain',
            [$this, 'render_meta_box'],
            'up_program_land',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        $size = get_post_meta($post->ID, '_up_size', true);
        $price = get_post_meta($post->ID, '_up_price', true);
        ?>
        <p>
            <label for="up_size">Taille (m²) :</label>
            <input type="number" id="up_size" name="up_size" value="<?php echo esc_attr($size); ?>" />
        </p>

        <p>
            <label for="up_price">Prix :</label>
            <input type="number" id="up_price" name="up_price" value="<?php echo esc_attr($price); ?>" />
        </p>
        <?php
    }

    public function save_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $fields = ['size', 'price'];
        foreach ($fields as $field) {
            if (isset($_POST['up_' . $field])) {
                update_post_meta(
                    $post_id,
                    '_up_' . $field,
                    sanitize_text_field($_POST['up_' . $field])
                );
            }
        }
    }
} 