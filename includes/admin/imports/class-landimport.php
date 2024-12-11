<?php
namespace UpProgramme\Admin\Imports;
use WP_Query;

class LandImport extends BaseImport {

    private $lots_data = [];
    private $files_data = [];
    private $lots_csv = null;
    private $files_csv = null;

    public function __construct($plugin_name = 'up-programme') {
        parent::__construct($plugin_name);
    }

    public function import_lots_csv($tmp_file) {
        error_log('Import lots CSV - Fichier: ' . $tmp_file);
        $this->lots_csv = $tmp_file;
    }

    public function import_files_csv($tmp_file) {
        error_log('Import files CSV - Fichier: ' . $tmp_file);
        $this->files_csv = $tmp_file;
    }

    protected function process_row($data) {
        $id = $data[$this->column_indices['id']];
        $title = $data[$this->column_indices['title']];
        $description = $data[$this->column_indices['description']];
        $ville_id = $data[$this->column_indices['ville']];
        $archive = $data[$this->column_indices['archive']] ?? '0';
        $plan = $data[$this->column_indices['plan']] ?? '';
        $import_id = $id;

        error_log("Processing terrain - ID: $id, Title: $title, Import ID: $import_id");

        // Vérifier si un terrain existe déjà
        $query = new WP_Query([
            'post_type' => 'up_program_land',
            'meta_query' => [
                [
                    'key' => 'external_id',
                    'value' => $id,
                    'compare' => '='
                ]
            ],
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
            error_log("Terrain existant mis à jour - Post ID: $post_id");
        } else {
            $post_data = array(
                'post_title'    => wp_strip_all_tags($title),
                'post_content'  => $description,
                'post_status'   => 'publish',
                'post_type'     => 'up_program_land',
            );
            $post_id = wp_insert_post($post_data);
            error_log("Nouveau terrain créé - Post ID: $post_id");
        }

        if (!is_wp_error($post_id)) {
            error_log("Mise à jour des métadonnées pour Post ID: $post_id");
            $this->update_post_metadata($post_id, $id, $ville_id, $archive, $plan, $import_id);
            
            if ($this->lots_csv) {
                error_log("Traitement des lots pour Post ID: $post_id, Import ID: $import_id");
                $this->process_lots_for_post($post_id, $import_id);
            }
            
            if ($this->files_csv) {
                error_log("Traitement des fichiers pour Post ID: $post_id, Import ID: $import_id");
                $this->process_files_for_post($post_id, $import_id);
            }
        } else {
            error_log("Erreur lors de la création/mise à jour du terrain - Title: $title");
        }
    }

    private function process_lots_for_post($post_id, $import_id) {
        error_log("Début process_lots_for_post - Post ID: $post_id, Import ID: $import_id");
        
        if (($handle = fopen($this->lots_csv, "r")) !== FALSE) {
            $header = fgetcsv($handle, 0, ";");
            error_log("En-tête du fichier lots: " . print_r($header, true));
            $lots = [];
            
            // Trouver l'index de la colonne ID
            $id_index = array_search('id', array_map('strtolower', $header));
            if ($id_index === false) {
                error_log("Colonne 'id' non trouvée dans l'en-tête. En-tête disponible : " . implode(', ', $header));
                return;
            }
            
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                error_log("Ligne brute: " . print_r($data, true));
                
                if (count($data) !== count($header)) {
                    error_log("Ligne ignorée - Nombre de colonnes incorrect");
                    continue;
                }
                
                $row = array_combine($header, $data);
                error_log("Données ligne après combine: " . print_r($row, true));
                
                $current_id = $data[$id_index];
                error_log("Comparaison - ID ligne: '$current_id', Import ID: '$import_id'");
                
                if ($current_id != $import_id) {
                    error_log("Lot ignoré - ID ne correspond pas");
                    continue;
                }
                
                // Trouver les indices des colonnes nécessaires
                $lot = [];
                
                // Numéro du lot
                foreach (['numrodulot', 'num_lot', 'lot','lot_id'] as $possible_key) {
                    if (isset($row[$possible_key])) {
                        $lot['id'] = $row[$possible_key];
                        break;
                    }
                }
                
                // Surface
                foreach (['superficie', 'surface'] as $possible_key) {
                    if (isset($row[$possible_key])) {
                        $lot['surface'] = $row[$possible_key];
                        break;
                    }
                }
                
                // Prix
                foreach (['prix', 'price'] as $possible_key) {
                    if (isset($row[$possible_key])) {
                        $lot['price'] = floatval($row[$possible_key]);
                        break;
                    }
                }
                
                // État
                foreach (['etat', 'state'] as $possible_key) {
                    if (isset($row[$possible_key])) {
                        $lot['state'] = $this->convert_state($row[$possible_key]);
                        break;
                    }
                }
                
                // Plan
                if (isset($row['plan']) && !empty($row['plan'])) {
                    $lot['plan_terrain'] = $this->clean_url($row['plan']);
                }
                
                error_log("Lot créé: " . print_r($lot, true));
                $lots[] = $lot;
            }
            
            if (!empty($lots)) {
                error_log("Mise à jour des lots pour Post ID $post_id - Nombre de lots: " . count($lots));
                $result = update_field('field_675964deeb787', $lots, $post_id);
                error_log("Résultat mise à jour lots: " . ($result ? "succès" : "échec"));
            } else {
                error_log("Aucun lot trouvé pour ce terrain");
            }
            
            fclose($handle);
        }
    }

    private function process_files_for_post($post_id, $import_id) {
        error_log("Début process_files_for_post - Post ID: $post_id, Import ID: $import_id");
        
        if (($handle = fopen($this->files_csv, "r")) !== FALSE) {
            $header = fgetcsv($handle, 0, ";");
            error_log("En-tête du fichier files: " . print_r($header, true));
            $files = [];
            
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                if (count($data) !== count($header)) {
                    error_log("Ligne ignorée - Nombre de colonnes incorrect");
                    continue;
                }
                
                $row = array_combine($header, $data);
                error_log("Données ligne: " . print_r($row, true));
                
                if (($row['id'] ?? '') != $import_id) {
                    error_log("Fichier ignoré - ID {$row['id']} ne correspond pas à import_id $import_id");
                    continue;
                }
                
                $label = $this->convert_document_label($row['reglement_alt'] ?? $row['reglement'] ?? '');
                
                $file = [
                    'label' => $label,
                    'files' => isset($row['reglement']) && !empty($row['reglement']) ? $this->clean_url($row['reglement']) : ''
                ];
                
                error_log("Fichier ajouté: " . print_r($file, true));
                $files[] = $file;
            }
            
            if (!empty($files)) {
                error_log("Mise à jour des fichiers pour Post ID $post_id - Nombre de fichiers: " . count($files));
                $result = update_field('field_6757eee2128ef', $files, $post_id);
                error_log("Résultat mise à jour fichiers: " . ($result ? "succès" : "échec"));
            } else {
                error_log("Aucun fichier trouvé pour ce terrain");
            }
            
            fclose($handle);
        }
    }

    private function convert_state($state) {
        $states = [
            'Vendu' => 'Vendu',
            'Disponible' => 'Disponible',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5'
        ];
        
        return $states[$state] ?? 'Disponible';
    }

    private function convert_document_label($label) {
        $labels = [
            "Plan de masse général" => "Plan de masse général",
            "Plan local d'urbanisme" => "Plan local d'urbanisme",
            "Etude géotechnique" => "Etude géotechnique",
            "Règlement du lotissement" => "Règlement du lotissement"
        ];
        
        return $labels[$label] ?? "Plan de masse général";
    }

    private function clean_url($url) {
        if (empty($url)) return '';
        return preg_replace('/^images\//', '', $url);
    }

    private function update_post_metadata($post_id, $id, $ville_id, $archive, $plan, $import_id) {
        update_post_meta($post_id, 'external_id', $id);
        update_post_meta($post_id, 'import_id', $import_id);
        
        if (!empty($plan)) {
            update_field('plan', $plan, $post_id);
        }

        update_field('archive', $archive, $post_id);

        if (!empty($ville_id)) {
            $ville_terms = get_terms([
                'taxonomy' => 'up_program_taxonomy_city',
                'hide_empty' => false
            ]);

            if (!is_wp_error($ville_terms) && !empty($ville_terms)) {
                $ville_term = array_filter($ville_terms, function($term) use ($ville_id) {
                    $term_ville_id = get_term_meta($term->term_id, 'identifiant', true);
                    return $term_ville_id == $ville_id;
                });

                if (!empty($ville_term)) {
                    $ville_term = reset($ville_term);
                    wp_set_object_terms($post_id, [$ville_term->term_id], 'up_program_taxonomy_city');
                }
            }
        }
    }
} 