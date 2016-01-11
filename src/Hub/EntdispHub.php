<?php


namespace Drupal\entdisp\Hub;

use Drupal\cfrreflection\CfrGen\InterfaceToConfigurator\InterfaceToConfiguratorInterface;
use Drupal\entdisp\EntdispConfigurator\EntdispConfigurator;
use Drupal\etcfrcontext\EtPluginHubInterface;
use Drupal\renderkit\EntityDisplay\EntityDisplayInterface;

class EntdispHub implements EntdispHubInterface {

  /**
   * @var \Drupal\cfrreflection\CfrGen\InterfaceToConfigurator\InterfaceToConfiguratorInterface
   */
  private $interfaceToConfigurator;

  /**
   * @var \Drupal\etcfrcontext\EtPluginHubInterface
   */
  private $etPluginHub;

  /**
   * @var \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface[]
   */
  private $displayManagersByEt = array();

  /**
   * @var \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface[]
   */
  private $displayManagersByEtBundle = array();

  /**
   * @return \Drupal\entdisp\Hub\EntdispHubInterface
   */
  static function create() {
    return new self(\cfrplugin(), \etcfrcontext());
  }

  /**
   * @param \Drupal\cfrreflection\CfrGen\InterfaceToConfigurator\InterfaceToConfiguratorInterface $interfaceToConfigurator
   * @param \Drupal\etcfrcontext\EtPluginHubInterface $etPluginHub
   */
  function __construct(InterfaceToConfiguratorInterface $interfaceToConfigurator, EtPluginHubInterface $etPluginHub) {
    $this->interfaceToConfigurator = $interfaceToConfigurator;
    $this->etPluginHub = $etPluginHub;
  }

  /**
   * @param string $entityType
   * @param bool $required
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  function etGetDisplayManager($entityType, $required = TRUE) {
    return array_key_exists($entityType, $this->displayManagersByEt)
      ? $this->displayManagersByEt[$entityType]
      : $this->displayManagersByEt[$entityType] = $this->etCreateDisplayManager($entityType, $required);
  }

  /**
   * @param string $entityType
   * @param string $bundleName
   * @param bool $required
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfigurator|\Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  function etBundleGetDisplayManager($entityType, $bundleName, $required = TRUE) {
    $key = $entityType . ':' . $bundleName;
    return array_key_exists($key, $this->displayManagersByEtBundle)
      ? $this->displayManagersByEtBundle[$key]
      : $this->displayManagersByEtBundle[$key] = $this->etBundleCreateDisplayManager($entityType, $bundleName, $required);
  }

  /**
   * @param string $entityType
   * @param bool $required
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfigurator
   */
  private function etCreateDisplayManager($entityType, $required = TRUE) {
    $context = $this->etPluginHub->etGetContext($entityType);
    $configurator = $required
      ? $this->interfaceToConfigurator->interfaceGetConfigurator(EntityDisplayInterface::class, $context)
      : $this->interfaceToConfigurator->interfaceGetOptionalConfigurator(EntityDisplayInterface::class, $context);
    return new EntdispConfigurator($configurator);
  }

  /**
   * @param string $entityType
   * @param string $bundleName
   * @param bool $required
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfigurator
   */
  private function etBundleCreateDisplayManager($entityType, $bundleName, $required = TRUE) {
    $context = $this->etPluginHub->etBundleGetContext($entityType, $bundleName);
    $configurator = $required
      ? $this->interfaceToConfigurator->interfaceGetConfigurator(EntityDisplayInterface::class, $context)
      : $this->interfaceToConfigurator->interfaceGetOptionalConfigurator(EntityDisplayInterface::class, $context);
    return new EntdispConfigurator($configurator);
  }

} 
