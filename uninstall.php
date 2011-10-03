<?php
	// If uninstalled not called from Wordpress exit
	if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
		exit ();
	
	delete_option( 'bvdn_looptijden_options' );

	//wp_cache_delete ( );
?>