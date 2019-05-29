<?php

namespace Drupal\unsplash_stream_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Render\Renderer;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class UnsplashTestController.
 */
class UnsplashTestController extends ControllerBase {

  /**
   * Drupal\Core\Render\Renderer definition.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Drupal\Core\Image\ImageFactory definition.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * UnsplashTestController constructor.
   *
   * @param \Drupal\Core\Render\Renderer $renderer
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   */
  public function __construct(Renderer $renderer, ImageFactory $image_factory) {
    $this->renderer = $renderer;
    $this->imageFactory = $image_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('image.factory')
    );
  }

  public function deliver($id, $image_style) {
    $raw_image = file_get_contents('unsplash://' . $id);
    $filename = $id . '.jpg';
    $file = file_save_data($raw_image, "public://$filename", FILE_EXISTS_REPLACE);
    $file_uri = $file->getFileUri();
    if ($image_style === 'none') {
      $variables = ['title' => $filename, 'uri' => $file_uri];
      $image = $this->imageFactory->get($file_uri);
      if ($image->isValid()) {
        $variables['width'] = $image->getWidth();
        $variables['height'] = $image->getHeight();
      }
      else {
        $variables['width'] = $variables['height'] = NULL;
      }
      $image_render_array = [
        '#theme' => 'image_style',
        '#style_name' => 'unsplash',
        '#title' => $variables['title'],
        '#uri' => $variables['uri'],
        '#width' => $variables['width'],
        '#height' => $variables['height'],
      ];
      $this->renderer->addCacheableDependency($image_render_array, $file);
      return $image_render_array;
    }
    else {
      return new RedirectResponse(ImageStyle::load($image_style)->buildUrl($file_uri));
    }
  }

}
