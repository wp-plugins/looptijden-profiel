<?php
/*
Plugin Name: Looptijden.nl
Plugin URI: http://www.looptijden.nl/hardlopen/wordpress-plugin
Description: Geef je eigen looptijden of wel je hardlooptijden weer op je eigen persoonlijke website
Version: 0.1
Author: Bjorn van der Neut
Author URI: http://plugins.vanderneut.nu/
License: GPLv2
*/

/*  Copyright 2011  Bjorn van der Neut  (email : bjorn@looptijden.nl)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// To support multilangues
load_plugin_textdomain( 'looptijden-profiel', false,  dirname( plugin_basename(__FILE__) ) . '/languages' );

// To retreive JSON strings from external website
//require_once(ABSPATH."/wp-includes/wp-includes/class-json.php");

// Hook when activating the plugin
register_activation_hook( __FILE__, 'bvdn_looptijden_install' );

// What to do when installing the plugin
function bvdn_looptijden_install( ) {
	// Check if wordpress version is high enough
    if ( version_compare( get_bloginfo( 'version' ), '3.1', '<' ) ) {
        deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
    }
}

register_activation_hook( __FILE__, 'bvdn_looptijden_add_defaults' );

// Register with hook 'wp_print_styles' the css file
add_action( 'wp_print_styles', 'bvdn_looptijden_styles' );

//Enqueue style-file, if it exists.
function bvdn_looptijden_styles() {
	$options = get_option( 'bvdn_looptijden_options' );
	
	// Dont load stylesheets if user disabled them in the settings page
	if( !isset ( $options[ 'chkbox_eigen_stylesheet' ] ) ||  $options[ 'chkbox_eigen_stylesheet' ] != 'on' ) {		
		$styleSheetFile = WP_PLUGIN_DIR . '/looptijden-profiel/css/bvdn-looptijden-style.css';
		$myStyleUrl = plugins_url( 'css/bvdn-looptijden-style.css', __FILE__ ); // Respects SSL, Style.css is relative to the current file

		if ( file_exists( $styleSheetFile ) ) {
			wp_register_style( 'bvdn_looptijden_styles', $myStyleUrl );
			wp_enqueue_style( 'bvdn_looptijden_styles' );
		}
	}
}

// Define default option settings
function bvdn_looptijden_add_defaults() {
	$options = get_option( 'bvdn_looptijden_options' );
    if ( !is_array( $options ) )  {
		$options = array( "chkbox_toon_looptijden" => "on", "chkbox_eigen_stylesheet" => "", "chkbox_persoonlijke_records" => "on", "chkbox_algemene_informatie" => "on", "text_aantal_looptijden" => "5", "chkbox_toon_inschrijvingen" => "on", "text_aantal_inschrijvingen" => "3" );
		update_option( 'bvdn_looptijden_options', $options );
	}
}

// Add admin menu action
add_action( 'admin_menu', 'bvdn_looptijden_create_menu' );

// Add admin menu's
function bvdn_looptijden_create_menu() {
	add_menu_page (
		__( 'Over Looptijden.nl', 'looptijden-profiel' ),
		__( 'Looptijden.nl', 'looptijden-profiel' ),
		'manage_options',
		__FILE__,
		'bvdn_looptijden_over',
		plugins_url( '/images/looptijden-icon.png', __FILE__ )
	);	

	add_submenu_page (
		__FILE__,
		__( 'Looptijden.nl instellingen', 'looptijden-profiel' ),
		__( 'Instellingen', 'looptijden-profiel' ), 
		'manage_options',
		__FILE__.'_instellingen',
		'bvdn_looptijden_instellingen_page'
	);

	add_submenu_page (
		__FILE__,
		__( 'Looptijden.nl hulp pagina', 'looptijden-profiel' ),
		'Hulp', 
		'manage_options',
		__FILE__.'_hulp',
		'bvdn_looptijden_hulp_page'
	);	
}

// Settings page
function bvdn_looptijden_instellingen_page() {
?>	

	<div class="wrap">
	
		<a href="http://www.looptijden.nl/" target="_blank">
			<div class="icon32" style="background: url(<?php echo plugins_url( '/images/looptijden-groot-icon.png', __FILE__ ) ?>) no-repeat scroll 0% 0% transparent;"></div>
		</a>
		
		<h2><?php _e( 'Looptijden.nl instellingen', 'looptijden-profiel' ); ?></h2>
	
		<form action="options.php" method="post">
			<?php settings_fields( 'bvdn_looptijden_options' ); ?>
			<?php do_settings_sections( __FILE__ ); ?>
			<input name="Submit" type="submit" value="<?php _e( 'Opslaan', 'looptijden-profiel' ); ?>" />
		</form>

	</div> <!-- /class="wrap" -->
<?php
}

// Register and define the settings
add_action( 'admin_init', 'bvdn_looptijden_admin_init' );

// Define what settings, sections and field we have on the settings page
function bvdn_looptijden_admin_init() {
    register_setting(
      'bvdn_looptijden_options', // settings group
      'bvdn_looptijden_options', // settings
      'bvdn_looptijden_validate_options' // fn to validate input
      );

	add_settings_section( 'bvdn_looptijden_main_section', __( 'Algemene instellingen', 'looptijden-profiel' ), 'bvdn_looptijden_section_algemeen', __FILE__ );
	add_settings_field( 'bvdn_looptijden_stylesheet',  __( 'Gebruik eigen stylesheets', 'looptijden-profiel' ), 'bvdn_looptijden_checkbox_eigen_stylesheet', __FILE__, 'bvdn_looptijden_main_section' );
	add_settings_field( 'bvdn_looptijden_algemene_informatie',  __( 'Toon algemene informatie', 'looptijden-profiel' ), 'bvdn_looptijden_checkbox_algemene_informatie', __FILE__, 'bvdn_looptijden_main_section' );
	
	add_settings_section( 'bvdn_looptijden_record_section', __( 'Persoonlijke records instellingen', 'looptijden-profiel' ), 'bvdn_looptijden_section_records', __FILE__ );
	add_settings_field( 'bvdn_looptijden_persoonlijke_records',  __( 'Toon persoonlijke records', 'looptijden-profiel' ), 'bvdn_looptijden_checkbox_persoonlijke_records', __FILE__, 'bvdn_looptijden_record_section' );

	add_settings_section( 'bvdn_looptijden_times_section', __( 'Wedstrijden instellingen', 'looptijden-profiel' ), 'bvdn_looptijden_section_times', __FILE__ );
	add_settings_field( 'bvdn_looptijden_looptijden',  __( 'Toon looptijden', 'looptijden-profiel' ), 'bvdn_looptijden_checkbox_looptijden', __FILE__, 'bvdn_looptijden_times_section' );
	add_settings_field( 'bvdn_looptijden_amount_records', __( 'Toon aantal looptijden (max 10)', 'looptijden-profiel' ), 'bvdn_looptijden_string_aantal_looptijden', __FILE__, 'bvdn_looptijden_times_section' );

	add_settings_section( 'bvdn_looptijden_inschrijvingen_section', __( 'Geplande wedstrijden instellingen', 'looptijden-profiel' ), 'bvdn_looptijden_section_inschrijvingen', __FILE__ );
	add_settings_field( 'bvdn_looptijden_inschrijvingen',  __( 'Toon geplande wedstrijden', 'looptijden-profiel' ), 'bvdn_looptijden_checkbox_inschrijvingen', __FILE__, 'bvdn_looptijden_inschrijvingen_section' );
	add_settings_field( 'bvdn_looptijden_amount_inschrijvingen', __( 'Toon aantal geplande wedstrijden (max 10)', 'looptijden-profiel' ), 'bvdn_looptijden_string_aantal_inschrijvingen', __FILE__, 'bvdn_looptijden_inschrijvingen_section' );
}

// Define all possible options
function bvdn_looptijden_checkbox_eigen_stylesheet() { 
	$options = get_option( 'bvdn_looptijden_options' );	
	$checked = "";

	if( $options[ 'chkbox_eigen_stylesheet' ] ) { $checked = ' checked="checked" '; }
	echo "<input ". $checked ." id='chkbox_eigen_stylesheet' name='bvdn_looptijden_options[chkbox_eigen_stylesheet]' type='checkbox' />";
 }

 function bvdn_looptijden_checkbox_persoonlijke_records() { 
	$options = get_option( 'bvdn_looptijden_options' );
	$checked = "";

	if( $options[ 'chkbox_persoonlijke_records' ]) { $checked = ' checked="checked" '; }
	echo "<input ". $checked ." id='chkbox_persoonlijke_records' name='bvdn_looptijden_options[chkbox_persoonlijke_records]' type='checkbox' />";
 }

 function bvdn_looptijden_checkbox_looptijden() { 
	$options = get_option( 'bvdn_looptijden_options' );
	$checked = "";

	if( $options[ 'chkbox_toon_looptijden' ]) { $checked = ' checked="checked" '; }
	echo "<input ". $checked ." id='chkbox_toon_looptijden' name='bvdn_looptijden_options[chkbox_toon_looptijden]' type='checkbox' />";
 }

function bvdn_looptijden_checkbox_algemene_informatie() { 
	$options = get_option( 'bvdn_looptijden_options' );
	$checked = "";

	if( $options[ 'chkbox_algemene_informatie' ] ) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='chkbox_algemene_informatie' name='bvdn_looptijden_options[chkbox_algemene_informatie]' type='checkbox' />";
 }

function bvdn_looptijden_string_aantal_looptijden() {
	$options = get_option( 'bvdn_looptijden_options' );
	$value = "";
	if( !empty ( $options[ 'text_aantal_looptijden' ] ) ) $value = $options[ 'text_aantal_looptijden' ];

	echo "<input id='plugin_text_string' name='bvdn_looptijden_options[text_aantal_looptijden]' size='40' type='text' value='". $value ."' />";
}

function bvdn_looptijden_checkbox_inschrijvingen() { 
	$options = get_option( 'bvdn_looptijden_options' );
	$checked = "";

	if( !empty( $options[ 'chkbox_toon_inschrijvingen' ] ) && $options[ 'chkbox_toon_inschrijvingen' ] ) { $checked = 'checked="checked"'; }
	echo "<input ". $checked ." id='chkbox_toon_inschrijvingen' name='bvdn_looptijden_options[chkbox_toon_inschrijvingen]' type='checkbox' />";
 }

 function bvdn_looptijden_string_aantal_inschrijvingen() {
	$options = get_option( 'bvdn_looptijden_options' );
	$value = "";

	if( !empty ( $options[ 'text_aantal_inschrijvingen' ] ) ) $value = $options[ 'text_aantal_inschrijvingen' ];
	echo "<input id='plugin_text_string' name='bvdn_looptijden_options[text_aantal_inschrijvingen]' size='40' type='text' value='". $value ."' />";
}

 function bvdn_looptijden_validate_options( $input ) {
	// Check our textbox option field contains no HTML tags - if so strip them out
	$input[ 'text_string' ] =  wp_filter_nohtml_kses( $input[ 'text_string' ] );	
	return $input; // return validated input
}		

// Draw the section header 'Over all'
function bvdn_looptijden_section_algemeen() {
	echo __( '<p>Hier onder kunt u alle algemene instellingen aanpassen van de Looptijden.nl plugin.</p>', 'looptijden-profiel' );
}

// Draw the section header 'Personal records'
function bvdn_looptijden_section_records() {
	echo __( '<p>Hier onder kunt u alle record (pr\'s) instellingen aanpassen van de Looptijden.nl plugin.</p>', 'looptijden-profiel' );
}

// Draw the section header 'Times'
function bvdn_looptijden_section_times() {
	echo __( '<p>Hier onder kunt u alle looptijden instellingen aanpassen van de Looptijden.nl plugin.</p>', 'looptijden-profiel' );
}

// Draw the section header 'Planed'
function bvdn_looptijden_section_inschrijvingen() {
	echo __( '<p>Hier onder kunt u alle geplande wedstrijden aanpassen van de Looptijden.nl plugin.</p>', 'looptijden-profiel' );
}

// Add dashboard setup
add_action( 'wp_dashboard_setup', 'bvdn_looptijden_dashboard_widget' );

// Register the dashboard widget
function bvdn_looptijden_dashboard_widget() {
	wp_add_dashboard_widget( 'dashboard_custom_feed', __( 'Looptijden.nl berichten', 'looptijden-profiel' ), 'bvdn_looptijden_dashboard' );
}

// Generate the dashboard widget with the RSS Feed content
function bvdn_looptijden_dashboard() {
	$bvdn_looptijden_feed = 'http://www.looptijden.nl/rss';
	
	$content = '';
	$content .= '<div class="rss-widget">';	
	
	$content = wp_widget_rss_output(
		array(
			'url' => $bvdn_looptijden_feed,
			'title' => __( 'Looptijden.nl nieuws', 'looptijden-profiel' ),
			'items' => 3,
			'show_summary' => 1,
			'show_author' => 0,
			'show_date' => 1
		)
	);
	
	$content .= '</div>';

	return $content;
}

// Help page
function bvdn_looptijden_hulp_page () {
	$content = '';

	$content .= '<div class="wrap">';
	
	$content .= '<a href="http://www.looptijden.nl/" target="_blank">';
	$content .= 	'<div class="icon32" style="background: url( '. plugins_url( '/images/looptijden-groot-icon.png', __FILE__ ) .' ) no-repeat scroll 0% 0% transparent;"><br></div>';
	$content .= '</a>';

	$content .= '	<h2>'. __( 'Hulp nodig', 'looptijden-profiel' ) .'</h2>';

	$content .= '	<table class="widefat">';
	$content .= '	<thead>';
	$content .= '		<tr>';
	$content .= '			<th>'. __( 'Hulp nodig', 'looptijden-profiel' ) .'</th>';
	$content .= '		</tr>';
	$content .= '	</thead>';
	$content .= '	<tbody>';
	$content .= '		<tr>';
	$content .= '			<td>';
	$content .= '				<p>'. __( '<b>Hoe toon ik de Looptijden.nl plugin op mijn website?</b><br />Je kan de plugin als widget gebruiken. Ga daarvoor naar het <a href="widgets.php">widget</a> onderdeel van Wordpress. Hier kan je de Looptijden.nl widget slepen naar een lokatie waar je de widget wilt tonen. Je kan vervolgens per widget opgeven welk profiel deze moet tonen doormiddel van het invoeren van de juiste profielcode.', 'looptijden-profiel' ). '</p>';
	$content .= '			</td>';
	$content .= '		</tr>';
	$content .= '		<tr>';
	$content .= '			<td>';
	$content .= '				<p>'. __( '<b>Waar kan ik mijn profielcode vinden?</b><br />Login op <a href="http://www.looptijden.nl/community/editprofiel" target="_blank">Looptijden.nl</a> en onder "wijzig je profiel" staat aan de onderkant je "unieke profielcode" vermeld.', 'looptijden-profiel' ). '</p>';
	$content .= '			</td>';
	$content .= '		</tr>';
	$content .= '		<tr>';
	$content .= '			<td>';
	$content .= '				<p>'. __( '<b>Waar kan in mijn instellingen aanpassen?</b><br />Als je gaat naar de <a href="admin.php?page=looptijden-profiel/looptijden-profiel.php_instellingen">Looptijden instellingen</a> zie je een overzicht van alle instelbare mogelijkheden van de Looptijden.nl plugin.', 'looptijden-profiel' ). '</p>';
	$content .= '			</td>';
	$content .= '		</tr>';
	$content .= '		<tr>';
	$content .= '			<td>';
	$content .= '				<p>'. __( '<b>Hoeveel profielen/widgets kan ik tegelijk tonen?</b><br />Je kan een onbeperkt aantal widgets tegelijk tonen hier zit geen maximum aanvast.', 'looptijden-profiel' ). '</p>';
	$content .= '			</td>';
	$content .= '		</tr>';
	$content .= '		<tr>';
	$content .= '			<td>';
	$content .= '				<p>'. __( '<b>Ik wil de informatie tonen op een pagina of in een nieuwsitem is dat mogelijk?</b><br />Ja door gebruik te maken van zogeheten shortcuts. Deze ziet er als volgt uit [looptijden code="xxxxxxxx"] waarbij je de xxxxxx moet vervangen voor je profiel code van looptijden. Deze shotcut kan je zowel op de pagina als in een nieuwsitem plaatsen.', 'looptijden-profiel' ). '</p>';
	$content .= '			</td>';
	$content .= '		</tr>';
	$content .= '	</tbody>';
	$content .= '	</table>';

	$content .= '</div>'; //end wrap

	echo $content;
}

// About Looptijden.nl page
function bvdn_looptijden_over() {
	$content = '';

	$content .= '<div class="wrap">';
	
	$content .= '<a href="http://www.looptijden.nl/" target="_blank">';
	$content .= 	'<div class="icon32" style="background: url( '. plugins_url( '/images/looptijden-groot-icon.png', __FILE__ ) .' ) no-repeat scroll 0% 0% transparent;"><br></div>';
	$content .= '</a>';

	$content .= '	<h2>'. __( 'Over Looptijden.nl', 'looptijden-profiel' ) .'</h2>';

	$content .= '	<table class="widefat">';
	$content .= '	<thead>';
	$content .= '		<tr>';
	$content .= '			<th>'. __( 'Over Looptijden.nl', 'looptijden-profiel' ) .'</th>';
	$content .= '		</tr>';
	$content .= '	</thead>';
	$content .= '	<tbody>';
	$content .= '		<tr>';
	$content .= '			<td>';
	$content .= '				<p>'. __( '<a href="http://www.looptijden.nl/" taret="_blank">Looptijden.nl</a> is een website gemaakt als hulpmiddel voor hardlopers om makkelijk hun hardloop prestaties bij te houden. De site is ontstaan als een uit de hand gelopen hobby met als doel om het voor hardlopers makkelijk te maken hun prestaties bij te houden. Een soortgelijke website was - en is - nog niet in het Nederlands aanwezig en hopelijk helpt deze site om je plezier en motivatie in hardlopen te vergroten!<br /><br />Looptijden.nl is in december 2008 online gegaan en is sindsdien in steeds sneller tempo gegroeid. Na het eerste jaar waren en al ruim duizend gebruikers actief op de site en met de introductie van de iPhone app is dat verder gegroeid tot dertienduizend in december 2010. Ook het aantal gebruikers welke dagelijks op de site actief is, is flink meegegroeid. Met enkele duizenden unieke bezoekers per dag behoord Looptijden.nl nu tot de grotere hardloopsites van Nederland.<br /><br />Niet alleen is het gebruik van Looptijden.nl geheel gratis, er is ook een minimale hoeveelheid advertenties zichtbaar op de site. Het bijhouden van je hardlooptijden moet namelijk wel leuk blijven! Ook is de <a href="http://www.looptijden.nl/hardlopen/iphone" target="_blank">iPhone app</a> en de <a href="http://www.looptijden.nl/hardlopen/android" target="_blank">Android app</a> geheel gratis te downloaden en te gebruiken.', 'looptijden-profiel' ). '</p>';
	$content .= '			</td>';
	$content .= '		</tr>';
	$content .= '	</tbody>';
	$content .= '	</table>';

	$content .= '</div>'; //end wrap

	echo $content;
}

// Initialisate the widget
add_action( 'widgets_init', 'bvdn_looptijden_registratie_widget' );

// Register the looptijden widget
function bvdn_looptijden_registratie_widget() {
	register_widget( 'bvdn_looptijden_widget' );
}

// The Looptijden widget class
class bvdn_looptijden_widget extends WP_Widget {
	// Widget description
	function bvdn_looptijden_widget() {
		$widget_ops = array(
			'classname' => 'looptijdenWidget',
			'description' => __( 'Toon de looptijden.nl profiel informatie in een widget', 'looptijden-profiel' )
		);

		$this->WP_Widget( 'bvdn_looptijden_widget', __( 'Looptijden.nl Profiel Widget', 'looptijden-profiel' ), $widget_ops );
	}
	
	// Generate the Widget admin settings
	function form( $instance ) {				
		$defaults = array( 'title' => __( 'Mijn Looptijden.nl profiel', 'looptijden-profiel' ), 'guid' =>  '', 'error' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		$title = $instance[ 'title' ];
		$guid = $instance[ 'guid' ];

		?>
		<p><?php _e( 'Titel:', 'looptijden-profiel' ); ?> <input class="wideflat" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ) ?>"></p>
		<p><?php _e( 'Profielcode:', 'looptijden-profiel' ); ?> <input class="wideflat" name="<?php echo $this->get_field_name( 'guid' ); ?>" type="text" value="<?php echo esc_attr( $guid ) ?>"><br /><a href="admin.php?page=looptijden-profiel/looptijden-profiel.php_hulp"><?php _e( 'Waar vind ik mijn profielcode?', 'looptijden-profiel' );?></a></p>
		<?php

		if( !empty( $instance[ 'error' ] ) ) {
			echo $instance[ 'error' ];
		}
	}
	
	// Update the widget settings
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
		$bvnd_looptijden_guid = strip_tags( $new_instance[ 'guid' ] );

		if( bvdn_validate_guid( $bvnd_looptijden_guid ) === true ) {
			$instance[ 'guid' ] = $bvnd_looptijden_guid;
			$instance[ 'error' ] = '';
		} else {
			$instance[ 'error' ] = '<div id="message" class="error">'. __( 'Ongeldige profielcode ingevoerd!', 'looptijden-profiel' ) .'</div>';
		}		

		return $instance;
	}
	
	// Generate the widget website layout
	function widget( $args, $instance ) {
		if( !empty ( $instance[ 'guid' ] ) ) {
			extract( $args);		
			
			$content = '';
			$content .= $before_widget;
			
			// Set the widget title
			$title = apply_filters( 'widget_title', $instance[ 'title' ] );		
			if( !empty( $title ) ) {
				$content .= $before_title . $title . $after_title;
			}	
			
			$content .= bvdn_build_looptijden_view( $instance[ 'guid' ], true );
			
			$content .= $after_widget;		

			echo $content;
		}
	}
}

// Generate the looptijden profile content
function bvdn_build_looptijden_view ( $guid ) {	
	// Check if not allready in cache	
	$cache = wp_cache_get( $guid, 'bvdn_looptijden_cached_data' );	

	if( empty( $cache ) ) {		
		// Get the data from external website
		$data = bvdn_get_looptijden_data( $guid );
		
		// If data is not empy build content
		if( !empty( $data ) ) {
			$options = get_option( 'bvdn_looptijden_options' );

			$cache = '';
	
			/***
			* Show Overall content
			***/

			$cache .= '<div class="looptijdenWidget">';

			if( isset( $options[ 'chkbox_algemene_informatie' ] ) && $options[ 'chkbox_algemene_informatie' ] == 'on' ) {
				$cache .= '<div class="looptijdenAlgemeen">';

				$name = !empty( $data[ 'Samenvatting' ][ 'Naam' ] ) ? $data[ 'Samenvatting' ][ 'Naam' ] : $data[ 'Samenvatting' ][ 'Username' ];
				$cache .= '	<h2><a href=" '. $data[ 'Samenvatting' ][ 'ProfielUrl' ] .' " target="_blank">'. $name .'</a></h2>';
				
				$cache .= '	<div class="looptijdenSamenvatting">';
				$cache .= '		<div class="looptijdenLabel">'. __( 'Aantal tijden:', 'looptijden-profiel' ).'</div>';
				$cache .= '		<div class="looptijdenValue">'. $data[ 'Samenvatting' ][ 'AantalTijden' ] .'</div>';
				$cache .= '		<div class="looptijdenLabel">'. __( 'Aantal km\'s:', 'looptijden-profiel' ).'</div>';
				$cache .= '		<div class="looptijdenValue">'. $data[ 'Samenvatting' ][ 'AantalKilometers' ] .'</div>';
				$cache .= '	</div>';

				$cache .= '</div>'; // End div class="looptijdenAlgemeen"
			}

			/***
			* Show personal records
			***/
			if( isset( $options[ 'chkbox_persoonlijke_records' ] ) && $options[ 'chkbox_persoonlijke_records' ] == 'on' ) {

				$cache .= '<div class="looptijdenRecords">';
				$cache .= '	<h3>'. __( 'Pr\'s:', 'looptijden-profiel' ) .'</h3>';				

				foreach( $data[ 'Records' ] as $records) {
					$cache .= '	<div class="looptijdenPR">';

					$cache .= '<div class="looptijdenField fieldAfstand">';
					$cache .= '	<div class="looptijdenLabel">'. __( 'Afstand', 'looptijden-profiel' ) .'</div>';
					$cache .= '	<div class="looptijdenValue">'. $records[ 'Afstand' ].'</div>';	
					$cache .= '</div>';
					
					$cache .= '<div class="looptijdenField fieldTijd">';
					$cache .= '	<div class="looptijdenLabel">'. __( 'Datum', 'looptijden-profiel' ) .'</div>';
					$cache .= '	<div class="looptijdenValue">'. $records[ 'Datum' ].'</div>';					
					$cache .= '</div>';

					$cache .= '<div class="looptijdenField fieldDatum">';
					$cache .= '	<div class="looptijdenLabel">'. __( 'Tijd', 'looptijden-profiel' ) .'</div>';
					$cache .= '	<div class="looptijdenValue">'. $records[ 'Tijd' ].'</div>';
					$cache .= '</div>';

					$cache .= '	</div>';
				}
				
				$cache .= '</div>'; // End div class="looptijdenRecords"
			}
			
			/***
			* Show times
			***/
			if( isset( $options[ 'chkbox_toon_looptijden' ] ) && $options[ 'chkbox_toon_looptijden' ] == 'on' ) {
				$cache .= '<div class="looptijdenTijden">';
				$cache .= '	<h3>'. __( 'Laatste tijden\'s:', 'looptijden-profiel' ) .'</h3>';				

				$toon_aantal_tijden = ( $options[ 'text_aantal_looptijden' ] > 0 ) ? $options[ 'text_aantal_looptijden' ] : 5;
				$x = 1;

				foreach( $data[ 'Tijden' ] as $tijden ) {
					// Check if we should stop building the content because the user only wants to show x amount of times
					if( $x > $toon_aantal_tijden ) {
						break;
					}

					$cache .= '	<div class="looptijdenTijd">';
					
					// Check for empty values and set &nbsp; else floating will fail
					$hartslagGemiddeld = empty( $tijden[ 'HartslagGemiddeld' ] ) ? "&nbsp;" : $tijden[ 'HartslagGemiddeld' ];
					$HartslagMaximaal = empty( $tijden[ 'HartslagMaximaal' ]) ? "&nbsp;" : $tijden[ 'HartslagMaximaal' ];
					$kCal = empty( $tijden[ 'Kcal' ]) ? "&nbsp;" : $tijden[ 'Kcal' ];
					$snelheid = empty( $tijden[ 'Snelheid' ]) ? "&nbsp;" : $tijden[ 'Snelheid' ];					
					
					// Check if the time is public or private. If private don't add a link to it
					if( $tijden[ 'IsZichtbaar' ] ) {
						$cache .= '<div class="looptijdenValueFullWidth"><a href='. $tijden[ 'Url' ] .' target="_blank">'. $tijden[ 'Titel' ].'</a></div>';
					} else {
						$cache .= '<div class="looptijdenValueFullWidth">'. $tijden[ 'Titel' ].'</div>';
					}
					
					$cache .= '<div class="looptijdenField fieldAfstand">';
					$cache .= '	<div class="looptijdenLabel">'. __( 'Afstand', 'looptijden-profiel' ) .'</div>';
					$cache .= '	<div class="looptijdenValue">'. $tijden[ 'Afstand' ].'</div>';
					$cache .= '</div>';
					
					$cache .= '<div class="looptijdenField fieldDatum">';
					$cache .= '	<div class="looptijdenLabel">'. __( 'Datum', 'looptijden-profiel' ) .'</div>';
					$cache .= '	<div class="looptijdenValue">'. $tijden[ 'Datum' ].'</div>';				
					$cache .= '</div>';
					
					$cache .= '<div class="looptijdenField fieldTijd">';
					$cache .= '	<div class="looptijdenLabel">'. __( 'Tijd', 'looptijden-profiel' ) .'</div>';
					$cache .= '	<div class="looptijdenValue">'. $tijden[ 'Tijd' ].'</div>';
					$cache .= '</div>';
					
					$cache .= '<div class="looptijdenField fieldSnelheid">';
					$cache .= '	<div class="looptijdenLabel">'. __( 'Snelheid', 'looptijden-profiel' ) .'</div>';
					$cache .= '<div class="looptijdenValue">'. $snelheid .'</div>';
					$cache .= '</div>';
					
					$cache .= '<div class="looptijdenField fieldCalorieen">';
					$cache .= '<div class="looptijdenLabel">'. __( 'Calorieen', 'looptijden-profiel' ) .'</div>';
					$cache .= '<div class="looptijdenValue">'. $kCal .'</div>';
					$cache .= '</div>';
					
					$cache .= '<div class="looptijdenField fieldHRGem">';
					$cache .= '<div class="looptijdenLabel">'. __( 'Hartslag (gem.)', 'looptijden-profiel' ) .'</div>';
					$cache .= '<div class="looptijdenValue">'. $hartslagGemiddeld .'</div>';
					$cache .= '</div>';
					
					$cache .= '<div class="looptijdenField fieldHRMax">';
					$cache .= '<div class="looptijdenLabel">'. __( 'Hartslag (max.)', 'looptijden-profiel' ) .'</div>';
					$cache .= '<div class="looptijdenValue">'. $HartslagMaximaal .'</div>';
					$cache .= '</div>';
					
					$cache .= '	</div>';

					$x++;
				}
				
				$cache .= '</div>'; // End div class="looptijdenTijden"
			}
			
			/***
			* Show planed
			***/
			if( isset( $options[ 'chkbox_toon_inschrijvingen' ] ) && $options[ 'chkbox_toon_inschrijvingen' ] == 'on' ) {
				$cache .= '<div class="looptijdenInschrijvingen">';
				$cache .= '	<h3>'. __( 'Geplande wedstrijden:', 'looptijden-profiel' ) .'</h3>';				

				$toon_aantal_inschrijvingen = ( $options[ 'text_aantal_inschrijvingen' ] > 0 ) ? $options[ 'text_aantal_inschrijvingen' ] : 5;
				$x = 1;

				if( count( $data[ 'Inschrijvingen' ] ) > 0 ) {
					foreach( $data[ 'Inschrijvingen' ] as $inschrijvingen ) {
						// Check if we should stop building the content because the user only wants to show x amount of planned matches
						if( $x > $toon_aantal_inschrijvingen ) {
							break;
						}

						$cache .= '<div class="looptijdenInschrijvingen">';	
						$cache .= '	<div class="looptijdenValueFullWidth">'. $inschrijvingen[ 'Titel' ] .'</div>';
						$cache .= '	<div class="looptijdenField fieldDatum">';
						$cache .= '		<div class="looptijdenLabel">'. __( 'Datum', 'looptijden-profiel' ) .'</div>';
						$cache .= '		<div class="looptijdenValue">'. $inschrijvingen[ 'Datum' ] .'</div>';
						$cache .= '	</div>';
						$cache .= '</div>';

						$x++;
					}
				} else {
					$cache .= '<div class="looptijdenInschrijvingen">';
					$cache .= '	<div class="looptijdenValueFullWidth">'. __( 'Geen geplande wedstrijden gevonden', 'looptijden-profiel' ).'</div>';
					$cache .= '</div>';
				}
				
				$cache .= '</div>'; // End div class="looptijdenInschrijvingen"
			}
			
			// Show footer image
			$cache .= '<div class="looptijdenLogo"><a href="http://www.looptijden.nl/" target="_blank"><img src='. plugins_url( '/images/LogoMiniTransparentLooptijden.png', __FILE__ ) .' alt="Looptijden.nl" /></a></div>';

			$cache .= '</div>'; // End div class="looptijdenWidget"
			
			// Store content into the cache
			wp_cache_set( $guid, $cache, 'bvdn_looptijden_cached_data', 43200 );			
		}
	}

	return $cache;
}

// Get the data according the GUID
function bvdn_get_looptijden_data( $guid ) {
	// Set default error message if something goes wrong
	$data = __( 'Fout in het ophalen van de data. Controleer of de opgegeven profielcode correct is', 'looptijden-profiel' );

	// check if guid is set
	if( !empty ( $guid ) ) {		
		// Make sure its a safe url
		$url = esc_url( 'https://www.looptijden.nl/api/json/StatistiekenVoorGebruiker?userGuid='. trim( $guid ) );

		// GET JSON content
		$json_data = wp_remote_request( $url );

		if( !is_wp_error( $json_data ) ) {
			// Get body data
			$json_data = wp_remote_retrieve_body( $json_data );

			// Decode the JSON content to an array
			$data = json_decode( $json_data, true );
		}
	}

	return $data;
}

// Function to check if GUID is valid
function bvdn_validate_guid( $guid ) {
	return ( !preg_match( '/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i', $guid )) ? false : true;		
}

// Register the shortcut looptijden
add_shortcode( 'looptijden', 'bvdn_looptijden_shortcut' );

// Make a shortcut [looptijden code="xxxxxxx"] possible
function bvdn_looptijden_shortcut( $attr ) {
	return bvdn_build_looptijden_view( $attr[ 'code' ], false );
}
?>