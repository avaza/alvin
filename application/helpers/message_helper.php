<?php
/*
 * * Messages Helper * *
 *
 * * Functions for html markup and layout of messages when sent/sending to any view
 *
 * * To print all messages within the view simple call printMessages();
 */

/**
 * @param $message
 * @return bool
 */
function printMessages( $message )
{
    if(isset($message) && $message)
    {
        $markups = parseMessages( $message );

        if( ! $markups) return false;

        foreach($markups as $markup)
        {
            echo $markup;
        }
    }

    return false;
}

/**
 * @param $messages
 * @return array|bool
 */
function parseMessages( $messages )
{
    if( ! isset( $messages )) return false;

    $messages = is_array( $messages ) ? [$messages] : structureMessage( $messages );

    foreach($messages as $message)
        {
            $markups[] = markupMessage( $message );
        }

    return empty( $markups ) ? false : $markups;
}

/**
 * @param $message
 * @return array
 */
function structureMessage( $message )
{
    return [
        [ 'message' => $message, 'type' => 'invalid', 'markup' => [] ]
    ];
}

/**
 * @param array $message
 *
 * @return string
 */
function markupMessage( $message )
{
    $markup = [ 'ctTag' => 'div', 'class' => 'alert alert-', 'close' => true, ];
    $design = array_merge( $markup, $message );

    // OPEN TAG WITH CLASS NAME
    $prepared  = "<{$design['ctTag']} class=\"{$design['class']}{$design['type']}\">\n";
    // INCLUDE CLOSE WITH X
    if($design['close']) $prepared .= "<button class=\"close\" data-dismiss=\"alert\">&times;</button>\n";
    // MESSAGE TEXT
    $prepared .= $design['message'];
    //CLOSE TAG
    $prepared .= "\n</{$design["ctTag"]}>\n";

    return $prepared;
}

/**
 * @param $message
 *
 * @return \stdClass
 */
function invalidWith( $message = null )
{
    $response = new stdClass();
    $response->valid = false;
    $response->message = [ 'type' => 'invalid', 'message' => $message ];

    return $response;
}

/**
 * @param string $message
 * @param \stdClass $object
 *
 * @return \stdClass
 */
function validWith( $object, $message = null )
{
    if(is_object( $object )) $object->valid = true;

    if( ! is_null( $message )) $object->message = [ 'type' => 'valid', 'message' => $message ];

    return $object;
}

/**
 * @param $response
 * @return mixed
 */
function extractMessage( $response )
{
    return $response->message;
}

