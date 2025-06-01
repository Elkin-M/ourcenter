<?php
if( !headers_sent() && '' == session_id() ) {
session_start();
}
?>