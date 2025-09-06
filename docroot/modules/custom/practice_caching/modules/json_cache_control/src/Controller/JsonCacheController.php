<?php

namespace Drupal\json_cache_control\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonCacheController {

  /**
   * Returns JSON data with Cache-Control header.
   *
   * @param int $id
   *   An example ID parameter.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function example($id) {
    $data = [
      'id' => $id,
      'message' => 'This is a JSON response with max-age=0',
    ];

    return new JsonResponse($data, 200, [
      'Cache-Control' => 'public, max-age=0',
    ]);
  }

}
