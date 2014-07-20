<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 19.04.12 12:08
 */
/**
 * Request operator
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class slRequest {

    private $_handler       = null;
    private $_response      = null;
    private $_host          = null;
    private $_agent         = 'Mozilla/5.0 (Windows NT 5.1; rv:8.0) Gecko/20100101 Firefox/8.0';

    private static $_auth   = null;

    static public function sendRequest($host, $uri = '/', $params = array(), $port = 80, $get_params = array(), $post_params = array(), $attachments = array(), $cookie_params = array(), $custom_headers = array(), $custom_body = '') {
        $method = isset($params['method']) ? strtoupper($params['method']) : slRequestMethod::GET;
        $protocol = isset($params['protocol']) ? $params['protocol'] : 'http';
        $host_prefix = 'http'. ($protocol == 'ssl' ? 's' : '') . '://';

//        if ($uri[strlen($uri) - 1] !== '/') $uri .= '/';

        $result        = '';
//        $referer       = 'http://' . $host;
        $agent         = 'Mozilla/5.0';
        $timeout       = 30;

        $request       = '';
        $get_string    = '?';
        $post_string   = '';
        $cookie_string = '';
        $crlf          = "\r\n";

        foreach($get_params as $k=>$v) {
            $get_string .= rawurlencode($k) . '=' .rawurlencode($v) . '&';
        }
        $get_string  = mb_substr($get_string, 0, -1);

        foreach($cookie_params as $k=>$v) {
            $cookie_string .= rawurlencode($k) . '='
                . strtr($v,
                    array_combine(str_split($tmp = ",; \t\r\n\013\014"),
                        array_map('rawurlencode', str_split($tmp))
                    )
                );
        }

        $request     = $method . ' ' . $host_prefix . $host . '/' . $uri . $get_string . ' HTTP/1.1' . $crlf;
        $request    .= 'Host: ' . $host . $crlf;
        $request    .= 'User-agent: ' . $agent . $crlf;
        $request    .= 'Accept: */*' . $crlf;
        $request    .= 'Accept-Language: *' . $crlf;
        $request    .= 'Accept-Encoding: *' . $crlf;
        $request    .= 'Accept-Charset: *' . $crlf;

        if (self::$_auth) {
            $request .=  'Authorization: Basic '.base64_encode(self::$_auth) . $crlf;
        }

        foreach($custom_headers as $k=>$v) {
            $request .= $k .': '. $v . $crlf;
        }

//        $request    .= 'Referer: ' . $referer . $crlf;
        if ($cookie_params) {
            $request    .= 'Cookie: ' . $cookie_string . $crlf;
        }
        $request    .= 'Connection: close' . $crlf;

        $content_type = isset($params['content_type']) ? $params['content_type'] : 'application/x-www-form-urlencoded';

        if ((($method == slRequestMethod::POST) || ($method == slRequestMethod::PUT)) && (!empty($post_params) || !empty($attachments) || !empty($custom_body))) {
            if (empty($attachments)) {
                $request .= 'Content-Type: ' . $content_type . $crlf;

                if (!is_array($post_params)) $post_params = array($post_params);
                foreach($post_params as $k=>$v) {
                    $post_string .= rawurlencode($k) . '=' .rawurlencode($v) . '&';
                }
                if ($post_string) $post_string = mb_substr($post_string, 0, -1);
            } else {
                $boundary = md5(uniqid(time()));
                $request .= 'Content-Type: multipart/form-data; boundary=' . $boundary .$crlf;
                foreach($post_params as $k=>$v) {
                    $post_string .= '--'.$boundary . $crlf;
                    $post_string .= 'Content-Disposition: form-data; name="'.$k. '"' . $crlf . $crlf .$v. $crlf;
                }
                foreach($attachments as $var_name=>$file_path) {
                    if (is_file($file_path)) {
                        $post_string .= '--'.$boundary . $crlf;
                        $file_name = basename($file_path);
                        $post_string .= 'Content-Disposition: form-data; name="'.$var_name.'"; filename="'
                                     . $file_name . '"' . $crlf;
                        $post_string .= 'Content-Type: application/octet-stream' . $crlf . $crlf;
                        $post_string .= file_get_contents($file_path) . $crlf;
                    }
                }
                $post_string .= '--' . $boundary . '--' . $crlf;
            }
            $request .= 'Content-Length: '. (strlen($post_string) + strlen($custom_body)) . $crlf .$crlf;
            if ($post_string) $request .= $post_string . $crlf;
            if ($custom_body) $request .= $custom_body . $crlf;
        }
        $request    .= $crlf;
//        vd($request);
        $error_number   = null;
        $error_message  = '';
        $handler = fsockopen((($protocol == 'ssl') ? 'ssl://' : '' ) .$host, $port, $error_number, $error_message, $timeout);

        fputs($handler, $request);
        $response_headers = array();
        $response_data = '';
        $process_headers = true;
        while ($line = fgets($handler)) {
            if (($line == "\n") || ($line == "\r\n")) $process_headers = false;
            $process_headers ? $response_headers[] = $line : $response_data .= $line;
        }
        fclose($handler);

        if (strpos(strtolower(var_export($response_headers, true)), "transfer-encoding: chunked") !== false) {
            $response_data = self::unchunk($response_data);
        }
        if (strpos(strtolower(var_export($response_headers, true)), "content-encoding: gzip") !== false) {
            $response_data = gzinflate(substr($response_data, 10, -8));//gzdecode($results);
        }
        else if (strpos(strtolower(var_export($response_headers, true)), "content-encoding: deflate") !== false) {
            $response_data = gzinflate($response_data);
        }
        return array('headers' => $response_headers, 'data' => $response_data);
    }

    static public function parseCookies($headers) {
        $cookies = array();
        for ($i = 0, $cnt = count($headers); $i < $cnt; ++$i) {
            $headers[$i] = rtrim($headers[$i], "\r\n");
            $pos         = strpos($headers[$i], 'Set-Cookie: ');
            if ($pos !== false) {
                $pos_end = strpos($headers[$i], ';', $pos);
                if ($pos_end === false) {
                    $pos_end = strlen($headers[$i]);
                }
                $tmp = substr($headers[$i], $pos + 12, $pos_end - $pos - 12);
                if (!$tmp) {
                    continue;
                }
                $pos1 = strpos($tmp, '=');
                $cookies[substr($tmp, 0, $pos1)] = '' . substr($tmp, $pos1 + 1);
            }
        }
        return $cookies;
    }

    static public function setAuthorization($params = false) {
        self::$_auth = $params;
    }

    static private function unchunk($data) {
        return preg_replace('/([0-9A-F]+)\r\n(.*)/sie',
            '($cnt=@base_convert("\1", 16, 10))
                               ?substr(($str=@strtr(\'\2\', array(\'\"\'=>\'"\', \'\\\\0\'=>"\x00"))), 0, $cnt).slRequest::unchunk(substr($str, $cnt+2))
                               :""
                              ',
            $data
        );
    }

}
