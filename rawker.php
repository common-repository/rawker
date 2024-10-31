<?php
/*
Plugin Name: Rawker
Version: 0.1
Plugin URI: http://jamietalbot.com/wp-hacks/rawker/
Description: Random header images tied to Gengo post groups.  Based on code from the Random Header plugin by <a href="http://www.martinet.nl/">Kamiel Martinet</a>.  Licensed under the <a href="http://www.opensource.org/licenses/mit-license.php">MIT License</a>, Copyright &copy; 2006 Jamie Talbot.
Author: Jamie Talbot
Author URI: http://jamietalbot.com

/*
Rawker - Random header images tied to Gengo post groups.
Copyright (c) 2006 Jamie Talbot

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the
Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software,
and to permit persons to whom the Software is furnished to
do so, subject to the following conditions:

The above copyright notice and this permission notice shall
be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

define ("RAWKER_BASE_DIR", "rawker/");
define ("RAWKER_DIR", "wp-content/plugins/" . RAWKER_BASE_DIR);
define ("RAWKER_IMG_DIRECTORY", '/img');
define ("RAWKER_OPTIONS_PAGE", "rawker_options_page.php");
define ("RAWKER_DOMAIN", "rawker");

class Rawker {

	function Rawker() {
		$this->version = 0.1;

		// Admin UI.
		if (is_admin()) {
			add_action('activate_rawker/rawker.php', array(& $this, 'activated'));
			add_action('admin_menu', array(& $this, 'admin_menu'));
		} else {
			add_action('wp_head', array(& $this, 'wp_head'));
		}
	}

	// Hook functions.

	function admin_menu() {
		add_options_page(__('Rawker Options', RAWKER_DOMAIN), __('Rawker', RAWKER_DOMAIN), 8, RAWKER_DIR . RAWKER_OPTIONS_PAGE);
	}

	function activated() {
		header('Location: admin.php?page=' . RAWKER_BASE_DIR . RAWKER_OPTIONS_PAGE);
		die();
	}

	// Auxilliary functions.

	function update_message($message) {
	  ?><div class="updated fade"><p><?php echo $message ?></p></div><?php
		return true;
	}

	function wp_head() {
		$images = get_option('rawker_images');
		$options = get_option('rawker_options');
		if (count($images)) {
		  if (($options['last_changed'] != ($today = date('Y-m-d')))) {
				$options['last_changed'] = $today;
				$options['banner_cached'] = array_rand($images);
				update_option('rawker_options', $options);
  		}
			$this->group = $images[$options['banner_cached']]['group'];
			?><style type="text/css">#<?= $options['header_id'] ?> {background-image:url(<?= $images[$options['banner_cached']]['img'] ?>);}</style><?php
		}
	}
}

$rawker = new Rawker();

function rawker_headline() {
	global $gengo, $wpdb, $rawker;
	
	if (!$gengo || !$rawker->group) return false;
	$language_id = $gengo->language_preference_id[0];
	if ($post_data = $wpdb->get_row("SELECT p.ID, p.post_title FROM $wpdb->posts AS p INNER JOIN $gengo->post2lang_table AS p2l ON p.ID = p2l.post_id WHERE p2l.translation_group = $rawker->group AND p2l.language_id = $language_id LIMIT 1")) {
		echo '<a href="' . get_permalink($post_data->ID) . '">' . $post_data->post_title . '</a>';
	}
}
?>