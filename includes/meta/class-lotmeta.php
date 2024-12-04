<?php
namespace UpProgramme\Meta;

require_once 'callbacks.php';

class LotMeta extends MetaBase {
    protected $post_type = 'up_program_lot';

    protected $meta_fields = [
        'up_price' => [
            'type' => 'string',
            'label' => 'Prix du lot',
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
            'get_callback' => 'format_price',
        ],
        'up_surface' => [
            'type' => 'string',
            'label' => 'Surface',
            'default' => '0',
            'sanitize_callback' => 'sanitize_text_field',
            'get_callback' => 'format_surface',
        ],
        'up_lot_number' => [
            'type' => 'string',
            'label' => 'Numéro de lot',
            'sanitize_callback' => 'sanitize_text_field',
            'get_callback' => 'format_lot_number',
        ]
    ];
    
} 