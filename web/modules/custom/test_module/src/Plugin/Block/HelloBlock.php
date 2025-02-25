<?php

namespace Drupal\test_module\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

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
    return [
      '#theme' => 'block_hello_block',
      '#test_var' => 'Hello from twig!',

      // '#markup' => $this->t('Hello, World!'),
    ];

  }

}
