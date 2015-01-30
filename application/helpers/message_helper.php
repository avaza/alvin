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
    if( $message )
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

    wrapMessage( $messages );

    $markups[] = markupMessage( $messages );

    return empty( $markups ) ? false : $markups;
}

/**
 * @param $message
 * @return array
 */
function wrapMessage( $message )
{
    if( is_array( $message ) && isset( $message[ 'type' ], $message[ 'message' ])) return $message;
    if( is_object( $message ) && isset( $message->type, $message->message )) return (array) $message;

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
 * @param $object /stdClass
 * @param $message string
 *
 * @return \stdClass
 */
function invalidWith( $message, $object = [] )
{
    if( is_array( $object )) $object = (object) $object;

    if( ! is_object( $object )) $object = new stdClass();

    $object->valid = false;
    $messages = [ 'type' => 'invalid', 'message' => $message ];

    if( isset( $object->message ) && is_array( $object->message ))
    {
        $messages = [ $object->message[ 'message' ], $message ];
    }

    $object->message = $messages;

    return $object;
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

