<?php
namespace UpProgramme\PostTypes;

class Lot {
    public function register() {
        add_action('init', [$this, 'register_post_type']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta']);
        add_action('post_edit_form_tag', [$this, 'update_edit_form']);
    }

    public function register_post_type() {
        $args = [
            'labels' => [
                'name' => 'Lots',
                'singular_name' => 'Lot',
                'add_new' => 'Ajouter Nouveau',
                'add_new_item' => 'Ajouter Nouveau Lot',
                'edit_item' => 'Modifier Lot',
                'new_item' => 'Nouveau Lot',
                'view_item' => 'Voir Lot',
                'search_items' => 'Rechercher Lots',
                'not_found' => 'Aucun lot trouvé',
                'not_found_in_trash' => 'Aucun lot trouvé dans la corbeille',
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
            'menu_position' => 7,
            'menu_icon' => 'dashicons-admin-multisite',
            'show_in_rest' => true,
        ];

        register_post_type('up_program_lot', $args);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'lot_details',
            'Détails du Lot',
            [$this, 'render_meta_box'],
            'up_program',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field(basename(__FILE__), 'lot_nonce');
        
        $surface = get_post_meta($post->ID, '_up_surface', true);
        $price = get_post_meta($post->ID, '_up_price', true);
        $rooms = get_post_meta($post->ID, '_up_rooms', true);
        $program = get_post_meta($post->ID, '_up_program_id', true);
        $pdf_url = get_post_meta($post->ID, '_up_pdf_file', true);

        // Récupérer tous les programmes immobiliers
        $programs = get_posts([
            'post_type' => 'real_estate_program',
            'posts_per_page' => -1,
        ]);
        ?>
        <p>
            <label for="up_surface">Surface (m²) :</label>
            <input type="number" id="up_surface" name="up_surface" value="<?php echo esc_attr($surface); ?>" />
        </p>

        <p>
            <label for="up_price">Prix :</label>
            <input type="number" id="up_price" name="up_price" value="<?php echo esc_attr($price); ?>" />
        </p>

        <p>
            <label for="up_rooms">Nombre de pièces :</label>
            <input type="number" id="up_rooms" name="up_rooms" value="<?php echo esc_attr($rooms); ?>" />
        </p>

        <p>
            <label for="up_program_id">Programme immobilier :</label>
            <select id="up_program_id" name="up_program_id">
                <option value="">Sélectionner un programme</option>
                <?php foreach ($programs as $prog) : ?>
                    <option value="<?php echo $prog->ID; ?>" <?php selected($program, $prog->ID); ?>>
                        <?php echo esc_html($prog->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="up_pdf_file">Fichier PDF :</label>
            <input type="file" id="up_pdf_file" name="up_pdf_file" accept=".pdf" />
            <?php if ($pdf_url) : ?>
                <br>
                <a href="<?php echo esc_url($pdf_url); ?>" target="_blank">Voir le PDF actuel</a>
                <label>
                    <input type="checkbox" name="delete_pdf" value="1"> Supprimer le PDF
                </label>
            <?php endif; ?>
        </p>
        <?php
    }

    public function save_meta($post_id) {
        if (!isset($_POST['lot_nonce']) || !wp_verify_nonce($_POST['lot_nonce'], basename(__FILE__))) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $fields = ['surface', 'price', 'rooms', 'program_id'];
        foreach ($fields as $field) {
            if (isset($_POST['up_' . $field])) {
                update_post_meta(
                    $post_id,
                    '_up_' . $field,
                    sanitize_text_field($_POST['up_' . $field])
                );
            }
        }

        // Gérer la suppression du PDF
        if (isset($_POST['delete_pdf']) && $_POST['delete_pdf'] == '1') {
            $this->delete_pdf($post_id);
        }

        // Gérer l'upload du PDF
        if (!empty($_FILES['up_pdf_file']['name'])) {
            $this->handle_pdf_upload($post_id);
        }
    }

    private function delete_pdf($post_id) {
        $old_pdf_url = get_post_meta($post_id, '_up_pdf_file', true);
        if ($old_pdf_url) {
            $upload_dir = wp_upload_dir();
            $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $old_pdf_url);
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            delete_post_meta($post_id, '_up_pdf_file');
        }
    }

    private function handle_pdf_upload($post_id) {
        $file_type = wp_check_filetype($_FILES['up_pdf_file']['name']);
        if ($file_type['type'] != 'application/pdf') {
            return;
        }

        require_once(ABSPATH . 'wp-admin/includes/file.php');

        add_filter('upload_dir', [$this, 'custom_upload_dir']);
        $upload = wp_handle_upload(
            $_FILES['up_pdf_file'],
            ['test_form' => false]
        );
        remove_filter('upload_dir', [$this, 'custom_upload_dir']);

        if (!empty($upload['error'])) {
            return;
        }

        update_post_meta($post_id, '_up_pdf_file', $upload['url']);
    }

    public function custom_upload_dir($dirs) {
        $dirs['subdir'] = '/lots';
        $dirs['path'] = $dirs['basedir'] . '/lots';
        $dirs['url'] = $dirs['baseurl'] . '/lots';
        return $dirs;
    }

    public function update_edit_form() {
        echo ' enctype="multipart/form-data"';
    }
} 