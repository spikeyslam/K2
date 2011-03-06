<?php
/**
 * @package WordPress
 * @subpackage K2
 * @since K2 unknown
 */

	// Check that the K2 folder has no spaces
	$dir_has_spaces = (strpos(TEMPLATEPATH, ' ') !== false);

	$k2usestyle = get_option( 'k2usestyle' );
	$k2advnav = get_option( 'k2advnav' );

	// Get post meta format
	$k2postmeta = (array) get_option( 'k2postmeta' );
?>

<div class="wrap">
	<?php if ( isset($_GET['defaults']) ): ?>
	<div class="updated fade">
		<p><?php _e('K2 has been restored to default settings.', 'k2'); ?></p>
	</div>
	<?php endif; ?>

	<?php if ( isset($_GET['saved']) ): ?>
	<div class="updated fade">
		<p><?php _e('K2 Options have been updated', 'k2'); ?></p>
	</div>
	<?php endif; ?>

	<?php if ($dir_has_spaces): ?>
		<div class="error">
		<?php printf( __('The K2 directory: <strong>%s</strong>, contains spaces. For K2 to function properly, you will need to remove the spaces from the directory name.', 'k2'), TEMPLATEPATH ); ?>
		</div>
	<?php endif; ?>

	<?php do_action('k2_options_top'); ?>

	<?php if ( function_exists('screen_icon') ) screen_icon(); ?>
	<h2><?php _e('K2 Options', 'k2'); ?></h2>
	<form action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>" method="post" id="k2-options">
		<ul class="options-list">

			<li>
				<label for="k2-usestyle"><?php _e('Style', 'k2'); ?></label>

				<select id="k2-usestyle" name="k2[usestyle]">
					<option value="3" <?php selected($k2usestyle, 3); ?>><?php _e('Flanking Sidebars (default)', 'k2'); ?></option>
					<option value="2" <?php selected($k2usestyle, 2); ?>><?php _e('Sidebars Right', 'k2'); ?></option>
					<option value="1" <?php selected($k2usestyle, 1); ?>><?php _e('Sidebars Left', 'k2'); ?></option>
					<option value="0" <?php selected($k2usestyle, 0); ?>><?php _e('No CSS', 'k2'); ?></option>
				</select>
			</li>


			<li>
				<label for="k2-advnav"><?php _e('AJAX archives & search', 'k2'); ?></label>

				<select id="k2-advnav" name="k2[advnav]">
					<option value="2" <?php selected( $k2advnav, 2 ); ?>><?php _e('On, with animation (default)', 'k2'); ?></option>
					<option value="1" <?php selected( $k2advnav, 1 ); ?>><?php _e('On, sans animation', 'k2'); ?></option>
					<option value="0" <?php selected( $k2advnav, 0 ); ?>><?php _e('Off', 'k2'); ?></option>
				</select>
			</li>


			<li>
				<label for="k2-debug"><?php _e('Minify & combine JavaScript', 'k2'); ?></label>

				<input id="k2-debug" name="k2[debug]" type="checkbox" value="debugmode" <?php checked('1', get_option('k2optimjs')); ?> />
			</li>


			<li>
				<h3><?php _e('Post Entry', 'k2'); ?></h3>

				<p class="description">
					<?php _e('Use the following keywords: %author%, %categories%, %comments%, %date%, %tags% and %time%.', 'k2'); ?>
				</p>

				<table class="form-table">
					<caption><?php _e('Standard Posts', 'k2'); ?></caption>
					<tbody>
						<tr>
							<th scope="row">
								<label for="k2-post-meta-standard-above"><?php _e('Top Meta:', 'k2'); ?></label>
							</th>
							<td>
								<input id="k2-post-meta-standard-above" name="k2[postmeta][standard-above]" type="text" value="<?php esc_attr_e($k2postmeta['standard-above']); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="k2-post-meta-standard-below"><?php _e('Bottom Meta:', 'k2'); ?></label>
							</th>
							<td>
								<input id="k2-post-meta-standard-below" name="k2[postmeta][standard-below]" type="text" value="<?php esc_attr_e($k2postmeta['standard-below']); ?>" />
							</td>
						</tr>
					</tbody>
				</table>

				<table class="form-table">
					<caption><?php _e('Asides', 'k2'); ?></caption>
					<tbody>
						<tr>
							<th scope="row">
								<label for="k2-post-meta-aside-above"><?php _e('Top Meta:', 'k2'); ?></label>
							</th>
							<td>
								<input id="k2-post-meta-aside-above" name="k2[postmeta][aside-above]" type="text" value="<?php esc_attr_e($k2postmeta['aside-above']); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="k2-post-meta-aside-below"><?php _e('Bottom Meta:', 'k2'); ?></label>
							</th>
							<td>
								<input id="k2-post-meta-aside-below" name="k2[postmeta][aside-below]" type="text" value="<?php esc_attr_e($k2postmeta['aside-below']); ?>" />
							</td>
						</tr>
					</tbody>
				</table>

<?php /* ?>
				<div id="meta-preview" class="postbox">
					<h3 class="handle"><span><?php _e('Preview', 'k2'); ?></span></h3>
					<?php
						query_posts('showposts=1&what_to_show=posts&order=desc&post_status=publish');
						if ( have_posts() ):
							the_post();
					?>
						<article id="entry-<?php the_ID(); ?>" <?php post_class(); ?>>
							<?php get_template_part('blocks/k2-' . get_post_type() ); ?>
						</article>
					<?php endif; ?>
				</div>
<?php */ ?>
			</li>

			<?php /* K2 Hook */ do_action('k2_display_options'); ?>
		</ul>

		<div class="submit">
			<?php wp_nonce_field('k2options'); ?>
			<input type="hidden" name="k2-options-submit" value="k2-options-submit" />

			<input type="submit" id="save" name="save" class="button-primary" value="<?php esc_attr_e('Save Changes', 'k2'); ?>" />

			<input type="submit" name="restore-defaults" id="restore-defaults" onClick="return confirmDefaults();" value="<?php esc_attr_e('Revert to K2 Defaults', 'k2'); ?>" class="button-secondary" />
		</div><!-- .submit -->
	</form>

</div><!-- .wrap -->
