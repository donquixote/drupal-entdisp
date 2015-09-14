<?php

namespace Drupal\entdisp\Plugin\views\row;

/**
 * @see \entity_views_plugin_row_entity_view
 * @see \views_plugin_ds_entity_view
 * @see \views_plugin_row_node_view
 */
abstract class EntityRowPluginBase extends \views_plugin_row {

  /**
   * @var string
   */
  private $entityType;

  /**
   * @param \view $view
   * @param \views_display $display
   * @param array|null $options
   *
   * @see \entity_views_plugin_row_entity_view::init()
   */
  public function init(&$view, &$display, $options = NULL) {
    parent::init($view, $display, $options);

    // Initialize the entity-type used.
    $table_data = views_fetch_data($this->view->base_table);
    $this->entityType = $table_data['table']['entity type'];
    // Set base table and field information as used by views_plugin_row to
    // select the entity id if used with default query class.
    $info = entity_get_info($this->entityType);
    if (!empty($info['base table']) && $info['base table'] == $this->view->base_table) {
      // The base class, \views_plugin_row, does not properly declare all properties.
      /** @noinspection PhpUndefinedFieldInspection */
      $this->base_table = $info['base table'];
      /** @noinspection PhpUndefinedFieldInspection */
      $this->base_field = $info['entity keys']['id'];
    }

    // Let child classes do something based on the entity type.
    $this->initEntityType($this->entityType);
  }

  /**
   * Do something based on the entity type.
   *
   * Called from $this->init().
   *
   * @param string $entityType
   */
  abstract protected function initEntityType($entityType);

  /**
   * @param object[] $rows
   *
   * @see \entity_views_plugin_row_entity_view::pre_render()
   */
  public function pre_render($rows) {
    if (empty($rows)) {
      return;
    }
    $entities = $this->getResultEntities($rows);
    if (empty($entities)) {
      return;
    }
    // Build the entities.
    $builds = $this->buildMultiple($this->entityType, $entities);
    foreach ($rows as $rowIndex => $row) {
      if (isset($builds[$rowIndex])) {
        $row->entdisp_row_build = $builds[$rowIndex];
      }
      else {
        unset($row->entdisp_row_build);
      }
    }
  }

  /**
   * @param object[] $rows
   *
   * @return object[]
   *
   * @see entity_views_plugin_row_entity_view::pre_render()
   */
  protected function getResultEntities(array $rows) {
    if (empty($this->entityType)) {
      // @todo Show a warning / log to watchdog.
      return array();
    }
    $relationship = !empty($this->relationship) ? $this->relationship : NULL;
    $field_alias = isset($this->field_alias) ? $this->field_alias : NULL;
    // Some views query classes want/allow a third parameter specifying the field name.
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    list($entityType, $entities) = $this->view->query->get_result_entities($rows, $relationship, $field_alias);
    if (empty($entityType)) {
      // @todo Show a warning / log to watchdog.
      return array();
    }
    if ($entityType !== $this->entityType) {
      // @todo Show a warning / log to watchdog.
      return array();
    }
    return $entities;
  }

  /**
   * @param object $row
   *
   * @return null|string
   */
  public function render($row) {
    return isset($row->entdisp_row_build)
      ? drupal_render($row->entdisp_row_build)
      : NULL;
  }

  /**
   * @param string $entityType
   * @param object[] $entities
   *
   * @return array[]
   */
  abstract protected function buildMultiple($entityType, array $entities);
}
