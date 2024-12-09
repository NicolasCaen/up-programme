<?php
namespace UpProgramme\Admin;
use WP_Query;
class Admin {
    public function register() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_menu', [$this, 'add_admin_pages']);

        FilterColumns::getInstance();
    }

    public function enqueue_scripts() {
        wp_enqueue_style(
            'up-programme-admin',
            UP_PROGRAMME_URL . 'assets/css/admin.css',
            [],
            UP_PROGRAMME_VERSION
        );
    }

    public function add_admin_pages() {
        // Page principale
        add_menu_page(
            'Programme Settings',
            'Programme Settings',
            'manage_options',
            'programme-settings',
            [$this, 'render_settings_page'],
            'dashicons-admin-multisite',
            20
        );

        // Sous-pages
        add_submenu_page(
            'programme-settings',
            'Importation Parcelles',
            'Import Parcelles',
            'manage_options',
            'import-parcelles',
            [$this, 'render_import_parcelles_page']
        );

        add_submenu_page(
            'programme-settings',
            'Importation Programmes',
            'Import Programmes',
            'manage_options',
            'import-programmes',
            [$this, 'render_import_programmes_page']
        );

        // Ajouter une sous-page pour l'importation des villes
        add_submenu_page(
            'programme-settings',
            'Importation Villes',
            'Import Villes',
            'manage_options',
            'import-villes',
            [$this, 'render_import_villes_page']
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Programme Settings</h1>
            <p>Bienvenue dans les paramètres de gestion des programmes immobiliers.</p>
            <h2>Options disponibles :</h2>
            <ul>
                <li><a href="<?php echo admin_url('admin.php?page=import-parcelles'); ?>">Importation des parcelles</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=import-programmes'); ?>">Importation des programmes</a></li>
            </ul>
        </div>
        <?php
    }

    public function render_import_parcelles_page() {
        ?>
        <div class="wrap">
            <h1>Importation de parcelles via CSV</h1>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="csv_file" accept=".csv" required>
                <?php submit_button('Importer Parcelles'); ?>
            </form>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES['csv_file']['tmp_name'])) {
                $file = $_FILES['csv_file']['tmp_name'];
                $this->process_csv($file, 'lots');
            }
            ?>
        </div>
        <?php
    }

    public function render_import_programmes_page() {
        ?>
        <div class="wrap">
            <h1>Importation de programmes via CSV</h1>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="csv_file" accept=".csv" required>
                <?php submit_button('Importer Programmes'); ?>
            </form>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES['csv_file']['tmp_name'])) {
                $file = $_FILES['csv_file']['tmp_name'];
                $this->process_csv($file, 'programs');
            }
            ?>
        </div>
        <?php
    }

    public function render_import_villes_page() {
        ?>
        <div class="wrap">
            <h1>Importation de villes via CSV</h1>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="csv_file" accept=".csv" required>
                <?php submit_button('Importer Villes'); ?>
            </form>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES['csv_file']['tmp_name'])) {
                $file = $_FILES['csv_file']['tmp_name'];
                $this->process_csv($file, 'cities');
            }
            ?>
        </div>
        <?php
    }

    private function process_csv($file, $type) {
        switch ($type) {
            case 'programs':
                $importer = new imports\ProgramImport($file);
                break;
            case 'cities':
                $importer = new imports\CityImport($file);
                break;
            case 'lots':
                $importer = new imports\LotImport($file);
                break;
            default:
                return;
        }
        
        $importer->process();
    }
} 