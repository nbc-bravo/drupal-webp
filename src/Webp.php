<?php

namespace Drupal\webp;

use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Class Webp
 *
 * @package Drupal\webp
 */
class Webp {

  use StringTranslationTrait;

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Webp constructor.
   *
   * @param \Drupal\Core\Image\ImageFactory $imageFactory
   *   Image factory to be used.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   Logger channel factory.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   String translation interface.
   */
  public function __construct(ImageFactory $imageFactory, LoggerChannelFactoryInterface $loggerFactory, TranslationInterface $stringTranslation) {
    $this->imageFactory = $imageFactory;
    $this->logger = $loggerFactory->get('webp');
    $this->setStringTranslation($stringTranslation);
  }

  /**
   * Creates a WebP copy of a JPEG/PNG image.
   *
   * @param $uri
   *   Image URI.
   * @param int $quality
   *   Image quality factor.
   *
   * @return bool|string
   *   The location of the WebP image if successful, FALSE if not successful.
   */
  public function createWebpCopy($uri, $quality = 100) {
    if ($image = $this->createGdImageResourceFromUri($uri)) {
      $pathInfo = pathinfo($uri);
      $destination = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
      if (imagewebp($image, $destination, $quality)) {
        imagedestroy($image);
        return $destination;
      }
      else {
        $error = $this->t('Could not generate WebP image.');
        $this->logger->error($error);
      }
    }

    return FALSE;
  }

  /**
   * Creates a GD image resource from a URI.
   *
   * @param $uri
   *   Source image URI.
   *
   * @return null|resource
   *   NULL or a GD image resource.
   */
  protected function createGdImageResourceFromUri($uri) {
    $image = $this->imageFactory->get($uri, 'gd');
    /** @var \Drupal\system\Plugin\ImageToolkit\GDToolkit $toolkit */
    $toolkit = $image->getToolkit();
    $resource = $toolkit->getResource();

    if ($resource === NULL) {
      $error = $this->t('Could not generate image resource from URI @uri.', [
        '@uri' => $uri,
      ]);
      $this->logger->error($error);
    }

    return $resource;
  }

  /**
   * Deletes all image style derivatives.
   */
  public function deleteImageStyleDerivatives() {
    // Remove the styles directory and generated images.
    if (@!file_unmanaged_delete_recursive(file_default_scheme() . '://styles')) {
      $error = $this->t('Could not delete image style directory while uninstalling WebP. You have to delete it manually.');
      $this->logger->error($error);
    }
  }

}
