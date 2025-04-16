<?php

namespace Drupal\test_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides ToDo Form.
 */
class FormController extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'todo_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Retrieve stored tasks database API.

    // $tasks = \Drupal::database()->select('users_data', 'u')
    //   ->fields('u', ['task'])
    //   ->condition('uid', \Drupal::currentUser()->id())
    //   ->orderBy('created', 'DESC')
    //   ->execute()
    //   ->fetchCol();
    
    $query = \Drupal::database()->select('users_data', 'u')
    ->fields('u', ['task', 'created'])
    ->condition('uid', \Drupal::currentUser()->id())
    ->orderBy('created', 'DESC'); // <-- Important
    
    $tasks = $query->execute()->fetchCol();
  
    // Input text field to add task.
    $form['input'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter your task here!'),
      '#required' => TRUE,
    ];

    // Add button to add a new task.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('ADD'),
        '#ajax' => [
          'callback' => '::myAjaxCallback',
          'disable-refocus' => FALSE,
          'wrapper' => 'task-list-wrapper',
          'progress' => [
            'type' => 'throbber',
            'message' => $this->t('Adding task...'),
          ],
        ],
    ];

    // Task List Wrapper (This will be updated via AJAX).
    $form['task_list'] = $this->buildTaskList();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   *
   */
  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {

    // Get user input
    $task = $form_state->getValue('input');

    if (!empty($task)) {
      // Store the task in the database.
      \Drupal::database()->insert('users_data')
        ->fields([
          'uid' => \Drupal::currentUser()->id(),
          'task' => $task,
          'created' => \Drupal::time()->getCurrentTime(),
        ])
        ->execute();
  
      $this->messenger()->addStatus($this->t('Task saved!'));
  
      // Clear the input field
      $form_state->setValue('input', '');
      $form_state->unsetValue('input');
    }
  
    return $this->buildTaskList();
  }

  /**
   * Deletes the task and redirects to /todo.
   */
  public function deleteTask($created) {
    $database = \Drupal::database();

    // Delete the task directly using 'created' + 'uid' as conditions.
 
      $query = $database->delete('users_data')
        ->condition('uid', \Drupal::currentUser()->id())
        ->condition('created', $created)
        ->execute();


      if ($query) {
        $this->messenger()->addStatus(t('Task was deleted successfully!'));
      }
      else {
        $this->messenger()->addError(t('Task deletion failed.'));
      }


    // Redirect to /todo after deletion.
    return new RedirectResponse('/todo');
  }

  private function buildTaskList() {
    $task_list = [
      '#type' => 'container',
      '#attributes' => ['id' => 'task-list-wrapper'],
    ];
  
    // $tasks = \Drupal::database()->select('users_data', 'u')
    //   ->fields('u', ['task', 'created'])
    //   ->condition('uid', \Drupal::currentUser()->id())
    //   ->execute()
    //   ->fetchCol();

    $query = \Drupal::database()->select('users_data', 'u')
      ->fields('u', ['task', 'created'])
      ->condition('uid', \Drupal::currentUser()->id())
      ->orderBy('created', 'DESC'); // <-- Important
    
    $tasks = $query->execute()->fetchAll();
  
    if (!empty($tasks)) {
      foreach ($tasks as $task) {
        $task_text = $task->task;
        $created_ts = $task->created;

        $task_list["task_$created_ts"] = [
          '#markup' => '<div style="display:inline-block; margin-right:10px;">' . $task_text . '</div>',
        ];
  
        if(!empty($created_ts)) { 
          $task_list["edit_$created_ts"] = [
            '#type' => 'link',
            '#title' => $this->t('Edit'),
            '#url' => Url::fromRoute('test_module.task_edit', ['created' => $created_ts], [
              'query' => ['task_val' => $task_text],
            ]),
            '#attributes' => ['class' => ['button']],
          ];

          $task_list["delete_$created_ts"] = [
            '#type' => 'link',
            '#title' => $this->t('Delete'),
            '#url' => Url::fromRoute('test_module.task_delete', ['created' => $created_ts]),
            '#attributes' => [
              'class' => ['button', 'button--danger'],
              'style' => 'margin-left: 5px; color: red;',
              'onclick' => 'return confirm("Are you sure you want to delete this task?");',
            ],
          ];
        }
      }
    } else {
      $task_list['empty'] = ['#markup' => $this->t('No tasks added yet.')];
    }
  
    return $task_list;
  }
  
}
