<?php

namespace Drupal\test_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides route responses for the test_module module.
 */
class UpdatedRecipieController extends ControllerBase {

  /**
   * Returns a Recipie page with paricular category.
   *
   * @return array
   *   Titles of requested taxonomy term.
   */
 // public function newRecipiePage(Request $request) {
  public function newRecipiePage() {

    // $node = \Drupal::entityTypeManager()->getStorage('node');
    // $ids = $node->getQuery()
    //   ->condition('status', 1)
    //   ->condition('type', 'recipies')
    //   // Or 'field_recipie_category.name'.
    //   ->condition('field_recipie_category.entity:taxonomy_term.name', 'italian')
    //   ->accessCheck(TRUE)
    //   ->execute();
    
    // $recipie_title = $node->loadMultiple($ids);
        
    // return [
    //   '#theme' => 'controller_template',
    //   '#controller_var' => 'Hello World from Twig Template!',
    //   '#array' => $recipie_title,
    // ];

//     $selected_tid = $request->query->get('term');

//     // Query for recipe nodes
//     $node_storage = \Drupal::entityTypeManager()->getStorage('node');
//     $query = $node_storage->getQuery()
//       ->condition('status', 1)
//       ->condition('type', 'recipies')
//       ->accessCheck(TRUE);

//     if ($selected_tid) {
//       $query->condition('field_recipie_category.entity:taxonomy_term.tid', $selected_tid);
//     }
    
//     $nids = $query->execute();
//     $nodes = Node::loadMultiple($nids);

//     $terms = \Drupal::entityTypeManager()
//             ->getStorage('taxonomy_term')
//             ->loadTree('cuisine');
    
    
//     dump($terms);
//     dump($selected_tid);
//     var_dump($terms);

//     // \Drupal::messenger()->addMessage('You selected the cuisine: ' . $terms);


//     return [
//       '#theme' => 'controller_template',
//       '#array' => $nodes,
//       '#terms' => $terms,
//       '#selected_tid' => $selected_tid,
//       '#attached' => ['library' => ['test_module/custom_styles']],
//     ];
// 1234567890-12345678901234567890123456789012345678912345678901234567890
                        // $term_id = $request->query->get('category');

                        // $terms = \Drupal::entityTypeManager()
                        //         ->getStorage('taxonomy_term')
                        //         ->loadTree('cuisine');
                        
                        // $recipes = [];
                        // if(!empty($term_id)) {
                        //     $query = \Drupal::entityQuery('node')
                        //             ->condition('type', 'recipies')
                        //             ->condition('status', 1)
                        //             ->condition('field_recipe_category:taxonomy_term.tid', $term_id)
                        //             ->accessCheck(TRUE);
                            
                        //     $nids = $query->execute();

                        //     if($nids) {
                        //       $recipes = Node::loadMultiple($nids);
                        //     }
                        // }

                        // else {
                        //   $term_id = 1;

                        //   $query = \Drupal::entityQuery('node')
                        //             ->condition('type', 'recipies')
                        //             ->condition('status', 1)
                        //             ->condition('field_recipe_category:taxonomy_term.tid', $term_id)
                        //             ->accessCheck(TRUE);
                            
                        //     $nids = $query->execute();

                        //     if($nids) {
                        //       $recipes = Node::loadMultiple($nids);
                        //     }
                        // }
// 1234567890-12345678901234567890123456789012345678912345678901234567890
    // $query = \Drupal::entityQuery('node')
    //         ->condition('type', 'recipies')
    //         ->condition('status', 1)
    //         ->accessCheck(TRUE);
    
    //         if ($term_id) {
    //   $query->condition('field_recipe_category:taxonomy_term.tid', $term_id); // Replace with your field name
    // }
    // $nids = $query->execute();


    // $recipes = [];
    // if($nids) {
    //   $recipes = Node::loadMultiple($nids);
    // }
// 1234567890-12345678901234567890123456789012345678912345678901234567890


// if(!empty($request)) {
//           $term_id = $request->query->get('category');


//           // Load taxonomy terms (cuisine vocabulary)
//           $terms = \Drupal::entityTypeManager()
//             ->getStorage('taxonomy_term')
//             ->loadTree('cuisine');

//           // Fallback to default if not selected
//           if (empty($term_id)) {
//             $term_id = 1; // Default cuisine tid
//           }

//           // Build the query for recipes
//           $nids = \Drupal::entityQuery('node')
//             ->condition('type', 'recipies')
//             ->condition('status', 1)
//             ->condition('field_recipie_category.target_id', $term_id)
//             ->accessCheck(TRUE)
//             ->execute();

//           $recipes = $nids ? Node::loadMultiple($nids) : [];
//             dump($terms);
//             dump($term_id);


              
//             return [
//                 '#theme' => 'controller-template',
//                 '#array' => $recipes,
//                 '#terms' => $terms,
//                 '#selected_term' => $term_id,
//                 '#attached' => ['library' => ['test_module/custom_styles']],
//               ];
// } 

// else {






$node = \Drupal::entityTypeManager()->getStorage('node');
    $ids = $node->getQuery()
      ->condition('status', 1)
      ->condition('type', 'recipies')
      // Or 'field_recipie_category.name'.
      ->condition('field_recipie_category.entity:taxonomy_term.name', 'italian')
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

