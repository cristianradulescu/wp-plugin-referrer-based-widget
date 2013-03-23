<?php
/**
 * Plugin Name: Referrer based widget
 * Description: Show/hide widget based on predefined referrers.
 * Author: Cristian Radulescu
 * Author URI: http://cristian-radulescu.ro
 * Version: 1.0
 */

/**
 * Based on text widget
 */
require_once dirname(__FILE__).'/../../../wp-includes/default-widgets.php';
class RBW_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_text', 'description' => __('Arbitrary text or HTML based on referrer'));
		$control_ops = array('width' => 400, 'height' => 350);
		parent::__construct('rbw_text', __('Referrer based widget'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
		$hide_for_referrers = apply_filters( 'hide_for_referrers', empty( $instance['hide_for_referrers'] ) ? '' : $instance['hide_for_referrers'], $instance );
    if ('' != $hide_for_referrers) {
      $hide_for_referrers = explode("\r\n", $hide_for_referrers);
    }
    $hide_widget = false;
    $current_referrer = wp_get_referer();
    if (false !== $current_referrer && $hide_for_referrers) {
      foreach ($hide_for_referrers as $referrer) {
//        var_dump($current_referrer);
//        var_dump($referrer);
//        var_dump(strpos($current_referrer, $referrer));
        if (false !== strpos($current_referrer, $referrer)) {
          $hide_widget = true;
          break;
        }
      }
    }

    if ($hide_widget) {
      ?>
      <script>
        jQuery(document).ready(function() {
          jQuery('#<?php echo $this->id ?>').hide();
        });
      </script>
      <?php
    }

		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
			<div class="textwidget">
        <?php echo !empty( $instance['filter'] ) ? wpautop( $text ) : $text; ?>
      </div>
		<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['hide_for_referrers'] = strip_tags($new_instance['hide_for_referrers']);
		if ( current_user_can('unfiltered_html') ) {
			$instance['text'] =  $new_instance['text'];
      $instance['hide_for_referrers'] = $new_instance['hide_for_referrers'];
    } else {
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
			$instance['hide_for_referrers'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['hide_for_referrers']) ) );
    }
		$instance['filter'] = isset($new_instance['filter']);

		return $instance;
	}


	function form( $instance ) {
		$instance = wp_parse_args(
        (array)$instance,
        array(
          'title' => '',
          'text' => '',
          'hide_for_referrers' => '',
        )
      );

		$title = strip_tags($instance['title']);
		$text = esc_textarea($instance['text']);
		$hide_for_referrs = esc_textarea($instance['hide_for_referrers']);
    ?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
      <input class="widefat"
             id="<?php echo $this->get_field_id('title'); ?>"
             name="<?php echo $this->get_field_name('title'); ?>"
             type="text"
             value="<?php echo esc_attr($title); ?>" />
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Content:'); ?></label>
      <textarea class="widefat"
                rows="10"
                cols="20"
                id="<?php echo $this->get_field_id('text'); ?>"
                name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>
    </p>

		<p>
      <input id="<?php echo $this->get_field_id('filter'); ?>"
             name="<?php echo $this->get_field_name('filter'); ?>"
             type="checkbox" <?php checked(isset($instance['filter']) ? $instance['filter'] : 0); ?> />&nbsp;
      <label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e('Automatically add paragraphs'); ?></label>
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('hide_for_referrers'); ?>"><?php _e('Hide for referrers (one per line):'); ?></label>
      <textarea class="widefat"
                rows="10"
                cols="20"
                id="<?php echo $this->get_field_id('hide_for_referrers'); ?>"
                name="<?php echo $this->get_field_name('hide_for_referrers'); ?>"><?php echo $hide_for_referrs; ?></textarea>
    </p>
  <?php
	}
}

function rbw_widgets_init() {
	register_widget('RBW_Widget');
	do_action('widgets_init');
}

add_action('init', 'rbw_widgets_init');