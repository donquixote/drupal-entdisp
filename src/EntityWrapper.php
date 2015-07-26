<?php

namespace Drupal\entdish;

use Drupal\renderkit\EntityDisplay\EntityDisplayInterface;

class EntityWrapper {

  /**
   * @var string
   */
  protected $type;

  /**
   * @var object
   */
  protected $entity;

  /**
   * @param string $type
   *   The entity type, e.g. 'node'.
   * @param object $entity
   *   The entity object.
   */
  function __construct($type, $entity) {
    $this->type = $type;
    $this->entity = $entity;
  }

  /**
   * Useful method for use in node or entity templates.
   *
   * @param \Drupal\renderkit\EntityDisplay\EntityDisplayInterface|string $handlerOrId
   *
   * @return string
   *   Rendered HTML.
   */
  function __invoke($handlerOrId) {
    if (is_object($handlerOrId)) {
      $handler = $handlerOrId;
      if (!$handler instanceof EntityDisplayInterface) {
        throw new \InvalidArgumentException();
      }
    }
    elseif (is_string($handlerOrId)) {
      $handler = _entdish_get_handler($handlerOrId);
      if (!$handler instanceof EntityDisplayInterface) {
        throw new \InvalidArgumentException();
      }
    }
    else {
      throw new \InvalidArgumentException();
    }
    $build = $this->build($handler);
    return drupal_render($build);
  }

  /**
   * @param \Drupal\renderkit\EntityDisplay\EntityDisplayInterface $handler
   *
   * @return array
   *   A Drupal render array.
   */
  function build(EntityDisplayInterface $handler) {
    $builds = $handler->buildMultiple($this->type, array($this->entity));
    return isset($builds[0])
      ? $builds[0]
      // @todo Is array() really a useful fallback?
      : array();
  }

  /**
   * @param \Drupal\renderkit\EntityDisplay\EntityDisplayInterface $handler
   * @param string[] $classes
   *
   * @return array
   */
  function buildWithContainerClasses(EntityDisplayInterface $handler, array $classes) {
    $build = $this->build($handler);
    return array(
      $build,
      '#theme_wrappers' => array('container'),
      '#attributes' => array('class' => $classes),
    );
  }

  /**
   * @param \Drupal\renderkit\EntityDisplay\EntityDisplayInterface $handler
   * @param string $class
   *
   * @return array
   */
  function buildWithContainerClass(EntityDisplayInterface $handler, $class) {
    return $this->buildWithContainerClasses($handler, array($class));
  }
}
