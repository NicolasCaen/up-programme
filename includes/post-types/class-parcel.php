<?php
namespace UpProgramme\PostTypes;

class Parcel {
    
    public function register() {
           
        add_action('init', [$this, 'register_post_type']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta']);
        add_action('post_edit_form_tag', [$this, 'update_edit_form']);
    }

    public function register_post_type() {
        $args = [
            'labels' => [
                'name' => 'Parcelles',
                'singular_name' => 'Parcelle',
                'add_new' => 'Ajouter',
                'add_new_item' => 'Ajouter une parcelle',
                'edit_item' => 'Modifier parcelle',
                'new_item' => 'Nouvelle parcelle',
                'view_item' => 'Voir parcelle',
                'search_items' => 'Rechercher parcelles',
                'not_found' => 'Aucune parcelle trouvé',
                'not_found_in_trash' => 'Aucune parcelle trouvé dans la corbeille',
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title',  'thumbnail', 'custom-fields'],
            'menu_position' => 7,
            'menu_icon' => 'dashicons-location-alt',
            'show_in_rest' => true,
        ];

        register_post_type('up_program_parcel', $args);
    }

    public function add_meta_boxes() {
    
        add_meta_box(
            'parcel_details',
            'Détails de la parcelle',
            [$this, 'render_meta_box'],
            'up_program_parcel',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
 
        wp_nonce_field(basename(__FILE__), 'parcel_nonce');
        
        $surface = get_post_meta($post->ID, 'up_surface', true);
        $price = get_post_meta($post->ID,   'up_price', true);

        $pdf_url = get_post_meta($post->ID, 'up_pdf_file', true);
        $parcel_number = get_post_meta($post->ID, 'up_parcel_number', true);
        $origin_id = get_post_meta($post->ID, 'up_origin_id', true);
        ?>
         <div class="up-flex-row__container">
         <div class="up-flex-row">
            <p>
                <label for="up_origin_id">ID source :</label>
                <input type="text" id="up_origin_id" name="up_origin_id" value="<?php echo esc_attr($origin_id); ?>" />
            </p>
        <p>
            <label for="up_parcel_number">Numéro de parcelles :</label>
            <input type="text" id="up_parcel_number" name="up_parcel_number" value="<?php echo esc_attr($parcel_number); ?>" />
        </p>

        <p>
            <label for="up_surface">Surface (m²) :</label>
            <input type="number" id="up_surface" name="up_surface" value="<?php echo esc_attr($surface); ?>" />
        </p>
        </div>
        <div class="up-flex-row">

            <p>
            <label for="up_price">Prix :</label>
            <input type="number" id="up_price" name="up_price" value="<?php echo esc_attr($price); ?>" />
        </p>
        </div>
        <div class="up-flex-row">
        <p>
            <?php
            // Si pdf_url est un array, prendre le premier élément
            if (is_array($pdf_url)) {
                $pdf_url = $pdf_url[0];
            }
            
            // Si c'est un ID, récupérer l'URL
            if (is_numeric($pdf_url)) {
                $pdf_url = wp_get_attachment_url($pdf_url);
            }
            ?>
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
        </div>
        </div>
        <?php
    }

    public function save_meta($post_id) {
        if (!isset($_POST['parcel_nonce']) || !wp_verify_nonce($_POST['parcel_nonce'], basename(__FILE__))) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $fields = ['surface', 'price', 'program_id', 'parcel_number', 'origin_id'];
        foreach ($fields as $field) {
            if (isset($_POST['up_' . $field])) {
                update_post_meta(
                    $post_id,
                    'up_' . $field,
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
        $old_pdf_url = get_post_meta($post_id, 'up_pdf_file', true);
        if ($old_pdf_url) {
            $upload_dir = wp_upload_dir();
            $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $old_pdf_url);
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            delete_post_meta($post_id, 'up_pdf_file');
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

        update_post_meta($post_id, 'up_pdf_file', $upload['url']);
    }

    public function custom_upload_dir($dirs) {
        $dirs['subdir'] = '/parcelles';
        $dirs['path'] = $dirs['basedir'] . '/parcels';
        $dirs['url'] = $dirs['baseurl'] . '/parcels';
        return $dirs;
    }

    public function update_edit_form() {
        echo ' enctype="multipart/form-data"';
    }
} 