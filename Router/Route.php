<?php

namespace Router;

defined('ABSPATH') or die("Bye bye");

class Route
{

    public function __construct()
    {
        $this->get();
    }

    public function get()
    {
        $file_paths = $this->loadRouteFiles();

        foreach ($file_paths as $file) {
            require_once(JP_SERVICES_PATH . $file);
        }
    }

    private function loadRouteFiles()
    {
        $json_file = JP_SERVICES_PATH . 'Router/Routes.json';
        if (file_exists($json_file)) {
            $json_data = file_get_contents($json_file);
            $data = json_decode($json_data, true);
            if (isset($data['file_paths'])) {
                return $data['file_paths'];
            }
        }
        return array();
    }
}

if (class_exists('Router\Route')) {
    new Route();
}