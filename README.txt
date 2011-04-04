To install this plugin in your Wordpress theme, just insert the following bit
of code where you'd like the FourSquare code to appear once you've installed 
and enabled the plugin:

<?php 
	if (function_exists('foursquare_local_get_code')) { 
		foursquare_local_get_code(); 
	} 
?>
