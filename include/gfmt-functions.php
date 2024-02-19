<?php

include_once plugin_dir_path( __FILE__ ).'/../include/constants.php';

function gfmt_get_field(string $field_name): string {
    $data = get_option(GFMT_DATA_DB_COLUMN_NAME, [''=>'']);
    
    /*
    *   $data = [
    *       [string 'key' => string 'value'],
    *       [string 'key' => string 'value'],
    *       :
    *       :
    *   ]
    */
    $returnValue = '';
    if (is_array($data)) {
        for ($index = 0; $index < count($data); $index++) {
            foreach ($data[$index] as $key => $value) {
                if ($key === $field_name) {
                    $returnValue = $value;
                    break;
                }
            }
        }
    }
    return esc_html($returnValue);
}

function gfmt_get_field_shortcode(array $atts): string {
    $atts = shortcode_atts([
        "field_name" => '',
    ], $atts);
    return gfmt_get_field($atts['field_name']);
}
add_shortcode( 'gfmt_get_field', 'gfmt_get_field_shortcode' );