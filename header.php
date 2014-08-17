<?php
/**
 * The template for displaying the header.
 *
 * @package WordPress
 * @subpackage K2
 * @since K2 unknown
 */

// Prevent users from directly loading this theme file
defined( 'K2_CURRENT' ) or die ( __('Error: This file can not be loaded directly.', 'k2') );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="template" content="K2 <?php k2info('version'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?php wp_title('&laquo;', true, 'right'); ?><?php bloginfo('name'); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />

<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>">

<?php if ( is_singular() ): ?>
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
<?php endif; ?>

<!--[if lt IE 9]>
<script src="<?php bloginfo( 'template_url' ); ?>/js/html5.js"></script>
<![endif]-->

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php /* K2 Hook */ do_action('template_body_top'); ?>

<div class="container">
<div id="page" class="hfeed">

	<?php /* K2 Hook */ do_action('template_before_header'); ?>

	<header id="header">

		<?php get_template_part( 'blocks/k2-header' ); ?>

		<?php /* K2 Hook */ do_action('template_header'); ?>

	</header> <!-- #header -->

	<hr />

	<?php /* K2 Hook */ do_action('template_before_content'); ?>
