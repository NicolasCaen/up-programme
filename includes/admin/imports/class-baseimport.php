<?php
namespace UpProgramme\Admin\Imports;

abstract class BaseImport {
    protected $plugin_name;
    protected $column_indices = [];

    public function __construct($plugin_name) {
        $this->plugin_name = $plugin_name;
    }

    public function process($tmp_file) {
        error_log("Début du processus d'import - Fichier: " . $tmp_file);
        
        if (($handle = fopen($tmp_file, "r")) !== FALSE) {
            error_log("Fichier ouvert avec succès");
            
            // Lire l'en-tête
            $header = fgetcsv($handle, 0, ";");
            error_log("En-tête du fichier: " . print_r($header, true));
            
            if ($header === false) {
                error_log("Erreur: Impossible de lire l'en-tête du fichier");
                throw new \Exception("Erreur lors de la lecture de l'en-tête du fichier");
            }
            
            try {
                // Configurer les indices des colonnes
                $this->setup_column_indices($header);
                error_log("Indices des colonnes configurés: " . print_r($this->column_indices, true));
                
                // Lire les données
                while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                    error_log("Traitement ligne: " . print_r($data, true));
                    
                    if (count($data) !== count($header)) {
                        error_log("Ligne ignorée - Nombre de colonnes incorrect");
                        continue;
                    }
                    
                    $this->process_row($data);
                }
            } catch (\Exception $e) {
                error_log("Erreur pendant le traitement: " . $e->getMessage());
                throw $e;
            }
            
            fclose($handle);
            error_log("Fichier fermé - Import terminé");
        } else {
            error_log("Erreur: Impossible d'ouvrir le fichier");
            throw new \Exception("Impossible d'ouvrir le fichier");
        }
    }

    protected function setup_column_indices($header) {
        error_log("Configuration des indices de colonnes pour l'en-tête: " . print_r($header, true));
        
        $required_columns = [
            'id' => ['id', 'ID', 'Id'],
            'title' => ['title', 'titre', 'Title', 'Titre'],
            'description' => ['description', 'Description'],
            'ville' => ['ville', 'city', 'Ville', 'City'],
            'archive' => ['archive', 'Archive'],
            'plan' => ['plan', 'Plan']
        ];

        foreach ($required_columns as $key => $possible_names) {
            $found = false;
            foreach ($possible_names as $name) {
                $index = array_search($name, $header);
                if ($index !== false) {
                    $this->column_indices[$key] = $index;
                    $found = true;
                    error_log("Colonne '$key' trouvée à l'index $index");
                    break;
                }
            }
            if (!$found) {
                error_log("Erreur: Colonne requise non trouvée: " . implode(' ou ', $possible_names));
                throw new \Exception("Colonne requise non trouvée : " . implode(' ou ', $possible_names));
            }
        }
    }

    abstract protected function process_row($data);
}
