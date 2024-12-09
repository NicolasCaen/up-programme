<?php
namespace UpProgramme\Admin\Imports;

abstract class BaseImport {
    protected $column_indices;
    protected $file;

    public function __construct($file) {
        $this->file = $file;
    }

    public function process() {
        if (($handle = fopen($this->file, 'r')) !== false) {
            // Lire l'en-tête pour obtenir les indices des colonnes
            $headers = fgetcsv($handle, 1000, ';');
            $this->column_indices = array_flip($headers);

            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                $this->process_row($data);
            }
            fclose($handle);
        }
    }

    abstract protected function process_row($data);
}
