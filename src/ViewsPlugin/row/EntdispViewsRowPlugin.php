<?php

namespace Drupal\entdisp\ViewsPlugin\row;

use Drupal\cfrapi\SummaryBuilder\SummaryBuilder_Static;

class EntdispViewsRowPlugin extends EntityViewsRowPluginBase {

  /**
   * @var \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  private $entdispManager;

  const ENTDISP_PLUGIN_KEY = 'entity_display_plugin';

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
    $options[ENTDISP_PLUGIN_KEY] = array('default' => array());
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

    if (isset($form_state['values']['row_options'][ENTDISP_PLUGIN_KEY])) {
      $conf = $form_state['values']['row_options'][ENTDISP_PLUGIN_KEY];
    }
    elseif (isset($form_state['input']['row_options'][ENTDISP_PLUGIN_KEY])) {
      $conf = $form_state['input']['row_options'][ENTDISP_PLUGIN_KEY];
    }
    else {
      $conf = $this->options[ENTDISP_PLUGIN_KEY];
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

    $form[ENTDISP_PLUGIN_KEY] = $this->entdispManager->confGetForm($conf, t('Row entity display'));

    return $form;
  }

  /**
   * Returns the summary of the settings in the display.
   */
  public function summary_title() {
    return $this->entdispManager->confGetSummary($this->options[ENTDISP_PLUGIN_KEY], new SummaryBuilder_Static());
  }

  /**
   * @param string $entityType
   * @param object[] $entities
   *
   * @return array[]
   *   A render array for each entity.
   */
  protected function buildMultiple($entityType, array $entities) {
    $display = $this->entdispManager->confGetEntityDisplay($this->options[ENTDISP_PLUGIN_KEY]);
    return $display->buildEntities($entityType, $entities);
  }
}
