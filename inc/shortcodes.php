<?php
class WP_USA_MAP_Shortcodes
{

    public static function instance()
    {

        // Store the instance locally to avoid private static replication
        static $instance = null;

        // Only run these methods if they haven't been run previously
        if ( null === $instance ) {
            $instance = new WP_USA_MAP_Shortcodes;
            $instance->setup_actions();
        }

        // Always return the instance
        return $instance;

        // The last metroid is in captivity. The galaxy is at peace.
    }

    public function setup_actions(){
        add_shortcode( 'wp_usa_map', array( $this, 'wp_usa_map' ) );
    }

    public function wp_usa_map(){
        ob_start(); ?>
        <div id="map" style="max-width: 1000px"></div>
        <?php
        global $wpusamap_now_in_state_page;
        if( $wpusamap_now_in_state_page ){
        $options = (array) WP_USA_MAP()->settings['options']; ?>
        <?php if( count($options) > 0 ) { ?>
        <style type="text/css">
            .wpusamap-legend{margin:0 auto;padding:0;list-style:none;
                width: 100%;
                display: flex;
                align-content: space-around;
                flex-direction: row;
                justify-content: space-around;
            }

            .wpusamap-legend li{
                display: flex;
                align-items: center;
                font-size: 14px;
            }

            .wpusamap-legend li .wpusamap-legend-color{
                width: 20px;
                height: 20px;
                display: inline-block;
                margin-right: 10px;
                border: 1px solid black;
            }
        </style>
        <ul class="wpusamap-legend">
        <?php foreach( $options as $option ){ ?>
            <li><span style="background-color: <?php echo $option['color']; ?>" class="wpusamap-legend-color"></span><span class="wpusamap-legend-label"><?php echo $option['name']; ?></span></li>
        <?php } ?>
            <li><span style="background-color: <?php echo WP_USA_MAP()->settings['activeColor']; ?>" class="wpusamap-legend-color"></span><span class="wpusamap-legend-label">Current</span></li>
        </ul>
        <?php } } ?>
        <?php
        return ob_get_clean();
    }
}

function WP_USA_MAP_Shortcodes()
{
    return WP_USA_MAP_Shortcodes::instance();
}
