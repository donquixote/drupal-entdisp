<?php

namespace Drupal\entdisp\Plugin\views\row;

class EntdispRowPlugin extends EntityRowPluginBase {

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
      '#type' => UNIPLUGIN_ELEMENT_TYPE,
      '#plugin_type' => 'entdisp',
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
    $display = entdisp()->handlerManager->settingsKeyGetHandler($this->options, 'entity_display_plugin');
    return $display->buildEntities($entityType, $entities);
  }
}
