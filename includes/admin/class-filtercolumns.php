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
        add_action('admin_init', [$this, 'init']);
    }

    public function init() {
        if (!is_admin()) {
            return;
        }

        if (empty($this->filters)) {
            $this->filters['lot'] = new LotFilter();
            $this->filters['program'] = new ProgramFilter();
            $this->filters['terrain'] = new TerrainFilter();
        }
    }
}