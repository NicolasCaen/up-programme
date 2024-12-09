<?php
namespace UpProgramme\Admin;

use UpProgramme\Admin\FilterColumns\LotFilter;
use UpProgramme\Admin\FilterColumns\ProgramFilter;
use UpProgramme\Admin\FilterColumns\TerrainFilter;

class FilterColumns {
    private static $instance = null;
    private $filters = [];

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        if (is_admin()) {
            add_action('current_screen', [$this, 'init']);
        }
    }

    public function init() {
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->base, ['edit', 'post'])) {
            return;
        }

        if (empty($this->filters)) {
            $this->filters['lot'] = new LotFilter();
            $this->filters['program'] = new ProgramFilter();
            $this->filters['terrain'] = new TerrainFilter();
        }
    }
}