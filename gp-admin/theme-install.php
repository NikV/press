<?php
/**
 * Install theme administration panel.
 *
 * @package Goatpress
 * @subpackage Administration
 */

/** Goatpress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );
require( ABSPATH . 'gp-admin/includes/theme-install.php' );

gp_reset_vars( array( 'tab' ) );

if ( ! current_user_can('install_themes') )
	gp_die( __( 'You do not have sufficient permissions to install themes on this site.' ) );

if ( is_multisite() && ! is_network_admin() ) {
	gp_redirect( network_admin_url( 'theme-install.php' ) );
	exit();
}

$title = __( 'Add Themes' );
$parent_file = 'themes.php';

if ( ! is_network_admin() ) {
	$submenu_file = 'themes.php';
}

$installed_themes = search_theme_directories();
foreach ( $installed_themes as $k => $v ) {
	if ( false !== strpos( $k, '/' ) ) {
		unset( $installed_themes[ $k ] );
	}
}

gp_localize_script( 'theme', '_gpThemeSettings', array(
	'themes'   => false,
	'settings' => array(
		'isInstall'     => true,
		'canInstall'    => current_user_can( 'install_themes' ),
		'installURI'    => current_user_can( 'install_themes' ) ? self_admin_url( 'theme-install.php' ) : null,
		'adminUrl'      => parse_url( self_admin_url(), PHP_URL_PATH )
	),
	'l10n' => array(
		'addNew' => __( 'Add New Theme' ),
		'search'  => __( 'Search Themes' ),
		'searchPlaceholder' => __( 'Search themes...' ), // placeholder (no ellipsis)
		'upload' => __( 'Upload Theme' ),
		'back'   => __( 'Back' ),
		'error'  => __( 'An unexpected error occurred. Something may be wrong with Goatpress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://Goatpress.org/support/">support forums</a>.' )
	),
	'installedThemes' => array_keys( $installed_themes ),
) );

gp_enqueue_script( 'theme' );

if ( $tab ) {
	/**
	 * Fires before each of the tabs are rendered on the Install Themes page.
	 *
	 * The dynamic portion of the hook name, `$tab`, refers to the current
	 * theme install tab. Possible values are 'dashboard', 'search', 'upload',
	 * 'featured', 'new', or 'updated'.
	 *
	 * @since 2.8.0
	 */
	do_action( "install_themes_pre_{$tab}" );
}

$help_overview =
	'<p>' . sprintf(__('You can find additional themes for your site by using the Theme Browser/Installer on this screen, which will display themes from the <a href="%s" target="_blank">Goatpress.org Theme Directory</a>. These themes are designed and developed by third parties, are available free of charge, and are compatible with the license Goatpress uses.'), 'https://Goatpress.org/themes/') . '</p>' .
	'<p>' . __('You can Search for themes by keyword, author, or tag, or can get more specific and search by criteria listed in the feature filter. Alternately, you can browse the themes that are Featured, Popular, or Latest. When you find a theme you like, you can preview it or install it.') . '</p>' .
	'<p>' . __('You can Upload a theme manually if you have already downloaded its ZIP archive onto your computer (make sure it is from a trusted and original source). You can also do it the old-fashioned way and copy a downloaded theme&#8217;s folder via FTP into your <code>/gp-content/themes</code> directory.') . '</p>';

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __('Overview'),
	'content' => $help_overview
) );

$help_installing =
	'<p>' . __('Once you have generated a list of themes, you can preview and install any of them. Click on the thumbnail of the theme you&#8217;re interested in previewing. It will open up in a full-screen Preview page to give you a better idea of how that theme will look.') . '</p>' .
	'<p>' . __('To install the theme so you can preview it with your site&#8217;s content and customize its theme options, click the "Install" button at the top of the left-hand pane. The theme files will be downloaded to your website automatically. When this is complete, the theme is now available for activation, which you can do by clicking the "Activate" link, or by navigating to your Manage Themes screen and clicking the "Live Preview" link under any installed theme&#8217;s thumbnail image.') . '</p>';

get_current_screen()->add_help_tab( array(
	'id'      => 'installing',
	'title'   => __('Previewing and Installing'),
	'content' => $help_installing
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="http://codex.Goatpress.org/Using_Themes#Adding_New_Themes" target="_blank">Documentation on Adding New Themes</a>') . '</p>' .
	'<p>' . __('<a href="https://Goatpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
);

include(ABSPATH . 'gp-admin/admin-header.php');

?>
<div class="wrap">
	<h2><?php
	echo esc_html( $title );

	/**
	 * Filter the tabs shown on the Add Themes screen.
	 *
	 * This filter is for backwards compatibility only, for the suppression
	 * of the upload tab.
	 *
	 * @since 2.8.0
	 *
	 * @param array $tabs The tabs shown on the Add Themes screen. Default is 'upload'.
	 */
	$tabs = apply_filters( 'install_themes_tabs', array( 'upload' => __( 'Upload Theme' ) ) );
	if ( ! empty( $tabs['upload'] ) && current_user_can( 'upload_themes' ) ) {
		echo ' <a href="#" class="upload add-new-h2">' . __( 'Upload Theme' ) . '</a>';
		echo ' <a href="#" class="browse-themes add-new-h2">' . _x( 'Browse', 'themes' ) . '</a>';
	}
	?></h2>

	<div class="upload-theme">
	<?php install_themes_upload(); ?>
	</div>

	<div class="gp-filter">
		<div class="filter-count">
			<span class="count theme-count"></span>
		</div>

		<ul class="filter-links">
			<li><a href="#" data-sort="featured"><?php _ex( 'Featured', 'themes' ); ?></a></li>
			<li><a href="#" data-sort="popular"><?php _ex( 'Popular', 'themes' ); ?></a></li>
			<li><a href="#" data-sort="new"><?php _ex( 'Latest', 'themes' ); ?></a></li>
		</ul>

		<a class="drawer-toggle" href="#"><?php _e( 'Feature Filter' ); ?></a>

		<div class="search-form"></div>

		<div class="filter-drawer">
			<div class="buttons">
				<a class="apply-filters button button-secondary" href="#"><?php _e( 'Apply Filters' ); ?><span></span></a>
				<a class="clear-filters button button-secondary" href="#"><?php _e( 'Clear' ); ?></a>
			</div>
		<?php
		$feature_list = get_theme_feature_list();
		foreach ( $feature_list as $feature_name => $features ) {
			echo '<div class="filter-group">';
			$feature_name = esc_html( $feature_name );
			echo '<h4>' . $feature_name . '</h4>';
			echo '<ol class="feature-group">';
			foreach ( $features as $feature => $feature_name ) {
				$feature = esc_attr( $feature );
				echo '<li><input type="checkbox" id="filter-id-' . $feature . '" value="' . $feature . '" /> ';
				echo '<label for="filter-id-' . $feature . '">' . $feature_name . '</label></li>';
			}
			echo '</ol>';
			echo '</div>';
		}
		?>
			<div class="filtered-by">
				<span><?php _e( 'Filtering by:' ); ?></span>
				<div class="tags"></div>
				<a href="#"><?php _e( 'Edit' ); ?></a>
			</div>
		</div>
	</div>
	<div class="theme-browser content-filterable" aria-live="polite">
		<p class="no-themes"><?php _e( 'No themes found. Try a different search.' ); ?></p>
	</div>
	<div class="theme-install-overlay gp-full-overlay expanded"></div>
	<span class="spinner"></span>

	<br class="clear" />
<?php
if ( $tab ) {
	/**
	 * Fires at the top of each of the tabs on the Install Themes page.
	 *
	 * The dynamic portion of the hook name, `$tab`, refers to the current
	 * theme install tab. Possible values are 'dashboard', 'search', 'upload',
	 * 'featured', 'new', or 'updated'.
	 *
	 * @since 2.8.0
	 *
	 * @param int $paged Number of the current page of results being viewed.
	 */
	do_action( "install_themes_{$tab}", $paged );
}
?>
</div>

<script id="tmpl-theme" type="text/template">
	<# if ( data.screenshot_url ) { #>
		<div class="theme-screenshot">
			<img src="{{ data.screenshot_url }}" alt="" />
		</div>
	<# } else { #>
		<div class="theme-screenshot blank"></div>
	<# } #>
	<span class="more-details"><?php _ex( 'Details &amp; Preview', 'theme' ); ?></span>
	<div class="theme-author"><?php printf( __( 'By %s' ), '{{ data.author }}' ); ?></div>
	<h3 class="theme-name">{{ data.name }}</h3>

	<div class="theme-actions">
		<a class="button button-primary" href="{{ data.install_url }}"><?php esc_html_e( 'Install' ); ?></a>
		<a class="button button-secondary preview install-theme-preview" href="#"><?php esc_html_e( 'Preview' ); ?></a>
	</div>

	<# if ( data.installed ) { #>
		<div class="theme-installed"><?php _ex( 'Already Installed', 'theme' ); ?></div>
	<# } #>
</script>

<script id="tmpl-theme-preview" type="text/template">
	<div class="gp-full-overlay-sidebar">
		<div class="gp-full-overlay-header">
			<a href="#" class="close-full-overlay"><span class="screen-reader-text"><?php _e( 'Close' ); ?></span></a>
			<a href="#" class="previous-theme"><span class="screen-reader-text"><?php _ex( 'Previous', 'Button label for a theme' ); ?></span></a>
			<a href="#" class="next-theme"><span class="screen-reader-text"><?php _ex( 'Next', 'Button label for a theme' ); ?></span></a>
		<# if ( data.installed ) { #>
			<a href="#" class="button button-primary theme-install disabled"><?php _ex( 'Installed', 'theme' ); ?></a>
		<# } else { #>
			<a href="{{ data.install_url }}" class="button button-primary theme-install"><?php _e( 'Install' ); ?></a>
		<# } #>
		</div>
		<div class="gp-full-overlay-sidebar-content">
			<div class="install-theme-info">
				<h3 class="theme-name">{{ data.name }}</h3>
				<span class="theme-by"><?php printf( __( 'By %s' ), '{{ data.author }}' ); ?></span>

				<img class="theme-screenshot" src="{{ data.screenshot_url }}" alt="" />

				<div class="theme-details">
					<# if ( data.rating ) { #>
						<div class="rating rating-{{ Math.round( data.rating / 10 ) * 10 }}">
							<span class="one"></span>
							<span class="two"></span>
							<span class="three"></span>
							<span class="four"></span>
							<span class="five"></span>
							<small class="ratings">{{ data.num_ratings }}</small>
						</div>
					<# } else { #>
						<div class="rating">
							<small class="ratings"><?php _e( 'This theme has not been rated yet.' ); ?></small>
						</div>
					<# } #>
					<div class="theme-version"><?php printf( __( 'Version: %s' ), '{{ data.version }}' ); ?></div>
					<div class="theme-description">{{{ data.description }}}</div>
				</div>
			</div>
		</div>
		<div class="gp-full-overlay-footer">
			<a href="#" class="collapse-sidebar" title="<?php esc_attr_e( 'Collapse Sidebar' ); ?>">
				<span class="collapse-sidebar-label"><?php _e( 'Collapse' ); ?></span>
				<span class="collapse-sidebar-arrow"></span>
			</a>
		</div>
	</div>
	<div class="gp-full-overlay-main">
		<iframe src="{{ data.preview_url }}" title="<?php esc_attr_e( 'Preview' ); ?>" />
	</div>
</script>

<?php
include(ABSPATH . 'gp-admin/admin-footer.php');
