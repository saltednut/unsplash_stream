<?php

namespace Drupal\unsplash_stream_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UnsplashTestController.
 */
class UnsplashTestController extends ControllerBase {
  
  public function deliver($id) {
    $raw_image = file_get_contents('unsplash://' . $id);
    $file = file_save_data($raw_image, "public://$id.jpg", FILE_EXISTS_REPLACE);

    $variables = array(
      'style_name' => 'large',
      'uri' => $file->getFileUri(),
    );

    $image = \Drupal::service('image.factory')->get($file->getFileUri());
    if ($image->isValid()) {
      $variables['width'] = $image->getWidth();
      $variables['height'] = $image->getHeight();
    }
    else {
      $variables['width'] = $variables['height'] = NULL;
    }

    $image_render_array = [
      '#theme' => 'image_style',
      '#width' => $variables['width'],
      '#height' => $variables['height'],
      '#style_name' => $variables['style_name'],
      '#uri' => $variables['uri'],
    ];

    $renderer = \Drupal::service('renderer');
    $renderer->addCacheableDependency($image_render_array, $file);

    return $image_render_array;

  }

}
