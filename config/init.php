<?php
if( !headers_sent() && '' == session_id() ) {
session_start();
}
date_default_timezone_set('America/Bogota');
?>