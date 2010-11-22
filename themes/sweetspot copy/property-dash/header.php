<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/css/reset.css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/css/grid.css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/css/jbase.css" media="screen" /> 
<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/property-dash/css/stickyfooter.css" media="screen" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'template_url' ); ?>/property-dash/style.css" />

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	 <div id="wrap" class="jbasewrap">