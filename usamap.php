<?php
/*
    @wordpress-plugin
    Plugin Name: WP USA Map
    Description: USA Map - added some more description test update
    Version: 1.0.0
    Author: Custom Development
    Author URI: https://www.wordplus.org
    License: GPL2
    Text Domain: wp-usa-map
    Domain Path: /languages
*/
defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . 'lib/wp-package-updater/class-wp-package-updater.php';

$WP_USA_MAP_updater = new WP_Package_Updater(
    'http://localhost:10008/',
    wp_normalize_path(__FILE__),
    wp_normalize_path(plugin_dir_path(__FILE__)),
);

if (!class_exists('WP_USA_MAP')) {
    class WP_USA_MAP
    {
        public  $version = '1.0.0';
        public  $path;
        public  $url;

        public $options;
        public $shortcodes;

        public static function instance()
        {
            // Store the instance locally to avoid private static replication
            static  $instance = null;
            // Only run these methods if they haven't been run previously

            if (null === $instance) {
                $instance = new WP_USA_MAP();
                $instance->load_textDomain();
                $instance->setup_vars();
                $instance->setup_actions();
                $instance->setup_classes();
            }

            // Always return the instance
            return $instance;
            // The last metroid is in captivity. The galaxy is at peace.
        }


        public function load_textDomain()
        {
            load_plugin_textdomain('wp-usa-map', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }


        public function setup_vars()
        {
            $this->path = plugin_dir_path(__FILE__);
            $this->url  = plugin_dir_url(__FILE__);
        }


        public function setup_actions()
        {
            $this->require_files();
            add_action('wp_enqueue_scripts', array($this, 'load_scripts'));
        }

        public function require_files()
        {
            require_once 'inc/options.php';
            require_once 'inc/shortcodes.php';
        }

        public function setup_classes()
        {
            $this->options   = WP_USA_MAP_Options();
            $this->load_options();
            $this->shortcodes   = WP_USA_MAP_Shortcodes();
        }

        public function load_options()
        {
            $this->settings = $this->options->settings;
        }

        public function load_scripts()
        {
            wp_register_script('wpusamaps-mapdata-js', plugins_url('assets/mapdata.js', __FILE__), [], $this->version);

            wp_register_script('wpusamaps-usmaps-js', plugins_url('assets/usmap.js', __FILE__), [
                'jquery',
                'wpusamaps-mapdata-js',
            ], $this->version);

            $fills = [
                'defaultFill' => WP_USA_MAP()->settings['defaultColor']
            ];

            ob_start();

            global $wpusamap_now_in_state_page, $wpusamap_state;
            $page_ids = [];

            $wpusamap_now_in_state_page = false;
            $current_page = 0;

            if (is_page()) {
                $current_page = (int) get_the_ID();
            }

            foreach (WP_USA_MAP()->settings['states'] as $code => $state) {
                if (!empty($state['page_id'])) {
                    $page_ids[] = (int) $state['page_id'];

                    if ($current_page === (int) $state['page_id']) {
                        $wpusamap_now_in_state_page = true;
                        $wpusamap_state = $state;
                    }
                }
            }

            foreach (WP_USA_MAP()->settings['options'] as $fill) {
                $fills[$fill['name']] = $fill['color'];
            }
            #echo '<pre>'; print_r($wpusamap_state); echo '</pre>';
            #echo '<pre>'; print_r($fills); echo '</pre>';

            foreach (WP_USA_MAP()->settings['states'] as $code => $state) {
                if ($wpusamap_now_in_state_page) {
                    $fill = (isset($wpusamap_state['states'][$code])) ? $wpusamap_state['states'][$code] : 'defaultFill';
                    $color = $fills[$fill];
                    $hoverColor = WP_USA_MAP()->options->adjustBrightness($color, -0.3);
                } else {
                    $color = WP_USA_MAP()->settings['inactiveColor'];
                    $hoverColor = WP_USA_MAP()->options->adjustBrightness($color, 0.3);
                }

                echo 'simplemaps_usmap_mapdata.state_specific["' . $code . '"]["color"]="' . $color . '";';
                echo 'simplemaps_usmap_mapdata.state_specific["' . $code . '"]["hover_color"]="' . $hoverColor . '";';

                if (!empty($state['page_id'])) {
                    $permalink = get_permalink($state['page_id']);
                    if (!!$permalink) {
                        echo 'simplemaps_usmap_mapdata.state_specific["' . $code . '"]["url"]="' . $permalink . '";';
                    }

                    if ((int) $current_page === (int) $state['page_id']) {
                        $color = WP_USA_MAP()->settings['activeColor'];
                        $hoverColor = WP_USA_MAP()->options->adjustBrightness($color, 0.3);

                        echo 'simplemaps_usmap_mapdata.state_specific["' . $code . '"]["color"]="' . $color . '";';
                        echo 'simplemaps_usmap_mapdata.state_specific["' . $code . '"]["hover_color"]="' . $hoverColor . '";';
                        echo 'simplemaps_usmap_mapdata.state_specific["' . $code . '"]["url"]=false;';
                    }
                }
            }
            $script = ob_get_clean();
            wp_add_inline_script('wpusamaps-usmaps-js', $script, 'before');

            wp_enqueue_script('wpusamaps-usmaps-js');
        }
    }
}

function WP_USA_MAP()
{
    return WP_USA_MAP::instance();
}

WP_USA_MAP();
