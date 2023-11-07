<?php

namespace Drupal\livis_api_middleware\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Middleware for the Livis API.
 */
class LivisApiMiddlewareStatisticsResourceController extends AbstractLivisApiMiddlewareController {

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
    $idRouteParam = $request->attributes->get('_raw_variables')->get('id');
    $path = $this->settings->get('livis_api')['statistics']['path'] . '/' . $idRouteParam;

    $response = parent::handleRequest($request, $path);

    return $response;
  }

}
