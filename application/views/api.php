<?php
header('Content-Type: application/x-json; charset=utf-8');
if( isset($message) )
{
    echo '<p>';
    echo json_encode( ['error' => $message] );
    echo '</p>';
}
elseif( isset($details) )
{
    echo '<pre>';
    echo json_encode( $details );
    echo '</pre>';
}
else
{
    echo '<p>';
    echo json_encode( ['error' => 'NODATA'] );;
    echo '</p>';
}
