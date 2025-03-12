<?php

namespace Drupal\test_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

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
    if (!$form_state->get('inputs')) {
      $form_state->set('inputs', []);
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

    // Retrieve saved inputs
    $inputs = $form_state->get('inputs');
    if (!empty($inputs)) {
      $form['task_list']['inputs'] = [
        '#theme' => 'item_list',
        '#items' => $inputs,
        '#title' => $this->t('Task List'),
      ];
    }

    return $form;
  }  
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display Task here.
   // $this->messenger()->addStatus($this->t('@task', ['@task' => $form_state->getValue('input')]));
   $input_value = $form_state->getValue('input');

   $inputs = $form_state->get('inputs');
   $inputs[] = $input_value;
   $form_state->set('inputs', $inputs);

   $form_state->setValue('input', '');

   $form_state->setRebuild(TRUE);
  }

  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['task_list']; // Return the updated task list container
  }
  
}

