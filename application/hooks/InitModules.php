<?php

defined('BASEPATH') or exit('No direct script access allowed');

class InitModules
{
    /**
     * Early init modules features
     */
    public function handle()
    {
        include_once(LIBSPATH.'App_modules.php');
        // Load the directory helper so the directory_map function can be used
        include_once(BASEPATH . 'helpers/directory_helper.php');

        // Fix for Heroku: Ensure hooks are initialized
        global $hooks;
        
        if (!class_exists('Hooks')) {
            if (file_exists(APPPATH . 'vendor/bainternet/php-hooks/php-hooks.php')) {
                require_once(APPPATH . 'vendor/bainternet/php-hooks/php-hooks.php');
            }
        }

        if (!isset($hooks) || $hooks === null) {
            if (class_exists('Hooks')) {
                $hooks = new Hooks();
            }
        }

        foreach (\App_modules::get_valid_modules() as $module) {
            $excludeUrisPath = $module['path'] . 'config' . DIRECTORY_SEPARATOR . 'csrf_exclude_uris.php';

            if (file_exists($excludeUrisPath)) {
                $uris = include_once($excludeUrisPath);

                if (is_array($uris)) {
                    hooks()->add_filter('csrf_exclude_uris', function ($current) use ($uris) {
                        return array_merge($current, $uris);
                    });
                }
            }
        }
    }
}
