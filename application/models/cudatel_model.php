<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cudatel_model extends Alvin_Model {

    protected $curlArray;
    protected $cookieLoc;

    function __construct()
    {
        parent::__construct();
    }
    /*
     *
     * __auth_user:5041
     * __auth_pass:1405
     *
     *
     *
     *
     *
     */
    public function validate($user)
    {
        //$ext = $user->ext;
        //$pin = $user->pin;

        $ext = 5041;
        $pin = 1405;

        $cuda = $this->authenticate($ext, $pin);
        if($cuda)
        {
            $user->cudatel = $cuda;
        }

        $user = (object) [
            'valid' => false,
            'message' => 'CudaTel authentication failed. Contact System Administrator.'
        ];

        return $user;
    }

    private function authenticate()
    {
        //TODO
        return true;
    }

    /**
     * Runs on load - Build cURL Objects
     *
     * @return mixed
     *
    protected function setupCurl()
    {
        // build base for GET and POST requests
        $base = curl_init();
        curl_setopt($base, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($base, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($base, CURLOPT_TIMEOUT, 10);
        curl_setopt($base, CURLINFO_HEADER_OUT, true);

        // Set options for all GET requests to the Cudatel (GET - EVERYTHING BUT AUTHENTICATION)
        $this->curlArray['get'] = curl_copy_handle($base);
        curl_setopt($this->curlArray['get'], CURLOPT_COOKIEFILE, $this->cookieLoc);
        curl_setopt($this->curlArray['get'], CURLOPT_HTTPGET, true);

        // Set options for all POST requests to the Cudatel (POST - ONLY AUTHENTICATION)
        $this->curlArray['post'] = curl_copy_handle($base);
        curl_setopt($this->curlArray['get'], CURLOPT_COOKIEJAR, $this->cookieLoc);
        curl_setopt($this->curlArray['post'], CURLOPT_POST, true);

        return $this;
    }

    /**
     * Runs on load - Check current Authentication and renew if needed (ALL CudaTel permission levels)
     *
     * @return boolean successful
     *
    protected function setupAuth()
    {
        // Extract the CudaTel "sessionId" from cookie file
        $this->existAuth();

        $authenticated = $this->checkAuth();

        if( ! $authenticated)
        {
            $authenticated = $this->renewAuth();
        }

        return $authenticated;
    }

    /**
     * Authenticate User and collect EXISTING CudaTel session and cookie
     *
     * @return mixed
     *
    protected function existAuth()
    {
        if(file_exists($this->cookieLoc))
        {
            $file_array = file($this->cookieLoc);
            foreach($file_array as $line)
            {
                if($line[0] != '#' && substr_count($line, "\t") == 6)
                {
                    $segments = explode("\t", $line);
                    $segments = array_map('trim', $segments);
                }

                if(isset($segments) && $segments['5'] == 'bps_session')
                {
                    $this->authArr['sess'] = $segments['6'];
                }
            }
        }

        return $this;
    }

    /**
     * Authenticate User and collect NEW CudaTel session and cookie
     *
     * @return mixed
     *
    protected function renewAuth()
    {
        $curlOptions = [
            'url'    => base_url() . '/gui/login/login',
            'type'   => 'POST',
            'params' => [
                '__auth_user' => $this->authArr['user'],
                '__auth_pass' => $this->authArr['pass']
            ]
        ];

        $response = $this->curl($curlOptions);
        if( ! $response || isset($response->error))
        {
            return false;
        }

        return true;
    }

    /**
     * Check if CudaTel session is valid (ALL CudaTel permission levels)
     *
     * @return boolean
     *
    protected function checkAuth()
    {
        $curlOptions = [
            'url' => base_url() . '/gui/login/status'
        ];

        $response = $this->curl($curlOptions);
        if( ! $response || isset($response->error))
        {
            return false;
        }

        return true;
    }

    /**
     * Run a cURL request (GET or POST) to a CudaTel url
     * @param array $curlOptions
     *
     * @return mixed
     *
    protected function curl($curlOptions)
    {
        $data = false;

        // Set Defaults or Overrides
        $curlDefault = ['type' => 'GET', 'params' => [], 'JSON' => false];
        $curlOptions = array_merge($curlDefault, $curlOptions);

        if($this->authArr['sess'] !== false)
        {
            $curlOptions['params']['sessionid'] = $this->authArr['sess'];
        }

        // Create parameter string
        $param_string = $this->createParameterString($curlOptions);

        // set final cURL options and Execute
        switch($curlOptions['type'])
        {
            case 'POST':
                curl_setopt($this->curlArray['post'], CURLOPT_URL, $curlOptions['url']);
                curl_setopt($this->curlArray['post'], CURLOPT_POSTFIELDS, $param_string);
                $data = curl_exec($this->curlArray['post']);
                break;

            case 'GET':
                curl_setopt($this->curlArray['get'], CURLOPT_URL, $curlOptions['url'] . $param_string);
                $data = curl_exec($this->curlArray['get']);
                break;
        }

        return $data;

        /* Set cookie file options based on valid/invalid session
        if(isset($this->authArr['sess']) && file_exists('/tmp/cudasess' . $this->authArr['user']))
        {
            curl_setopt($curl, CURLOPT_COOKIEFILE, $this->ckieLoc);
        }
        else
        {
            curl_setopt($curl, CURLOPT_COOKIEJAR, $this->ckieLoc);
        }

        // Pull data with current options -- receive raw response
        $data = curl_exec($curl);
        curl_close($curl);
        die($pull);
        // Parse raw response -- extract JSON string
        $JSON = substr($pull, strpos('{'));

        // De-Code JSON string -- return as stdClass (PHP Object)
        $data = json_decode($JSON);

        return $data;*
    }

    protected function createParameterString($curlOptions)
    {
        $param_string = '';

        if($curlOptions['type'] === 'GET')
        {
            $param_string.= '?';
        }

        if(count($curlOptions['params']) > 0)
        {
            $param_string.= http_build_query($curlOptions['params'], null, '&');
        }

        return $param_string;
    }

    /**
     * Check if instance is ready to make requests
     *
     * @return boolean
     *
    protected function isReady()
    {
        if(is_object($this->curlArray['get']))
        {
            if($this->authArr['sess'] !== false)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Destroy all open cURL objects and close CudaTel sessions
     *
     * @return void
     *
    function __destruct()
    {
        if(isset($this->curlArray['get']))
        {
            curl_close($this->curlArray['get']);
        }
        if(isset($this->curlArray['post']))
        {
            curl_close($this->curlArray['post']);
        }
    }*/
}
    