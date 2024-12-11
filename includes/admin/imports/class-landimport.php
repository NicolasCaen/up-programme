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

        } else {
            $post_data = array(
                'post_title'    => wp_strip_all_tags($title),
                'post_content'  => $description,
                'post_status'   => 'publish',
                'post_type'     => 'up_program_land',
            );
            $post_id = wp_insert_post($post_data);
        }

        if (!is_wp_error($post_id)) {

            $this->update_post_metadata($post_id, $id, $ville_id, $archive, $plan, $import_id);
            
            if ($this->lots_csv) {
                $this->process_lots_for_post($post_id, $import_id);
            }
            
            if ($this->files_csv) {
                $this->process_files_for_post($post_id, $import_id);
            }
        } 
    }

    private function process_lots_for_post($post_id, $import_id) {
   
        
        if (($handle = fopen($this->lots_csv, "r")) !== FALSE) {
            $header = fgetcsv($handle, 0, ";");

            $lots = [];
            
            // Trouver l'index de la colonne ID
            $id_index = array_search('id', array_map('strtolower', $header));
            if ($id_index === false) {
                return;
            }
            
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                
                if (count($data) !== count($header)) {
                    continue;
                }
                
                $row = array_combine($header, $data);
                
                $current_id = $data[$id_index];
                
                if ($current_id != $import_id) {
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
                
                $lots[] = $lot;
            }
            
            if (!empty($lots)) {
                $result = update_field('field_675964deeb787', $lots, $post_id);
            } 
            
            fclose($handle);
        }
    }

    private function process_files_for_post($post_id, $import_id) {
        
        if (($handle = fopen($this->files_csv, "r")) !== FALSE) {
            $header = fgetcsv($handle, 0, ";");
            $files = [];
            
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                if (count($data) !== count($header)) {
                    continue;
                }
                
                $row = array_combine($header, $data);

                
                if (($row['id'] ?? '') != $import_id) {

                    continue;
                }
                
                $label = $row['label'] ?? $row['label'] ?? '10';
                
                $file = [
                    'label' => $label,
                    'files' => isset($row['files']) && !empty($row['files']) ? $this->clean_url($row['files']) : ''
                ];
                
                error_log("Fichier ajouté: " . print_r($file, true));
                $files[] = $file;
            }
            
            if (!empty($files)) {
                $result = update_field('field_6757eee2128ef', $files, $post_id);
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
        // Si le label est vide ou non numérique, retourne la valeur par défaut
        if (empty($label) || !is_numeric($label)) {
            return "1"; // 1 = Plan de masse général par défaut
        }
        
        // Vérifie si le nombre est entre 1 et 4
        $label = intval($label);
        if ($label >= 1 && $label <= 4) {
            return (string)$label;
        }
        
        return "1"; // Valeur par défaut
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