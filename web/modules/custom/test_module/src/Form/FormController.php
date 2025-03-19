<?php

namespace Drupal\test_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

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
 // Store previous inputs in form state if not already set
    // if (!$form_state->get('inputs')) {
    //   $form_state->set('inputs', []);
    // }

    if (!$form_state->get('tasks')) {
      $form_state->set('tasks', []);
    }

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
    
    // Retrieve stored tasks
    $tasks = $form_state->get('tasks');

    // Display stored tasks with Edit and Delete buttons
    foreach ($tasks as $index => $task) {
      $form['task_list']["task_$index"] = [
        '#type' => 'fieldset',
        '#title' => $task, // Show task text as title
      ];

      // Edit button
      $form['task_list']["edit_$index"] = [
        '#type' => 'submit',
        '#value' => $this->t('Edit'),
        '#name' => "edit_$index",
        '#ajax' => [
          'callback' => '::myAjaxCallback',
          'wrapper' => 'task-list-wrapper',
        ],
        '#submit' => ['::submitForm', '::editTask'],
        '#task_index' => $index, // Store index for reference
      ];

      // Delete button
      $form['task_list']["delete_$index"] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete'),
        '#name' => "delete_$index",
        '#ajax' => [
          'callback' => '::myAjaxCallback',
          'wrapper' => 'task-list-wrapper',
        ],
        '#submit' => ['::submitForm', '::deleteTask'],
        '#task_index' => $index,
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
   $tasks = $form_state->get('tasks');

   // Add task only if not empty.
   if (!empty($task)) {
     $tasks[] = $task;
     $form_state->set('tasks', $tasks);

     // Clear the input field after adding.
     $form_state->setValue('input', '');
   }

   // Rebuild the form.
   $form_state->setRebuild(TRUE);
  
  }

  /**
   * Edit task by index and reset it in the list.
   */
  public function editTask(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $index = $trigger['#task_index'];

    $tasks = $form_state->get('tasks');

    // Check if the task exists before editing.
    if (isset($tasks[$index])) {
      // Place the selected task back in the input field for editing.
      $form_state->setValue('input', $tasks[$index]);

      // Remove the original task to avoid duplication.
      unset($tasks[$index]);
      $form_state->set('tasks', array_values($tasks)); // Re-index tasks after removing.
    }
    // Rebuild the form.
    $form_state->setRebuild(TRUE);
  }

  /**
   * Delete task by index.
   */
  public function deleteTask(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $index = $trigger['#task_index'];

    $tasks = $form_state->get('tasks');

    // Check if the task exists before deleting.
    if (isset($tasks[$index])) {
      unset($tasks[$index]);
      $form_state->set('tasks', array_values($tasks)); // Re-index tasks after removing.
    }

    // Rebuild the form.
    $form_state->setRebuild(TRUE);
  }

  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['task_list']; // Return the updated task list container
  }
  
}



