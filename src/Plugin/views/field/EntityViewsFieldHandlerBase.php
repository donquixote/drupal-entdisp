<?php

namespace Drupal\entdisp\Plugin\views\field;

abstract class EntityViewsFieldHandlerBase extends \views_handler_field {

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
    // Build the entities.
    $builds = $this->buildMultiple($entityType, $entities);
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
   * @param string $entityType
   * @param object[] $entities
   *
   * @return array[]
   *   A render array for each entity.
   */
  abstract protected function buildMultiple($entityType, array $entities);

  protected function doBuildMultiple($entityType, array $entities) {
    $builds = array();
    foreach ($entities as $rowIndex => $entity) {
      $builds[$rowIndex] = array(
        '#markup' => entity_label($entityType, $entity),
      );
    }
    return $builds;
  }

}
