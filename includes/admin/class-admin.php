<?php
namespace UpProgramme\Admin;



class Admin {
    public function register() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        
    }

    public function enqueue_scripts() {
        wp_enqueue_style(
            'up-programme-admin',
            UP_PROGRAMME_URL . 'assets/css/admin.css',
            [],
            UP_PROGRAMME_VERSION
        );
    }
} 