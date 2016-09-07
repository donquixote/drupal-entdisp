<?php
namespace Drupal\entdisp\Hub;

interface EntdispHubInterface {

  /**
   * @return \Drupal\entdisp\Hub\EntdispHubInterface
   */
  public function optional();

  /**
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  public function getGenericDisplayManager();

  /**
   * @param string $entityType
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  public function etGetDisplayManager($entityType);

  /**
   * @param string $entityType
   * @param string $bundleName
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  public function etBundleGetDisplayManager($entityType, $bundleName);
}
