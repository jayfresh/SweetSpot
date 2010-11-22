<?php 
/*

Template Name: Handle IPN

*/

$vars;
if($_POST) {
	$vars = $_POST;
} else {
	$vars = $_GET;
}
handle_ipn($vars);

?>
