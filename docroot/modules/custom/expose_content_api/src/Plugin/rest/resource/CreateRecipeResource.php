<?php

namespace Drupal\expose_content_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Drupal\common_utility\Service\CommonUtility;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * Provides a REST resource to create Recipe content.
 *
 * @RestResource(
 *   id = "recipe_create_resource",
 *   label = @Translation("Recipe Create Resource"),
 *   uri_paths = {
 *     "create" = "/api/recipe"
 *   }
 * )
 */
class CreateRecipeResource extends ResourceBase {

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


  public function post(Request $request) {
    $data = json_decode($request->getContent(), TRUE);
    if (empty($data['title']) || empty($data['recipe_ingredients'])
    || empty($data['recipe_instructions']) || empty($data['recipe_dish_type'])
    || empty($data['recipe_description']) || empty($data['recipe_total_time'])) {
      throw new BadRequestHttpException('Missing required fields.');
    }
    try {
      // $node = $this->commonUitlity->createNode('recipe', $data);
      // if ($node && is_object($node)) {
      //   return new ResourceResponse([
      //     'message' => 'Recipe created successfully.',
      //     'nid' => $node->id(),
      //   ], 201);
      // }
      $node = Node::create([
        'type' => 'recipe',
        'title' => $data['title'],
        'field_recipe_description' => $data['recipe_description'],
        'field_recipe_dish_type' => $data['recipe_dish_type'],
        'field_recipe_ingredients' => $data['recipe_ingredients'],
        'field_recipe_instructions' => $data['recipe_instructions'],
        'field_recipe_total_time' => $data['recipe_total_time'],
        // 'field_recipe_image' => [
        //     'target_id' => $this->saveBase64Image($data['recipe_image']),
        //     'alt' => $data['image_alt'] ?? 'Recipe image',
        //   ],
        'status' => 1,
      ]);
      $node->save();
      return new ResourceResponse(['message' => 'Recipe created', 'nid' => $node->id()], 201);
    }
    catch (\Exception $e) {
      $this->logger->error('Error creating node: ' . $e->getMessage());
      throw new \Symfony\Component\HttpKernel\Exception\HttpException(500, 'Failed to create node.');
    }
    
  }

  public function saveBase64Image($base64Image) {
    $image_data = $base64Image; // base64 string
    $image_binary = base64_decode($image_data);
    $file_system = \Drupal::service('file_system');
    $filename = 'recipe_' . time() . '.jpg';
    $uri = 'public://' . $filename;

    $unique_uri = $file_system->getDestinationFilename($uri, 'rename');
    $real_path = $file_system->realpath($unique_uri);
    // file_put_contents($real_path, base64_decode($data['image_base64']));
    file_put_contents($real_path, $image_binary);
    $file = File::create([
      'uri' => $unique_uri,
      'status' => FileInterface::STATUS_PERMANENT,
    ]);
    $file->save();
    return $file->id();

    // $file = file_save_data($image_binary, 'public://recipe_' . time() . '.jpg', FILE_EXISTS_RENAME);
    // $file->setPermanent();
    // $file->save();

    // // Get the file system service
    // $file_system = \Drupal::service('file_system');

    // // Generate a base filename
    // $filename = 'recipe_' . time() . '.jpg';
    // $uri = 'public://' . $filename;

    // // Ensure unique filename (like FILE_EXISTS_RENAME)
    // $unique_uri = $file_system->getDestinationFilename($uri, FileSystemInterface::EXISTS_RENAME);

    // // Save the file
    // $real_path = $file_system->realpath($unique_uri);
    // file_put_contents($real_path, base64_decode($data['image_base64']));

    // // Create and save the file entity
    // $file = File::create([
    //   'uri' => $unique_uri,
    //   'status' => FILE_STATUS_PERMANENT,
    // ]);
    // $file->save();

    
  }
}
