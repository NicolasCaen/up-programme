<?php
namespace UpProgramme\Admin\Imports;
use WP_Query;

class ProgramImport extends BaseImport {
    protected function process_row($data) {
        $id = $data[$this->column_indices['id']];
        $title = $data[$this->column_indices['title']];
        $description = $data[$this->column_indices['description']];
        $type_logement = $data[$this->column_indices['type']];
        $rooms = $data[$this->column_indices['rooms']];
        $dispositif = $data[$this->column_indices['dispositif']];
        $ville_id = $data[$this->column_indices['ville']];

        // Vérifier si un programme existe déjà
        $query = new WP_Query([
            'post_type' => 'up_program_program',
            'title' => $title,
            'posts_per_page' => 1,
        ]);

        if ($query->have_posts()) {
            $existing_post = $query->posts[0];
            $post_id = $existing_post->ID;
            $post_data = array(
                'ID'           => $post_id,
                'post_content' => $description,
            );
            wp_update_post($post_data);
        } else {
            $post_data = array(
                'post_title'    => wp_strip_all_tags($title),
                'post_content'  => $description,
                'post_status'   => 'publish',
                'post_type'     => 'up_program_program',
            );
            $post_id = wp_insert_post($post_data);
        }

        if (!is_wp_error($post_id)) {
            $this->update_post_metadata($post_id, $id, $type_logement, $rooms, $dispositif, $ville_id);
            echo "<div class='notice notice-success'><p>Programme importé/mis à jour avec succès : {$title}</p></div>";
        } else {
            echo "<div class='notice notice-error'><p>Erreur lors de l'importation/mise à jour du programme : {$title}</p></div>";
        }
    }

    private function update_post_metadata($post_id, $id, $type_logement, $rooms, $dispositif, $ville_id) {
        update_post_meta($post_id, 'external_id', $id);

        if (!empty($type_logement)) {
            $types = explode(',', $type_logement);
            foreach ($types as $type) {
                wp_set_object_terms($post_id, trim($type), 'up_program_property_type', true);
            }
        }

        if (!empty($rooms)) {
            $room_numbers = explode(',', $rooms);
            foreach ($room_numbers as $room) {
                wp_set_object_terms($post_id, trim($room), 'up_program_taxonomy_rooms', true);
            }
        }

        if (!empty($dispositif)) {
            $dispositifs = explode(',', $dispositif);
            foreach ($dispositifs as $disp) {
                wp_set_object_terms($post_id, trim($disp), 'up_program_taxonomy_scheme', true);
            }
        }

        // Gestion de la ville
        if (!empty($ville_id)) {
            // Récupérer tous les termes de ville
            $ville_terms = get_terms([
                'taxonomy' => 'up_program_taxonomy_city',
                'hide_empty' => false
            ]);

            if (!is_wp_error($ville_terms) && !empty($ville_terms)) {
                // Filtrer pour trouver la ville correspondante
                $ville_term = array_filter($ville_terms, function($term) use ($ville_id) {
                    $term_ville_id = get_term_meta($term->term_id, 'identifiant', true);
                    return $term_ville_id == $ville_id;
                });

                if (!empty($ville_term)) {
                    // Prendre le premier (et normalement unique) résultat
                    $ville_term = reset($ville_term);
                    // Associer le terme au programme
                    wp_set_object_terms($post_id, $ville_term->term_id, 'up_program_taxonomy_city');
                } else {
                    echo "<div class='notice notice-warning'><p>Ville non trouvée pour l'ID : {$ville_id}</p></div>";
                }
            } else {
                echo "<div class='notice notice-warning'><p>Erreur lors de la récupération des villes</p></div>";
                if (is_wp_error($ville_terms)) {
                    echo "<div class='notice notice-error'><p>" . $ville_terms->get_error_message() . "</p></div>";
                }
            }
        }
    }
}