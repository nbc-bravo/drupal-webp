<?php

namespace Drupal\webp\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    /* @var \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler */
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('imageapi_optimize')) {
      return;
    }

    if ($route = $collection->get('image.style_public')) {
      $route->setDefault('_controller', '\Drupal\webp\Controller\ImageStyleDownloadController::deliver');
    }
  }

}
