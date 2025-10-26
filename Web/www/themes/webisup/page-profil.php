<?php
/* Template Name: REMP SSO Login */

use App\Controllers\RempSsoController;

$controller = new RempSsoController();
$controller->handleRequest();
get_header();
$controller->render();

get_footer();
