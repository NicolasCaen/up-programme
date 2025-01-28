<?php
namespace UpProgramme;

class Init {
    public static function get_services() {
        up_debug('Loading services...'); // Debug
        return [
            PostTypes\Program::class,
            PostTypes\Land::class,
            PostTypes\Lot::class,
            PostTypes\Parcel::class,
            Taxonomies\City::class,
            Taxonomies\Scheme::class,
            Taxonomies\State::class,
            Taxonomies\Taxprogram::class,
            Taxonomies\Taxland::class,
            Taxonomies\PropertyType::class,
            Taxonomies\Level::class,
            Taxonomies\Rooms::class,
            Meta\LotMeta::class,
            Meta\ParcelMeta::class,
            Meta\ProgramMeta::class,
            Admin\Admin::class,
            Admin\FilterColumns::class,
  
        ];
    }

    public static function register_services() {
        foreach (self::get_services() as $class) {
            up_debug('Initializing: ' . $class); // Debug
            if (class_exists($class)) {
                $service = new $class();
                if (method_exists($service, 'register')) {
                    $service->register();
                    up_debug('Registered: ' . $class); // Debug
                }
            } else {
                up_debug('Class not found: ' . $class); // Debug
            }
        }
    }
} 

