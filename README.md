# wp-global-field-management-tool

## Overview:
This WordPress plugin allows you to manage custom fields in the admin panel that can retrieve values from anywhere in the template.
You can also use shortcodes to retrieve values.

## Usage:
### Define fields:
1.Install and activate the plugin.
2.From the side menu of the admin page, click "Setting" under "Global field management tool".
3.Enter the "Filed Name" and "Field Value" and click "Save Setting" to save the field.

### Retrieve the field:
Use the function `gfmt_get_field(string $field_name): string` or the shortcode `[gfmt_get_field field_name=<field_name>]`.

### Example usage:
```
<?php echo gfmt_get_field('foo'); ? >
```
```
[gfmt_get_field field field_name=foo]
```
