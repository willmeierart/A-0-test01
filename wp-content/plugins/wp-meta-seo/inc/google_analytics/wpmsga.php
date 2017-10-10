<?php
/**
 * Class manage instance of WPMSGA
 */

if (!class_exists('WPMSGA_Manager')) {

    final class WPMSGA_Manager {

        private static $instance = null;
        public $config = null;
        public $tracking = null;
        public $frontend_item_reports = null;
        public $backend_item_reports = null;
        public $wpmsga_controller = null;

        /**
         * Construct forbidden
         */
        private function __construct() {
            if (null !== self::$instance) {
                _doing_it_wrong(__FUNCTION__, __("This is not allowed, please read the documentation!", 'wp-meta-seo'), '4.6');
            }
        }

        public static function instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

    }

}

/**
 * Returns a unique instance of WPMSGA
 */
function WPMSGA() {
    return WPMSGA_Manager::instance();
}

/*
 * Start WPMSGA
 */
WPMSGA();
