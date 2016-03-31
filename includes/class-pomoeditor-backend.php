<?php
/**
 * POMOEditor Backend Functionality
 *
 * @package POMOEditor
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace POMOEditor;

/**
 * The Backend Functionality
 *
 * Hooks into various backend systems to load
 * custom assets and add the editor interface.
 *
 * @internal Used by the System.
 *
 * @since 1.0.0
 */
final class Backend extends Handler {
	// =========================
	// ! Hook Registration
	// =========================

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 *
	 * @uses Registry::get() to retrieve enabled post types.
	 */
	public static function register_hooks() {
		// Don't do anything if not in the backend
		if ( ! is_admin() ) {
			return;
		}

		// Setup stuff
		static::add_action( 'plugins_loaded', 'load_textdomain', 10, 0 );

		// Plugin information
		static::add_action( 'in_plugin_update_message-' . plugin_basename( PME_PLUGIN_FILE ), 'update_notice' );

		// Script/Style Enqueues
		static::add_action( 'admin_enqueue_scripts', 'enqueue_assets' );
	}

	// =========================
	// ! Setup Stuff
	// =========================

	/**
	 * Load the text domain.
	 *
	 * @since 1.0.0
	 */
	public static function load_textdomain() {
		// Load the textdomain
		load_plugin_textdomain( 'pomoeditor', false, dirname( PME_PLUGIN_FILE ) . '/languages' );
	}

	// =========================
	// ! Plugin Information
	// =========================

	/**
	 * In case of update, check for notice about the update.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin The information about the plugin and the update.
	 */
	public static function update_notice( $plugin ) {
		// Get the version number that the update is for
		$version = $plugin['new_version'];

		// Check if there's a notice about the update
		$transient = "pomoeditor-update-notice-{$version}";
		$notice = get_transient( $transient );
		if ( $notice === false ) {
			// Hasn't been saved, fetch it from the SVN repo
			$notice = file_get_contents( "http://plugins.svn.wordpress.org/pomoeditor/assets/notice-{$version}.txt" ) ?: '';

			// Save the notice
			set_transient( $transient, $notice, YEAR_IN_SECONDS );
		}

		// Print out the notice if there is one
		if ( $notice ) {
			echo apply_filters( 'the_content', $notice );
		}
	}

	// =========================
	// ! Script/Style Enqueues
	// =========================

	/**
	 * Enqueue necessary styles and scripts.
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_assets(){
		// Only bother if we're viewing the editor screen
		if ( get_current_screen()->id != 'tools_page_pomoeditor' ) {
			return;
		}

		// Interface styling
		wp_enqueue_style( 'pomoeditor-interface', plugins_url( 'css/interface.css', PME_PLUGIN_FILE ), '1.0.0', 'screen' );

		// Interface javascript
		wp_enqueue_script( 'pomoeditor-framework-js', plugins_url( 'js/framework.js', PME_PLUGIN_FILE ), array( 'backbone' ), '1.0.0' );
		wp_enqueue_script( 'pomoeditor-interface-js', plugins_url( 'js/interface.js', PME_PLUGIN_FILE ), array( 'pomoeditor-framework-js' ), '1.0.0' );

		// Localize the javascript
		wp_localize_script( 'pomoeditor-interface-js', 'pomoeditorL10n', array(
			'SourceEditingNotice' => __( 'You should not edit the source text; errors may occur with displaying the translated text if you do.', 'pomoeditor' ),
			'ContextEditingNotice' => __( 'You should not edit the context; errors may occur with displaying the translated text if you do.', 'pomoeditor' ),
			'ConfirmAdvancedEditing' => __( 'Are you sure you want enable advanced editing? You may break some of your translations if you change the source text or context values.', 'pomoeditor' ),
			'ConfirmCancel' => __( 'Are you sure you want to discard your changes?', 'pomoeditor' ),
			'ConfirmDelete' => __( 'Are you sure you want to delete this entry? It cannot be undone.', 'pomoeditor' ),
			'ConfirmSave' => __( 'You have uncommitted translation changes, do you want to discard them before saving?', 'pomoeditor' ),
			'Saving' => __( 'Saving Translations...', 'pomoeditor' ),
		) );
	}
}