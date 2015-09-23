<?php


namespace Drupal\entdisp;

use Drupal\entdisp\Manager\EntdispPluginManager;
use Drupal\uniplugin\PluginTypeDIC\DefaultPluginTypeServiceContainer;

class EntdispHub {

  /**
   * @var \Drupal\entdisp\Manager\EntdispPluginManagerInterface[]
   */
  private $managers = array();

  /**
   * @var \Drupal\uniplugin\PluginTypeDIC\DefaultPluginTypeServiceContainer[]
   *   Format: $[$entityType] = $pluginTypeDIC
   */
  private $uniPluginTypeDICs = array();

  /**
   * @param string $entityType
   *
   * @return \Drupal\entdisp\Manager\EntdispPluginManagerInterface
   */
  function etGetManager($entityType) {
    if (array_key_exists($entityType, $this->managers)) {
      return $this->managers[$entityType];
    }
    else {
      return $this->managers[$entityType] = new EntdispPluginManager($this->etGetPluginTypeDIC($entityType)->manager, $entityType);
    }
  }

  /**
   * @param string $entity_type
   *
   * @return \Drupal\uniplugin\PluginTypeDIC\DefaultPluginTypeServiceContainer|null
   */
  private function etGetPluginTypeDIC($entity_type) {
    if (array_key_exists($entity_type, $this->uniPluginTypeDICs)) {
      return $this->uniPluginTypeDICs[$entity_type];
    }
    else {
      /* @see hook_entdisp_info() */
      return $this->uniPluginTypeDICs[$entity_type] = new DefaultPluginTypeServiceContainer(
        'entdisp_info',
        array($entity_type));
    }
  }

} 
