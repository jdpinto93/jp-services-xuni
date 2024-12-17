<?php

namespace Parts\Metaboxes;

defined('ABSPATH') or die('No script kiddies please!');

class UtilitiesFields
{
    public static function add_meta_boxes($screens, $post, $args)
    {
        foreach ($screens as $single_screen) {
            add_meta_box(
                $single_screen . '_meta_box',
                __('Campos Adicionales', 'jp-services'),
                function ($post) use ($args) {
                    self::meta_box_callback(
                        $post,
                        $args['nonce_field'],
                        $args['nonce_data'],
                        $args['meta_fields']
                    );
                },
                $single_screen,
                'normal',
                'low'
            );
        }
    }

    private static function meta_box_callback($post, $nonce_field, $nonce_data, $meta_fields)
    {
        wp_nonce_field($nonce_data, $nonce_field);
        self::field_generator($post, $meta_fields);
    }

    public static function media_fields()
    { ?> <style>
textarea {
    width: 100%;
}

.form-table select {
    width: 100%;
}
</style>
<script>
jQuery(document).ready(function($) {
    if (typeof wp.media !== 'undefined') {
        var _custom_media = true,
            _orig_send_attachment = wp.media.editor.send.attachment;
        $('.new-media').click(function(e) {
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $(this);
            var id = button.attr('id').replace('_button', '');
            _custom_media = true;
            wp.media.editor.send.attachment = function(props, attachment) {
                if (_custom_media) {
                    if ($('input#' + id).data('return') == 'url') {
                        $('input#' + id).val(attachment.url);
                    } else {
                        $('input#' + id).val(attachment.id);
                    }
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
<?php }

    public static function field_generator($post, $meta_fields)
    {
        $output = '';
        foreach ($meta_fields as $meta_field) {
            $label = '<label for="' . esc_attr($meta_field['id']) . '">' . esc_html($meta_field['label']) . '</label>';
            $meta_value = get_post_meta($post->ID, $meta_field['id'], true);
            if (empty($meta_value)) {
                if (isset($meta_field['default'])) {
                    $meta_value = $meta_field['default'];
                }
            }
            switch ($meta_field['type']) {
                case 'text':
                case 'email':
                case 'url':
                case 'password':
                case 'number':
                case 'color':
                case 'tel':
                case 'date':
                    $input = sprintf(
                        '<input %s id="%s" name="%s" type="%s" value="%s">',
                        $meta_field['type'] !== 'color' ? 'style="width: 100%;"' : '',
                        esc_attr($meta_field['id']),
                        esc_attr($meta_field['id']),
                        esc_attr($meta_field['type']),
                        esc_attr($meta_value)
                    );
                    break;

                case 'textarea':
                    $input = sprintf(
                        '<textarea id="%s" name="%s" rows="5">%s</textarea>',
                        esc_attr($meta_field['id']),
                        esc_attr($meta_field['id']),
                        esc_textarea($meta_value)
                    );
                    break;

                case 'checkbox':
                    $input = sprintf(
                        '<input%s id="%s" name="%s" type="checkbox" value="1">',
                        $meta_value === '1' ? ' checked' : '',
                        esc_attr($meta_field['id']),
                        esc_attr($meta_field['id'])
                    );
                    break;

                case 'radio':
                    $input = '<fieldset>';
                    $input .= '<legend class="screen-reader-text">' . esc_html($meta_field['label']) . '</legend>';
                    $i = 0;
                    /** @var array $meta_field */
                    foreach ($meta_field['options'] as $key => $value) {
                        $meta_field_value = !is_numeric($key) ? $key : $value;
                        $input .= sprintf(
                            '<label><input %s id="%s" name="%s" type="radio" value="%s"> %s</label>%s',
                            $meta_value === $meta_field_value ? 'checked' : '',
                            esc_attr($meta_field['id']),
                            esc_attr($meta_field['id']),
                            esc_attr($meta_field_value),
                            esc_html($value),
                            $i < count($meta_field['options']) - 1 ? '<br>' : ''
                        );
                        $i++;
                    }
                    $input .= '</fieldset>';
                    break;

                case 'select':
                    $input = sprintf(
                        '<select id="%s" name="%s">',
                        esc_attr($meta_field['id']),
                        esc_attr($meta_field['id'])
                    );
                    /** @var array $meta_field */
                    foreach ($meta_field['options'] as $key => $value) {
                        $meta_field_value = !is_numeric($key) ? $key : $value;
                        $input .= sprintf(
                            '<option %s value="%s">%s</option>',
                            $meta_value === $meta_field_value ? 'selected' : '',
                            esc_attr($meta_field_value),
                            esc_html($value)
                        );
                    }
                    $input .= '</select>';
                    break;

                case 'wysiwyg':
                    ob_start();
                    wp_editor(
                        $meta_value,
                        $meta_field['id'],
                        array(
                            'textarea_name' => $meta_field['id']
                        )
                    );
                    $input = ob_get_clean();
                    break;

                case 'media':
                    $meta_type = '';
                    if ($meta_value) {
                        if ($meta_field['returnvalue'] == 'ID') {
                            $meta_type = $meta_value;
                        } else {
                            $meta_type = wp_get_attachment_url($meta_value);
                        }
                    }
                    $input = sprintf(
                        '<input style="display:none;" id="%s" name="%s" type="text" value="%s"  data-return="%s"><div id="preview%s" style="margin-right:10px;border:1px solid #e2e4e7;background-color:#fafafa;display:inline-block;width: 100px;height:100px;background-image:url(%s);background-size:cover;background-repeat:no-repeat;background-position:center;"></div><input style="width: 19%%;margin-right:5px;" class="button new-media" id="%s_button" name="%s_button" type="button" value="Select" /><input style="width: 19%%;" class="button remove-media" id="%s_buttonremove" name="%s_buttonremove" type="button" value="Clear" />',
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_value,
                        $meta_field['returnvalue'],
                        $meta_field['id'],
                        $meta_type,
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_field['id'],
                        $meta_field['id']
                    );
                    break;

                case 'categories':
                    $categoriesargs = array(
                        'selected' => $meta_value,
                        'hide_empty' => 0,
                        'echo' => 0,
                        'name' => esc_attr($meta_field['id']),
                        'id' => esc_attr($meta_field['id']),
                        'show_option_none' => 'My Custom Field 8',
                    );
                    $input = wp_dropdown_categories($categoriesargs);
                    break;

                case 'users':
                    $usersargs = array(
                        'selected' => $meta_value,
                        'echo' => 0,
                        'name' => esc_attr($meta_field['id']),
                        'id' => esc_attr($meta_field['id']),
                        'show_option_none' => 'My Custom Field 9',
                    );
                    $input = wp_dropdown_users($usersargs);
                    break;

                default:
                    $input = '';
                    break;
            }
            $output .= self::format_rows($label, $input);
        }
        echo '<table class="form-table"><tbody>' . $output . '</tbody></table>';
    }

    private static function format_rows($label, $input)
    {
        return '<tr><th>' . $label . '</th><td>' . $input . '</td></tr>';
    }

    public static function save_fields($post_id, $nonce_field, $nonce_data, $meta_fields)
    {
        if (!isset($_POST[$nonce_field]))
            return $post_id;
        $nonce = $_POST[$nonce_field];
        if (!wp_verify_nonce($nonce, $nonce_data))
            return $post_id;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;
        foreach ($meta_fields as $meta_field) {
            if (isset($_POST[$meta_field['id']])) {
                switch ($meta_field['type']) {
                    case 'email':
                        $_POST[$meta_field['id']] = sanitize_email($_POST[$meta_field['id']]);
                        break;
                    case 'text':
                        $_POST[$meta_field['id']] = sanitize_text_field($_POST[$meta_field['id']]);
                        break;
                }
                update_post_meta($post_id, $meta_field['id'], $_POST[$meta_field['id']]);
            } else if ($meta_field['type'] === 'checkbox') {
                update_post_meta($post_id, $meta_field['id'], '0');
            }
        }
    }
}

if (class_exists('Parts\Metaboxes\UtilitiesFields')) {
    new UtilitiesFields;
};