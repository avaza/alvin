<?php


//TODO determine if you need these\/
/**
 * Parses database query results with the provided options
 * @param mixed $result
 * @param array $options
 *
 * @return mixed
 */
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