<?php
/**
 * Plugin Name: Admin Bar Backend Search
 * Version: 0.1.1
 * Description: Adds a search item which centralize all search types into one search form, which is placed in the new Admin Bar.
 * Author: Dominik Schilling
 * Author URI: http://wphelper.de/
 * Plugin URI: http://wpgrafie.de/wp-plugins/admin-bar-backend-search/en/
 *
 * Text Domain: dsab-backend-search
 * Domain Path: /lang
 *
 * License: GPLv2 or later
 *
 *	Copyright (C) 2011-2012 Dominik Schilling
 *
 *	This program is free software; you can redistribute it and/or
 *	modify it under the terms of the GNU General Public License
 *	as published by the Free Software Foundation; either version 2
 *	of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */


/**
 * Don't call this file directly.
 */
if ( ! class_exists( 'WP' ) ) {
	die();
}

/**
 * The class will centralize all search types into one search form, which is placed in the new Admin Bar.
 */
final class AB_Backend_Search {
	/**
	 * Saves all search types.
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public static $search_types = array();

	/**
	 * Init.
	 *
	 * @since 0.1.0
	 */
	public static function init() {
		// Oh, no Admin Bar, no search
		if( ! is_admin_bar_showing() )
			return;

		// Localize plugin
		self::load_textdomain();

		// Generate search types
		self::$search_types = self::get_search_types();

		if( empty( self::$search_types ) )
			return;

		// Add scripts to queue
		self::enqueue_scripts();

		// Add search menu to admin bar
		add_action( 'admin_bar_menu', array( __CLASS__, 'add_search_form' ), 10 );
	}

	/**
	 * Loads plugin textdomain.
	 *
	 * @since 0.1.0
	 */
	protected static function load_textdomain() {
		return load_plugin_textdomain( 'ab-backend-search', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	}

	/**
	 * Adds script and style to queue.
	 *
	 * @since 0.1.0
	 */
	protected static function enqueue_scripts() {
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.dev' : '';

		wp_enqueue_style(
			'ab-backend-search',
			plugins_url( "css/ab-backend-search$suffix.css", __FILE__ ),
			array( 'admin-bar' ),
			'01122011'
		);

		wp_enqueue_script(
			'ab-backend-search',
			plugins_url( "js/ab-backend-search$suffix.js", __FILE__ ),
			array( 'admin-bar' ),
			'01122011'
		);
	}

	/**
	 * Generates the search types.
	 *
	 * @since 0.1.0
	 *
	 * @return array The list of search types
	 */
	protected static function get_search_types() {
		$types = array();

		if ( ! is_network_admin() ) {
			/* Posts, Pages and Custom Post Types */
			$post_types = get_post_types( array( 'show_ui' => true ), 'objects' );
			if ( ! empty( $post_types ) ) {
				foreach ( $post_types as $post_type => $data ) {
					if ( current_user_can( $data->cap->edit_posts ) )
						$types[ $data->name ] = array(
							'title' => $data->labels->name,
							'url' => admin_url( 'edit.php' ),
							'hidden' => array(
								'post_type' => $data->name
							)
						);
				}
			}

			/* Media */
			if ( current_user_can( 'upload_files' ) )
				$types['media'] = array(
					'title' => __( 'Media', 'ab-backend-search' ),
					'url' => admin_url( 'upload.php' )
				);

			/* Links */
			if ( current_user_can( 'manage_links' ) )
				$types['links'] = array(
					'title' => __( 'Links', 'ab-backend-search' ),
					'url' => admin_url( 'link-manager.php' )
				);

			/* Comments */
			if ( current_user_can( 'edit_posts' ) )
				$types['comments'] = array(
					'title' => __( 'Comments', 'ab-backend-search' ),
					'url' => admin_url( 'edit-comments.php' )
				);

			/* Users */
			if ( current_user_can( 'list_users' ) )
				$types['users'] = array(
					'title' => __( 'Users', 'ab-backend-search' ),
					'url' => admin_url( 'users.php' )
				);

			/* Installed Plugins */
			if ( current_user_can( 'activate_plugins' ) )
				$types['installed_plugins'] = array(
					'title' => __( 'Installed Plugins', 'ab-backend-search' ),
					'url' => admin_url( 'plugins.php' )
				);

			/* Installed Themes */
			if( current_user_can( 'switch_themes' ) && current_user_can( 'edit_theme_options' ) )
				$types['installed_themes'] = array(
					'title' => __( 'Installed Themes', 'ab-backend-search' ),
					'url' => admin_url( 'themes.php' )
				);

			/* New Plugins */
			if ( ! is_multisite() && current_user_can( 'install_plugins' ) )
				$types['new_plugins'] = array(
					'title' => __( 'New Plugins', 'ab-backend-search' ),
					'url' => admin_url( 'plugin-install.php' ),
					'hidden' => array(
						'tab' => 'search',
						'type' => 'term'
					)
				);

			/* New Themes */
			if ( ! is_multisite() && current_user_can( 'install_themes' ) )
				$types['new_themes'] = array(
					'title' => __( 'New Themes', 'ab-backend-search' ),
					'url' => admin_url( 'theme-install.php' ),
					'hidden' => array(
						'tab' => 'search',
						'type' => 'term'
					)
				);
		} else {
			/* Users */
			if ( current_user_can( 'manage_network_users' ) )
				$types['ms_users'] = array(
					'title' => __( 'Users', 'ab-backend-search' ),
					'url' => network_admin_url( 'users.php' )
				);

			/* Sites */
			if ( current_user_can( 'manage_sites' ) )
				$types['ms_sites'] = array(
					'title' => __( 'Sites', 'ab-backend-search' ),
					'url' => network_admin_url( 'sites.php' )
				);

			/* Installed Plugins */
			if ( current_user_can( 'activate_plugins' ) )
				$types['ms_installed_plugins'] = array(
					'title' => __( 'Installed Plugins', 'ab-backend-search' ),
					'url' => network_admin_url( 'plugins.php' )
				);

			/* Installed Themes */
			if ( current_user_can( 'install_themes' ) )
				$types['ms_installed_themes'] = array(
					'title' => __( 'Installed Themes', 'ab-backend-search' ),
					'url' => network_admin_url( 'themes.php' )
				);

			/* New Plugins */
			if ( current_user_can( 'install_plugins' ) )
				$types['ms_new_plugins'] = array(
					'title' => __( 'New Plugins', 'ab-backend-search' ),
					'url' => network_admin_url( 'plugin-install.php' ),
					'hidden' => array(
						'tab' => 'search',
						'type' => 'term'
					)
				);

			/* New Themes */
			if ( current_user_can( 'manage_network_themes' ) )
				$types['ms_new_themes'] = array(
					'title' => __( 'New Themes', 'ab-backend-search' ),
					'url' => network_admin_url( 'theme-install.php' ),
					'hidden' => array(
						'tab' => 'search',
						'type' => 'term'
					)
				);
		}

		if ( ! empty( self::$search_types ) )
			$types = array_merge( $types, (array) self::$search_types );

		return apply_filters( 'ab_backend_search_types', $types );
	}

	/**
	 * Helper function to add hidden fields as data attributes.
	 *
	 * @since 0.1.0
	 *
	 * @param array $fields Hidden fields
	 * @return string HTML data attributes
	 */
	private static function _render_hidden_fields( $fields ) {
		$hidden = '';

		if ( empty( $fields ) )
			return $hidden;

		foreach( $fields as $name => $value )
				$hidden .= sprintf( ' data-%s="%s"', esc_attr( $name ), esc_attr( $value ) );

		return $hidden;
	}

	/**
	 * Renders the search form.
	 *
	 * @since 0.1.1
	 *
	 */
	private static function _render_search_form() {
		$radio = $c_url = $c_hidden =  '';
		$first = true;

		// Build radio and hidden inputs
		foreach( self::$search_types as $type => $data ) {
			$hidden = empty( $data['hidden'] ) ? '' : self::_render_hidden_fields( $data['hidden'] );

			$radio .= sprintf(
				'<label><input type="radio" name="search-type" data-url="%s"%s%s/> %s</label>',
				esc_attr( $data['url'] ),
				$hidden,
				checked( $first, true, false ),
				$data['title']
			);

			if ( $first ) {
				$c_url = $data['url'];

				if ( ! empty( $data['hidden'] ) )
					foreach ( $data['hidden'] as $name => $value )
						$c_hidden .= "<input type='hidden' name='{$name}' value='{$value}'/>";

				$first = false;
			}
		}

		// Build search form
		$form  = '<form action="' . esc_url( $c_url ) . '" method="get" id="adminbarsearch">';
		$form .= '<input class="adminbar-input" autocomplete="off" name="s" id="adminbar-search" tabindex="10" type="text" value="" maxlength="150" />';
		$form .= '<div class="search-arrow" title="' . esc_attr( __( 'Click to choose a search type', 'ab-backend-search' ) ) . '"><span></span></div>';
		$form .= '<div class="search-options">';
		$form .= '<h3>' . __( 'Choose type:', 'ab-backend-search' ) . '</h3>';
		$form .= $radio;
		$form .= '</div>';
		$form .= $c_hidden;
		$form .= '</form>';

		return $form;
	}

	/**
	 * Adds the search form to the Admin Bar.
	 *
	 * @since 0.1.0
	 *
	 * @param array $wp_admin_bar Admin bar object
	 */
	public static function add_search_form( $wp_admin_bar ) {
		// Add form to Admin Bar
		$wp_admin_bar->add_menu( array(
			'parent' => 'top-secondary',
			'id'     => 'search',
			'title'  => self::_render_search_form(),
			'meta'   => array(
				'class'    => 'admin-bar-search hide-if-no-js'
			)
		) );
	}
}

// Please load. Thanks.
add_action( 'admin_init', array( 'AB_Backend_Search', 'init' ), 20 );
