<?php
namespace UpProgramme\Admin\FilterColumns;

class LotFilter extends BaseFilter {
    public function __construct() {
        $this->post_type = 'up_program_lot';
        $this->taxonomies = [
            'up_program_property_type',    // Type de propriété
            'up_program_taxonomy_rooms',   // Nombre de pièces
            'up_program_taxonomy_level',   // Étage
            'up_program_taxonomy_program', // Programme
            'up_program_taxonomy_scheme',  // Dispositif
            'up_program_taxonomy_state'    // État
        ];
        
        parent::__construct();
    }
}
