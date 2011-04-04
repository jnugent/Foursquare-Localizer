<?php
/*
Plugin Name: FourSquare Localizer
Plugin URI: http://vegangeek.ca
Description: Extracts the most recent place you've been in Foursquare and displays the location
Version: 0.5
Author: Jason Nugent
Author URI: http://vegangeek.ca
License: GPL2
*/
/*  Copyright 2010  Jason Nugent  (email : jason.nugent@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('admin_init', 'foursquare_local_options_init');
add_action('admin_menu', 'foursquare_local_options_add_page');

if (!function_exists('foursquare_local_options_init')) {
	function foursquare_local_options_init() {
		register_setting('foursquare_local_options', 'foursquare_local');
	}
}

if (!function_exists('foursquare_local_options_add_page')) {
	function foursquare_local_options_add_page() {
		add_options_page('Foursquare Local Options', 'Foursquare Options', 'manage_options', 'foursquare_local_options', 'foursquare_local_options_do_page');
	}
}

if (!function_exists('foursquare_local_options_do_page')) {
	function foursquare_local_options_do_page() {

		$options = get_option('foursquare_local');

		$clientID = $options['clientid'];
		$clientSecret = $options['clientsecret'];
		$redirectURL = urlencode($options['redirecturl']);

		$code = $_GET['code'];
		if ($code != '') {
			$contents = file_get_contents("https://foursquare.com/oauth2/access_token?client_id=" . $clientID . "&client_secret=" . $clientSecret .
			"&grant_type=authorization_code&redirect_uri=" . $redirectURL . "&code=" . urlencode($code));

			$options[token] = json_decode($contents);
			update_option('foursquare_local', $options);
		}

	?>
		<div class="wrap">
			<h2>FourSquare Localizer Options</h2>

			<p>You'll need to log into Foursquare and register this site as an application.  I don't want to keep track of everyone's client IDs or their client secret. You
			can find out how to do all of that <a href="https://foursquare.com/oauth/">from FourSquare</a>.</p>
			<form method="post" action="options.php">
			<?php settings_fields('foursquare_local_options'); ?>
			<table class="form-table" border="1">
				<tr valign="top">
					<th scope="row">Your Foursquare Client ID:</th>
					<td><input name="foursquare_local[clientid]" type="text" value="<?php echo htmlentities2($options['clientid']); ?>" size="50" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Your Foursquare Client Secret:</th>
					<td><input name="foursquare_local[clientsecret]" type="text" value="<?php echo htmlentities2($options['clientsecret']); ?>" size="50" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Your Foursquare Redirect URL (usually this page):</th>
					<td><input name="foursquare_local[redirecturl]" type="text" value="<?php echo htmlentities2($options['redirecturl']); ?>" size="50" /></td>
				</tr>
				<tr valign="top"><th scope="row">Your Access OAuth2 Token for Foursquare:</th>
					<td>
						<?php if ($options['token'] == ''): ?>
							You don't have an OAuth2 token for Foursquare set up yet. Please
							<a href="https://foursquare.com/oauth2/authenticate?client_id=<?php echo $clientID; ?>&response_type=code&redirect_uri=<?php echo urlencode($redirectURL); ?>">authorize one</a>.
						<?php else: ?>
							<?php echo htmlentities2($options['token']->access_token); ?> <br /> (If you want to cancel this, log into <a href="http://foursquare.com/settings">Foursquare</a> and do so)
						<?php endif; ?>
					</td>
				</tr>
			</table>
			<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
		</div>
	<?php
	}
}

if (!function_exists('foursquare_local_get_code')) {
	function foursquare_local_get_code() {
		$options = get_option('foursquare_local');
		if ($options['token']->access_token != '') {
			echo '<span id="foursquare_local">';
			$curr_time = time();

			$cached_content = get_option('foursquare_local_content');
			$last_update = get_option('foursquare_local_last_update');
			$last_update = intval($last_update) != 0 ? $last_update : $curr_time ;
			if ($curr_time - $last_update < 600 && $cached_content != '') {
				echo $cached_content;
			} else {

				if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
					$foursquareUrl = 'https://api.foursquare.com/v2/users/self/checkins?limit=1&oauth_token=' . urlencode($options['token']->access_token);
					$feed_contents = wp_remote_fopen($foursquareUrl);
					$venue_array = json_decode($feed_contents, TRUE);
					$city = $venue_array['response']['checkins']['items']['0']['venue']['location']['city'];
					$state = $venue_array['response']['checkins']['items']['0']['venue']['location']['state'];
					$date = $venue_array['response']['checkins']['items']['0']['createdAt'];
					$content .=  $city . ", " . $state . " on " . date('F jS, Y', $date) . '<br />';
					echo $content;
					echo '</span>';
					update_option('foursquare_local_content', $content);
					update_option('foursquare_local_last_update', time());
				}
			}
		}
	}
}
?>