<?php

namespace Drupal\entdish\Plugin\views\field;

/**
 * @see entity_views_handler_field_entity
 */
class EntdishViewsFieldHandler extends EntityViewsFieldHandlerBase {

  /**
   * @return array
   *   Format: $[$option_key] = $default_value
   */
  public function option_definition() {
    $options = parent::option_definition();
    $options['entity_display_plugin'] = array('default' => NULL);
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
      '#type' => 'entdish_plugin',
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
    $settings = isset($this->options['entity_display_plugin'])
      ? $this->options['entity_display_plugin']
      : array();
    $display = _entdish_settings_get_handler($settings);
    return $display->buildMultiple($entityType, $entities);
  }
}
