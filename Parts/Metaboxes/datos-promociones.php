<?php

namespace Parts\Metaboxes;

defined('ABSPATH') or die('No script kiddies please!');


class Datos
{
    private $data;
    private $meta_fields;

    public function __construct()
    {
        if (is_admin()) {
            $this->data = file_get_contents(JP_SERVICES_PATH . '/Parts/Assets/config/fields-datos-promociones.json');
            $this->meta_fields = json_decode($this->data, true);

            add_action('promociones_add_form_fields', array($this, 'create_fields'), 10, 2);
            add_action('promociones_edit_form_fields', array($this, 'edit_fields'),  10, 2);
            add_action('created_promociones', array($this, 'save_fields'), 10, 1);
            add_action('edited_promociones',  array($this, 'save_fields'), 10, 1);
            add_action('admin_footer', array($this, 'media_fields'));
            add_action('admin_enqueue_scripts', 'wp_enqueue_media');
        }
    }

    public function media_fields()
    {
        $render_script = <<<HTML
            <script>
                jQuery(document).ready(function($) {
                    if (typeof wp.media !== 'undefined') {
                        var _custom_media = true,
                            _orig_send_attachment = wp.media.editor.send.attachment;
                        $('.newtermmeta-media').click(function(e) {
                            var send_attachment_bkp = wp.media.editor.send.attachment;
                            var button = $(this);
                            var id = button.attr('id').replace('_button', '');
                            _custom_media = true;
                            wp.media.editor.send.attachment = function(props, attachment) {
                                if (_custom_media) {
                                    $('input#' + id).val(attachment.id);
                                    $('div#preview' + id).css('background-image', 'url(' + attachment.url + ')');
                                } else {
                                    return _orig_send_attachment.apply(this, [props, attachment]);
                                };
                            }
                            wp.media.editor.open(button);
                            return false;
                        });
                        $('.add_media').on('click', function() {
                            _custom_media = false;
                        });
                        $('.remove-media').on('click', function() {
                            var parent = $(this).parents('td');
                            parent.find('input[type="text"]').val('');
                            parent.find('div').css('background-image', 'url()');
                        });
                    }
                });
            </script>
        HTML;
        echo $render_script;
    }

    public function create_fields($taxonomy)
    {
        $output = '';
        foreach ($this->meta_fields as $meta_field) {
            $label = '<label for="' . $meta_field['id'] . '">' . $meta_field['label'] . '</label>';
            $meta_value = '';
            if (empty($meta_value)) {
                if (isset($meta_field['default'])) {
                    $meta_value = $meta_field['default'];
                }
            }
            switch ($meta_field['type']) {
                case 'media':
                    $meta_url = '';
                    if ($meta_value) {
                        $meta_url = wp_get_attachment_url($meta_value);
                    }
                    $input = sprintf(
                        '<input style="display:none;" id="%s" name="%s" type="text" value="%s"><div id="preview%s" style="margin-right:10px;border:2px solid #eee;display:inline-block;width: 100px;height:100px;background-image:url(%s);background-size:contain;background-repeat:no-repeat;"></div><input style="width: 19%%;margin-right:5px;" class="button newtermmeta-media" id="%s_button" name="%s_button" type="button" value="Select" /><input style="width: 19%%;" class="button remove-media" id="%s_buttonremove" name="%s_buttonremove" type="button" value="Clear" />',
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_value,
                        $meta_field['id'],
                        $meta_url,
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_field['id']
                    );
                    break;
                case 'checkbox':
                    $input = sprintf(
                        '<input %s id=" %s" name="%s" type="checkbox" value="1">',
                        $meta_value === '1' ? 'checked' : '',
                        $meta_field['id'],
                        $meta_field['id']
                    );
                    break;
                case 'radio':
                    $input = '<fieldset>';
                    $input .= '<legend class="screen-reader-text">' . $meta_field['label'] . '</legend>';
                    $i = 0;
                    /** @var array $meta_field */
                    foreach ($meta_field['options'] as $key => $value) {
                        $meta_field_value = !is_numeric($key) ? $key : $value;
                        $input .= sprintf(
                            '<label><input %s id=" %s" name="%s" type="radio" value="%s"> %s</label>%s',
                            $meta_value === $meta_field_value ? 'checked' : '',
                            $meta_field['id'],
                            $meta_field['id'],
                            $meta_field_value,
                            $value,
                            $i < count($meta_field['options']) - 1 ? '<br>' : ''
                        );
                        $i++;
                    }
                    $input .= '</fieldset>';
                    break;
                case 'select':
                    $input = sprintf(
                        '<select id="%s" name="%s">',
                        $meta_field['id'],
                        $meta_field['id']
                    );
                    /** @var array $meta_field */
                    foreach ($meta_field['options'] as $key => $value) {
                        $meta_field_value = !is_numeric($key) ? $key : $value;
                        $input .= sprintf(
                            '<option %s value="%s">%s</option>',
                            $meta_value === $meta_field_value ? 'selected' : '',
                            $meta_field_value,
                            $value
                        );
                    }
                    $input .= '</select>';
                    break;
                case 'textarea':
                    $input = sprintf(
                        '<textarea id="%s" name="%s" rows="5">%s</textarea>',
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_value
                    );
                    break;
                case 'pages':
                    $pagesargs = array(
                        'selected' => $meta_value,
                        'echo' => 0,
                        'name' => $meta_field['id'],
                        'id' => $meta_field['id'],
                        'show_option_none' => 'Select a page',
                    );
                    $input = wp_dropdown_pages($pagesargs);
                    break;
                case 'categories':
                    $categoriesargs = array(
                        'selected' => $meta_value,
                        'hide_empty' => 0,
                        'echo' => 0,
                        'name' => $meta_field['id'],
                        'id' => $meta_field['id'],
                        'show_option_none' => 'Select a category',
                    );
                    $input = wp_dropdown_categories($categoriesargs);
                    break;
                case 'users':
                    $usersargs = array(
                        'selected' => $meta_value,
                        'echo' => 0,
                        'name' => $meta_field['id'],
                        'id' => $meta_field['id'],
                        'show_option_none' => 'Select a user',
                    );
                    $input = wp_dropdown_users($usersargs);
                    break;
                default:
                    $input = sprintf(
                        '<input %s id="%s" name="%s" type="%s" value="%s">',
                        $meta_field['type'] !== 'color' ? '' : '',
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_field['type'],
                        $meta_value
                    );
            }
            $output .= '<div class="form-field">' . $this->format_rows($label, $input) . '</div>';
        }
        echo $output;
    }

    public function edit_fields($term, $taxonomy)
    {
        $output = '';
        foreach ($this->meta_fields as $meta_field) {
            $label = '<label for="' . $meta_field['id'] . '">' . $meta_field['label'] . '</label>';
            $meta_value = get_term_meta($term->term_id, $meta_field['id'], true);
            switch ($meta_field['type']) {
                case 'media':
                    $meta_url = '';
                    if ($meta_value) {
                        $meta_url = wp_get_attachment_url($meta_value);
                    }
                    $input = sprintf(
                        '<input style="display:none;" id="%s" name="%s" type="text" value="%s"><div id="preview%s" style="margin-right:10px;border:2px solid #eee;display:inline-block;width: 100px;height:100px;background-image:url(%s);background-size:contain;background-repeat:no-repeat;"></div><input style="width: 19%%;margin-right:5px;" class="button newtermmeta-media" id="%s_button" name="%s_button" type="button" value="Select" /><input style="width: 19%%;" class="button remove-media" id="%s_buttonremove" name="%s_buttonremove" type="button" value="Clear" />',
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_value,
                        $meta_field['id'],
                        $meta_url,
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_field['id']
                    );
                    break;
                case 'checkbox':
                    $input = sprintf(
                        '<input %s id=" %s" name="%s" type="checkbox" value="1">',
                        $meta_value === '1' ? 'checked' : '',
                        $meta_field['id'],
                        $meta_field['id']
                    );
                    break;
                case 'radio':
                    $input = '<fieldset>';
                    $input .= '<legend class="screen-reader-text">' . $meta_field['label'] . '</legend>';
                    $i = 0;
                    /** @var array $meta_field */
                    foreach ($meta_field['options'] as $key => $value) {
                        $meta_field_value = !is_numeric($key) ? $key : $value;
                        $input .= sprintf(
                            '<label><input %s id=" %s" name="%s" type="radio" value="%s"> %s</label>%s',
                            $meta_value === $meta_field_value ? 'checked' : '',
                            $meta_field['id'],
                            $meta_field['id'],
                            $meta_field_value,
                            $value,
                            $i < count($meta_field['options']) - 1 ? '<br>' : ''
                        );
                        $i++;
                    }
                    $input .= '</fieldset>';
                    break;
                case 'select':
                    $input = sprintf(
                        '<select id="%s" name="%s">',
                        $meta_field['id'],
                        $meta_field['id']
                    );
                    /** @var array $meta_field */
                    foreach ($meta_field['options'] as $key => $value) {
                        $meta_field_value = !is_numeric($key) ? $key : $value;
                        $input .= sprintf(
                            '<option %s value="%s">%s</option>',
                            $meta_value === $meta_field_value ? 'selected' : '',
                            $meta_field_value,
                            $value
                        );
                    }
                    $input .= '</select>';
                    break;
                case 'textarea':
                    $input = sprintf(
                        '<textarea id="%s" name="%s" rows="5">%s</textarea>',
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_value
                    );
                    break;
                case 'pages':
                    $pagesargs = array(
                        'selected' => $meta_value,
                        'echo' => 0,
                        'name' => $meta_field['id'],
                        'id' => $meta_field['id'],
                        'show_option_none' => 'Select a page',
                    );
                    $input = wp_dropdown_pages($pagesargs);
                    break;
                case 'categories':
                    $categoriesargs = array(
                        'selected' => $meta_value,
                        'hide_empty' => 0,
                        'echo' => 0,
                        'name' => $meta_field['id'],
                        'id' => $meta_field['id'],
                        'show_option_none' => 'Select a category',
                    );
                    $input = wp_dropdown_categories($categoriesargs);
                    break;
                case 'users':
                    $usersargs = array(
                        'selected' => $meta_value,
                        'echo' => 0,
                        'name' => $meta_field['id'],
                        'id' => $meta_field['id'],
                        'show_option_none' => 'Select a user',
                    );
                    $input = wp_dropdown_users($usersargs);
                    break;
                default:
                    $input = sprintf(
                        '<input %s id="%s" name="%s" type="%s" value="%s">',
                        $meta_field['type'] !== 'color' ? '' : '',
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_field['type'],
                        $meta_value
                    );
            }
            $output .= $this->format_rows($label, $input);
        }
        echo '<div class="form-field">' . $output . '</div>';
    }

    public function format_rows($label, $input)
    {
        return '<tr class="form-field"><th>' . $label . '</th><td>' . $input . '</td></tr>';
    }

    public function save_fields($term_id)
    {
        foreach ($this->meta_fields as $meta_field) {
            if (isset($_POST[$meta_field['id']])) {
                $meta_value = $_POST[$meta_field['id']];
                update_term_meta($term_id, $meta_field['id'], $meta_value);
            } else {
                update_term_meta($term_id, $meta_field['id'], '');
            }
        }
    }
}
if (class_exists('Parts\Metaboxes\Datos')) {
    new Datos();
};
