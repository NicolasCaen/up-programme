<?php
namespace UpProgramme\Admin\Imports;
use WP_Query;
use Exception;
class ParcelImport extends BaseImport {


    protected function process_row($data) {
        error_log("indices : ".print_r($this->column_indices,true));
        error_log('indice_lot ' . $this->column_indices['lot_id']);
        error_log('indice_surface ' . $this->column_indices['surface']);
        // Récupération des données du CSV
        $id = $data[$this->column_indices['lot_id']];
        $programme = $data[$this->column_indices['up_program_taxonomy_land']];
        $surface = $data[$this->column_indices['surface']];
        $prix = $data[$this->column_indices['price']];
        $pdf = $data[$this->column_indices['plan_terrain']];
        $etat = $data[$this->column_indices['etat']];
        $description = $data[$this->column_indices['description']];
        // Vérifier si le lot existe déjà (par son up_origin_id)
        $existing_parcels = new WP_Query([
            'post_type' => 'up_program_parcel',
            'meta_query' => [
                [
                    'key' => 'up_origin_id',
                    'value' => $id,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1
        ]);

        if ($existing_parcels->have_posts()) {
            // Mise à jour du parcel existant
            $parcel = $existing_parcels->posts[0];
            $post_id = $parcel->ID;
            $post_data = [
                'ID' => $post_id,
                'post_title' => $description,
                'post_status' => 'publish',
            ];
            wp_update_post($post_data);
        } else {
            // Création d'un nouveau parcel
            $post_data = [
                'post_title' => $description,
                'post_type' => 'up_program_parcel',
                'post_status' => 'publish',
            ];
            $post_id = wp_insert_post($post_data);
        }

        if (!is_wp_error($post_id)) {
            // Mise à jour des métadonnées avec les nouveaux noms
            update_post_meta($post_id, 'external_id', $id);
            update_post_meta($post_id, 'up_origin_id', $id);
            update_post_meta($post_id, 'up_lot_number', $id);
            update_post_meta($post_id, 'up_surface', $surface);
            update_post_meta($post_id, 'up_price', $prix);

            
            // Traitement spécial pour le PDF
            if (!empty($pdf)) {
                $upload_dir = wp_upload_dir();
                $pdf_url = $upload_dir['baseurl'] . '/parcel/' . ltrim($pdf, '/');
                update_post_meta($post_id, 'up_pdf_file', $pdf_url);
            }


            // Mise à jour de la taxonomie programme
            if (!empty($programme)) {
                // Vérifie si $programme est un chiffre
                if (is_numeric($programme)) {
                    // Mappe les valeurs numériques aux slugs correspondants
                    $programme_map = [
                        10 => 'le-cotil-de-lorne-saint-a', // Slug pour 10
                        1  => 'potigny',                   // Slug pour 1
                        // Ajoutez d'autres correspondances ici
                    ];
            
                    // Récupère le slug correspondant ou null si non trouvé
                    $term = isset($programme_map[(int)$programme]) ? $programme_map[(int)$programme] : null;
                } else {
                    // Sinon, utilise directement $programme comme slug
                    $term = trim($programme);
                }
            
                // Si un terme valide a été déterminé, applique-le à l'objet
                if (!empty($term)) {
                    wp_set_object_terms($post_id, $term, 'up_program_taxonomy_terrain');
                }
            }
            

            // Mise à jour de l'état
            if (!empty($etat)) {
                // Vérifie si $etat est un chiffre
                if (is_numeric($etat)) {
                    // Mappe les valeurs numériques aux slugs correspondants
                    $etat_map = [
                        0 => 'disponible', // Slug pour 0
                        1 => 'vendu',      // Slug pour 1
                        2 => 'reserve',    // Exemple pour 2
                        // Ajoutez d'autres correspondances ici
                    ];
            
                    // Récupère le slug correspondant ou null si non trouvé
                    $term = isset($etat_map[(int)$etat]) ? $etat_map[(int)$etat] : null;
                } else {
                    // Sinon, utilise directement $etat comme slug
                    $term = trim($etat);
                }
            
                // Si un terme valide a été déterminé, applique-le à l'objet
                if (!empty($term)) {
                    wp_set_object_terms($post_id, $term, 'up_program_taxonomy_state');
                }
            }
            
            
    
            

            echo "<div class='notice notice-success'><p>parcelle importé/mis à jour avec succès : </p></div>";
        } else {
            echo "<div class='notice notice-error'><p>Erreur lors de l'importation/mise à jour du parcelle : </p></div>";
        }
    }
}
