<?php

namespace NyroDev\UtilityBundle\Embed\RequestResolvers;

class CurlIPv4 extends \Embed\RequestResolvers\Curl {
	
    /**
     * Resolves the current url and get the content and other data.
	 * Add resolver to IPv4
     */
    protected function resolve()
    {
        $connection = curl_init();

        $tmpCookies = str_replace('//', '/', sys_get_temp_dir().'/embed-cookies.txt');

        curl_setopt_array($connection, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => $this->config['max_redirections'],
            CURLOPT_CONNECTTIMEOUT => $this->config['connection_timeout'],
            CURLOPT_TIMEOUT => $this->config['timeout'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => '',
            CURLOPT_AUTOREFERER => true,
            CURLOPT_COOKIEJAR => $tmpCookies,
            CURLOPT_COOKIEFILE => $tmpCookies,
            CURLOPT_USERAGENT => $this->config['user_agent'],
			CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        ));

        $this->content = curl_exec($connection);
        $this->result = curl_getinfo($connection);

        if ($this->content === false) {
            $this->result['error'] = curl_error($connection);
            $this->result['error_number'] = curl_errno($connection);
        }

        curl_close($connection);

        if (($content_type = $this->getResult('content_type'))) {
            if (strpos($content_type, ';') !== false) {
                list($mimeType, $charset) = explode(';', $content_type);

                $this->result['mime_type'] = $mimeType;

                $charset = substr(strtoupper(strstr($charset, '=')), 1);

                if (!empty($charset) && !empty($this->content) && ($charset !== 'UTF-8')) {
                    $this->content = @mb_convert_encoding($this->content, 'UTF-8', $charset);
                }
            } elseif (strpos($content_type, '/') !== false) {
                $this->result['mime_type'] = $content_type;
            }
        }
    }

}