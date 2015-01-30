<?php 

class Cudatel {

    public $sessionid;
    protected $_ci;

    function __construct()
    {
        $this->_ci =& get_instance();
        $this->_ci->load->library('curl');
        $this->_ci->load->library('session');
    }

    public function session($command)
    {
        return call_user_func_array([$this, $command . 'Session'], []);
    }

    public function data($command, $url, $params)
    {
        $this->_ci->session->user->sessionid = 0;
        $params['sessionid'] = $this->_ci->session->user->sessionid;
        $arguments = [ $url, $params ];

        $response =  call_user_func_array([$this, $command . 'Data'], $arguments);

        return $this->parse($command, $response, $url);
    }

    protected function parse($command, $response, $url)
    {
        $arguments = ['response' => $response, 'url' => $url];
        return call_user_func_array([$this, $command . 'Parser'], $arguments);
    }

    protected function createSession()
    {
        $user = $this->_ci->session->user;

        $user->ext = 5041;
        $user->pin = 1405;

        $creds = [ '__auth_user' => $user->ext, '__auth_pass' => $user->pin ];

        if( $this->data('post', '/gui/login/login', $creds) )
        {
            return true;
        }

        return ['message' => 'USER HAS BAD CREDENTIALS (CUDATEL)'];
    }

    protected function refreshSession()
    {
        if( isset($this->_ci->session->cuda) && $this->session('check') )
        {
            return true;
        }

        if( ! isset($this->_ci->session->cuda) )
        {
            return $this->createSession();
        }
    }

    protected function checkSession()
    {
        $check = $this->data('get', '/gui/user/status', ["sessionid" => $this->_ci->session->cuda]);

        if( ! isset($check->error) && isset($check->bbx_user_id) )
        {
            return true;
        }

        return ['message' => $check->error];
    }



    protected function destroySession($sessionId)
    {
        //TODO should return boolean
        return $this->data('get', '/gui/login/logout', ["sessionid" => $sessionId]);
    }

    protected function getData($url, $params)
    {
        return $this->_ci->curl->fetch($url, $params);
    }

    protected function postData($url, $params = [])
    {
        return $this->_ci->curl->fetch($url, $params, [ 'type' => 'POST' ]);
    }

    protected function postParser($response, $url)
    {
        die($response);
        $parser = getParserTemplate($url);
        //TODO First get the data into JSON format
        $unparsed = json_decode( $response );
        //TODO Check for an error and return if it exists
        if(isset($unparsed->error))
        {
            return $unparsed;
        }

        if( isset($unparsed->data) )
        {
            //TODO Parse to get sessionid
            //$this->_ci->session->cuda = $unparsed->sessionid;
        }

        return true;
    }

    protected function getParser($response, $url)
    {
        $parser = getParserTemplate($url);
        die($response);
        $parsed = new stdClass();
        //TODO First get the data into JSON format
        $unparsed = json_decode( $response );
        //TODO Check for an error and return if it exists
        if(isset($unparsed->error))
        {
            return $unparsed;
        }

        if( isset($unparsed->data) )
        {

            //TODO Parse to get desireable data (Object)
            //$this->_ci->session->cuda = $unparsed->sessionid;
        }

        return $parsed;
    }

    protected function getParserTemplate($url)
    {
        //$segments = explode($url, '/');
        $parser = true;//$segments[max(array_keys($segments))];
        return $parser;
    }
}