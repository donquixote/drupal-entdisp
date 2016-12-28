<?php

namespace Drupal\entdisp\ViewsPlugin\row;

use Drupal\cfrapi\SummaryBuilder\SummaryBuilder_Static;

class EntdispViewsRowPlugin extends EntityViewsRowPluginBase {

  /**
   * @var \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  private $entdispManager;

  /**
   * @var string
   */
  private $entdispEntityType;

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
    $this->entdispEntityType = $entityType;
  }

  /**
   * @return array
   *   Format: $[$option_key] = $default_value
   */
  public function option_definition() {
    $options = parent::option_definition();
    $options[ENTDISP_PLUGIN_KEY] = ['default' => []];
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

    // Force the views UI height..
    if (FALSE) {
      $form['placeholder'] = [
        '#type' => 'container',
        '#attributes' => [
          'style' => 'min-height: 600px; width: 10px; float: left; margin-right: -10px;',
        ],
      ];
    }

    $form[ENTDISP_PLUGIN_KEY] = [
      /* @see entdisp_element_info() */
      '#type' => 'entdisp',
      '#title' => t('Row entity display'),
      '#default_value' => $this->options[ENTDISP_PLUGIN_KEY],
      '#entity_type' => $this->entdispEntityType,
    ];

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

    try {
      return $this->entdispManager
        ->confGetEntityDisplay($this->options[ENTDISP_PLUGIN_KEY])
        ->buildEntities($entityType, $entities);
    }
    catch (\Exception $e) {
      watchdog('cfrplugin',
        'Broken entity display plugin in Views row plugin for @view_name/@display_id.'
        . "\n" . 'Exception message: %message',
        [
          '@view_name' => $this->view->name,
          '@display_id' => $this->view->current_display,
          '%message' => $e->getMessage(),
        ],
        WATCHDOG_WARNING);

      return [];
    }
  }
}
