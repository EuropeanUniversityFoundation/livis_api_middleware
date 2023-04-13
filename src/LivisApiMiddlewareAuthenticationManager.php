<?php

namespace Drupal\livis_api_middleware;

use GuzzleHttp\Client;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Drupal\Core\TempStore\SharedTempStoreFactory;

/**
 * Service for managing HOME API authentication.
 */
class LivisApiMiddlewareAuthenticationManager {

  /**
   * Login crendetials to LIVIS API.
   *
   * @var array
   */
  private $credentials;

  /**
   * Settings for LIVIS API.
   *
   * @var Drupal\Core\Site\Settings
   */
  private $settings;

  /**
   * Guzzle Client for authenticating.
   *
   * @var GuzzleHttp\Client
   */
  protected $authClient;

  /**
   * Drupal SharedTempStoreFactory.
   *
   * @var Drupal\Core\TempStore\SharedTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Drupal SharedTempStore created by Factory.
   *
   * @var Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempStore;

  /**
   * JWT Token for LIVIS API endpoints.
   *
   * @var string
   */
  private $token;

  /**
   * Constructor.
   */
  public function __construct(SharedTempStoreFactory $temp_store_factory, Settings $settings) {
    $this->settings = $settings;
    $this->credentials = [
      'email' => $this->settings->get('livis_api')['credentials']['username'],
      'password' => $this->settings->get('livis_api')['credentials']['password'],
    ];

    $this->authClient = new Client([
      'base_uri' => $this->settings->get('livis_api')['login']['base_uri'],
    ]);

    // Creates or retrieves SharedTempStore.
    $this->tempStoreFactory = $temp_store_factory;
    $this->tempStore = $this->tempStoreFactory->get('livis_api_middleware');

    // Sets token data saved in tempStore.
    $this->token = $this->tempStore->get('token');
  }

  /**
   * Fetches token from LIVIS login endpoint.
   *
   * @return \GuzzleHttp\Psr7\Response
   *   Returns the response.
   */
  public function getToken($force_renew = FALSE): array {
    if ($this->tokenSaved() && !$force_renew) {
      return [
        'token' => $this->token,
      ];
    }

    // echo ('Old token:' . $this->token);
    $options = [
      'json' => $this->credentials,
    ];
    $path = $this->settings->get('livis_api')['login']['path'];

    try {
      $response = $this->authClient->request('POST', $path, $options);
    }
    catch (ClientException | ServerException $e) {
      $error = $this->getError($e);
      return $error;
    }

    $body = json_decode($response->getBody()->getContents());

    $this->tempStore->set('token', $body->token);
    $this->token = $body->token;

    return [
      'token' => $this->token,
    ];
  }

  /**
   * Checks if token is saved to tempstore.
   *
   * @return bool
   *   Returns if saved token should be used.
   */
  protected function tokenSaved(): bool {
    $is_saved = !is_null($this->tempStore->get('token'));

    return $is_saved;
  }

  /**
   * Gets error from exception response.
   *
   * @param \GuzzleHttp\ClientException|\GuzzleHttp\ServerException $exception
   *   Incoming exception.
   *
   * @return array
   *   Array with message and status_code keys.
   */
  protected function getError($exception) {
    $message = $exception->getMessage();
    $status_code = $exception->getResponse()->getStatusCode();

    return [
      'message' => $message,
      'status_code' => $status_code,
    ];
  }

}
