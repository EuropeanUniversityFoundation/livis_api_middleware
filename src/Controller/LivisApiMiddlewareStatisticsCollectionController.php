<?php

namespace Drupal\livis_api_middleware\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Middleware for the Livis API.
 */
class LivisApiMiddlewareStatisticsCollectionController extends AbstractLivisApiMiddlewareController {

  /**
   * Handle incoming request for inventory endpoint.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Original request.
   * @param string $path
   *   Subpath to send request to.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response.
   */
  public function handleRequest(Request $request, $path = NULL): JsonResponse {
    $path = $this->settings->get('livis_api')['statistics']['path'];

    $response = parent::handleRequest($request, $path);

    return $response;
  }

}
