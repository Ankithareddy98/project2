<?php

namespace Drupal\test_module\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Hello' Block.
 */
#[Block(
    id: "hello_block",
    admin_label: new TranslatableMarkup("Hello block"),
    category: new TranslatableMarkup("Hello World")
  )]

class HelloBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $config = $this->getConfiguration();
    $firstName = !empty($config['first_name']) ? $config['first_name'] : $this->t('First Name Empty');
    $lastName = !empty($config['last_name']) ? $config['last_name'] : $this->t('Last Name Empty');
    $display_value = !empty($config['display_last_name']);
    $display_last_name1 = $display_value ? "$firstName $lastName" : $firstName;
    
    return [
      '#theme' => 'block_hello_block',
      '#test_var' => 'Hello from twig!',
      '#firstName_entered' => $firstName,
      '#lastName_entered' => $lastName,
      '#conditional_display' => $display_last_name1,
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'first_name' => '',
      'last_name' => '',
      'display_last_name' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('FirstName'),
      '#description' => $this->t('Enter the First Name here'),
      '#default_value' => $this->configuration['first_name'],
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('LastName'),
      '#description' => $this->t('Enter the Last Name here'),
      '#default_value' => $this->configuration['last_name'],
    ];

    $form['display_last_name'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display Last Name'),
      '#description' => $this->t('Please check the box to display last name'),
      '#default_value' => $this->configuration['display_last_name'] ?? 0,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['first_name'] = $values['first_name'];
    $this->configuration['last_name'] = $values['last_name'];
    $this->configuration['display_last_name'] = $values['display_last_name'];
  }

}
