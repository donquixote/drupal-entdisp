<?php

namespace Drupal\entdisp\ViewsPlugin\field;

use Drupal\entdisp\Util\EntityUtil;

/**
 * Some properties are not properly declared in views base classes.
 *
 * @property int|null $position
 *   Index to distinguish this field handler from other field handlers within
 *   the same view, @see \view::_pre_query().
 */
abstract class EntityViewsFieldHandlerBase extends \views_handler_field {

  /**
   * @var string|null
   */
  private $fieldEntityType;

  /**
   * Overrides @see \views_handler_field::$query, to declare other query types
   * that implement an ->add_field() method, or where ->get_result_entities()
   * has 3 parameters instead of 2.
   *
   * @var \views_plugin_query|\views_plugin_query_default|\SearchApiViewsQuery
   */
  var $query = NULL;

  /**
   * Initializes the entity type with the field's entity type.
   *
   * @see \entity_views_handler_field_entity::init()
   *
   * @param \view $view
   * @param array $options
   */
  public function init(&$view, &$options) {
    parent::init($view, $options);

    $fieldEntityType = EntityUtil::entityPropertyExtractInnermostType($this->definition['type']);

    if (!$fieldEntityType) {
      $message = 'Cannot determine field entity type.';
      watchdog('entdisp', $message);
      if (user_access('administer site configuration')) {
        drupal_set_message($message, 'warning');
      }
    }

    $this->fieldEntityType = $fieldEntityType;
    $this->initEntityType($fieldEntityType);
  }

  /**
   * @param string $entityType
   */
  abstract protected function initEntityType($entityType);

  /**
   * Overriden to add the field for the entity id.
   *
   * @see \views_handler_field_entity::query()
   */
  public function query() {

    $table_alias = $base_table = $this->view->base_table;
    $base_field = $this->view->base_field;

    if (!empty($this->relationship)) {
      foreach ($this->view->relationship as $relationship) {
        if ($relationship->alias == $this->relationship) {
          $base_table = $relationship->definition['base'];
          $table_alias = $relationship->alias;

          $table_data = views_fetch_data($base_table);
          $base_field = empty($relationship->definition['base field']) ? $table_data['table']['base']['field'] : $relationship->definition['base field'];
        }
      }
    }

    // Add the field if the query back-end implements an add_field() method,
    // just like the default back-end.
    if (method_exists($this->query, 'add_field')) {
      $this->field_alias = $this->query->add_field($table_alias, $base_field, '');
    }

    // Additional fields are not needed.
    # $this->add_additional_fields();
  }

  /**
   * Runs before any fields are rendered.
   *
   * This gives the handlers some time to set up before any handler has
   * been rendered.
   *
   * @param object[] $rows
   *   An array of all objects returned from the query.
   */
  public function pre_render(&$rows) {

    $entities = $this->getResultEntities($rows);

    // Build the entities.
    $builds = $this->buildMultiple($this->fieldEntityType, $entities);

    foreach ($rows as $rowIndex => $row) {
      if (isset($builds[$rowIndex])) {
        $row->entdisp_field_builds[$this->position] = $builds[$rowIndex];
      }
      else {
        unset($row->entdisp_field_builds[$this->position]);
      }
    }
  }

  /**
   * @param object[] $rows
   *
   * @return object[]
   *
   * @see EntityFieldHandlerHelper::pre_render()
   */
  private function getResultEntities(array $rows) {

    $relationship = !empty($this->relationship) ? $this->relationship : NULL;

    $resultEntitiesOrFalse = $this->query->get_result_entities(
      $rows,
      $relationship,
      // Some views query classes want/allow a third parameter specifying the
      // field name.
      $this->field_alias);

    if (FALSE === $resultEntitiesOrFalse) {
      $this->watchdogAndMessage(
        'Cannot fetch entities: !method returned !false.',
        [
          '!method' => '<code>'
            . get_class($this->query) . '::get_result_entities()'
            . '</code>',
          '!false' => '<code>FALSE</code>',
        ]);

      return [];
    }

    list($entityType, $entities) = $resultEntitiesOrFalse;

    if ($entityType !== $this->fieldEntityType) {
      $this->watchdogAndMessage(
        'Entity type mismatch. Expected @expected, found @found instead.',
        [
          '@expected' => "'$entityType'",
          '@found' => "'$this->fieldEntityType'",
        ]);
      return [];
    }

    return $entities;
  }

  /**
   * @param object $row
   *
   * @return string
   */
  public function render($row) {
    return isset($row->entdisp_field_builds[$this->position])
      ? drupal_render($row->entdisp_field_builds[$this->position])
      : NULL;
  }

  /**
   * @param string $entityType
   * @param object[] $entities
   *
   * @return array[]
   *   A render array for each entity.
   */
  abstract protected function buildMultiple($entityType, array $entities);

  /**
   * @param string $message
   * @param string[] $replacements
   */
  private function watchdogAndMessage($message, array $replacements = []) {

    $message .= "<br/>\n"
      . 'In Views field %field_id of !views_display.';

    $view = $this->view;
    $view_name = $view->name;
    $view_label = $view->get_human_name();
    $display_id = $view->current_display;
    $display = $view->display[$display_id];
    $display_label = $display->display_title;

    $replacements += [
      '!views_display' => l(
        "$view_label: $display_label",
        "admin/structure/views/view/$view_name/edit/$display_id",
        ['attributes' => ['target' => '_blank']]),
      '%field_id' => $this->options['id'],
    ];

    watchdog('entdisp', $message, $replacements);

    if (user_access('administer site configuration')) {
      drupal_set_message(t($message, $replacements), 'warning');
    }
  }

}
