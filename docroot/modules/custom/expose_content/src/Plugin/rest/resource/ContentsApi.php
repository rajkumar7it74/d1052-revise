<?php
 
namespace Drupal\expose_content\Plugin\rest\resource;
 
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Drupal\common_utility\Service\CommonUtility;
use Drupal\Core\Entity\EntityTypeManagerInterface;
 
/**
* Provides a REST Resource for contents.
*
* @RestResource(
*   id = "contents_api",
*   label = @Translation("Expose Contents Resource"),
*   uri_paths = {
*     "canonical" = "/api/v1/expose/contents"
*   }
* )
*/
class ContentsApi extends ResourceBase {
 
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
    protected $entityTypeManager;
  
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
      CommonUtility $commonUitlity,
      EntityTypeManagerInterface $entityTypeManager) {
      parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
      $this->currentUser = $current_user;
      $this->commonUitlity = $commonUitlity;
      $this->entityTypeManager = $entityTypeManager;
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
        $container->get('common_utility.common_utility'),
        $container->get('entity_type.manager')
      );
    }
 
  /**
   * Responds to GET requests.
   */
  public function get() {
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadByProperties(['status' => 1]); // published nodes
 
    $data = [];
    foreach ($nodes as $node) {
      $data[] = [
        'id' => $node->id(),
        'title' => $node->label(),
        'type' => $node->bundle(),
      ];
    }
 
    // Create response
    $response = new ResourceResponse($data, 200);
 
    // Add caching metadata
    $cache_metadata = new CacheableMetadata();
    $cache_metadata->setCacheMaxAge(300); // 5 minutes
    $cache_metadata->addCacheTags(['node_list']); // invalidate when nodes change
    $cache_metadata->addCacheContexts(['user']); // vary by user role
 
    // Attach cache to response
    $response->addCacheableDependency($cache_metadata);
 
    return $response;
  }
 
}