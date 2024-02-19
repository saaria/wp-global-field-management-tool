<?php
/*
    Plugin Name: Global field management tool for WordPress
    Plugin URI:
    Description: Define and manage custom fields that can be called from anywhere in the template.
    Version: 1.0.0
    Author: YUKI
    Author URI: 
    License: GPLv2
*/

include_once plugin_dir_path( __FILE__ ).'include/constants.php';

add_action('init', 'GlobalFieldManaTool::init');

class GlobalFieldManaTool {

    public static function init() {
        return new self();
    }

    private function __construct() {
        include_once plugin_dir_path( __FILE__ ).'include/gfmt-functions.php';

        wp_enqueue_style('gfmt-style', plugin_dir_url(__FILE__) . 'gfmt-style.css', array(), GFMT_VERSION);
        wp_enqueue_script('mamdragdrop', plugin_dir_url(__FILE__) . 'gfmt-script.js', array(), GFMT_VERSION);

        if (is_admin() && is_user_logged_in()) {
            add_action('admin_menu', [$this, 'set_plugin_menu']);
            add_action('admin_menu', [$this, 'set_plugin_sub_menu']);
            add_action('admin_init', [$this, 'save_config']);
            add_action('admin_notices', [$this, 'admin_notices']);
        }
    }

    public function set_plugin_menu() {
        add_menu_page(
            'Global field management tool', // page title
            'Global field Management tool', // menu title
            'manage_options',               // menu privilege
            GFMT_PLUGIN_ID,                 // menu slug
            [$this, 'show_about_plugin'],   // callback
            'dashicons-admin-tools',        // menu icon
            99                              // menu position(offset)
        );
    }

    public function set_plugin_sub_menu() {
        add_submenu_page(
            GFMT_PLUGIN_ID,             // parent menu slug
            'Setting',                  // submenu page title
            'Setting',                  // submenu title
            'manage_options',           // spbmenu privilege
            GFMT_SETTING_MENU_SLUG,     // submenu slug
            [$this, 'show_config_form'] // callback
        );
    }

    public function show_about_plugin() {
        $html = '<h1>Global field management tool</h1>';
        $html .= '<p>Define and manage custom fields that can be called from anywhere in the template.</p>';
        $html .= '<p><a href="'.admin_url().'admin.php?page='.GFMT_SETTING_MENU_SLUG.'">Go to setting page.</a></p>';
        echo $html;
    }

    public function show_config_form() {
        $data = get_option(GFMT_DATA_DB_COLUMN_NAME, [[''=>'']]);
?>
        <div class="gfmt-wrap">
            <h1>Setting</h1>
                <form action="" method='post' id="gfmt-setting">
                <div class="gfmt-wrap__buttons gfmt-wrap__buttons--right">
                    <button type="submit" class="button button-primary button-large">Save Setting</button>
                </div>
                <table id="field-tabel">
                    <thead>
                        <tr>
                            <th>Dragger</th>
                            <th>Lock Name and Del</th>
                            <th>Field Name</th>
                            <th>Field Value</th>
                            <th>Control</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php wp_nonce_field(GFMT_CREDENTIAL_ACTION, GFMT_CREDENTIAL_NAME); ?>
                    <?php for ($index = 0; $index < count($data); $index++) { ?>
                        <tr data-index="<?=$index?>">
                        <?php foreach ($data[$index] as $key => $value) { ?>
                            <?php $isDefaultLock = !empty($key) ? true : false; ?>
                            <td class="draggable" draggable="true"><span class="dashicons dashicons-menu"></span></td>
                            <td><label class="toggle-button"><input type="checkbox" id="lock-field-<?=$index?>" data-index="<?=$index?>"<?php if ($isDefaultLock) echo ' checked'; ?> /></label></td>
                            <td>
                                <input id="field-name-<?=$index?>" type="text" maxlength="32" name="field-name[]" value="<?=$key?>" data-index="<?=$index?>" aria-label="field-name-<?=$index?>" aria-required="true" required<?php if ($isDefaultLock) echo ' readonly'; ?> />
                            </td>
                            <td>
                                <input id="field-value-<?=$index?>" type="text" maxlength="128" name="field-value[]" value="<?=$value?>" aria-label="field-value-<?=$index?>" aria-required="true" required />
                            </td>
                            <td><button id="delete-row-<?=$index?>" type="button" class="button button-secondary" data-index="<?=$index?>"<?php if ($isDefaultLock) echo ' disabled'; ?>>Delete Field</button></td>
                        <?php } ?>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                </form>
                <div class="gfmt-wrap__buttons gfmt-wrap__buttons--left">
                    <button class="button button-secondary" id="add-filed">Add Filed</button>
                </div>
        </div>
<?php
    }
    
    public function save_config() {
        if (isset($_POST[GFMT_CREDENTIAL_NAME]) && $_POST[GFMT_CREDENTIAL_NAME]) {
            if (check_admin_referer(GFMT_CREDENTIAL_ACTION, GFMT_CREDENTIAL_NAME)) {
                /*if ((isset($_POST['field-name']) && isset($_POST['field-value']))
                    && (is_array($_POST['field-name']) && is_array($_POST['field-value']))
                    && (count($_POST['field-name']) == count($_POST['field-value']))
                ) {*/
                if (self::request_data_validater([$_POST['field-name'], $_POST['field-value']])) {
                    $data = [];
                    for ($i = 0; $i < count($_POST['field-name']); $i++) { 
                        $data[] = [$_POST['field-name'][$i] => $_POST['field-value'][$i]];
                    }
                    update_option(GFMT_DATA_DB_COLUMN_NAME, $data);
                    $GLOBALS['wp_object_cache']->delete(GFMT_DATA_DB_COLUMN_NAME, 'options'); // delete cache
                    $completed_text = "Succeed: The settings are saved.";
                } else {
                    $completed_text = "Failed: Failed to save settings.";
                }
                set_transient(GFMT_COMPLETE_TRANSIENT_KEY, $completed_text, 5);
            } 
        }  
    }

    /* display message after save */
    public function admin_notices() {
        global $pagenow;
        if ( $pagenow != 'admin.php' ) {
            return;
        }

		if ($notice = get_transient(GFMT_COMPLETE_TRANSIENT_KEY)){
		?>
		<div id="message" class="notice notice-<?php echo strpos($notice, 'Succeed') !== false ? 'success' : 'error';?> is-dismissible">
			<p><?=$notice?></p>
		</div>
		<?php
		}
	}

    private static function request_data_validater($data = []) {
        if (!is_array($data)) {
            $data = array($data); 
        }
        $ret = false;
        $length = 0;
        foreach ($data as $value) {
            $ret = (isset($value) === true && is_array($value) === true) && ($length === 0 || ($length !== 0 && $length === count($value)));
            if ($ret === false) {
                break;
            } else {
                $length = count($value);
            }
        }
        return $ret;
    }
}