<?php
namespace UpProgramme\PostTypes;

class Land {
    public function register() {
        add_action('init', [$this, 'register_post_type']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta']);
        add_action('post_edit_form_tag', [$this, 'update_edit_form']);
    }

    public function register_post_type() {
        $args = [
            'labels' => [
                'name' => 'Terrains',
                'singular_name' => 'Terrain',
                'add_new' => 'Ajouter Nouveau',
                'add_new_item' => 'Nouveau',
                'edit_item' => 'Modifier Terrain',
                'new_item' => 'Nouveau Terrain',
                'view_item' => 'Voir Terrain',
                'search_items' => 'Rechercher Terrains',
                'not_found' => 'Aucun terrain trouvé',
                'not_found_in_trash' => 'Aucun terrain trouvé dans la corbeille',
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title',  'thumbnail'],
            'menu_position' => 6,
            'menu_icon' => 'dashicons-palmtree',
            'show_in_rest' => true,
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
        wp_nonce_field(basename(__FILE__), 'land_nonce');

        $size = get_post_meta($post->ID, 'up_size', true);
        $price = get_post_meta($post->ID, 'up_price', true);
        $pdf_urls = get_post_meta($post->ID, 'up_pdf_files', true);
        ?>
        <p>
            <label for="up_size">Surface (m²) :</label>
            <input type="number" id="up_size" name="up_size" value="<?php echo esc_attr($size); ?>" />
        </p>

        <p>
            <label for="up_price">Prix :</label>
            <input type="number" id="up_price" name="up_price" value="<?php echo esc_attr($price); ?>" />
        </p>

        <?php
        $pdf_labels = [
            'Plan de masse',
            "Plan local d'urbanisme",
            "Etude géotechnique",
            'Règlement du lotissement',
            'Autre document'
        ];

        for ($i = 1; $i <= 5; $i++) :
            $pdf_url = isset($pdf_urls[$i-1]) ? $pdf_urls[$i-1] : '';

            // Si pdf_url est un ID, récupérer l'URL
            if (is_numeric($pdf_url)) {
                $pdf_url = wp_get_attachment_url($pdf_url);
            }
            ?>
            
            <p>
                <label for="up_pdf_file_<?php echo $i; ?>"><?php echo esc_html($pdf_labels[$i-1]); ?> :</label>
                <input type="file" id="up_pdf_file_<?php echo $i; ?>" name="up_pdf_file_<?php echo $i; ?>" accept=".pdf" />
                <?php if ($pdf_url) : ?>
                    <br>
                    <a href="<?php echo esc_url($pdf_url); ?>" target="_blank">Voir le PDF actuel</a>
                    <label>
                        <input type="checkbox" name="delete_pdf_<?php echo $i; ?>" value="1"> Supprimer le PDF
                    </label>
                <?php endif; ?>
            </p>
        <?php endfor; ?>
        <?php
    }

    public function save_meta($post_id) {
        if (!isset($_POST['land_nonce']) || !wp_verify_nonce($_POST['land_nonce'], basename(__FILE__))) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $fields = ['size', 'price'];
        foreach ($fields as $field) {
            if (isset($_POST['up_' . $field])) {
                update_post_meta(
                    $post_id,
                    'up_' . $field,
                    sanitize_text_field($_POST['up_' . $field])
                );
            }
        }

        $pdf_urls = [];
        for ($i = 1; $i <= 5; $i++) {
            // Gérer la suppression du PDF
            if (isset($_POST['delete_pdf_' . $i]) && $_POST['delete_pdf_' . $i] == '1') {
                $this->delete_pdf($post_id, $i);
            }

            // Gérer l'upload du PDF
            if (!empty($_FILES['up_pdf_file_' . $i]['name'])) {
                $pdf_url = $this->handle_pdf_upload($post_id, $i);
                if ($pdf_url) {
                    $pdf_urls[] = $pdf_url;
                }
            } else {
                $existing_pdf_url = get_post_meta($post_id, 'up_pdf_file_' . $i, true);
                if ($existing_pdf_url) {
                    $pdf_urls[] = $existing_pdf_url;
                }
            }
        }

        update_post_meta($post_id, 'up_pdf_files', $pdf_urls);
    }

    private function delete_pdf($post_id, $index) {
        $old_pdf_url = get_post_meta($post_id, 'up_pdf_file_' . $index, true);
        if ($old_pdf_url) {
            $upload_dir = wp_upload_dir();
            $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $old_pdf_url);
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            delete_post_meta($post_id, 'up_pdf_file_' . $index);
        }
    }

    private function handle_pdf_upload($post_id, $index) {
        $file_type = wp_check_filetype($_FILES['up_pdf_file_' . $index]['name']);
        if ($file_type['type'] != 'application/pdf') {
            return;
        }

        require_once(ABSPATH . 'wp-admin/includes/file.php');

        add_filter('upload_dir', [$this, 'custom_upload_dir']);
        $upload = wp_handle_upload(
            $_FILES['up_pdf_file_' . $index],
            ['test_form' => false]
        );
        remove_filter('upload_dir', [$this, 'custom_upload_dir']);

        if (!empty($upload['error'])) {
            return;
        }

        update_post_meta($post_id, 'up_pdf_file_' . $index, $upload['url']);
        return $upload['url'];
    }

    public function custom_upload_dir($dirs) {
        $dirs['subdir'] = '/terrains';
        $dirs['path'] = $dirs['basedir'] . '/terrains';
        $dirs['url'] = $dirs['baseurl'] . '/terrains';
        return $dirs;
    }

    public function update_edit_form() {
        echo ' enctype="multipart/form-data"';
    }
}
