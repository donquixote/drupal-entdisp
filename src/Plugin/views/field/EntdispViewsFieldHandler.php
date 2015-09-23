<?php

namespace Drupal\entdisp\Plugin\views\field;

/**
 * @see entity_views_handler_field_entity
 */
class EntdispViewsFieldHandler extends EntityViewsFieldHandlerBase {

  /**
   * @var \Drupal\entdisp\Manager\EntdispPluginManagerInterface
   */
  private $entdispManager;

  /**
   * @param string $entityType
   *
   * @throws \RuntimeException
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
   *
   * @throws \RuntimeException
   */
  public function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['entity_display_plugin'] = array(
      '#type' => UIKIT_ELEMENT_TYPE,
      UIKIT_K_TYPE_OBJECT => $this->entdispManager->getUikitElementType(),
      '#default_value' => $this->options['entity_display_plugin'],
    );

    return $form;
  }

  /**
   * Returns the summary of the settings in the display.
   */
  function summary_title() {
    return $this->entdispManager->settingsGetSummary($this->options['entity_display_plugin']);
  }

  /**
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
