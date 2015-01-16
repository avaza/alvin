<?php 
class Curl {

    /**
     * @var
     */
    protected $handler;
    /**
     * @var array
     */
    protected $curlOpt;
    /**
     * @var
     */
    protected $request;

    /**
     *
     */
    public function __construct()
    {
        $this->$curlOpt = [
            'server' => 'cudatel',
            'type' => 'GET',
            'cookie' => [
                'storage' => '/opt/alvin/cache/',
                'filename' => "",
                'session' => true,
                'head_value' => null
            ],
            'request' => [
                'referrer' => null,
                'autoReferrer' => true,
                'connectTimeout' => 5,
                'followLocation' => true,
                'returnTransfer' => true,
                'userAgent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2'
            ]
        ];
    }

    /**
     * @param null  $url
     * @param array $params
     * @param array $options
     * @return mixed
     */
    public function fetch( $url = null, $params = [], $options = [] )
    {
        $this->curlOpt = array_merge($this->curlOpt, $options);

        $this->request = [
            'url' => $url,
            'params' => $params
        ];

        if(isset($this->request['url']))
        {
            return $this->executeCurl();
        }

        die('You must include a URL');
    }

    /**
     * @return mixed
     */
    protected function executeCurl()
    {
        $this->createRequest();
        $this->createHandler();

        return $this->executeHandle();
    }

    /**
     * @return $this
     */
    function createRequest()
    {
        $this->request['url'] = $this->buildUrl();
        $this->request['query'] = $this->buildQuery();

        return $this;
    }

    /**
     * @return $this
     */
    function createHandler()
    {
        $this->handler =  curl_init();

        if( $this->curlOpt['type'] == 'POST' ){
            curl_setopt( $this->handler, CURLOPT_POST, true );
            curl_setopt( $this->handler, CURLOPT_POSTFIELDS, $this->request['query'] );
        }

        $cookieFile = $this->curlOpt['cookie']['storage'] . $this->curlOpt['cookie']['filename'];
        curl_setopt( $this->handler, CURLOPT_URL, $this->request['url'] );
        curl_setopt( $this->handler, CURLOPT_USERAGENT, $this->curlOpt['request']['userAgent'] );
        curl_setopt( $this->handler, CURLOPT_COOKIEJAR,  $cookieFile );
        curl_setopt( $this->handler, CURLOPT_COOKIEFILE, $cookieFile );
        curl_setopt( $this->handler, CURLOPT_AUTOREFERER, $this->curlOpt['request']['autoReferrer'] );
        curl_setopt( $this->handler, CURLOPT_COOKIESESSION, $this->curlOpt['cookie']['session'] );
        curl_setopt( $this->handler, CURLOPT_RETURNTRANSFER, $this->curlOpt['request']['returnTransfer'] );
        curl_setopt( $this->handler, CURLOPT_FOLLOWLOCATION, $this->curlOpt['request']['followLocation'] );
        curl_setopt( $this->handler, CURLOPT_CONNECTTIMEOUT, $this->curlOpt['request']['userAgent'] );

        return $this;
    }

    /**
     * @return mixed
     */
    protected function executeHandle()
    {
        $result = curl_exec( $this->handler );
        curl_close( $this->handler );
        $this->request = [
            'url' => null,
            'params' => []
        ];

        return $result;
    }

    /**
     * @return string
     */
    protected function buildUrl()
    {
        $url = $this->request['url'];

        if($this->isNotQualified($url))
        {
            $server = $this->getServerUrl($this->curlOpt['server']);
            $url = substr($server, -1) == '/' ? $server . $url : $server . '/' . $url;
        }

        if($this->curlOpt['type'] == 'GET')
        {
            $url .= "?" . $this->buildQuery();
        }

        return $url;
    }

    /**
     * @param $url
     * @return bool
     */
    protected function isNotQualified($url)
    {
        if(substr($url, 0, 6) != 'http://')
        {
           return false;
        }

        return true;
    }

    /**
     * @param $server
     * @return mixed
     */
    protected function getServerUrl($server)
    {
        $urls = [
            'alvin' => 'http://' . $_SERVER['HTTP_HOST'],
            'cudatel' => 'http://192.168.1.254'
        ];

        return $urls[$server];
    }

    /**
     * @return string
     */
    protected function buildQuery()
    {
        if( is_array($this->request['params']) && !empty($this->request['params']))
        {
            return http_build_query($this->request['params'], NULL, '&');
        }

        return "";
    }
}
