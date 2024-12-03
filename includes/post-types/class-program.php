<?php
namespace UpProgramme\PostTypes;

class Program {
    public function register() {
        add_action('init', [$this, 'register_post_type']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta']);
        add_action('post_edit_form_tag', [$this, 'update_edit_form']);
    }

    public function register_post_type() {
        $labels = [
            'name' => 'Programmes',
            'singular_name' => 'Programme Immobilier',
            'add_new' => 'Ajouter Nouveau',
            'add_new_item' => 'Ajouter Nouveau Programme',
            'edit_item' => 'Modifier Programme',
            'new_item' => 'Nouveau Programme',
            'view_item' => 'Voir Programme',
            'search_items' => 'Rechercher Programmes',
            'not_found' => 'Aucun programme trouvé',
            'not_found_in_trash' => 'Aucun programme trouvé dans la corbeille',
            'menu_name' => 'Programmes',
            'featured_image' => 'Image du Programme',
            'set_featured_image' => 'Définir l\'image du programme',
            'remove_featured_image' => 'Supprimer l\'image du programme',
            'use_featured_image' => 'Utiliser comme image du programme',
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'menu_position' => 5,
            'menu_icon' => 'dashicons-admin-home',
            'rewrite' => ['slug' => 'neuf'],
            'show_in_rest' => true,
            'publicly_queryable' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'capability_type' => 'post',
        ];

        register_post_type('up_program_program', $args);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'program_details',
            'Détails du Programme',
            [$this, 'render_meta_box'],
            'up_program_program',
            'normal',
            'high'
        );

        add_meta_box(
            'program_gallery',
            'Galerie du Programme',
            [$this, 'render_gallery_meta_box'],
            'up_program_program',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field(basename(__FILE__), 'program_nonce');

        $location = get_post_meta($post->ID, '_up_location', true);
        $price_from = get_post_meta($post->ID, '_up_price_from', true);
        $price_to = get_post_meta($post->ID, '_up_price_to', true);
        $surface_from = get_post_meta($post->ID, '_up_surface_from', true);
        $surface_to = get_post_meta($post->ID, '_up_surface_to', true);
        $delivery_date = get_post_meta($post->ID, '_up_delivery_date', true);
        $status = get_post_meta($post->ID, '_up_status', true);
        $energy_rating = get_post_meta($post->ID, '_up_energy_rating', true);
        ?>
        
        <div class="program-meta-box">
            <p>
                <label for="up_location">Adresse complète :</label>
                <input type="text" id="up_location" name="up_location" 
                       value="<?php echo esc_attr($location); ?>" class="widefat" />
            </p>

            <div class="price-group">
                <p>
                    <label for="up_price_from">Prix à partir de :</label>
                    <input type="number" id="up_price_from" name="up_price_from" 
                           value="<?php echo esc_attr($price_from); ?>" />
                </p>
                <p>
                    <label for="up_price_to">Prix jusqu'à :</label>
                    <input type="number" id="up_price_to" name="up_price_to" 
                           value="<?php echo esc_attr($price_to); ?>" />
                </p>
            </div>

            <div class="surface-group">
                <p>
                    <label for="up_surface_from">Surface à partir de (m²) :</label>
                    <input type="number" id="up_surface_from" name="up_surface_from" 
                           value="<?php echo esc_attr($surface_from); ?>" />
                </p>
                <p>
                    <label for="up_surface_to">Surface jusqu'à (m²) :</label>
                    <input type="number" id="up_surface_to" name="up_surface_to" 
                           value="<?php echo esc_attr($surface_to); ?>" />
                </p>
            </div>

            <p>
                <label for="up_delivery_date">Date de livraison :</label>
                <input type="date" id="up_delivery_date" name="up_delivery_date" 
                       value="<?php echo esc_attr($delivery_date); ?>" />
            </p>

            <p>
                <label for="up_status">État du programme :</label>
                <select id="up_status" name="up_status">
                    <option value="">Sélectionner un état</option>
                    <option value="available" <?php selected($status, 'available'); ?>>Disponible</option>
                    <option value="under_construction" <?php selected($status, 'under_construction'); ?>>En construction</option>
                    <option value="reserved" <?php selected($status, 'reserved'); ?>>Réservé</option>
                    <option value="sold" <?php selected($status, 'sold'); ?>>Vendu</option>
                </select>
            </p>

            <p>
                <label for="up_energy_rating">Classe énergétique :</label>
                <select id="up_energy_rating" name="up_energy_rating">
                    <option value="">Sélectionner une classe</option>
                    <?php
                    $ratings = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                    foreach ($ratings as $rating) {
                        printf(
                            '<option value="%s" %s>%s</option>',
                            $rating,
                            selected($energy_rating, $rating, false),
                            $rating
                        );
                    }
                    ?>
                </select>
            </p>
        </div>
        <?php
    }

    public function render_gallery_meta_box($post) {
        $gallery_images = get_post_meta($post->ID, '_up_gallery_images', true);
        ?>
        <div class="program-gallery">
            <div id="program_gallery_container">
                <?php
                if (!empty($gallery_images)) {
                    $images = explode(',', $gallery_images);
                    foreach ($images as $image_id) {
                        echo wp_get_attachment_image($image_id, 'thumbnail');
                    }
                }
                ?>
            </div>
            <input type="hidden" id="up_gallery_images" name="up_gallery_images" 
                   value="<?php echo esc_attr($gallery_images); ?>" />
            <button type="button" class="button" id="upload_gallery_images">
                Ajouter des images
            </button>
        </div>
        <script>
            jQuery(document).ready(function($) {
                var mediaUploader;
                $('#upload_gallery_images').click(function(e) {
                    e.preventDefault();
                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }
                    mediaUploader = wp.media({
                        title: 'Choisir des images',
                        button: {
                            text: 'Utiliser ces images'
                        },
                        multiple: true
                    });

                    mediaUploader.on('select', function() {
                        var attachments = mediaUploader.state().get('selection').map(
                            function(attachment) {
                                attachment = attachment.toJSON();
                                return attachment.id;
                            }
                        );
                        var currentImages = $('#up_gallery_images').val();
                        var newImages = currentImages ? 
                            currentImages + ',' + attachments.join(',') : 
                            attachments.join(',');
                        $('#up_gallery_images').val(newImages);
                        
                        // Rafraîchir l'affichage des images
                        location.reload();
                    });
                    mediaUploader.open();
                });
            });
        </script>
        <?php
    }

    public function save_meta($post_id) {
        if (!isset($_POST['program_nonce']) || 
            !wp_verify_nonce($_POST['program_nonce'], basename(__FILE__))) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $fields = [
            'location',
            'price_from',
            'price_to',
            'surface_from',
            'surface_to',
            'delivery_date',
            'status',
            'energy_rating',
            'gallery_images'
        ];

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

    public function update_edit_form() {
        echo ' enctype="multipart/form-data"';
    }
} 