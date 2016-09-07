<?php

namespace Drupal\entdisp\EntityDisplay;

use Drupal\renderkit\EntityDisplay\EntityDisplayInterface;

class EntdispBrokenEntityDisplay implements EntityDisplayInterface {

  /**
   * @var mixed
   */
  private $invalidHandler;

  /**
   * @return static
   */
  public static function create() {
    return new static();
  }

  /**
   * @param mixed $invalidHandler
   *
   * @return $this
   */
  public function setInvalidHandler($invalidHandler) {
    $this->invalidHandler = $invalidHandler;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getInvalidHandler() {
    return $this->invalidHandler;
  }

  /**
   * Builds render arrays from the entities provided.
   *
   * Both the entities and the resulting render arrays are in plural, to allow
   * for more performant implementations.
   *
   * Array keys and their order must be preserved, although implementations
   * might remove some keys that are empty.
   *
   * @param string $entityType
   *   E.g. 'node' or 'taxonomy_term'.
   * @param object[] $entities
   *   Entity objects for which to build the render arrays.
   *   The array keys can be anything, they don't need to be the entity ids.
   *
   * @return array[]
   *   An array of render arrays, keyed by the original array keys of $entities.
   */
  public function buildEntities($entityType, array $entities) {
    return array();
  }

  /**
   * Same as ->buildEntities(), just for a single entity.
   *
   * @param string $entity_type
   *   E.g. 'node' or 'taxonomy_term'.
   * @param object $entity
   *   Single entity object for which to build a render arary.
   *
   * @return array
   */
  public function buildEntity($entity_type, $entity) {
    return array();
  }
}
