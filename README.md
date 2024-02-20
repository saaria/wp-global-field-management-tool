# wp-global-field-management-tool

![wp-global-field-management-tool_screenshot](https://github.com/saaria/wp-global-field-management-tool/assets/63186517/f49e6a98-43b3-4ac9-8ccd-2875bee12993)

## Overview:
This WordPress plugin allows you to manage custom fields in the admin panel that can retrieve values from anywhere in the template.
You can also use shortcodes to retrieve values.
The field format is text type only.

## Usage:
### Define fields:
1.Install and activate the plugin.
2.From the side menu of the admin page, click "Setting" under "Global field management tool".
3.Enter the "Filed Name" and "Field Value" and click "Save Setting" to save the field.

### Retrieve the field:
Use the function `gfmt_get_field(string $field_name): string` or the shortcode `[gfmt_get_field field_name=<field_name>]`.

### Example usage:
```
<?php echo gfmt_get_field('foo'); ?>
```
```
[gfmt_get_field field field_name=foo]
```
