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

    $tasks = \Drupal::database()->select('users_data', 'u')
      ->fields('u', ['task'])
      ->condition('uid', \Drupal::currentUser()->id())
      ->execute()
      ->fetchCol();
  
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
        // '#ajax' => [
        //   'callback' => '::myAjaxCallback',
        //   'disable-refocus' => FALSE,
        //   'wrapper' => 'task-list-wrapper',
        //   'progress' => [
        //     'type' => 'throbber',
        //     'message' => $this->t('Adding task...'),
        //   ],
        // ],
    ];

    // Task List Wrapper (This will be updated via AJAX).
    $form['task_list'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'task-list-wrapper'],
    ];

    // Display stored tasks with Edit and Delete buttons.
    if (!empty($tasks)) {
      foreach ($tasks as $index => $task) {

        // Display task as plain text with Edit/Delete buttons.
        $form['task_list']["task_$index"] = [
          '#markup' => '<div>' . $task . '</div>',
        ];

        $form['task_list']["edit_$index"] = [
          '#type' => 'link',
          '#title' => $this->t('Edit'),
          '#url' => Url::fromRoute('test_module.task_edit', ['task_id' => $index], [
            'query' => ['task_val' => $task],
          ]),
          '#attributes' => [
        // Adds CSS classes to make the link look like a button.
            'class' => ['button', 'edit-button'],
          ],
        ];

        // Delete link with confirmation.
        $form['task_list']["delete_$index"] = [
          '#type' => 'link',
          '#title' => $this->t('Delete'),
          '#url' => Url::fromRoute('test_module.task_delete', ['task_id' => $index]),
          '#attributes' => [
            'class' => ['button', 'button--danger'],
            'style' => 'margin-left: 5px; color: red;',
            'onclick' => 'return confirm("Are you sure you want to delete this task?");',
          ],
        ];
      }
    }
    else {
      $form['task_list']['empty'] = [
        '#markup' => $this->t('No tasks added yet.'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Add a new task when 'ADD' is clicked.
    $task = $form_state->getValue('input');

    // Store user input to database.
    \Drupal::database()->insert('users_data')
      ->fields([
        'uid' => \Drupal::currentUser()->id(),
        'task' => $task,
      ])
      ->execute();
    $this->messenger()->addStatus($this->t('Task saved to the database successfully!'));

    $form_state->setRebuild(TRUE);
    $form_state->setValue('input', '');
    $form_state->unsetValue('input');

  }

  /**
   *
   */
  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
    // Return the updated task list.
    return $form['task_list'];
  }

  /**
   * Deletes the task and redirects to /todo.
   */
  public function deleteTask($task_id) {
    $database = \Drupal::database();

    // Fetch the actual task name using the index.
    $task_query = $database->select('users_data', 'u')
      ->fields('u', ['task'])
      ->condition('uid', \Drupal::currentUser()->id())
    // Get the task at the given index.
      ->range($task_id, 1)
      ->execute()
      ->fetchField();

    if ($task_query) {
      // Now delete using the correct task name.
      $query = $database->delete('users_data')
        ->condition('uid', \Drupal::currentUser()->id())
        ->condition('task', $task_query)
        ->execute();

      if ($query) {
        $this->messenger()->addStatus(t('Task was deleted successfully!'));
      }
      else {
        $this->messenger()->addError(t('Task deletion failed.'));
      }
    }
    else {
      $this->messenger()->addError(t('Task not found.'));
    }

    // Redirect to /todo after deletion.
    return new RedirectResponse('/todo');
  }

}
