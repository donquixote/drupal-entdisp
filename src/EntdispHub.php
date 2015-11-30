<?php


namespace Drupal\entdisp;

use Drupal\entdisp\Manager\EntdispManager;
use Drupal\renderkit\EntityDisplay\EntityDisplayInterface;

class EntdispHub {

  /**
   * @var \Drupal\entdisp\Manager\EntdispManagerInterface[]
   */
  private $displayManagersByEt = array();

  /**
   * @var \Drupal\entdisp\Manager\EntdispManagerInterface[]
   */
  private $displayManagersByEtBundle = array();

  /**
   * @param string $entityType
   *
   * @return \Drupal\entdisp\Manager\EntdispManagerInterface
   */
  function etGetDisplayManager($entityType) {
    return array_key_exists($entityType, $this->displayManagersByEt)
      ? $this->displayManagersByEt[$entityType]
      : $this->displayManagersByEt[$entityType] = $this->etCreateDisplayManager($entityType);
  }

  /**
   * @param string $entityType
   * @param string $bundleName
   *
   * @return \Drupal\entdisp\Manager\EntdispManager|\Drupal\entdisp\Manager\EntdispManagerInterface
   */
  function etBundleGetDisplayManager($entityType, $bundleName) {
    $key = $entityType . ':' . $bundleName;
    return array_key_exists($key, $this->displayManagersByEtBundle)
      ? $this->displayManagersByEtBundle[$key]
      : $this->displayManagersByEtBundle[$key] = $this->etBundleCreateDisplayManager($entityType, $bundleName);
  }

  /**
   * @param string $entityType
   *
   * @return \Drupal\entdisp\Manager\EntdispManager
   */
  private function etCreateDisplayManager($entityType) {
    $context = etplugin()->etGetContext($entityType);
    $handlerMap = uniplugin()->interfaceContextGetHandlerMap(EntityDisplayInterface::class, $context);
    return new EntdispManager($handlerMap);
  }

  /**
   * @param string $entityType
   * @param string $bundleName
   *
   * @return \Drupal\entdisp\Manager\EntdispManager
   */
  private function etBundleCreateDisplayManager($entityType, $bundleName) {
    $context = etplugin()->etBundleGetContext($entityType, $bundleName);
    $handlerMap = uniplugin()->interfaceContextGetHandlerMap(EntityDisplayInterface::class, $context);
    return new EntdispManager($handlerMap);
  }

} 
