<?php

namespace Drupal\test_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;

/**
 * Provides a form to edit a task.
 */
class EditTaskForm extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
      return 'edit_task_form';
    }
  
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $task_id = NULL) {

      $task_value = \Drupal::request()->query->get('task_val');

      if (!$task_value) {
        $this->messenger()->addError($this->t('Task not found.'));
        // return new RedirectResponse('/todo');
        $form_state = \Drupal::service('form_builder')->getFormState();
        $form_state->setRedirect('test_module.form');
        return [];
      }
  
      $form['edit_task'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Edit Task'),
        '#default_value' => $task_value,
        '#required' => TRUE,
      ];

      $form['task_id'] = [
        '#type' => 'hidden',
        '#value' => $task_id,
      ];
      
  
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save Changes'),
      ];

      return $form;
    }
  
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
      $task_id = $form_state->getValue('task_id');
      $new_task = $form_state->getValue('edit_task');

      $original_task = \Drupal::request()->query->get('task_val');

      // Update the task in DB.
      \Drupal::database()->update('users_data')
        ->fields(['task' => $new_task])
        ->condition('uid', \Drupal::currentUser()->id())
        ->condition('task', $original_task)
        ->execute();
  
      $this->messenger()->addStatus($this->t('Task updated successfully!'));
  
      // Redirect back to the to-do list.
      $form_state->setRedirect('test_module.form');
    }
  }
  