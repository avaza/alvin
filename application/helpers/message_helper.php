<?php
/*
 * * Messages Helper * *
 *
 * * Functions for html markup and layout of messages when sent/sending to any view
 *
 * * To print all messages within the view simple call printMessages();
 */

/**
 * @param null  $messages
 * @return bool
 */
function printMessages( $messages = null )
{
    if( is_null( $messages )) return false;

    foreach( $messages as $message ):
        echo $message['opens'] . $message['message'] . $message['close'];
    endforeach;

    return false;
}

/**
 * @param null  $message
 * @param array $object
 * @param bool  $valid
 * @param array $markup
 * @return array|object
 */
function message( $message = null, $object = [], $valid = false, $markup = [] )
{
    $type = $valid ? 'valid' : 'invalid';

    if( is_null( $message )) die( 'Please include a message when calling message()' );

    if( is_array( $object )) $object = (object) $object;

    $object->valid = $valid;

    if( ! isset( $object->messages )) $object->messages = [];

    if( ! is_array( $object->messages )) die( 'Messages structure error' );

    $object->messages = addMessage( $object->messages, $message, $type, $markup );

    return $object;
}

/**
 * @param        $messages
 * @param        $newMessage
 * @param string $type
 * @param        $markup
 * @return array
 */
function addMessage( $messages, $newMessage, $type = 'invalid', $markup )
{
    $newMessage = wrapMessage( $newMessage, $type, $markup );

    if( ! empty( $newMessage )) $messages[] = $newMessage;

    return $messages;
}

/**
 * @param $message
 * @param $type
 * @param $markup
 * @return array
 */
function wrapMessage( $message, $type, $markup )
{
    $layout = [ 'tag' => 'div', 'class' => "alert alert-{$type}", 'closeable' => true, ];
    $design = array_merge( $layout, $markup );

    $markup = getMarkup( $design );

    return array_merge( compact( 'message' ), $markup );
}

/**
 * @param $design
 * @return array
 */
function getMarkup( $design )
{
    extract( $design );

    if( ! isset( $tag, $class, $closeable )) return [];

    $opens = "<{$tag} class=\"{$class}\">\n";
    $opens.= $closeable ? "<button class=\"close\" data-dismiss=\"alert\">&times;</button>\n" : '';
    $close = "\n</{$tag}>\n";

    return compact( 'opens', 'close' );
}

/**
 * @return array|object
 */
function validationMessages()
{
    $errors = [];
    $string = validation_errors();
    $tags = [ '<p>', '</p>' ];
    $messages = multiExplode( $string, $tags );

    $validation = [];

    foreach( $messages as $message ):
        $validation = (array) message( $message, $errors );
    endforeach;

    return $validation['messages'];
}

/**
 * @param null  $string
 * @param array $delimiters
 * @return array|bool
 */
function multiExplode( $string = null, $delimiters = [] )
{
    if( is_null( $string ) || ! is_array( $delimiters ) || empty( $delimiters )) return false;

    $strLimit = '';

    foreach( $delimiters as $i => $delimiter ):
        $strLimit .= preg_quote( $delimiter ) . '|';
    endforeach;

    $strLimit .= "\\n";

    return preg_split(":({$strLimit}):", trim( $string ), 0, PREG_SPLIT_NO_EMPTY);
}

