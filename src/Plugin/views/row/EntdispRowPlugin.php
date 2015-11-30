<?php

namespace Drupal\entdisp\Plugin\views\row;

class EntdispRowPlugin extends EntityRowPluginBase {

  /**
   * @var \Drupal\entdisp\Manager\EntdispManagerInterface
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
    $this->entdispManager = entdisp()->etGetDisplayManager($entityType);
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

    if (isset($form_state['values']['row_options']['entity_display_plugin'])) {
      $conf = $form_state['values']['row_options']['entity_display_plugin'];
    }
    elseif (isset($form_state['input']['row_options']['entity_display_plugin'])) {
      $conf = $form_state['input']['row_options']['entity_display_plugin'];
    }
    else {
      $conf = $this->options['entity_display_plugin'];
    }

    // Force the views UI height..
    if (FALSE) {
      $form['placeholder'] = array(
        '#type' => 'container',
        '#attributes' => array(
          'style' => 'min-height: 600px; width: 10px; float: left; margin-right: -10px;',
        ),
      );
    }

    $form['entity_display_plugin'] = $this->entdispManager->confGetForm($conf);

    return $form;
  }

  /**
   * Returns the summary of the settings in the display.
   */
  function summary_title() {
    return $this->entdispManager->confGetSummary($this->options['entity_display_plugin']);
  }

  /**
   * @param string $entityType
   * @param object[] $entities
   *
   * @return array[]
   *   A render array for each entity.
   */
  protected function buildMultiple($entityType, array $entities) {
    $display = $this->entdispManager->confGetEntityDisplay($this->options['entity_display_plugin']);
    return $display->buildEntities($entityType, $entities);
  }
}
