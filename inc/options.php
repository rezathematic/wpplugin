<?php
defined( 'ABSPATH' ) || exit;
class WP_USA_MAP_Options
{
    protected  $path ;
    public  $settings ;
    public static function instance()
    {
        static  $instance = null ;

        if ( null === $instance ) {
            $instance = new WP_USA_MAP_Options();
            $instance->setup_globals();
            $instance->setup_actions();
        }

        return $instance;
    }

    public function setup_globals()
    {
        $this->path = WP_USA_MAP()->path . '/views/';
        $defaults = array(
            'defaultColor'  => '#656565',
            'inactiveColor' => '#000',
            'activeColor'   => '#656565',
        );

        $args = get_option( 'wp-usa-map-settings', array() );
        $this->settings = wp_parse_args( $args, $defaults );
    }

    public function setup_actions()
    {
        add_action( 'admin_menu', array( $this, 'settings_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'add_color_picker' ) );
    }

    /**
     * Settings page
     */
    public function settings_page()
    {
        add_menu_page(
            __( 'WP USA MAP' ),
            __( 'WP USA MAP' ),
            'manage_options',
            'wp-usa-map',
            array( $this, 'settings_page_html' )
        );
    }

    public function add_color_picker( $hook )
    {

        if ( $hook === 'toplevel_page_wp-usa-map' && is_admin() ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
        }

    }

    public function settings_page_html()
    {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        if ( isset( $_POST['_wpnonce'] ) && !empty($_POST['_wpnonce']) && wp_verify_nonce( $_POST['_wpnonce'], 'wp-usa-map-settings' ) ) {
            unset( $_POST['_wpnonce'], $_POST['_wp_http_referer'] );

            if ( isset( $_POST['save'] ) ) {
                unset( $_POST['save'] );
                $this->update_settings( $_POST );
            }

        }

        include $this->path . 'layout-settings.php';
    }

    public function update_settings( $settings )
    {

        foreach ( $settings as $key => $value ) {
            /** Processing checkbox groups **/

            if ( is_array( $value ) ) {
                $this->settings[$key] = array();
                foreach ( $value as $_key => $val ) {
                    $this->settings[$key][$_key] = $val;
                }
            } else {

                $this->settings[$key] = sanitize_text_field( $value );

            }

        }

        update_option( 'wp-usa-map-settings', $this->settings );
    }

    public function get_states(){
        $state_list = array('AL'=>"Alabama",
            'AK'=>"Alaska",
            'AZ'=>"Arizona",
            'AR'=>"Arkansas",
            'CA'=>"California",
            'CO'=>"Colorado",
            'CT'=>"Connecticut",
            'DE'=>"Delaware",
            'DC'=>"District Of Columbia",
            'FL'=>"Florida",
            'GA'=>"Georgia",
            'HI'=>"Hawaii",
            'ID'=>"Idaho",
            'IL'=>"Illinois",
            'IN'=>"Indiana",
            'IA'=>"Iowa",
            'KS'=>"Kansas",
            'KY'=>"Kentucky",
            'LA'=>"Louisiana",
            'ME'=>"Maine",
            'MD'=>"Maryland",
            'MA'=>"Massachusetts",
            'MI'=>"Michigan",
            'MN'=>"Minnesota",
            'MS'=>"Mississippi",
            'MO'=>"Missouri",
            'MT'=>"Montana",
            'NE'=>"Nebraska",
            'NV'=>"Nevada",
            'NH'=>"New Hampshire",
            'NJ'=>"New Jersey",
            'NM'=>"New Mexico",
            'NY'=>"New York",
            'NC'=>"North Carolina",
            'ND'=>"North Dakota",
            'OH'=>"Ohio",
            'OK'=>"Oklahoma",
            'OR'=>"Oregon",
            'PA'=>"Pennsylvania",
            'RI'=>"Rhode Island",
            'SC'=>"South Carolina",
            'SD'=>"South Dakota",
            'TN'=>"Tennessee",
            'TX'=>"Texas",
            'UT'=>"Utah",
            'VT'=>"Vermont",
            'VA'=>"Virginia",
            'WA'=>"Washington",
            'WV'=>"West Virginia",
            'WI'=>"Wisconsin",
            'WY'=>"Wyoming",
            'PR'=>"Puerto Rico");

        return $state_list;
    }

    /**
     * Increases or decreases the brightness of a color by a percentage of the current brightness.
     *
     * @param   string  $hexCode        Supported formats: `#FFF`, `#FFFFFF`, `FFF`, `FFFFFF`
     * @param   float   $adjustPercent  A number between -1 and 1. E.g. 0.3 = 30% lighter; -0.4 = 40% darker.
     *
     * @return  string
     *
     * @author  maliayas
     */
    function adjustBrightness($hexCode, $adjustPercent) {
        $hexCode = ltrim($hexCode, '#');

        if (strlen($hexCode) == 3) {
            $hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
        }

        $hexCode = array_map('hexdec', str_split($hexCode, 2));

        foreach ($hexCode as & $color) {
            $adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
            $adjustAmount = ceil($adjustableLimit * $adjustPercent);

            $color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
        }

        return '#' . implode($hexCode);
    }


}
function WP_USA_MAP_Options()
{
    return WP_USA_MAP_Options::instance();
}
