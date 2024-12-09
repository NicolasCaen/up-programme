<?php
namespace UpProgramme\Admin\FilterColumns;

class ProgramFilter extends BaseFilter {
    public function __construct() {
        $this->post_type = 'up_program_program';
        $this->taxonomies = [
            'up_program_taxonomy_program', // Programme
            // Ajoutez ici les autres taxonomies spécifiques aux programmes
        ];
        
        parent::__construct();
    }
}
