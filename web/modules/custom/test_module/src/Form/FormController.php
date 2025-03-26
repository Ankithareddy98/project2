<?php

namespace Drupal\test_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

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
    $edit_index = $form_state->get('edit_index');
    $edit_value = $form_state->get('edit_value');

    // Input text field to add task.
    $form['input'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter your task here!'),
      '#required' => empty($form_state->get('edit_index')),
    ];

    // Add button to add a new task.
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

    // Task List Wrapper (This will be updated via AJAX).
    $form['task_list'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'task-list-wrapper'],
    ];

    // Display stored tasks with Edit and Delete buttons.
    if (!empty($tasks)) {
      foreach ($tasks as $index => $task) {
        // Check if task is being edited.
        if ($edit_index === (string) $index) {
          // Show textfield to edit task.
          $form['task_list']["task_$index"] = [
          '#type' => 'textfield',
          '#default_value' => $edit_value ?: $task,
          '#size' => 30,
          '#validated' => FALSE,  // <-- This skips validation during edit.
          ];

          // Save button to save changes.
          $form['task_list']["save_$index"] = [
            '#type' => 'submit',
            '#value' => $this->t('Save'),
            '#name' => 'save_' . $index,
            '#submit' => ['::saveEditedTask'],
            '#ajax' => [
              'callback' => '::myAjaxCallback',
              'wrapper' => 'task-list-wrapper',
            ],
          ];
        }
        else {
          // Display task as plain text with Edit/Delete buttons.
          $form['task_list']["task_$index"] = [
            '#markup' => '<div>' . $task . '</div>',
          ];

          // Edit button to switch to edit mode.
          $form['task_list']["edit_$index"] = [
            '#type' => 'submit',
            '#value' => $this->t('Edit'),
            '#name' => 'edit_' . $index,
            '#submit' => ['::editTaskCallback'],
            '#ajax' => [
              'callback' => '::myAjaxCallback',
              'wrapper' => 'task-list-wrapper',
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
      $this->messenger()->addStatus($this->t('Task added successfully!'));
    }

    // Rebuild the form.
    $form_state->setRebuild(TRUE);
  }

  /**
   * Switch task to edit mode and rebuild form.
   */
  public function editTaskCallback(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $button_name = $triggering_element['#name'];

    // Extract the index from button name (edit_0, edit_1, etc.)
    preg_match('/edit_(\d+)/', $button_name, $matches);
    $index = $matches[1];

    // Set edit mode.
    $tasks = \Drupal::state()->get('todo_tasks', []);
    $form_state->set('edit_index', $index);
    $form_state->set('edit_value', $tasks[$index] ?? '');

    // Rebuild form to reflect edit mode.
    $form_state->setRebuild(TRUE);
  }

  /**
   * Saves the edited task and updates via AJAX.
   */
  public function saveEditedTask(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $button_name = $triggering_element['#name'];

    // Extract the index from button name (save_0, save_1, etc.)
    preg_match('/save_(\d+)/', $button_name, $matches);
    $index = $matches[1];

    $task_value = $form_state->getValue(['task_list', "task_$index"]);
   // $task_value = $form_state->getValue("task_$index");
    $tasks = \Drupal::state()->get('todo_tasks', []);

    // Update task if valid.
    if (isset($tasks[$index]) && !empty($task_value)) {
      $tasks[$index] = $task_value;
      \Drupal::state()->set('todo_tasks', $tasks);
      $this->messenger()->addStatus($this->t('Task updated successfully.'));
    }
    else {
      $this->messenger()->addError($this->t('Task not found or empty value.'));
    }

    // Reset edit mode.
    $form_state->set('edit_index', NULL);
    $form_state->set('edit_value', NULL);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Callback to refresh the task list via AJAX.
   */
  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['task_list']; // Return the updated task list
  }

  /**
   * Deletes the task and redirects to /todo.
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
}
