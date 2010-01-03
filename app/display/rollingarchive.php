<div id="rollingarchives" style="display:none;">

	<div id="rollnavigation">
		<div id="pagetrackwrap"><div id="pagetrack"><div id="pagehandle"><div id="rollhover"><div id="rolldates"></div></div></div></div></div>

		<div id="rollpages"></div>
		
		<a id="rollprevious" title="<?php _e('Older', 'k2'); ?>" href="#">
			<span>&laquo;</span> <?php _e('Older', 'k2'); ?>
		</a>
		<div id="rollload" title="<?php _e('Loading', 'k2'); ?>">
			<span><?php _e('Loading', 'k2'); ?></span>
		</div>
		<a id="rollnext" title="<?php _e('Newer', 'k2'); ?>" href="#">
			<?php _e('Newer', 'k2'); ?> <span>&raquo;</span>
		</a>

		<div id="texttrimmer">
			<div id="trimmertrim"><span><?php _e('Trim', 'k2'); ?></span></div>
			<div id="trimmeruntrim"><span><?php _e('Untrim', 'k2'); ?></span></div>
		</div>
	</div> <!-- #rollnavigation -->
</div> <!-- #rollingarchives -->

<div id="rollingcontent" class="hfeed" aria-live="polite" aria-atomic="true">
	<?php include(TEMPLATEPATH . '/app/display/theloop.php'); ?>
</div><!-- #rollingcontent .hfeed -->

<?php
	if ( defined('DOING_AJAX') and true == DOING_AJAX ) {
		add_action( 'k2_dynamic_content', array('K2', 'setup_rolling_archives') );
	} else {
		add_action( 'wp_footer', array('K2', 'setup_rolling_archives') );
	}
?>
