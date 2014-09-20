<?php
/**
 * K2 main class.
 *
 * @package WordPress
 * @subpackage K2
 * @since K2 unknown
 */

// Prevent users from directly loading this class file
defined('K2_CURRENT') or die ( __('Error: This file can not be loaded directly.', 'k2') );

class K2 {

	public function __construct() {
		$this->init();
		add_action( 'after_theme_setup', array( $this, 'init' ), 0 );
	}

	/**
	 * Initializes K2
	 *
	 * @uses do_action() Provides 'k2_init' action
	 */
	function init() {
		global $wp_version;

		// Load required classes and includes
		require_once(TEMPLATEPATH . '/app/includes/info.php');
		require_once(TEMPLATEPATH . '/app/includes/display.php');
		require_once(TEMPLATEPATH . '/app/includes/media.php');
		require_once(TEMPLATEPATH . '/app/includes/widgets.php');
		require_once(TEMPLATEPATH . '/app/includes/pluggable.php');

		//if ( defined('K2_HEADERS') and K2_HEADERS == true )
		//	require_once(TEMPLATEPATH . '/app/classes/header.php');

		// Check installed version, upgrade if needed
		$k2version = get_theme_mod('k2version');

		if ( $k2version === false )
			$this->install();
		elseif ( version_compare($k2version, K2_CURRENT, '<') )
			$this->upgrade($k2version);

		// This theme uses post thumbnails
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu()
		add_theme_support( 'nav-menus' );

		// This theme supports Post Formats
		add_theme_support( 'post-formats', array( 'aside' ) );

		// Add default posts and comments RSS feed links to head
		add_theme_support( 'automatic-feed-links' );

		// This theme allows users to set a custom background
		add_theme_support( 'custom-background' );

		// Custom Headers
		add_theme_support( 'custom-header' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'header' => __( 'Header Menu', 'k2' ),
		) );

		// Actions and Filters
		add_filter( 'mce_css',            array( $this, 'admin_style_visual_editor' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_print_scripts',   array( $this, 'enqueue_scripts' ) );
		add_action( 'template_redirect',  array( $this, 'dynamic_content' ) );
		add_filter( 'query_vars',         array( $this, 'add_custom_query_vars' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ) );

		// There may be some things we need to do before K2 is initialised
		// Let's do them now
		do_action('k2_init');
	}


	/**
	 * Starts the installation process
	 *
	 * @uses do_action() Provides 'k2_install' action
	 */
	function install() {
		set_theme_mod( 'k2version', K2_CURRENT );

		set_theme_mod( 'k2sbstyle', 'right' );
		set_theme_mod( 'k2primarysize', '8' );
		set_theme_mod( 'k2sb1size', '3' );
		set_theme_mod( 'k2sb2size', '3' );
		set_theme_mod( 'k2advnav', '1' );
		set_theme_mod( 'k2animations', '1' );

		set_theme_mod( 'k2postmeta', array(
			'standard-above' => __('Published by %author% on %date% in %categories%. %comments% %tags%', 'k2'),
			'standard-below' => '',
			'aside-above' => '',
			'aside-below' => __('Published by %author% on %date%. %comments%', 'k2')
		) );

		// Call the install handlers
		do_action('k2_install');
	}


	/**
	 * Starts the upgrade process
	 *
	 * @uses do_action() Provides 'k2_upgrade' action
	 * @param string $previous Previous version K2
	 */
	function upgrade($previous) {
		// Install options
		$this->install();

		// Call the upgrade handlers
		do_action('k2_upgrade', $previous);

		// Update the version
		set_theme_mod('k2version', K2_CURRENT);
	}


	/**
	 * Adds k2dynamic into the list of query variables, used for dynamic content
	 */
	function add_custom_query_vars($query_vars) {
		$query_vars[] = 'k2dynamic';

		return $query_vars;
	}


	/**
	 * Filter to prevent redirect_canonical() from redirecting dynamic content
	 */
	function prevent_dynamic_redirect($redirect_url) {
		if ( strpos($redirect_url, 'k2dynamic=' ) !== false )
			return false;

		return $redirect_url;
	}


	/**
	 * Return the home page link, used for dynamic content
	 */
	function get_home_url() {
		if ( ('page' == get_option('show_on_front')) and ($page_id = get_option('page_for_posts')) ) {
			return get_page_link($page_id);
		}

		return get_bloginfo('url') . '/';
	}


	/**
	 * Handles displaying dynamic content such as LiveSearch, RollingArchives
	 *
	 * @uses do_action() Provides 'k2_dynamic_content' action
	 */
	function dynamic_content() {
		$k2dynamic = get_query_var('k2dynamic');

		if ( $k2dynamic ) {
			define('DOING_AJAX', true);

			// Send the header
			header('Content-Type: ' . get_bloginfo('html_type') . '; charset=' . get_bloginfo('charset'));

			get_template_part('blocks/k2-loop');

			if ( 'init' == $k2dynamic ) {
				$rolling_state = k2_get_rolling_archives_state();
			?>
				<script type="text/javascript">
				// <![CDATA[
					K2.RollingArchives.setState(
						<?php echo $rolling_state['curpage']; ?>,
						<?php echo $rolling_state['maxpage']; ?>,
						<?php echo json_encode( $rolling_state['query'] ); ?>,
						<?php echo json_encode( $rolling_state['pagedates'] ); ?>
					);
				// ]]>
				</script>

			<?php
			}

			// K2 Hook
			do_action('k2_dynamic_content', $k2dynamic);
			exit;
		}
	}


	/**
	 * Register K2 scripts with WordPress' script loader
	 */
	function register_scripts() {
		// If debug mode is off, load minimized scripts, else don't... Duh!
		if ( 1 == get_option('k2optimjs') ) {

			wp_register_script('k2functions',
				get_bloginfo('template_directory') . '/js/k2.min.js',
				array('jquery'), K2_CURRENT);

			wp_register_script('k2advnav',
				get_bloginfo('template_directory') . '/js/k2.advnav.min.js',
				array('jquery', 'k2functions'), K2_CURRENT);

			wp_register_script('k2options',
				get_bloginfo('template_directory') . "/js/k2.options.min.js",
				array('jquery', 'jquery-ui-sortable'), K2_CURRENT);

		} else {
			// Third-Party Scripts
			wp_register_script('bbq',
				get_bloginfo('template_directory') . '/js/uncompressed/jquery.bbq.js',
				array('jquery'), '1.2.1', true);

			wp_register_script('hoverintent',
				get_bloginfo('template_directory') . '/js/uncompressed/jquery.hoverintent.js',
				array('jquery'), '5');

			wp_register_script('superfish',
				get_bloginfo('template_directory') . '/js/uncompressed/jquery.superfish.js',
				array('jquery', 'hoverintent'), '1.4.8');

			wp_register_script('easing',
				get_bloginfo('template_directory') . '/js/uncompressed/jquery.easing.js',
				array('jquery'), '1.3', true);

			wp_register_script('hotkeys',
				get_bloginfo('template_directory') . '/js/uncompressed/jquery.hotkeys.js',
				array('jquery'), '0.8', true);

			wp_register_script('ui',
				get_bloginfo('template_directory') . '/js/uncompressed/jquery.ui.js',
				array('jquery'), '1.8.2', true);

			// K2 Scripts
			wp_register_script('k2functions',
				get_bloginfo('template_directory') . "/js/uncompressed/k2.functions.js",
				array('jquery', 'superfish'), K2_CURRENT);

			wp_register_script('k2options',
				get_bloginfo('template_directory') . "/js/uncompressed/k2.options.js",
				array('jquery', 'jquery-ui-sortable'), K2_CURRENT);

			wp_register_script('k2slider',
				get_bloginfo('template_directory') . "/js/uncompressed/k2.slider.js",
				array('jquery'), K2_CURRENT, true);

			wp_register_script('k2livesearch',
				get_bloginfo('template_directory') . "/js/uncompressed/k2.livesearch.js",
				array('jquery', 'bbq', 'hotkeys'), K2_CURRENT);

			wp_register_script('k2advnav',
				get_bloginfo('template_directory') . "/js/uncompressed/k2.rollingarchives.js",
				array('jquery', 'bbq', 'easing', 'ui', 'k2slider', 'hotkeys', 'k2livesearch'), K2_CURRENT);
            wp_localize_script('k2advnav', 'k2advnav_i18n', array(
                'pagetext' => __('%d of %d', 'k2'),
                'older' => __('Older', 'k2'),
                'newer' => __('Newer', 'k2'),
                'loading' => __('Loading', 'k2'),
                'text_trim' => __('Collapse Text', 'k2'),
                'text_untrim' => __('Expand Text', 'k2'),
            ));
		}
	}


	/**
	 * Enqueues scripts needed by K2
	 */
	function enqueue_scripts() {
		// Load our scripts
		if ( ! is_admin() ) {
			wp_enqueue_script('k2functions');

			if ( '1' == get_theme_mod('k2advnav') )
				wp_enqueue_script('k2advnav');

			// WP 2.7 threaded comments
			if ( is_singular() && get_option('thread_comments') )
				wp_enqueue_script( 'comment-reply' );
		}
	}


	/**
	 *
	 */
	function customize_register( $wp_customize ) {
		$wp_customize->add_setting( 'k2sbstyle' , array(
			'default'     => 'right',
			'transport'   => 'refresh',
		) );

		$wp_customize->add_setting( 'k2primarysize' , array(
			'default'     => '9',
			'transport'   => 'refresh',
		) );

		$wp_customize->add_setting( 'k2sb1size' , array(
			'default'     => '3',
			'transport'   => 'refresh',
		) );

		$wp_customize->add_setting( 'k2sb2size' , array(
			'default'     => '3',
			'transport'   => 'refresh',
		) );

		$wp_customize->add_setting( 'k2advnav' , array(
			'default'     => '1',
			'transport'   => 'refresh',
		) );

		$wp_customize->add_setting( 'k2animations' , array(
			'default'     => '1',
			'transport'   => 'refresh',
		) );


		// Layout Section
		$wp_customize->add_section( 'k2layout' , array(
			'title'      => __( 'Layout', 'k2' ),
			'priority'   => 30,
		) );

		$wp_customize->add_control(
			'k2sbstyle', 
			array(
				'label'    => __('Sidebars', 'k2'),
				'section'  => 'k2layout',
				'settings' => 'k2sbstyle',
				'type'     => 'radio',
				'choices'  => array(
					'right' => __('Right', 'k2'),
					'left' => __('Left', 'k2'),
					'both' => __('Both', 'k2'),
				),
			)
		);

		if ( is_active_sidebar('widgets-sidebar-1') || is_active_sidebar('widgets-sidebar-2') ) {
			$wp_customize->add_control(
				'k2primarysize', 
				array(
					'label'    => __('Primary Column Size', 'k2'),
					'section'  => 'k2layout',
					'settings' => 'k2primarysize',
					'type'     => 'select',
					'choices'  => array_combine( range(1, 12), range(1, 12) ),
				)
			);
	
			if ( is_active_sidebar('widgets-sidebar-1') ) {
				$wp_customize->add_control(
					'k2sb1size', 
					array(
						'label'    => __('Sidebar 1 Column Size', 'k2'),
						'section'  => 'k2layout',
						'settings' => 'k2sb1size',
						'type'     => 'select',
						'choices'  => array_combine( range(1, 12), range(1, 12) ),
					)
				);
			}
	
			if ( is_active_sidebar('widgets-sidebar-2') ) {
				$wp_customize->add_control(
					'k2sb2size', 
					array(
						'label'    => __('Sidebar 2 Column Size', 'k2'),
						'section'  => 'k2layout',
						'settings' => 'k2sb2size',
						'type'     => 'select',
						'choices'  => array_combine( range(1, 12), range(1, 12) ),
					)
				);
			}
		}


		// Advanced Navigation Section
		$wp_customize->add_section( 'k2advnav' , array(
			'title'      => __( 'Advanced Navigation', 'k2' ),
			'priority'   => 30,
		) );

		$wp_customize->add_control(
			'k2advnav', 
			array(
				'label'    => __('Dynamic Archives & Search', 'k2'),
				'section'  => 'k2advnav',
				'settings' => 'k2advnav',
				'type'     => 'radio',
				'choices'  => array(
					'1' => __('On', 'k2'),
					'0' => __('Off', 'k2'),
				),
			)
		);

		$wp_customize->add_control(
			'k2animations', 
			array(
				'label'    => __('Animations', 'k2'),
				'section'  => 'k2advnav',
				'settings' => 'k2animations',
				'type'     => 'radio',
				'choices'  => array(
					'1' => __('On', 'k2'),
					'0' => __('Off', 'k2'),
				),
			)
		);
	}

	/**
	 * Helper function to load all php files in given directory using require_once
	 *
	 * @param string $dir_path directory to scan
	 * @param array $ignore list of files to ignore
	 */
	function include_all($dir_path, $ignore = false) {
		// Open the directory
		$dir = @dir($dir_path) or die( sprintf( __('Could not open required directory' , 'k2'), $dir_path ) );

		// Get all the files from the directory
		while(($file = $dir->read()) !== false) {
			// Check the file is a file, and is a PHP file
			if(is_file($dir_path . $file) and (!$ignore or !in_array($file, $ignore)) and preg_match('/\.php$/i', $file)) {
				include_once($dir_path . $file);
			}
		}

		// Close the directory
		$dir->close();
	}


	/**
	 * Helper function to search for files based on given criteria
	 *
	 * @param string $path directory to search
	 * @param array $ext file extensions
	 * @param integer $depth depth of search
	 * @param mixed $relative relative to which path
	 * @return array paths of files found
	 */
	function files_scan($path, $ext = false, $depth = 1, $relative = true) {
		$files = array();

		// Scan for all matching files
		$this->_files_scan( trailingslashit($path), '', $ext, $depth, $relative, $files);

		return $files;
	}


	/**
	 * Recursive function for files_scan
	 *
	 * @param string $base_path
	 * @param string $path
	 * @param string $ext
	 * @param string $depth
	 * @param mixed $relative
	 * @param string $files
	 * @return array paths of files found
	 */
	function _files_scan($base_path, $path, $ext, $depth, $relative, &$files) {
		if (!empty($ext)) {
			if (!is_array($ext)) {
				$ext = array($ext);
			}
			$ext_match = implode('|', $ext);
		}

		// Open the directory
		if(($dir = @dir($base_path . $path)) !== false) {
			// Get all the files
			while(($file = $dir->read()) !== false) {
				// Construct an absolute & relative file path
				$file_path = $path . $file;
				$file_full_path = $base_path . $file_path;

				// If this is a directory, and the depth of scan is greater than 1 then scan it
				if(is_dir($file_full_path) and $depth > 1 and !($file == '.' or $file == '..')) {
					$this->_files_scan($base_path, $file_path . '/', $ext, $depth - 1, $relative, $files);

				// If this is a matching file then add it to the list
				} elseif(is_file($file_full_path) and (empty($ext) or preg_match('/\.(' . $ext_match . ')$/i', $file))) {
					if ( $relative === true ) {
						$files[] = $file_path;
					} elseif ( $relative === false ) {
						$files[] = $file_full_path;
					} else {
						$files[] = str_replace($relative, '', $file_full_path);
					}
				}
			}

			// Close the directory
			$dir->close();
		}
	}


	/**
	 * Move an existing file to a new path
	 *
	 * @param string $source original path
	 * @param string $dest new path
	 * @param boolean $overwrite if destination exists, overwrite
	 * @return string new path to file
	 */
	function move_file($source, $dest, $overwrite = false) {
		return $this->_copy_or_move_file($source, $dest, $overwrite, true);
	}

	function copy_file($source, $dest, $overwrite = false) {
		return $this->_copy_or_move_file($source, $dest, $overwrite, false);
	}

	function _copy_or_move_file($source, $dest, $overwrite = false, $move = false) {
		// check source and destination folder
		if ( file_exists($source) and is_dir(dirname($dest)) ) {

			// destination is a folder, assume move to there
			if ( is_dir($dest) ) {
				if ( DIRECTORY_SEPARATOR != substr($dest, -1) )
					$dest .= DIRECTORY_SEPARATOR;

				$dest = $dest . basename($source);
			}

			// destination file exists
			if ( is_file($dest) ) {
				if ($overwrite) {
					// Delete existing destination file
					@unlink($dest);
				} else {
					// Find a unique name
					$dest = $this->get_unique_path($dest);
				}
			}

			if ($move) {
				if ( rename($source, $dest) )
					return $dest;
			} else {
				if ( copy($source, $dest) )
					return $dest;
			}
		}
		return false;
	}

	function get_unique_path($source) {
		$source = pathinfo($source);

		$path = trailingslashit($source['dirname']);
		$filename = $source['filename'];
		$ext = $source['extension'];

		$number = 0;
		while ( file_exists($path . $filename . ++$number . $ext) );

		return $path . sanitize_title_with_dashes($filename . $number) . $ext;
	}
}

// Decrease the priority of redirect_canonical
remove_action( 'template_redirect', 'redirect_canonical' );
add_action( 'template_redirect', 'redirect_canonical', 11 );
