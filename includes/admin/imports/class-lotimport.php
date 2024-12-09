<?php
namespace UpProgramme\Admin\Imports;
use WP_Query;
use Exception;
class LotImport extends BaseImport {


    protected function process_row($data) {
        // Récupération des données du CSV
        $id = $data[$this->column_indices['id']];
        $programme = $data[$this->column_indices['programme']];
        $surface = $data[$this->column_indices['surface']];
        $prix = $data[$this->column_indices['prix']];
        $prix55 = $data[$this->column_indices['prix-55']];
        $prix20 = $data[$this->column_indices['prix-20']];
        $prix10 = $data[$this->column_indices['prix-10']];
        $pdf = $data[$this->column_indices['pdf']];
        $description = $data[$this->column_indices['description']];
        $type = $data[$this->column_indices['type']];
        $rooms = $data[$this->column_indices['rooms']];
        $etage = $data[$this->column_indices['etage']];
        $dispositif = $data[$this->column_indices['dispositif']];
        $etat = $data[$this->column_indices['etat']];

        // Vérifier si le lot existe déjà (par son up_origin_id)
        $existing_lots = new WP_Query([
            'post_type' => 'up_program_lot',
            'meta_query' => [
                [
                    'key' => 'up_origin_id',
                    'value' => $id,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1
        ]);

        if ($existing_lots->have_posts()) {
            // Mise à jour du lot existant
            $lot = $existing_lots->posts[0];
            $post_id = $lot->ID;
            $post_data = [
                'ID' => $post_id,
                'post_title' => $description,
                'post_status' => 'publish',
            ];
            wp_update_post($post_data);
        } else {
            // Création d'un nouveau lot
            $post_data = [
                'post_title' => $description,
                'post_type' => 'up_program_lot',
                'post_status' => 'publish',
            ];
            $post_id = wp_insert_post($post_data);
        }

        if (!is_wp_error($post_id)) {
            // Mise à jour des métadonnées avec les nouveaux noms
            update_post_meta($post_id, 'external_id', $id);
            update_post_meta($post_id, 'up_origin_id', $id);
            //update_post_meta($post_id, 'up_lot_number', $numero);
            update_post_meta($post_id, 'up_surface', $surface);
            update_post_meta($post_id, 'up_price', $prix);
            update_post_meta($post_id, 'up_price55', $prix55);
            update_post_meta($post_id, 'up_price20', $prix20);
            update_post_meta($post_id, 'up_price10', $prix10);
            
            // Traitement spécial pour le PDF
            if (!empty($pdf)) {
                $upload_dir = wp_upload_dir();
                $pdf_url = $upload_dir['baseurl'] . '/' . ltrim($pdf, '/');
                update_post_meta($post_id, 'up_pdf_file', $pdf_url);
            }

            // Mise à jour des taxonomies
            if (!empty($type)) {
                wp_set_object_terms($post_id, trim($type), 'up_program_property_type');
            }

            if (!empty($rooms)) {
                wp_set_object_terms($post_id, trim($rooms), 'up_program_taxonomy_rooms');
            }

            if (!empty($etage)) {
                wp_set_object_terms($post_id, trim($etage), 'up_program_taxonomy_level');
            }

            // Mise à jour de la taxonomie programme
            if (!empty($programme)) {
                wp_set_object_terms($post_id, trim($programme), 'up_program_taxonomy_program');
            }


            if (!empty($dispositif)) {
                $dispositifs = explode(',', $dispositif);
                foreach ($dispositifs as $disp) {
                    wp_set_object_terms($post_id, trim($disp), 'up_program_taxonomy_scheme', true);
                }
            }
    
            // Mise à jour de l'état
            if (!empty($etat)) {
                wp_set_object_terms($post_id, trim($etat), 'up_program_taxonomy_state');
            }
    
            

            echo "<div class='notice notice-success'><p>Lot importé/mis à jour avec succès : {$description}</p></div>";
        } else {
            echo "<div class='notice notice-error'><p>Erreur lors de l'importation/mise à jour du lot : {$description}</p></div>";
        }
    }
}
