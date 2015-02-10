<?php
/*
protected function parseResult($result, $options = [])
{
    $default = [ 'raw' => false, 'as' => 'object', 'custom' => [] ];
    $options = array_merge( $default, $options );

    if( $options['raw'] ) return $result;

    $result = call_user_func_array(
        [ $this, $options[ 'as' ] . 'Results' ],
        [ $result, $options[ 'custom' ]]
    );

    return $result;
}

protected function objectResults( $result, $custom = [] )
{
    if( is_object( $result )) return $result;

    if( ! is_array( $result )) return false;

    if( count( $result ) === 1 && is_object( $result['0'] )) return $result['0'];

    if( empty( $custom )) return $result;
}

protected function arrayResults( $result, $custom = [] )
{
    if( is_object( $result )) return (array) $result;

    if( ! is_array( $result )) return false;

    if( count( $result ) === 1 && is_object( $result['0'] )) return (array) $result['0'];

    if( empty( $custom )) return (array) $result;
}

protected function jsonResults( $result, $custom = [] )
{
    if( is_object( $result )) return json_encode( $result );

    if( ! is_array( $result )) return json_encode( false );

    if( count( $result ) === 1 && is_object( $result['0'] )) json_encode( $result['0'] );

    if( empty( $custom )) return json_encode( $result );
}

public function parseInput()
{
    if( ! isset( $this->parser )) return $this->input->post( null, true );

    $parsed = ['input' => $this->input->post( null, true )];

    foreach($this->parser as $input => $parse):
        $parsed[ $parse ] = $this->input->post( $input, true );
    endforeach;

    return $parsed;
}
*/
function redirectBack()
{
    redirect( $_SERVER[ 'HTTP_REFERER' ]);
}


/**
 * Will always return as $type or false
 * @param string $data - Data to be cast
 * @param string $type - Type to cast $data to
 *
 * @return mixed
 */
function cast( $data, $type = 'object')
{
    switch( $type )
    {
        case 'object':
            if( is_object( $data )) return $data;
            if( is_array( $data )) return (object) $data;
            if( is_string( $data )) return (object) compact( 'data' );
            return false;

            break;
        case 'array':
            if( is_array( $data )) return $data;
            if( is_object( $data )) return (array) $data;
            if( is_string( $data )) return compact( 'data' );
            return false;

            break;
        case 'json':
            if( is_object( $data ) || is_array( $data )) return json_encode( $data );
            if( is_string( $data ) && is_array( json_decode( $data, true ))) return $data;
            return false;

            break;
    }
}