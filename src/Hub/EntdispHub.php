<?php


namespace Drupal\entdisp\Hub;

use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\cfrreflection\CfrGen\InterfaceToConfigurator\InterfaceToConfiguratorInterface;
use Drupal\entdisp\EntdispConfigurator\EntdispConfigurator;
use Drupal\etcfrcontext\EtCfrContextHubInterface;
use Drupal\renderkit\EntityDisplay\EntityDisplayInterface;

class EntdispHub implements EntdispHubInterface {

  /**
   * @var \Drupal\cfrreflection\CfrGen\InterfaceToConfigurator\InterfaceToConfiguratorInterface
   */
  private $interfaceToConfigurator;

  /**
   * @var \Drupal\etcfrcontext\EtCfrContextHubInterface
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
  public static function create() {
    return new self(\cfrplugin(), \etcfrcontext());
  }

  /**
   * @param \Drupal\cfrreflection\CfrGen\InterfaceToConfigurator\InterfaceToConfiguratorInterface $interfaceToConfigurator
   * @param \Drupal\etcfrcontext\EtCfrContextHubInterface $etPluginHub
   * @param bool $required
   */
  public function __construct(InterfaceToConfiguratorInterface $interfaceToConfigurator, EtCfrContextHubInterface $etPluginHub, $required = TRUE) {
    $this->interfaceToConfigurator = $interfaceToConfigurator;
    $this->etPluginHub = $etPluginHub;
    $this->required = $required;
  }

  /**
   * @return \Drupal\entdisp\Hub\EntdispHubInterface
   */
  public function optional() {
    return new self($this->interfaceToConfigurator, $this->etPluginHub, FALSE);
  }

  /**
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  public function getGenericDisplayManager() {
    return NULL !== $this->genericDisplayManager
      ? $this->genericDisplayManager
      : $this->genericDisplayManager = $this->contextCreateDisplayManager(NULL);
  }

  /**
   * @param string $entityType
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  public function etGetDisplayManager($entityType) {
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
  public function etBundleGetDisplayManager($entityType, $bundleName) {
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
