<?php
/**
 * Settings page
 */
defined( 'ABSPATH' ) || exit;

?>
<style type="text/css">
    .wpum-tab{
        display: none;
    }

    .wpum-tab.active{
        display: block;
    }


    .cols{
        overflow: hidden;
    }

    .cols .col{
        width: 49%;
        float: left;
    }

    @media only screen and (max-width: 1050px){
        .cols .col{
            width: 100%;
            float: none;
        }
    }

    .wpum-tab .form-table th{
        width: auto;
    }

    .wpum-tab#customization .form-table th{
        width: 200px;
    }

    input[type=checkbox], input[type=radio]{
        margin: 0 5px 0 0;
    }

    .table{
        text-align: left;
    }

    .table td,
    .table th{
        padding: 5px 25px 5px 0;
        vertical-align: top;
    }

    .wp-picker-container .wp-color-result.button{
        margin: 0 !important;
    }

    .states-relationship{
        max-height: 200px;
        overflow: auto;
        background: white;
        padding: 15px;
        border: 1px solid #ccc;
    }

    #map-options td{
        vertical-align: middle;
    }

    #map-options td .remove{
        cursor: pointer;
    }

    .select-states{
        min-height: 240px !important;overflow: auto;background: white;padding: 15px !important;border: 1px solid #ccc;
    }
</style>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var hash = location.href.split('#')[1];
        if(typeof hash != 'undefined'){
            var selector = jQuery("#wpum-tabs > a[href='#"+ hash+"']");
            jQuery('#wpum-tabs > a').removeClass('nav-tab-active');
            jQuery('.wpum-tab').removeClass('active');

            jQuery( selector ).addClass('nav-tab-active');
            jQuery( '#' + hash ).addClass('active');
        }

        $('.color-selector').wpColorPicker();

        updateOptions();

        $('.state-option').each(function(){
            var select = $(this);
            var val = select.attr('data-selected');
            select.val(val);
        });

        $('#addNewOption').click(function (event){
           event.preventDefault();

            var lastRow = $('#map-options tbody tr:last');

            var newIndex = 0;

            console.log( lastRow );
            if( lastRow.length > 0 ) {
                newIndex = parseInt(lastRow.attr('data-index')) + 1;
            }

            var row = '<tr data-index="' + newIndex + '">';
            row += '<td>';
            row += '<input type="text" class="optionName" name="options[' + newIndex + '][name]" value="" placeholder="Option name">';
            row += '</td>';
            row += '<td>';
            row += '<input type="text" name="options[' + newIndex + '][color]" class="color-selector" value="gray" />';
            row += '</td>';
            row += '<td><span class="remove">Remove</span></td>';
            row += '</tr>';

            $(row).appendTo($('#map-options tbody'));

            $('.color-selector').wpColorPicker();

            updateOptions();
        });

        $(document).on('change', '#map-options .optionName', function (){
            updateOptions();
        });

        $(document).on('click', '#map-options .remove', function (){
            $(this).closest('tr').remove();
            updateOptions();
        });
    });

    function updateOptions(){
        var options = '';
        jQuery('#map-options tbody tr').each(function(){
            var row = jQuery(this);
            var name  = row.find('.optionName').val().trim();

            if( name !== '' ){
                options += '<option>' + name + '</option>';
            }
        });

        jQuery('.state-option').each(function(){
           var select = jQuery(this);
           var val = select.val();
           select.html(options);

           if( val !== null ){
            select.val(val);
           }
        });
    }
</script>
<div class="wrap">
    <h1><?php _e( 'WP USA MAP', 'wp-usa-map' ); ?></h1>
    <form action="" method="POST">
        <?php wp_nonce_field( 'wp-usa-map-settings' ); ?>
        <div id="general" class="wpum-tab active">
              <?php
              #echo '<pre>'; print_r($this->settings); echo '</pre>';

              echo '<h2>General</h2>';
              echo '<table class="table" style="text-align: left">';
              echo '<tbody>';
              echo '<tr>';
              echo '<td>';
              echo 'Default color';
              echo '</td>';
              echo '<td>';
              echo '<input type="text" name="defaultColor" class="color-selector"  value="' . esc_attr( $this->settings[ 'defaultColor'] ) . '" />';
              echo '</td>';
              echo '</tr>';
              echo '<tr>';
              echo '<td>';
              echo 'Inactive color';
              echo '</td>';
              echo '<td>';
              echo '<input type="text" name="inactiveColor" class="color-selector"  value="' . esc_attr( $this->settings[ 'inactiveColor'] ) . '" />';
              echo '</td>';
              echo '</tr>';
              echo '<tr>';
              echo '<td>';
              echo 'Active color';
              echo '</td>';
              echo '<td>';
              echo '<input type="text" name="activeColor" class="color-selector"  value="' . esc_attr( $this->settings[ 'activeColor'] ) . '" />';
              echo '</td>';
              echo '</tr>';
              echo '</tbody>';
              echo '</table>';

              echo '<h2>Options</h2>';
              echo '<table id="map-options" class="table" style="text-align: left">';
              echo '<thead>';
              echo '<th>Option</th>';
              echo '<th>Color</th>';
              echo '</thead>';
              echo '<tbody>';
              if( isset( $this->settings['options']) && is_array( $this->settings['options'] ) ){
                  foreach( $this->settings['options'] as $index => $option ){
                      echo '<tr data-index="' . $index . '">';
                      echo '<td>';
                      echo '<input type="text" class="optionName" name="options[' . $index . '][name]" value="' . $option['name'] . '" placeholder="Option name">';
                      echo '</td>';
                      echo '<td>';
                      echo '<input type="text" name="options[' . $index . '][color]" class="color-selector" value="' . $option['color'] . '" />';
                      echo '</td>';
                      echo '<td><span class="remove">Remove</span></td>';
                      echo '</tr>';
                  }
              }
              echo '</tbody>';
              echo '<tfoot>';
              echo '<tr>';
              echo '<td colspan="2">';
              echo '<span id="addNewOption" class="button">Add option</span>';
              echo '</td>';
              echo '</tr>';
              echo '</tfoot>';
              echo '</table>';

              $pages  = get_pages();
              #print_r($pages);

              echo '<h2>States</h2>';
                $states = WP_USA_MAP()->options->get_states();
                echo '<table id="map-states" class="table" style="text-align: left">';
                echo '<thead>';
                  echo '<th>State</th>';
                  echo '<th>Page</th>';
                  echo '<th>Option</th>';
                echo '</thead>';
                echo '<tbody>';
                foreach( $states as $code => $state ){
                    $pages_select  = '<select multiple name="states[' . $code . '][page_id]" class="select-states">';
                    $pages_select .= walk_page_dropdown_tree( $pages, 0 );
                    $pages_select .= '</select>';

                    echo '<tr>';
                    echo '<td>' . $state . '</td>';
                    echo '<td>';
                    echo $pages_select;
                    echo '<td>';

                    echo '<div class="states-relationship">';
                    echo '<table>';
                    foreach($states as $_code => $_state){
                        echo '<tr>';
                        echo '<td>' . $_state . '</td>';
                        echo '<td>';
                        echo '<select name="states[' . $code . '][states][' . $_code . ']" data-selected="' . (isset($this->settings['states'][$code]['states'][$_code]) ? $this->settings['states'][$code]['states'][$_code] : '') .'" class="state-option">';
                        echo '</select>';
                        echo '</td>';
                        echo  '</tr>';
                    }
                    echo '</table>';
                    echo '</div>';
                    echo '</td>';
                    echo '</td>';
                    echo '</tr>';
                }
              echo '<tbody>';
              echo '</table>';
              ?>
        </div>

        <p class="submit">
            <input type="submit" name="save" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'wp-usa-map' ); ?>">
        </p>
    </form>
</div>