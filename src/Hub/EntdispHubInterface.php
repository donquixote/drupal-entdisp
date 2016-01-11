<?php
namespace Drupal\entdisp\Hub;

interface EntdispHubInterface {

  /**
   * @return \Drupal\entdisp\Hub\EntdispHubInterface
   */
  function optional();

  /**
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  function getGenericDisplayManager();

  /**
   * @param string $entityType
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  function etGetDisplayManager($entityType);

  /**
   * @param string $entityType
   * @param string $bundleName
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  function etBundleGetDisplayManager($entityType, $bundleName);
}
