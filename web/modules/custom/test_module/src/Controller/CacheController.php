<?php

namespace Drupal\test_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;

class CacheController extends ControllerBase {
    /**
     * Returns the retrieved tasks from database.
     */
    public function taskPage() {
        
        $query = \Drupal::database()->select('users_data', 'u')
            ->fields('u', ['task'])
            ->condition('uid', \Drupal::currentUser()->id())
            ->execute();

        $tasks = $query->fetchCol();
        
        $build= [];

        if(!empty($tasks)){
            $build['tasks'] = [
                '#theme' => 'item_list',
                '#items' => $tasks,
                '#title' => $this->t('Task List'),
                '#cache' => [
                    'tags' => ['users_data_tasks:'. \Drupal::currentUser()->id()],
                    'max-age' => Cache::PERMANENT,
                ],
            ];
        }
        else {
            $build = [
                '#markup' => $this->t('You have no tasks!')
            ];
        }
        
        return $build;
    }

}