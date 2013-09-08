<?php

namespace Qiniu;

class Response
{
    public $code;
    public $protocol;
    public $message;
    public $headers;
    public $body;
    public $data;
    public $error;

    /**
     * @param string      $response
     * @param bool|string $error
     */
    public function __construct($response, $error = false)
    {
        if (is_string($response)) {
            $this->parseResponse($response);
        }
        $this->error = $error;
    }

    /**
     * Parse response
     *
     * @param $response
     */
    public function parseResponse($response)
    {
        $body_pos = strpos($response, "\r\n\r\n");
        $header_string = substr($response, 0, $body_pos);
        $header_lines = explode("\r\n", $header_string);

        $headers = array();
        $code = false;
        $body = false;
        $protocol = null;
        $message = null;

        foreach ($header_lines as $index => $line) {
            if ($index === 0) {
                preg_match("/^(HTTP\/\d\.\d) (\d{3}) (.*?)$/", $line, $match);
                list(, $protocol, $code, $message) = $match;
                continue;
            }
            list($key, $value) = explode(":", $line);
            $headers[strtolower(trim($key))] = trim($value);
        }

        if (is_numeric($code)) {
            $body_string = substr($response, $body_pos + 4);
            if (!empty($headers['transfer-encoding']) && $headers['transfer-encoding'] == 'chunked') {
                $body = self::chuckDecode($body_string);
            } else {
                $body = (string)$body_string;
            }
            $result['header'] = $headers;
        }

        $this->headers = $headers;
        $this->body = $body;
        $this->code = (int)$code;
        $this->message = $message;
        $this->protocol = $protocol;

        if (strpos($this->headers['content-type'], 'json')) {
            $this->data = json_decode($body, true);
        }
    }

    /**
     * Get header
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    public function header($key, $default = null)
    {
        $key = strtolower($key);
        return !isset($this->headers[$key]) ? $this->headers[$key] : $default;
    }

    /**
     * Is error?
     *
     * @return bool
     */
    public function isError()
    {
        return !!$this->error;
    }


    /**
     * Is response cachable?
     *
     * @return bool
     */
    public function isCachable()
    {
        return $this->code >= 200 && $this->code < 300 || $this->code == 304;
    }

    /**
     * Is empty?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return in_array($this->code, array(201, 204, 304));
    }

    /**
     * Is 200 ok?
     *
     * @return bool
     */
    public function isOk()
    {
        return $this->code === 200;
    }

    /**
     * Is successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->code >= 200 && $this->code < 300;
    }

    /**
     * Is redirect?
     *
     * @return bool
     */
    public function isRedirect()
    {
        return in_array($this->code, array(301, 302, 303, 307));
    }

    /**
     * Is forbidden?
     *
     * @return bool
     */
    public function isForbidden()
    {
        return $this->code === 403;
    }

    /**
     * Is found?
     *
     * @return bool
     */
    public function isNotFound()
    {
        return $this->code === 404;
    }

    /**
     * Is client error?
     *
     * @return bool
     */
    public function isClientError()
    {
        return $this->code >= 400 && $this->code < 500;
    }

    /**
     * Is server error?
     *
     * @return bool
     */
    public function isServerError()
    {
        return $this->code >= 500 && $this->code < 600;
    }

    /**
     * Decode chunk
     *
     * @param $str
     * @return string
     */
    protected static function chuckDecode($str)
    {
        $body = '';
        while ($str) {
            $chunk_pos = strpos($str, "\r\n") + 2;
            $chunk_size = hexdec(substr($str, 0, $chunk_pos));
            $str = substr($str, $chunk_pos);
            $body .= substr($str, 0, $chunk_size);
        }
        return $body;
    }
}