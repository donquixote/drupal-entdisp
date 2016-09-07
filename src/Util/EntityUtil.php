<?php

namespace Drupal\entdisp\Util;

use Drupal\cfrapi\Util\UtilBase;

class EntityUtil extends UtilBase {

  /**
   * Extracts the innermost type for a type string like list<list<date>>.
   *
   * Copied from "entity" module:
   * https://drupal.org/project/entity
   *
   * @param string $type
   *   The type to examine.
   *
   * @return string
   *   For list types, the innermost type. The type itself otherwise.
   *
   * @see entity_property_extract_innermost_type()
   */
  public static function entityPropertyExtractInnermostType($type) {
    while (strpos($type, 'list<') === 0 && $type[strlen($type) - 1] === '>') {
      $type = substr($type, 5, -1);
    }
    return $type;
  }
}
