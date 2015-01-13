<?php 
class Curl {

    protected $handler;
    protected $options;
    protected $request;

    public function __construct()
    {
        $this->options = [
            'server' => 'cudatel',
               'url' => null,
              'post' => null,
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
        $this->request = [
            'url' => null,
            'params' => []
        ];

    }

    public function fetch( $curlArray = [] )
    {
        $requestArray = array_merge($this->request, $curlArray);
        $request = $this->createRequest($requestArray);
        $this->handler = $this->curlHandle($request);
    }

    function createRequest($requestArray)
    {
        if( ! isset($requestArray['url'])) die('You must include a URL with your request');
        if( isset($requestArray['params']) && is_array($requestArray['params']))
        {
            $request['']$query_string = http_build_query($params, NULL, '&');
        }




        if($this->isDynamic($url))
        {
            //TODO
            if( isset($params['post']) )         curl_setopt( $ch, CURLOPT_POSTFIELDS, $params['post'] );
            if( isset($params['refer']) )        curl_setopt( $ch, CURLOPT_REFERER, $params['refer'] );
        }

        return $url;

    }

    function curlHandle($request)
    {
        $options = array_merge($this->options, $request);
        $this->handler =  curl_init();

        curl_setopt( $ch, CURLOPT_URL, $options['url'] );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_POST, isset($request['post']) );

        if( isset($request['post']) )         curl_setopt( $ch, CURLOPT_POSTFIELDS, $request['post'] );
        if( isset($request['refer']) )        curl_setopt( $ch, CURLOPT_REFERER, $request['refer'] );

        curl_setopt( $ch, CURLOPT_USERAGENT, $useragent );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, ( isset($request['timeout']) ? $request['timeout'] : 5 ) );
        curl_setopt( $ch, CURLOPT_COOKIESESSION, true );
        curl_setopt( $ch, CURLOPT_COOKIEJAR,  $request['cookiefile'] );
        curl_setopt( $ch, CURLOPT_COOKIEFILE, $request['cookiefile'] );

        $result = curl_exec( $ch );
        curl_close( $ch );
        return $result;
    }

    protected function isDynamic($url)
    {
        if(substr($url, 0, 6) != 'http://')
        {
           return false;
        }

        return true;
    }

    protected function getServerUrl($server)
    {
        switch($server)
        {
            case 'alvin':
                return 'http://' . $_SERVER['HTTP_HOST'];
                break;
            case 'cudatel':
                return 'http://192.168.1.254';
                break;
        }
    }
}
