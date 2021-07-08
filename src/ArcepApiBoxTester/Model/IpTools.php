<?php

namespace ArcepApiBoxTester\Model;

class IpTools {

    /**
     * Check IPv4 Address validity
     * @param $string string IP Address to validate
     * @return bool
     */
    static public function isValidIPv4(string $string): bool
    {
        return (filter_var($string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false);
    }

    /**
     * Check IPv6 Address validity
     * @param $string string IP Address to validate
     * @return bool
     */
    static public function isValidIPv6(string $string): bool
    {
        return (filter_var($string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false);
    }

    /**
     * Check if IPv4 address is in IP Range
     * @param $ipAddress string IPv4 address
     * @param $ipArray array Array of ranges ['Network address', netmask]
     * @return bool
     */
    static public function isInIPRange(string $ipAddress, array $ipArray): bool
    {
        $ipAddress = sprintf("%032s", decbin(ip2long($ipAddress)));
        if (empty($ipArray)) return false;
        foreach ($ipArray as $ip) {
            $networkAddress = sprintf("%032s", decbin(ip2long($ip[0])));
            $networkMask = $ip[1];
            if (strcmp(substr($networkAddress, 0, $networkMask), substr($ipAddress, 0, $networkMask)) == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get Client IP Address
     * @return string Client's IP Address
     */
    static public function getClientIP(): string
    {

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $proxy_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            $proxy_ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $proxy_ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
            $proxy_ip = $_SERVER['HTTP_FORWARDED'];
        } elseif (!empty($_SERVER['HTTP_VIA'])) {
            $proxy_ip = $_SERVER['HTTP_VIA'];
        } elseif (!empty($_SERVER['HTTP_X_COMING_FROM'])) {
            $proxy_ip = $_SERVER['HTTP_X_COMING_FROM'];
        } elseif (!empty($_SERVER['HTTP_COMING_FROM'])) {
            $proxy_ip = $_SERVER['HTTP_COMING_FROM'];
        }

        // Avoid returning "unknown" as IP address
        if (!empty($proxy_ip)) {
            $proxy_ip = str_replace('unknown', '', $proxy_ip);
        }

        if (!empty($proxy_ip)) {
            $tmp = array_filter(explode(',', $proxy_ip));
            foreach ($tmp as $address) {
                $address = trim($address);
                if(self::isValidIPv6($address) ||
                    (self::isValidIPv4($address) && !self::isInIPRange($address, [['10.0.0.0', 8], ['192.168.0.0', 16], ['172.16.0.0', 12]]))
                ) {
                    $proxy_ip = $address;
                    break;
                }
            }
            return $proxy_ip;
        }

        // default
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Get API ISP Identifier based on ASN
     * @param $asnumber string ASN
     * @return string Identifier
     */
    static public function getApiIspIdentifier(string $asnumber): string
    {
        switch($asnumber) {
            case 5410:
                return "Bouygues_Telecom";
            case 12322:
                return "Free";
            case 3215:
                return "Orange";
            case 15557:
            case 21502:
                return "SFR";
        }
        return "unknown";
    }
}