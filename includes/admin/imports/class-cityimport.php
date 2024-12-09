<?php
namespace UpProgramme\Admin\Imports;

class CityImport extends BaseImport {
    protected function process_row($data) {
        // Récupération des données du CSV
        $id = $data[$this->column_indices['id']];
        $name = $data[$this->column_indices['name']];
        $code_postal = $data[$this->column_indices['code_postal']];
        
        // Vérifier si la ville existe déjà
        $existing_term = term_exists($name, 'up_program_taxonomy_city');
        
        if ($existing_term) {
            // Mise à jour du terme existant
            $term_id = $existing_term['term_id'];
            wp_update_term($term_id, 'up_program_taxonomy_city', [
                'name' => $name
            ]);
        } else {
            // Création d'un nouveau terme
            $term = wp_insert_term($name, 'up_program_taxonomy_city');
            if (!is_wp_error($term)) {
                $term_id = $term['term_id'];
            } else {
                echo "<div class='notice notice-error'><p>Erreur lors de la création de la ville : {$name}</p></div>";
                return;
            }
        }

        // Mise à jour des métadonnées
        update_term_meta($term_id, 'external_id', $id);
        update_term_meta($term_id, 'code_postal', $code_postal);

        echo "<div class='notice notice-success'><p>Ville importée/mise à jour avec succès : {$name}</p></div>";
    }
}
