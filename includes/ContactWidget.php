<?php

namespace RRZE\Contact;

defined('ABSPATH') || exit;

require_once ABSPATH . 'wp-includes/class-wp-widget.php';

class CampoWidget extends \WP_Widget
{

    protected $pluginFile;
    protected $settings;

    public function __construct($pluginFile, $settings)
    {

        $this->pluginFile = $pluginFile;
        $this->settings = $settings;

        parent::__construct(
            'campo_widget',
            __('Contact Widget', 'rrze-contact'),
            array('description' => __('Displays a lecture', 'rrze-contact'))
        );
    }

    // Creating widget front-end
    public function widget($args, $instance)
    {
        $atts = '';
        $atts .= (!empty($instance['show']) ? ' show=' . $instance['show'] : '');
        $atts .= (!empty($instance['hide']) ? ' hide=' . $instance['hide'] : '');
        $field = ($instance['task'] == 'lectures-single' ? 'lv_id' : 'campoid');

        $shortcode = new Shortcode($this->pluginFile, $this->settings);
        $shortcode->onLoaded();

        echo $args['before_widget'];
        echo do_shortcode('[contact task="' . $instance['task'] . '" ' . $field . '=' . $instance['campoid'] . $atts . ']');
        echo $args['after_widget'];
    }

    public function getSelectHTML($name, $selectedID = 0)
    {
        $aOptions = [
            'lectures-single' => __('Lecture', 'rrze-contact'),
        ];
        $output = "<select id='{$this->get_field_id($name)}' name='{$this->get_field_name($name)}' class='widefat'>";
        foreach ($aOptions as $ID => $txt) {
            $sSelected = selected($selectedID, $ID, false);
            $output .= "<option value='$ID' $sSelected>$txt</option>";
        }
        $output .= "</select></p>";
        return $output;
    }

    public function getInputHTML($name, $label, $val = '')
    {
        return "<input type='text' id='{$this->get_field_id($name)}' name='{$this->get_field_name($name)}' placeholder=' . $label . ' class='widefat' value='" . (!empty($val) ? $val : '') . "'>";
    }

    // Widget Backend
    public function form($instance)
    {
        echo '<br \>';
        echo $this->getSelectHTML('task', !empty($instance['task']) ? $instance['task'] : null);
        echo $this->getInputHTML('campoid', 'Contact ID', !empty($instance['campoid']) ? $instance['campoid'] : null);
        echo '<br \>&nbsp;';
    }

    // Updating widget replacing old instances with new
    public function update($new_instance, $old_instance)
    {
        $instance = [];
        $instance['task'] = (!empty($new_instance['task']) ? $new_instance['task'] : '');
        $instance['campoid'] = (!empty($new_instance['campoid']) ? $new_instance['campoid'] : '');
        $instance['show'] = (!empty($new_instance['show']) ? $new_instance['show'] : '');
        $instance['hide'] = (!empty($new_instance['hide']) ? $new_instance['hide'] : '');
        return $instance;
    }
}
