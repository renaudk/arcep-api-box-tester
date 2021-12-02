<?php
namespace ArcepApiBoxTester\Model;
use GuzzleHttp\Exception\BadResponseException;
use InvalidArgumentException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

class OAuthProvider extends AbstractProvider {

    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    private $urlAccessToken;

    /**
     * @var string
     */
    private $authLogin;

    /**
     * @var string
     */
    private $authPassword;

    /**
     * @var string
     */
    private $clientIpAddress;

    /**
     * @var string
     */
    private $responseError = 'error';

    /**
     * @var string
     */
    private $responseCode;

    /**
     * @var string
     */
    private $responseMessage;

    /**
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        $this->assertRequiredOptions($options);

        $possible   = $this->getConfigurableOptions();
        $configured = array_intersect_key($options, array_flip($possible));

        foreach ($configured as $key => $value) {
            $this->$key = $value;
        }

        // Remove all options that are only used locally
        $options = array_diff_key($options, $configured);

        parent::__construct($options, $collaborators);
    }

    public function getBaseAuthorizationUrl()
    {
        return null;
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->urlAccessToken;
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return null;
    }

    protected function getDefaultScopes()
    {
        return null;
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data[$this->responseError])) {
            $error = $data[$this->responseError];
            if (!is_string($error)) {
                $error = var_export($error, true);
            }
            $code  = $this->responseCode && !empty($data[$this->responseCode])? $data[$this->responseCode] : 0;
            if (!is_int($code)) {
                $code = intval($code);
            }
            throw new IdentityProviderException($error, $code, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return null;
    }

    /**
     * Returns all options that can be configured.
     *
     * @return array
     */
    protected function getConfigurableOptions(): array
    {
        return array_merge($this->getRequiredOptions(), []);
    }

    /**
     * Returns all options that are required.
     *
     * @return array
     */
    protected function getRequiredOptions(): array
    {
        return [
            'urlAccessToken',
            'authLogin',
            'authPassword',
            'clientIpAddress',
        ];
    }

    /**
     * Verifies that all required options have been passed.
     *
     * @param  array $options
     * @return void
     * @throws InvalidArgumentException
     */
    private function assertRequiredOptions(array $options)
    {
        $missing = array_diff_key(array_flip($this->getRequiredOptions()), $options);

        if (!empty($missing)) {
            throw new InvalidArgumentException(
                'Required options not defined: ' . implode(', ', array_keys($missing))
            );
        }
    }

    /**
     * Inject X-Subscriber-IP HTTP header on access token request
     * @param array $params
     * @return RequestInterface
     */
    protected function getAccessTokenRequest(array $params): RequestInterface
    {
        $request = parent::getAccessTokenRequest($params);
        $request = $request->withHeader('X-Subscriber-IP', $this->clientIpAddress);
        return $request;
    }

    /**
     * Sends a request and returns the parsed response.
     *
     * @param  RequestInterface $request
     * @throws IdentityProviderException
     * @return mixed
     */
    public function getParsedResponse(RequestInterface $request)
    {
        try {
            $response = $this->getResponse($request);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            $this->responseCode = $e->getResponse()->getStatusCode();
            $this->responseMessage = $e->getResponse()->getReasonPhrase();
        }

        $parsed = $this->parseResponse($response);

        $this->checkResponse($response, $parsed);

        return $parsed;
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function getResponse(RequestInterface $request): ResponseInterface
    {
        return $this->getHttpClient()->send($request,['auth' => [$this->authLogin, $this->authPassword]]);
    }

    /**
     * Requests an access token using a specified grant and option set.
     *
     * @param  mixed $grant
     * @param  array $options
     * @throws IdentityProviderException
     * @return AccessTokenInterface
     */
    public function getAccessToken($grant, array $options = []): AccessTokenInterface
    {
        $grant = $this->verifyGrant($grant);

        $params = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
        ];

        $params   = $grant->prepareRequestParameters($params, $options);
        $request  = $this->getAccessTokenRequest($params);
        $response = $this->getParsedResponse($request);
        if (false === is_array($response)) {
            if($this->responseCode > 200) {
                throw new UnexpectedValueException(
                    'Invalid response received from Authorization Server: '.$this->responseCode.' '.$this->responseMessage
                );
            } else throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }
        $prepared = $this->prepareAccessTokenResponse($response);
        $token    = $this->createAccessToken($prepared, $grant);

        return $token;
    }
    protected function getAllowedClientOptions(array $options)
    {
        $client_options = ['cert', 'ssl_key'];

        $parent_options = parent::getAllowedClientOptions($options);

        return array_merge($parent_options, $client_options);
    }
}
