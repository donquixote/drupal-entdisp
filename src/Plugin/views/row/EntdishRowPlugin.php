<?php

namespace Drupal\entdish\Plugin\views\row;

class EntdishRowPlugin extends EntityRowPluginBase {

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
    $plugin_id = isset($this->options['entity_display_plugin']['plugin_id'])
      ? $this->options['entity_display_plugin']['plugin_id']
      : 'entdish_title_link';
    $display = _entdish_get_handler($plugin_id);
    return $display->buildMultiple($entityType, $entities);
  }
}
