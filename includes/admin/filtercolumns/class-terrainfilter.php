<?php
namespace UpProgramme\Admin\FilterColumns;

class TerrainFilter extends BaseFilter {
    public function __construct() {
        $this->post_type = 'up_program_terrain';
        $this->taxonomies = [
            'up_program_taxonomy_city', // Programme
            // Ajoutez ici les autres taxonomies spécifiques aux terrains
        ];
        
        parent::__construct();
    }
}
