<?php

	if(!defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) {
		exit();
	}

	delete_option('foursquare_local');
	delete_option('foursquare_local_last_update');
	delete_option('foursquare_local_content');
?>
