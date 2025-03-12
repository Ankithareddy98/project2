<?php

namespace Drupal\test_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

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
    ];

    return $form;
  }  
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display Task here.
    $this->messenger()->addStatus($this->t('@task', ['@task' => $form_state->getValue('input')]));
  }
}