<?php
namespace Weixin;

/**
 * Defines a few helper methods.
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 * @deprecated Deprecated since v0.8.3.
 */
class WeixinOAuthRequest
{

    /**
     * Contains the last API call.
     *
     * @ignore
     *
     *
     */
    public $url;

    /**
     * Contains the last HTTP status code returned.
     *
     *
     * @ignore
     *
     *
     */
    public $http_code;

    /**
     * Contains the last HTTP headers returned.
     *
     * @ignore
     *
     *
     */
    public $http_info;

    /**
     * print the debug info
     *
     * @ignore
     *
     *
     */
    public $debug = FALSE;

    /**
     * boundary of multipart
     *
     * @ignore
     *
     *
     */
    public static $boundary = '';

    /**
     * Set timeout default.
     *
     * @ignore
     *
     *
     */
    public $timeout = 30;

    /**
     * Set connect timeout.
     *
     * @ignore
     *
     *
     */
    public $connecttimeout = 30;

    /**
     * Verify SSL Cert.
     *
     * @ignore
     *
     *
     */
    public $ssl_verifypeer = FALSE;

    /**
     * Respons format.
     *
     * @ignore
     *
     *
     */
    public $format = 'json';

    /**
     * Decode returned json data.
     *
     * @ignore
     *
     *
     */
    public $decode_json = TRUE;

    /**
     * Set the useragnet.
     *
     * @ignore
     *
     *
     */
    public $useragent = 'Weixin OAuth2 v0.1';

    /**
     * Set the postdata.
     *
     * @ignore
     *
     *
     */
    public $postdata = '';

    /**
     * Set the http header.
     *
     * @ignore
     *
     *
     */
    public $http_header = array();

    public function __construct()
    {}

    /**
     * POST json信息到指定的URL
     * 
     * @param string $url            
     * @param string $json            
     * @return array
     */
    public function postJson($url, $json)
    {
        // if(!self::isJson($json))
        // throw new WeixinException("不是有效地Json格式数据");
        
        // $client = new Zend_Http_Client();
        // $client->setUri($url);
        // $client->setRawData($json);
        // $client->setEncType(Zend_Http_Client::ENC_URLENCODED);
        // $client->setConfig(array('maxredirects'=>3));
        // $response = $client->request('POST');
        // $message = $response->getBody();
        // $message = preg_replace("/^\xEF\xBB\xBF/", '', $message);
        // $message = preg_replace("/[\n\t\s\r]+/", '', $message);
        // return json_decode($message,true);
    }

    /**
     * 执行GET操作
     * 
     * @param string $url            
     * @param array $params            
     * @return string
     */
    public function doGet($url, $params = array())
    {
        // $client = new Zend_Http_Client();
        // $client->setUri($url);
        // $client->setParameterGet($params);
        // $client->setEncType(Zend_Http_Client::ENC_URLENCODED);
        // $client->setConfig(array('maxredirects'=>3));
        // $response = $client->request('GET');
        // $message = $response->getBody();
        // $message = preg_replace("/^\xEF\xBB\xBF/", '', $message);
        // $message = preg_replace("/[\n\t\s\r]+/", '', $message);
        // return $message;
    }

    /**
     *
     * @ignore
     *
     *
     */
    public static function build_http_query_multi($params)
    {
        if (! $params)
            return '';
        uksort($params, 'strcmp');
        $pairs = array();
        self::$boundary = $boundary = uniqid('------------------');
        $MPboundary = '--' . $boundary;
        $endMPboundary = $MPboundary . '--';
        $multipartbody = '';
        foreach ($params as $parameter => $value) {
            if (in_array($parameter, array(
                'pic',
                'image'
            )) && $value{0} == '@') {
                $url = ltrim($value, '@');
                $content = file_get_contents($url);
                $array = explode('?', basename($url));
                $filename = $array[0];
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"' . "\r\n";
                $multipartbody .= "Content-Type: image/unknown\r\n\r\n";
                $multipartbody .= $content . "\r\n";
            } else {
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
                $multipartbody .= $value . "\r\n";
            }
        }
        $multipartbody .= $endMPboundary;
        return $multipartbody;
    }

    /**
     * Format and sign an OAuth / API request
     *
     * @return string
     * @ignore
     *
     *
     */
    protected function oAuthRequest($url, $method, $parameters, $multi = false)
    {
        switch ($method) {
            case 'GET':
                $url = $url . '?' . http_build_query($parameters);
                return $this->http($url, 'GET');
            default:
                $headers = array();
                if (! $multi) {
                    if ((is_array($parameters) || is_object($parameters))) {
                        $body = http_build_query($parameters);
                    } else {
                        $body = $parameters;
                        $headers[] = "Content-Length: " . strlen($body);
                    }
                } else {
                    $body = self::build_http_query_multi($parameters);
                    $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
                }
                return $this->http($url, $method, $body, $headers);
        }
    }

    /**
     * Make an HTTP request
     *
     * @return string API results
     * @ignore
     *
     *
     */
    protected function http($url, $method, $postfields = NULL, $headers = array())
    {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array(
            $this,
            'getHeader'
        ));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (! empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (! empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }
        // if (isset($this->access_token) && $this->access_token)
        // $headers[] = "Authorization: OAuth2 " . $this->access_token;
        $headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;
        if ($this->debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);
            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ci));
            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return $response;
    }

    /**
     * Get the header info to store.
     *
     * @return int
     * @ignore
     *
     *
     */
    public function getHeader($ch, $header)
    {
        $i = strpos($header, ':');
        if (! empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }

    /**
     * GET wrappwer for oAuthRequest.
     *
     * @return mixed
     */
    public function get($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * POST wreapper for oAuthRequest.
     *
     * @return mixed
     */
    public function post($url, $parameters = array(), $multi = false)
    {
        $response = $this->oAuthRequest($url, 'POST', $parameters, $multi);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * DELTE wrapper for oAuthReqeust.
     *
     * @return mixed
     */
    public function delete($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'DELETE', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }
}
