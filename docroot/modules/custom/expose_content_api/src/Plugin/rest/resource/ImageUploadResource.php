<?php

namespace Drupal\expose_content_api\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Drupal\file\Entity\File;

/**
 * Provides a REST resource for image upload.
 *
 * @RestResource(
 * id = "image_upload_resource",
 * label = @Translation("Image Upload Resource"),
 * uri_paths = {
 *   "create" = "/api/image-upload"
 * }
 *)
 */
class ImageUploadResource extends ResourceBase {

  /**
   * Responds to POST requests.
   */
  public function post(Request $request) {
    $uploadedFile = $request->files->get('image');

    if ($uploadedFile instanceof UploadedFile) {
      $filename = $uploadedFile->getClientOriginalName();
      $destination = 'sites/default/files/uploads/' . $filename;
      $uploadedFile->move('sites/default/files/uploads', $filename);

      $file = File::create([
        'uri' => 'public://uploads/' . $filename,
      ]);
      $file->save();

      return new ResourceResponse(['message' => 'Image uploaded successfully']);
    }

    return new ResourceResponse(['error' => 'No image found'], 400);
  }
}
