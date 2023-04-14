<?php

namespace Drupal\livis_api_middleware\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\TempStore\SharedTempStoreFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\livis_api_middleware\LivisApiMiddlewareAuthenticationManager;

/**
 * Middleware for the HOME API.
 */
class LivisApiMiddlewareCitiesController extends ControllerBase {

  /**
   * LIVIS API settings.
   *
   * @var Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * HOME API Authentication MAnager Service.
   *
   * @var \Drupal\livis_api_middleware\LivisApiMiddlewareAuthenticationManager
   */
  protected $authManager;

  /**
   * Guzzle Client for forwarding request.
   *
   * @var GuzzleHttp\Client
   */
  protected $client;

  /**
   * Temporary store factory
   *
   * @var Drupal\Core\TempStore\SharedTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Shared temporary store
   *
   * @var Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempStore;

  /**
   * Token retrieved.
   *
   * @var string
   */
  protected $token;

  /**
   * Indicates if a second attempt to login is allowed and has not happened.
   *
   * @var bool
   */
  private $secondAttemptLeft = TRUE;

  /**
   * Constructor.
   */
  public function __construct(
    LivisApiMiddlewareAuthenticationManager $auth_manager,
    Settings $settings,
    SharedTempStoreFactory $temp_store_factory)
    {
    $this->settings = $settings;
    $this->authManager = $auth_manager;
    $this->client = new Client([
      'base_uri' => $this->settings->get('livis_api')['base_uri'],
    ]);
    $this->tempStore = $temp_store_factory->get('livis_api_middleware');
  }

  /**
   * Gets services from the container for the Controller.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('livis_api_middleware.authentication_manager'),
      $container->get('settings'),
      $container->get('tempstore.shared')
    );
  }

  /**
   * Handle incoming request for inventory endpoint.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Original request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response.
   */
  public function handleRequest(Request $request): JsonResponse {
    $response = $this->authManager->getToken(!$this->secondAttemptLeft);

    if (!isset($response['token'])) {
      return new JsonResponse($response, $response['status_code']);
    }
    else {
      $this->token = $response['token'];
    }

    $method = $request->getMethod();

    if ($method == 'GET') {
      $response = $this->sendApiGetRequest($request);
    }
    else if ($method == 'POST') {
      $response = $this->sendApiPostRequest($request);
    }

    $status_code = $response->getStatusCode();

    if ($status_code == 401 && $this->secondAttemptLeft) {
      $this->secondAttemptLeft = FALSE;
      $this->tempStore->set('expired', TRUE);
      $this->tempStore->delete('token');
      $response = $this->handleRequest($request);
    }
    else if ($status_code == 200 || $status_code == 201) {
      $response = new JsonResponse(json_decode($response->getBody()), $status_code);
    }
    else {
      $response = new JsonResponse(json_decode($response->getBody()->getContents()), $status_code);
    }

    return $response;
  }

  /**
   * Creates and sends the GET request to LIVIS API cities endpoint.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   The original Symfony request.
   *
   * @return GuzzleHttp\Psr7\Response
   *   The API response.
   */
  protected function sendApiGetRequest(Request $request): Response {
    $query = $request->query->all();

    // Converting numeric parameters to integer.
    foreach ($query as $name => $value) {
      if (is_numeric($value)) {
        $query[$name] = intval($value);
      }
    }

    $options = [
      'headers' => [
        'Authorization' => 'Bearer ' . $this->token,
      ],
      'query' => $query,
    ];
    $path = $this->settings->get('livis_api')['cities']['path'];
    try {
      $response = $this->client->request('GET', $path, $options);
    }
    catch (ClientException | ServerException $e) {

      return $e->getResponse();
    }

    return $response;
  }

  /**
   * Creates and sends a POST request to LIVIS API cities endpoint.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *
   * @return GuzzleHttp\Psr7\Response
   */
  protected function sendApiPostRequest(Request $request): Response {
    $body = json_decode($request->getContent());

    $options = [
      'headers' => [
        'Authorization' => 'Bearer ' . $this->token,
      ],
      'json' => $body,
    ];
    $path = $this->settings->get('livis_api')['cities']['path'];
    try {
      $response = $this->client->request('POST', $path, $options);
    }
    catch (ClientException | ServerException $e) {

      return $e->getResponse();
    }

    return $response;
  }

}
