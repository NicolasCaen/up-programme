<?php
namespace UpProgramme\Meta;

class ProgramMeta extends MetaBase {

    protected $post_type = 'up_program_program';
    
    protected $meta_fields = [
        'up_lot_number' => [
            'type' => 'string',
            'label' => 'Numéro du lot'
        ],
        'up_lot_price' => [
            'type' => 'number',
            'label' => 'Prix du lot',
            'default' => 0
        ],
        'up_lot_surface' => [
            'type' => 'number',
            'label' => 'Surface',
            'default' => 0
        ],
        'up_lot_numero' => [
            'type' => 'string',
            'label' => 'Numéro de lot'
        ]
    ];
} 