<?php

namespace Drupal\expose_content_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Cache\CacheableResponseInterface;
use Psr\Log\LoggerInterface;
use Drupal\expose_content_api\Service\ContentUtility;

/**
 * Provides a REST API endpoint for content.
 *
 * @RestResource(
 *   id = "expose_content_api_resource",
 *   label = @Translation("Expose Content API"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/content/{content_type}/{node_id}",
 *   }
 * )
 */
class ContentEndpointResource extends ResourceBase {

  /**
    * A current user instance.
    *
    * @var \Drupal\Core\Session\AccountProxyInterface
    */
  protected $currentUser;

  /**
    * The content utility service.
    *
    * @var \Drupal\expose_content_api\Service\ContentUtility
    */
    protected $contentUtility;

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
    * @param \Drupal\expose_content_api\Service\ContentUtility $contentUtility
    *   The content utility service.
    */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    ContentUtility $contentUtility) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentUser = $current_user;
    $this->contentUtility = $contentUtility;
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
      $container->get('expose_content_api.content_utility')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing content data.
   */
  public function get($content_type, $node_id) {
    $node = $this->contentUtility->loadSpecificTypeSpecificNode($content_type, $node_id);
      $data = [];
      if (empty($node)) {
        return new ResourceResponse(['No content is available.']);
      }
      $data[] = $this->contentUtility->getNodeFieldsBasedOnContentType($node);
      return new ResourceResponse($data);
    }
}
