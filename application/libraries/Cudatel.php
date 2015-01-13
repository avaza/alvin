<?php 

class Cudatel {

    protected $rootUrl;
    protected $authArr;
    protected $curlArr;
    protected $ckieLoc;

    /**
     * @param array $params
     *
     * @return mixed OR die
     */
    function __construct($params)
    {
        $this->authArr = array_merge($this->authArr, [
            'sess' => false,        // CudaTel SessionId
            'data' => false         // CudaTel UserData
        ]);

        // Set Static CudaTel root URL ($this->rootUrl)
        $this->rootUrl = 'http://' . $_SERVER['HTTP_HOST'];

        // Set Static Cookie file location ($this->ckieLoc)
        $this->ckieLoc = '/tmp/cudasess' . $this->authArr['user'];

        // Create all needed cURL Objects ($this->curlArr)
        $this->setupCurl();
        // Set additional auth parameters ($this->authArr)
        $this->setupAuth();
        // Check cURL and Auth are valid or DIE
        if($this->isReady())
        {
            return true;
        }

        return false;
    }

    /**
     * Check if CudaTel username and password are valid (ALL CudaTel permission levels)
     * @param array $params
     *
     * @return boolean
     */
    public function hasValidCredentials($params)
    {
        $this->authArr = [
            'user' => $params['0'], // CudaTel Username
            'pass' => $params['1'], // CudaTel Password
        ];

        {
            "error":"NOTAUTHORIZED",
            "data":{
                "copyright":2014,
                "demo":0
            }
        }

        {
            "data":{
                "seen_help_hint":1,
                "copyright":2014,
                "bbx_user_username_printable":"Josh Murray (5041)",
                "bbx_user_username":"5041",
                "demo":0,
                "serial":"423739",
                "bbx_extension_id":980,
                "bbx_user_id":66,
                "bbx_user_full_name":"Josh Murray"
            }
        }

    }

    /**
     * Check if CudaTel session is valid (ALL CudaTel permission levels)
     *
     * @return boolean
     */
    protected function setupCurl()
    {   
        // Set shared cURl options for all CudaTel requests
        $this->curlArr['share'] = curl_share_init();
        die('sharing');
        curl_share_setopt($sh, CURLSHOPT_SHARE, CURL_LOCK_DATA_COOKIE);
        die('shared');
        // build base for GET and POST requests
        $base = curl_init();
        curl_setopt($base, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($base, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($base, CURLOPT_COOKIESESSION, true);
        curl_setopt($base, CURLOPT_TIMEOUT, 10);
        curl_setopt($base, CURLINFO_HEADER_OUT, true);

        // Set options for all GET requests to the Cudatel
        $this->curlArr['get'] = clone $base;
        curl_setopt($this->curlArr['get'], CURLOPT_SHARE, $this->curlArr['share']);
        curl_setopt($this->curlArr['get'], CURLOPT_HTTPGET, true);
        
        // Set options for all POST requests to the Cudatel
        $this->curlArr['post'] = clone $base;
        curl_setopt($this->curlArr['post'], CURLOPT_SHARE, $this->curlArr['share']);
        curl_setopt($this->curlArr['post'], CURLOPT_POST, true);

        // Options still unset after build: [CURLOPT_URL, CURLOPT_POSTFIELDS (POST only)]
    }

    /**
     * Runs upon Intstantiation.
     * @param string $user - Cudatel Username
     * @param string $pass - Cudatel Password
     *
     * @return boolean true if successful OR false if unsuccessful
     */
    protected function setupAuth()
    {
        // Extract the CudaTel "sessionid" from cookie file
        $this->existAuth();

        // Check if extracted "sessionid" is valid or re-authenticate
        if(!$this->checkAuth())
        {
            $this->renewAuth();
        }

        // Re-Check and return authenticated boolean
        return $this->checkAuth();
    }

    /**
     * Check each line of cookie to determine if it contains the session_id.
     *
     * @return instance
     */
    protected function existAuth()
    {
        if(file_exists($this->ckieLoc))
        {
            $file_array = file($this->ckieLoc);
            foreach($file_array as $line)
            {
                if($line[0] != '#' && substr_count($line, "\t") == 6)
                {
                    $segments = explode("\t", $line);
                    $segments = array_map('trim', $segments);
                }

                if($segments['5'] == 'bps_session')
                {
                    $this->authArr['sess'] = $segments['6'];
                }
            }
        }

        return $this;
    }

    /**
     * Check if CudaTel session is valid (ALL CudaTel permission levels)
     *
     * @return boolean
     */
    protected function checkAuth()
    {
        $authenticated = false;
        $url = $this->rootUrl . '/gui/login/status';
        
        if($this->authArr['sess'] !== false)
        {      
            // Perform cURL authentication check
            $data = $this->curl($url);

            // Check response for errors
            if(!isset($data->error))
            {
                $authenticated = true;
            }
        }

        return $authenticated;
    }

    /**
     * Authenticate User and collect NEW CudaTel session and cookie
     *
     * @return instance
     */
    protected function renewAuth()
    {
        // Set parameters for CudaTel Login
        $auth_params = [
            '__auth_user' => $this->authArr['user'],
            '__auth_pass' => $this->authArr['pass']
        ];

        // Perform CudaTel login
        $url = $this->rootUrl . '/gui/login/login';
        $this->authArr['data'] = $this->curl($url, $auth_params, 'POST');

        die($this->authArr['data']);
        if(!$this->authArr['data'] || isset($this->authArr['data']->error))
        {
            die('Unable to refresh Session - DATA : ' . json_encode($this->authArr['data']));
        }   

        $this->setUserSessionFromCookie();
        
        return $this;     
    }

    /**
     * Run a cURL request (GET or POST) to a CudaTel url
     * @param string $url    - The URL to send the request to (EX: 'http://192.168.1.254/gui/cdr/cdr')
     * @param array  $params - An array of values to send with request
     * @param string $type   - The type of request to make (only needed if POST)
     *
     * @return response
     */
    protected function curl($url, $params = [], $type = 'GET')
    {      
        // Check valid session or create
        if(!$this->authArr['sess'])
        {
            $this->setupAuth();
        }

        $params['sessionid'] = $this->authArr['sess'];

        // Attach root to URL
        $fullURL = $this->rootUrl . $url;

        // Create parameter string
        if(count($params) > 0)
        {
            $param_string = http_build_query($params, NULL, '&');
            $url_w_params = $fullURL . '?' . $param_string;
        }
        
        // Set Type-Sensitive Options and EXECUTE
        switch ($type) {
            case 'POST':
                curl_setopt($this->curlArr['post'], CURLOPT_URL, $fullURL);
                curl_setopt($this->curlArr['post'], CURLOPT_POSTFIELDS, $param_string);
                $data = curl_exec($this->curlArr['post']);
                break;
            
            case 'GET':
                curl_setopt($this->curlArr['get'], CURLOPT_URL, $url_w_params);
                $data = curl_exec($this->curlArr['get']);
                break;
        }

        die($data);

        /*// Set cookie file options based on valid/invalid session        
        if(isset($this->authArr['sess']) && file_exists('/tmp/cudasess' . $this->authArr['user']))
        {            
            curl_setopt($curl, CURLOPT_COOKIEFILE, $this->ckieLoc);
        } 
        else
        {
            curl_setopt($curl, CURLOPT_COOKIEJAR, $this->ckieLoc);
        }*/

        // Pull data with current options -- receive raw response
        $data = curl_exec($curl);
        curl_close($curl);
        die($pull);
        // Parse raw response -- extract JSON string
        $JSON = substr($pull, strpos('{'));

        // De-Code JSON string -- return as stdClass (PHP Object)
        $data = json_decode($JSON);

        return $data;
    }

    /**
     * Check if instance is ready to make requests
     *
     * @return boolean
     */
    protected function isReady()
    {
        if(is_object($this->curlArr['get']) && is_object($this->curlArr['get']))
        {
            if($this->authArr['sess'] !== false)
            {
                return true;
            }
        }

        return false;
    }
    





    /**
     * Run a cURL GET request to a CudaTel url
     * @param string $url EXAMPLE : '/gui/cdr/cdr'
     * @param array $params - An array of parameters to include in the url
     *
     * @return response
     */
    public function get($url, $params)
    {
        $url = $this->rootUrl . $url;

        $data = $this->curl($url, $params);

        die($data);
        return $this->toJSON();
    }
    
    /**
     * Run a cURL POST request to a CudaTel url
     * @param string $url EXAMPLE : '/gui/cdr/cdr'
     * @param array $params - An array of values to post to the url
     *
     * @return response
     */
    public function post($url, $params)
    {
        $url = $this->rootUrl . $url;

        return $this->toJSON($this->curl($url, $params, 'POST'));
    }

    /*
     * Run a URL request (GET or POST) to a CudaTel url
     * @param string $url EXAMPLE : 'http://192.168.1.254/gui/cdr/cdr'
     * @param array  $params - An array of values to add into the url(GET) OR post to the url(POST)
     *
     * @return response

    protected function curl($url, $params = [], $type = 'GET')
    {      
        // Ensure that a new cURL object will be created
        unset($curl);

        // Set "sessionid" parameter if current session is valid
        if($this->authArr['sess'] !== false)
        {
            $params['sessionid'] = $this->authArr['sess'];
        }

        // Create string from parameters
        if(count($params) > 0)
        {
            $param_string = http_build_query($params, NULL, '&');
            $url_w_params = $url . '?' . $param_string;
        }
        
        // Set Type-Sensitive Options
        switch ($type) {
            case 'POST':
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_POST, TRUE);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $param_string);
                break;
            
            case 'GET':
                $curl = curl_init($url_w_params);
                break;
        }

        // Set Type-Insensitive Options
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

        // Set cookie file options based on valid/invalid session        
        if(isset($this->authArr['sess']) && file_exists('/tmp/cudasess' . $this->authArr['user']))
        {            
            curl_setopt($curl, CURLOPT_COOKIEFILE, $this->ckieLoc);
        } 
        else
        {
            curl_setopt($curl, CURLOPT_COOKIEJAR, $this->ckieLoc);
        }

        // Pull data with current options -- receive raw response
        $pull = curl_exec($curl);
        curl_close($curl);
        die($pull);
        // Parse raw response -- extract JSON string
        $JSON = substr($pull, strpos('{'));

        // De-Code JSON string -- return as stdClass (PHP Object)
        $data = json_decode($JSON);

        return $data;
    }     */

    protected function toJSON($input)
    {

        // Set variable to be input before checking type
        $JSON = $input;

        if(is_object($JSON) || is_array($JSON))
        {
            $JSON = json_encode($JSON);
        }

        // Check if created a valid JSON string
        $validJSON = json_decode($JSON);
        
        if($validJSON === null)
        {
            die('Unable to create JSON string from input');
        }
        else
        {
            return $JSON;
        }
    }

    /**
     * Destroy all open cURL objects and close CudaTel sessions
     *
     * @return void
     */
    function __destruct()
    {      
        // Close the shared resources of all cURL objects
        curl_share_close($sh);

        // Close the resources of the GET cURL object
        curl_close($this->curlArr['get']);

        // Close the resources of the POST cURL object
        curl_close($this->curlArr['post']);
    }

/*  MORE helpful cURL options:
    CURLOPT_COOKIESESSION = new session
    CURLOPT_HEADER = show header in output
    CURLOPT_HTTPGET = set type back to get
    CURLOPT_TIMEOUT
    CURLOPT_URL
    CURLOPT_USERPWD // A username and password formatted as "[username]:[password]" to use for the connection.
*/
    function authenticateCredentials(){
        $url = 'http://192.168.1.254/gui/login';
        $ch = curl_init($url);
        $options = array(CURLOPT_POST => TRUE, CURLOPT_POSTFIELDS => '__auth_user=' . $this->session->userdata('ext') . '&__auth_pass=' . $this->session->userdata('pin'), CURLOPT_COOKIEJAR => $this->session->userdata('session_file'), CURLOPT_RETURNTRANSFER => TRUE);
        curl_setopt_array($ch, $options);
        $output = curl_exec ($ch);
        curl_close($ch);
        $login_data = (json_decode($output, TRUE));
        if(isset($login_data['error']) && $login_data['error'] == 'NOTAUTHORIZED'){
            return FALSE;
        } else{
            if(isset($login_data['data'])){
                $logged = $login_data['data'];
            }
            if(isset($logged['bbx_user_username']) && $logged['bbx_user_username'] == $this->session->userdata('ext')){
               return TRUE;
            }
        }
    }

}