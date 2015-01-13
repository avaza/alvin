<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Mock
 * This controller provides testing routes for to mock the CudaTel System
 * (EXCLUDE FROM PRODUCTION)
 *
 * @author Josh Murray
 */

class Mock extends Alvin_Controller{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Mocks valid credentials from Alvin system
     *
     * @return string
     */
    function alvinCreds()
    {
        $fakeAlvin = [
            'username' => 'j.murray',
            'extension' => '5041',
            'pin' => '1405'
        ];

        return json_encode($fakeAlvin);
    }

    /**
     * Mocks valid credentials from CudaTel system
     *
     * @return string
     */
    function cudaCreds()
    {
        $fakeATel = ['bbx_user_id' => 1000];

        return json_encode($fakeATel);
    }

    /**
     * Mocks errors from CudaTel system
     *
     * @return string
     */
    function cudaError()
    {
        $fakeATel = ['error' => 'NOTAUTHORIZED'];

        return json_encode($fakeATel);
    }

    /**
     * Mocks a call's data from CudaTel system
     *
     * @param $leg_count
     * @return string
     */
    function callLegs($leg_count)
    {
        $fakeATel = $leg_count === 0 ? [] : ['cdr' => range(1, $leg_count)];

        return json_encode($fakeATel);
    }
}
