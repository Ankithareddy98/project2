<?php
namespace Drupal\test_module\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the test_module module.
 */
class RecipieController extends ControllerBase {

  /**
   * Returns a Recipie page with paricular category.
   *
   * @return array
   *   A simple renderable array.
   */
  public function myRecipiePage() {
    return [
      '#markup' => 'Hello, world',
    ];
  }

}

