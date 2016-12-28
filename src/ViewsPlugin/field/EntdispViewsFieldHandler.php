<?php

namespace Drupal\entdisp\ViewsPlugin\field;

use Drupal\cfrapi\SummaryBuilder\SummaryBuilder_Static;

/**
 * @see entity_views_handler_field_entity
 */
class EntdispViewsFieldHandler extends EntityViewsFieldHandlerBase {

  /**
   * @var string|null
   */
  private $fieldEntityType;

  /**
   * @var \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  # private $entdispManager;

  /**
   * Key in the options array where the plugin configuration is found.
   */
  const ENTDISP_PLUGIN_KEY = 'entity_display_plugin';

  /**
   * @param string $entityType
   *
   * @throws \RuntimeException
   */
  protected function initEntityType($entityType) {
    $this->fieldEntityType = $entityType;
    // This method is called on every cache rebuild, so we avoid calling entdisp() here.
    # $this->entdispManager = entdisp()->etGetDisplayManager($entityType);
  }

  /**
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  protected function getEntdispManager() {
    return entdisp()->etGetDisplayManager($this->fieldEntityType);
  }

  /**
   * @return array
   *   Format: $[$option_key] = $default_value
   */
  public function option_definition() {
    $options = parent::option_definition();
    $options[self::ENTDISP_PLUGIN_KEY] = ['default' => []];
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

    $form[self::ENTDISP_PLUGIN_KEY] = [
      '#type' => 'entdisp',
      '#title' => t('Entity display'),
      '#default_value' => $this->options[self::ENTDISP_PLUGIN_KEY],
      '#entity_type' => $this->fieldEntityType,
    ];

    return $form;
  }

  /**
   * Returns the summary of the settings in the display.
   */
  public function summary_title() {
    return $this->getEntdispManager()->confGetSummary($this->options[self::ENTDISP_PLUGIN_KEY], new SummaryBuilder_Static());
  }

  /**
   * @param string $entityType
   * @param object[] $entities
   *
   * @return array[] A render array for each entity.
   * A render array for each entity.
   */
  protected function buildMultiple($entityType, array $entities) {

    try {
      return $this->getEntdispManager()
        ->confGetEntityDisplay($this->options[self::ENTDISP_PLUGIN_KEY])
        ->buildEntities($entityType, $entities);
    }
    catch (\Exception $e) {
      watchdog('cfrplugin',
        'Broken entity display plugin in Views field handler for @view_name/@display_id.'
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
