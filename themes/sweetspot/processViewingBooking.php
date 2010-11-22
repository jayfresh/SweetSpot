<?php
/*
 
 Template Name: ProcessViewingBooking

 */

if($_POST) {
	processViewingBooking($_POST);
} else {
	processViewingBooking($_GET);
}

?>