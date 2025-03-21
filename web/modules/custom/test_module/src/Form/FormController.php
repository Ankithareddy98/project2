<?php

namespace Drupal\test_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\AppendCommand;


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
    // Retrieve stored tasks from state API.
    $tasks = \Drupal::state()->get('todo_tasks', []);

    // Input text field to add task.
    $form['input'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Enter your task here!'),
        '#required' => TRUE,
    ];

    // Add button.
    $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('ADD'),
        '#ajax' => [
        'callback' => '::myAjaxCallback',
        'event' => 'click',
        'disable-refocus' => FALSE,
        'wrapper' => 'task-list-wrapper',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Adding task...'),
        ],
      ],
    ];

    // Task List Wrapper (This will be updated via AJAX)
    $form['task_list'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'task-list-wrapper'],
    ];
 
     // Display stored tasks with Edit and Delete buttons
     if (!empty($tasks)) {
      foreach ($tasks as $index => $task) {
        $form['task_list']["task_$index"] = [
          '#type' => 'fieldset',
          '#title' => $task, // Show task text as title
        ];

        // Delete link to delete the task via URL.
        $form['task_list']["task_$index"]["delete_$index"] = [
          '#type' => 'link',
          '#title' => $this->t('Delete'),
          '#url' => Url::fromRoute('test_module.task_delete', ['task_id' => $index]),
          '#attributes' => [
            'class' => ['button', 'button--danger'],
            'style' => 'margin: auto; color: red;',
            'onclick' => 'return confirm("Are you sure you want to delete this task?");',
          ],
        ];

        // Edit link with a route to /todo/edit/{task_id}.
        $form['task_list']["task_$index"]["edit_$index"] = [
          '#type' => 'link',
          '#title' => $this->t('Edit'),
          '#url' => Url::fromRoute('test_module.task_edit', ['task_id' => $index]),
          '#attributes' => [
            'class' => ['button', 'button--primary'],
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
   $tasks = \Drupal::state()->get('todo_tasks', []);

   // Add task only if not empty.
   if (!empty($task)) {
     $tasks[] = $task;
     // Save tasks to state API.
     \Drupal::state()->set('todo_tasks', $tasks);

     // Clear the input field after adding.
     $form_state->setValue('input', '');

     // Show success message.
     $this->messenger()->addStatus($this->t('Task added successfully!'));;
   }

   // Rebuild the form.
   $form_state->setRebuild(TRUE);
  
  }

  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['task_list']; // Return the updated task list container
  }
/**
 * Delete task and redirect.
 */
public function deleteTask($task_id) {
  $tasks = \Drupal::state()->get('todo_tasks', []);

  // Check if task exists and delete it.
  if (isset($tasks[$task_id])) {
    unset($tasks[$task_id]);

    // Re-index and save tasks.
    \Drupal::state()->set('todo_tasks', array_values($tasks));
    $this->messenger()->addStatus(t('Task deleted successfully.'));
  }
  else {
    $this->messenger()->addError(t('Task not found.'));
  }

  // Redirect to /todo after deletion.
  return new RedirectResponse('/todo');
  }
  
  /**
   * Builds and processes the edit task form.
   */
  public function editTask($task_id) {
    $tasks = \Drupal::state()->get('todo_tasks', []);

    // Check if task exists.
    if (!isset($tasks[$task_id])) {
      \Drupal::messenger()->addError($this->t('Task not found.'));
      return new RedirectResponse('/todo');
    }
    dump($tasks[$task_id]);
  
    // Build the form for editing.
    $form['task_id'] = [
      '#type' => 'hidden',
      '#value' => $task_id,
    ];

    $form['task_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Edit Task'),
    // '#default_value' => $this->t('Test Task Value'),
      '#value' => $tasks[$task_id],
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Changes'),
      '#submit' => [$this, '::saveEditedTask'],
    ];
   return $form;
  }

  /**
   * Saves the edited task and redirects to /todo.
   */
  public function saveEditedTask(array &$form, FormStateInterface $form_state) {
    $task_id = $form_state->getValue('task_id');
    $task_value = $form_state->getValue('task_value');

    $tasks = \Drupal::state()->get('todo_tasks', []);

    if (isset($tasks[$task_id])) {
      $tasks[$task_id] = $task_value;
      \Drupal::state()->set('todo_tasks', $tasks);
      \Drupal::messenger()->addStatus($this->t('Task updated successfully.'));
    }
    else {
      \Drupal::messenger()->addError($this->t('Task not found.'));
    }
    return new RedirectResponse('/todo');
    
    // Redirect to /todo after editing.
    // $form_state->setRedirect('test_module.form');

  }

}



