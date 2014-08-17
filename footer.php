<?php
/**
 * The template for displaying the footer.
 *
 * @package WordPress
 * @subpackage K2
 * @since K2 unknown
 */
?>

	<?php /* K2 Hook */ do_action('template_after_content'); ?>

</div><!-- #page -->
<hr />

<?php /* K2 Hook */ do_action('template_before_footer'); ?>

<footer id="footer">

	<?php get_template_part( 'blocks/k2-footer' ); ?>

	<?php /* K2 Hook */ do_action('template_footer'); ?>

</footer><!-- #footer -->

</div><!-- .container -->
<?php wp_footer(); ?>

<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

</body>
</html>
