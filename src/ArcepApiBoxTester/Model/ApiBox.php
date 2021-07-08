<?php


namespace ArcepApiBoxTester\Model;
use stdClass;

class ApiBox
{
    /**
     * Return API info for ISP identifier
     * @param $ispApiId string ISP Identifier
     * @return stdClass|false
     */
    static public function getApiParams(string $ispApiId) : stdClass
    {
        $apiCredentials = json_decode(file_get_contents($_ENV['API_CREDENTIALS_JSON']));
        if(isset($apiCredentials->isps->$ispApiId))
            return $apiCredentials->isps->$ispApiId;
        return false;
    }
}