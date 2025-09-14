<?php

namespace Drupal\expose_content_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Drupal\common_utility\Service\CommonUtility;
// To enabled caching
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Cache\CacheableJsonResponse;

/**
 * Provides a REST API endpoint for content.
 *
 * @RestResource(
 *   id = "expose_contents_resource",
 *   label = @Translation("Expose All Contents"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/content",
 *   }
 * )
 */
class ContentsResource extends ResourceBase {

  /**
    * A current user instance.
    *
    * @var \Drupal\Core\Session\AccountProxyInterface
    */
  protected $currentUser;

  /**
    * The common utility service.
    *
    * @var \Drupal\common_utility\Service\CommonUtility
    */
    protected $commonUitlity;

  /**
    * Constructs a Drupal\rest\Plugin\ResourceBase object.
    *
    * @param array $configuration
    *   A configuration array containing information about the plugin instance.
    * @param string $plugin_id
    *   The plugin_id for the plugin instance.
    * @param mixed $plugin_definition
    *   The plugin implementation definition.
    * @param array $serializer_formats
    *   The available serialization formats.
    * @param \Psr\Log\LoggerInterface $logger
    *   A logger instance.
    * @param \Drupal\Core\Session\AccountProxyInterface $current_user
    *   A current user instance.
    * @param \Drupal\common_utility\Service\CommonUtility $commonUitlity
    *   The common utility service.
    */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    CommonUtility $commonUitlity) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentUser = $current_user;
    $this->commonUitlity = $commonUitlity;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('custom_rest'),
      $container->get('current_user'),
      $container->get('common_utility.common_utility')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing content data.
   */
  public function get() {
    $data = [];
    $nodes = $this->commonUitlity->loadAllNodes();
    if (empty($nodes)) {
      return new ResourceResponse(['No content is available.']);
    }
    foreach ($nodes as $node) {
      $data[] = $this->commonUitlity->getNodeFieldsBasedOnContentType($node);
    }
    // Old response without caching.
    //return new ResourceResponse($data);
    // New code with caching.
    $response = new ResourceResponse($data, 200);
    $cache_metadata = new CacheableMetadata();
    // Set cache max-age (e.g., 1 hour = 3600 seconds)
    $cache_metadata->setCacheMaxAge(120);
    // Add cache contexts (e.g., user roles, languages)
    $cache_metadata->addCacheContexts(['user']);
    // Add cache tags (e.g., invalidate when node changes)
    $cache_metadata->addCacheTags(['node:list', 'node:1']);
    // Attach metadata to response
    //$cache_metadata->applyTo($response);
    $response->addCacheableDependency($cache_metadata);

    // Not recommened way.
    $response->headers->set('Cache-Control', 'public, max-age=3600');
    $cache_metadata->applyTo($response);


    // $response = new CacheableJsonResponse($data);
    // $cache_metadata = new CacheableMetadata();
    // $cache_metadata->addCacheTags(['my_resource_tag']);
    // $cache_metadata->addCacheContexts(['user']);
    // $cache_metadata->setCacheMaxAge(3600); // Cache for 1 hour

    
    //dump($response);

    
//\Drupal::service('devel.dumper')->dump($response->getCacheableMetadata());


    return $response;
  }
}
