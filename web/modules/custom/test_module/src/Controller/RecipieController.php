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
   *   Titles of requested taxonomy term.
   */
  public function myRecipiePage() {

    $taxonomy_term_name = 'italian';

    $node = \Drupal::entityTypeManager()->getStorage('node');
    $ids = $node->getQuery()
      ->condition('status', 1)
      ->condition('type', 'recipies')
      // Or 'field_recipie_category.name'.
      ->condition('field_recipie_category.entity:taxonomy_term.name', 'indian')
      ->accessCheck(TRUE)
      ->execute();
    
    $recipie_title = $node->loadMultiple($ids);
        
    return [
      '#theme' => 'controller_template',
      '#controller_var' => 'Hello World from Twig Template!',
      '#array' => $recipie_title,
    ];

  }

}
