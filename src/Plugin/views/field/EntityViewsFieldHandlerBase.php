<?php

namespace Drupal\entdisp\Plugin\views\field;

abstract class EntityViewsFieldHandlerBase extends \views_handler_field {

  /**
   * @var string
   */
  private $fieldEntityType;

  /**
   * Initialize the entity type with the field's entity type.
   *
   * @see \entity_views_handler_field_entity::init()
   *
   * @param \view $view
   * @param array $options
   */
  public function init(&$view, &$options) {
    parent::init($view, $options);
    $fieldEntityType = entity_property_extract_innermost_type($this->definition['type']);
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
   * @return string
   */
  protected function getFieldEntityType() {
    return $this->fieldEntityType;
  }

  function query() {
    // do nothing -- to override the parent query.
  }

  /**
   * Run before any fields are rendered.
   *
   * This gives the handlers some time to set up before any handler has
   * been rendered.
   *
   * @param object[] $rows
   *   An array of all objects returned from the query.
   */
  function pre_render(&$rows) {
    /**
     * @var string $entityType
     * @var object[] $entities
     */
    list($entityType, $entities) = $this->getResultEntities($rows);
    if ($entityType !== $this->fieldEntityType) {
      $message = 'Entity type mismatch.';
      watchdog('entdisp', $message);
      if (user_access('administer site configuration')) {
        drupal_set_message($message, 'warning');
      }
    }
    // Build the entities.
    $builds = $this->buildMultiple($entities);
    foreach ($rows as $rowIndex => $row) {
      if (isset($builds[$rowIndex])) {
        /** @noinspection PhpUndefinedFieldInspection */
        $row->entdisp_field_builds[$this->position] = $builds[$rowIndex];
      }
      else {
        /** @noinspection PhpUndefinedFieldInspection */
        unset($row->entdisp_field_builds[$this->position]);
      }
    }
  }

  /**
   * @param object[] $rows
   *
   * @return mixed[]
   *
   * @see EntityFieldHandlerHelper::pre_render()
   */
  protected function getResultEntities(array $rows) {
    $relationship = !empty($this->relationship) ? $this->relationship : NULL;
    $field_alias = isset($this->real_field) ? $this->real_field : NULL;
    // Some views query classes want/allow a third parameter specifying the field name.
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    list($entityType, $entities) = $this->view->query->get_result_entities($rows, $relationship, $field_alias);
    return array($entityType, $entities);
  }

  /**
   * @param object $row
   *
   * @return string
   */
  function render($row) {
    /** @noinspection PhpUndefinedFieldInspection */
    return isset($row->entdisp_field_builds[$this->position])
      ? drupal_render($row->entdisp_field_builds[$this->position])
      : NULL;
  }

  /**
   * @param object[] $entities
   *
   * @return array[]
   *   A render array for each entity.
   */
  abstract protected function buildMultiple(array $entities);

  /**
   * @param object[] $entities
   *
   * @return array
   */
  protected function doBuildMultiple(array $entities) {
    $builds = array();
    foreach ($entities as $rowIndex => $entity) {
      $builds[$rowIndex] = array(
        '#markup' => entity_label($this->fieldEntityType, $entity),
      );
    }
    return $builds;
  }

}
