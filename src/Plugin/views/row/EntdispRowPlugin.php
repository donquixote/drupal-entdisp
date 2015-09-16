<?php

namespace Drupal\entdisp\Plugin\views\row;

class EntdispRowPlugin extends EntityRowPluginBase {

  /**
   * @var \Drupal\entdisp\Manager\EntdispPluginManagerInterface
   */
  private $entdispManager;

  /**
   * Do something based on the entity type.
   *
   * Called from $this->init().
   *
   * @param string $entityType
   */
  protected function initEntityType($entityType) {
    $this->entdispManager = entdisp()->etGetManager($entityType);
  }

  /**
   * @return array
   *   Format: $[$option_key] = $default_value
   */
  public function option_definition() {
    $options = parent::option_definition();
    $options['entity_display_plugin'] = array('default' => array());
    return $options;
  }

  /**
   * Overrides the options form.
   *
   * @param array $form
   * @param array $form_state
   *
   * @return array
   */
  public function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['entity_display_plugin'] = array(
      '#type' => UIKIT_ELEMENT_TYPE,
      '#uikit_element_object' => $this->entdispManager->getUikitElementType(),
      '#default_value' => $this->options['entity_display_plugin'],
    );

    return $form;
  }

  /**
   * @param string $entityType
   * @param object[] $entities
   *
   * @return array[]
   *   A render array for each entity.
   */
  protected function buildMultiple($entityType, array $entities) {
    $display = $this->entdispManager->settingsGetEntityDisplay($this->options['entity_display_plugin']);
    return $display->buildEntities($entityType, $entities);
  }
}
