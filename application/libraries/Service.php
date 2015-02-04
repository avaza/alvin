<?php 
class Service {

    public $data;
    protected $_ci;

    function __construct( $params = null )
    {
        $this->_ci =& get_instance();
        $this->_ci->load->model('service_model');
    }

    protected function getIfExists( $record, $models = [], $hash = [] )
    {
        if( empty( $models )) die( 'You must provide at least one model to search' );

        foreach( $hash as $column => $value ):
            $record[ $column ] = $this->_ci->service_model->hash( $record[ $column ] );
        endforeach;

        foreach( $models as $model ):
            $modelName = $model;
            $found = $this->_ci->$modelName->exists( $record, true );
            if( $found !== false ) return $found;
        endforeach;

        return false;
    }
}