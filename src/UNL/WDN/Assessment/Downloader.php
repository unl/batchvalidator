<?php
class UNL_WDN_Assessment_Downloader extends Spider_Downloader
{
    private $curl = null;

    public function __construct()
    {
        $this->curl = curl_init();

        curl_setopt_array(
            $this->curl,
            array(
                CURLOPT_AUTOREFERER    => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'silverorange-spider',
                CURLOPT_HEADERFUNCTION  => function($ch, $header){
                    //Extract header data
                    $parts = explode(':', $header);

                    //We need a key value pair, so fail early if only one item was found
                    if (count($parts) != 2) {
                        return strlen($header);
                    }

                    //Get the key
                    $key = $parts[0];

                    //Get the value
                    $value = trim($parts[1]);

                    //We are only looking for the content-type, fail early
                    if (strtolower($key) != 'content-type') {
                        return strlen($header);
                    }

                    //Only accept these content types
                    $accept_headers = array('text/html', 'application/xhtml+xml');

                    //The value can be formatted like 'text/html; charset=iso-8859-1', so we need to parse it.
                    //We don't care about the second parameter (charset=iso-8859-1)
                    $media_type_data = explode(';', $value);

                    //is it acceptable?
                    if (in_array(strtolower(trim($media_type_data[0])), $accept_headers)) {
                        return strlen($header);
                    }

                    //Not an acceptable content type, don't download
                    return false;
                }
            )
        );
    }

    public function download($uri)
    {
        curl_setopt($this->curl, CURLOPT_URL, $uri);
        $result = curl_exec($this->curl);
        if (!$result) {
            throw new Exception('Error downloading ' . $uri. $result);
        }
        return $result;
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }
}