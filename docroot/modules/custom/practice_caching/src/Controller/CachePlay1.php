<?php
namespace Drupal\practice_caching\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class CachePlay1 {

  /**
   * Example of a simple controller method that returns a JSON response.
   *
   * Note. You must enable the RESTful Web Services module to run this.
   *
   * @param int $nid
   *  The node ID.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function jsonExample1(int $nid): JsonResponse {
    if ($nid == 0) {
      //  $build['#cache']['tags'][] = 'node_list';
      $data = [
        'nid' => $nid,
        'name' => 'Fred Bloggs.',
        'age' => 45,
        'occupation' => 'Builder',
      ];
    }
    else {
      $node = \Drupal::service('entity_type.manager')->getStorage('node')->load($nid);
      if ($node) {
        $data = [
          'nid' => $nid,
          'name' => $node->label(),
          'age' => 35,
          'occupation' => $node->hasField('field_recipe_description') ? $node->get('field_recipe_description')->value : '',
        ];
      }
    }

    return new JsonResponse($data, 200, [
      'Cache-Control' => 'public, max-age=3607',
    ]);
  }
  /**
   * 'Cache-Control' => 'public, max-age=3607'
   * If you set 'Cache-Control' => 'public, max-age=3607' in a custom
   * path or custom REST resource, you're essentially instructing both browsers
   * and intermediate caches (like CDNs or proxies) to store the response and
   * reuse it for up to 3607 seconds (just over an hour) without revalidating
   * it. Here's what that means in practice:
   * 
   * public: Allows the response to be cached by any cache, including shared
   * caches like CDNs or proxy servers. This is ideal for non-sensitive data
   * that can be reused across users.
   *
   * max-age=3607: Specifies that the cached response is considered fresh for
   * 3607 seconds. During this time, clients and intermediaries can serve the
   * cached version without contacting the origin server.
   *
   * Improved Performance: Reduces server load and latency for repeat
   * requests to that resource. Clients and proxies can serve the cached response instantly.
   *
   * Stale Data Risk: If the resource changes frequently, users might see outdated
   * data until the cache expires. You’ll need cache-busting
   * strategies (like versioned URLs or ETags) if freshness is critical.
   *
   * SEO and API Behavior: For public APIs or endpoints serving static
   * content (e.g., images, configuration files), this is beneficial. But
   * for dynamic or user-specific data, you might want to use private or no-cache instead.
   *
   * Security Considerations: Be cautious with public if the response contains
   * sensitive or user-specific data. It could be cached and served to unintended recipients.
   *
   * Browser Behavior: The browser stores the response and serves it from cache for
   * 3607 seconds. No network request is made during this period unless the user
   * manually refreshes or bypasses the cache.
   *
   * CDN/Proxy Behavior: If the response is marked public, shared caches (like CDNs)
   * can also store and serve it to other users for 3607 seconds.
   *
   * After 3607 Seconds: The cached response is considered stale. The browser will then:
   *    Either revalidate it using ETag or Last-Modified headers (if present), or
   *    Fetch a fresh copy from the server.
   *
   * 🧪 Example Use Case
   * Let’s say you have a REST endpoint /api/products that returns product data. If you set:
   *
   * Cache-Control: public, max-age=3607
   * Then:
   *    A user who hits that endpoint will get the data and store it.
   *    For the next 3607 seconds, any repeat request will use the cached version.
   *    After that, the browser will re-fetch or revalidate the data.
   */
}
