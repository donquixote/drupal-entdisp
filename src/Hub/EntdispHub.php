<?php


namespace Drupal\entdisp\Hub;

use Drupal\cfrapi\Context\CfrContextInterface;
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
   * @var bool
   */
  private $required = TRUE;

  /*
   * @var \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface|null
   */
  private $genericDisplayManager;

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
   * @param bool $required
   */
  function __construct(InterfaceToConfiguratorInterface $interfaceToConfigurator, EtPluginHubInterface $etPluginHub, $required = TRUE) {
    $this->interfaceToConfigurator = $interfaceToConfigurator;
    $this->etPluginHub = $etPluginHub;
    $this->required = $required;
  }

  /**
   * @return \Drupal\entdisp\Hub\EntdispHubInterface
   */
  function optional() {
    return new self($this->interfaceToConfigurator, $this->etPluginHub, FALSE);
  }

  /**
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  function getGenericDisplayManager() {
    return NULL !== $this->genericDisplayManager
      ? $this->genericDisplayManager
      : $this->genericDisplayManager = $this->contextCreateDisplayManager(NULL);
  }

  /**
   * @param string $entityType
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
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
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfigurator|\Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
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
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfigurator
   */
  private function etCreateDisplayManager($entityType) {
    $context = $this->etPluginHub->etGetContext($entityType);
    return $this->contextCreateDisplayManager($context);
  }

  /**
   * @param string $entityType
   * @param string $bundleName
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfigurator
   */
  private function etBundleCreateDisplayManager($entityType, $bundleName) {
    $context = $this->etPluginHub->etBundleGetContext($entityType, $bundleName);
    return $this->contextCreateDisplayManager($context);
  }

  /**
   * @param \Drupal\cfrapi\Context\CfrContextInterface $context
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfigurator
   */
  private function contextCreateDisplayManager(CfrContextInterface $context = NULL) {
    $configurator = $this->required
      ? $this->interfaceToConfigurator->interfaceGetConfigurator(EntityDisplayInterface::class, $context)
      : $this->interfaceToConfigurator->interfaceGetOptionalConfigurator(EntityDisplayInterface::class, $context);
    return new EntdispConfigurator($configurator);
  }
}
