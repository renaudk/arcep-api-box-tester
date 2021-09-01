<?php /** @noinspection PhpPrivateFieldCanBeLocalVariableInspection */

namespace ArcepApiBoxTester\Controller;
use ArcepApiBoxTester\Model\ApiBox;
use ArcepApiBoxTester\Model\DotEnv;
use ArcepApiBoxTester\Model\IpTools;
use BadMethodCallException;
use Exception;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use ArcepApiBoxTester\Model\OAuthProvider;
use GeoIp2\Model\Asn;
use GeoIp2\Model\Isp;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use MaxMind\Db\Reader\InvalidDatabaseException;

class Tester {

    private $IP;
    private $IPVersion;
    private $Host;
    private $ASN;
    private $ApiIspId;
    private $ApiParams;
    private $Error;
    private $Env;
    private $ASNOrg;
    private $ISP;

    public function MainLoader()
    {
        // Get environment from cookie (specific ARCEP tool feature)
        $this->Env = "cap"; // default environment
        if(isset($_COOKIE['env']) && in_array($_COOKIE['env'], ["cap", "staging", "production"])) {
            $this->Env = $_COOKIE['env'];
        } else {
            setcookie("env", $this->Env);
        }
        $envFile = '../.'.$this->Env.'.env';

        // Init environment variables
        try {
            (new DotEnv($envFile))->load();
        } catch(Exception $e) {
            $this->Error = "Failed to load environment variables. Please make sure .env file is created in project's";
            $this->Error .= "root directory.<br/>";
            $this->Error .= $e->getMessage();
        }

        // OPTIONAL: Script to handle access restriction
        if(isset($_ENV['ACCESS_SCRIPT'])) {
            /** @noinspection PhpIncludeInspection */
            require $_ENV['ACCESS_SCRIPT'];
        }

        if(isset($_ENV['MAXMIND_MMDB_FILE'])) {

            // Init maxmind reader
            try {
                $reader = new Reader($_ENV['MAXMIND_MMDB_FILE']);
            } catch (Exception $e) {
                $this->Error = $e->getMessage();
                $this->Error .= "<br/>Make sure Maxmind GeoIP database is present.";
            }

            if (isset($reader)) {
                // Get client's IP address
                $this->IP = IpTools::getClientIP();

                // Set IP Version
                $this->IPVersion = (IpTools::isValidIPv6($this->IP) ? '6' : '4');

                // Set Host
                $this->Host = gethostbyaddr($this->IP);

                // Grab ISP info from Maxmind database
                try {
                    // Try ASN method first
                    $this->_setIspInfo($reader->asn($this->IP));
                } catch (AddressNotFoundException $e) {
                    $this->Error = "Address not found in database: " . $this->IP;
                } catch (BadMethodCallException $e) {
                    // If ASN Method doesn't exists, we are probably using ISP database, let's try ISP method
                    try {
                        $this->_setIspInfo($reader->isp($this->IP));
                    } catch (AddressNotFoundException $e) {
                        $this->Error = "Address not found in database: " . $this->IP;
                    } catch (BadMethodCallException $e) {
                        $this->Error = "Maxmind database doesn't provide ASN info, please use GeoLite2-ASN or ";
                        $this->Error .= "GeoIP2-ISP.";
                    } catch (InvalidDatabaseException $e) {
                        $this->Error = "Maxmind invalid database.";
                    }
                } catch (InvalidDatabaseException $e) {
                    $this->Error = "Maxmind invalid database.";
                }

                // Override ISP for Cap environment
                if(!isset($this->Error) && $this->Env == "cap") {
                    $this->ApiIspId = "Bouygues_Telecom";
                    $this->ApiParams = ApiBox::getApiParams($this->ApiIspId);
                }
            }
        }

        // Routing based on "cmd" GET param
        if(isset($this->Error)) {
            http_response_code(500);
            switch(@$_GET['cmd']) {
                case 'getAccessToken':
                    $this->_jsonResponse([]);
                    break;
                default:
                    $this->_errorPage();
            }
        } else {
            switch (@$_GET['cmd']) {
                case 'getAccessToken':
                    $this->_getAccessToken();
                    break;
                default:
                    $this->_mainPage();
            }
        }
    }

    /**
     * Fill ISP/ASN information using Maxmind DB
     * @param $isp Asn|Isp ASN or ISP Maxmind model
     */
    private function _setIspInfo($isp) {
        // Set ISP info (by Maxmind)
        $this->ISP = $isp;

        // Set ASN (source MaxMind)
        $this->ASN = $this->ISP->autonomousSystemNumber;

        // Set ASN Organization (source MaxMind)
        $this->ASNOrg = $this->ISP->autonomousSystemOrganization;

        // API ISP Identification (based on ASN)
        if ($this->ASN > 0) {
            $ApiIspId = IpTools::getApiIspIdentifier($this->ASN);
            $this->ApiIspId = $ApiIspId;
            $this->ApiParams = ApiBox::getApiParams($ApiIspId);
        } else {
            $this->ASN = "unknown";
            $this->ApiIspId = "unknown";
        }
    }

    private function _mainPage() {
        require __DIR__.'/../Views/MainPage.php';
    }

    private function _errorPage() {
        require __DIR__.'/../Views/Error.php';
    }

    private function _getAccessToken() {
        // Get input as JSON
        $input = json_decode(file_get_contents("php://input"));

        if(!isset($input->ipv4) || !isset($input->ipv6)) {
            $this->_jsonResponse(['error'=>'Bad JSON payload!']);
            return;
        }

        if(!isset($input->ipv4->IPAddress) && !isset($input->ipv6->IPAddress)) {
            $this->_jsonResponse(['error'=>'No IP address reported in payload!']);
            return;
        }

        // Default behavior : use default stack.
        $IP = $this->IP;

        switch($this->ApiParams->ipStack) {
            case "ipv4Only":
                if(isset($input->ipv4->IPAddress)) $IP = $input->ipv4->IPAddress;
                else {
                    $this->_jsonResponse(['error'=>'No IPv4 address reported in payload while \"ipStack\" is set to \"ipv4Only\" !']);
                    return;
                }
                break;
            case "ipv6Only":
                if(isset($input->ipv6->IPAddress)) $IP = $input->ipv6->IPAddress;
                else {
                    $this->_jsonResponse(['error'=>'No IPv6 address reported in payload while \"ipStack\" is set to \"ipv6Only\" !']);
                    return;
                }
                break;
            case "ipv4Preferred":
                if(isset($input->ipv4->IPAddress)) $IP = $input->ipv4->IPAddress;
                else $IP = $input->ipv6->IPAddress;
                break;
            case "ipv6Preferred":
                if(isset($input->ipv6->IPAddress)) $IP = $input->ipv6->IPAddress;
                else $IP = $input->ipv4->IPAddress;
                break;
        }

        try {
            // Instantiate OAuth provider
            $provider = new OAuthProvider(array_merge((array)$this->ApiParams,[
                'clientIpAddress'         => $IP
            ]));

            // Try to get an access token using the client credentials grant.
            $accessToken = $provider->getAccessToken('client_credentials');

            // Send response
            $this->_jsonResponse($accessToken);
        } catch (Exception $e) {
            // Failed to get the access token
            $error = 'OAuth2 Client initialization error: '.$e->getMessage();
            if($e instanceof IdentityProviderException && isset($e->getResponseBody()['errorDescription'])) {
                $error .= ' Desc: ' . $e->getResponseBody()['errorDescription'];
            }
            $this->_jsonResponse(['error'=>$error]);
        }
    }

    private function _jsonResponse($array) {
        // Set JSON Content-Type HTTP header
        header('Content-Type: application/json');

        // Handle error or send response
        if(isset($this->Error)) {
            echo json_encode([
                'error2' => $this->Error
            ]);
        } else echo json_encode($array);
    }
}
