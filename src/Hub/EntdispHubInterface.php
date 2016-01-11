<?php
namespace Drupal\entdisp\Hub;

interface EntdispHubInterface {

  /**
   * @param string $entityType
   * @param bool $required
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  function etGetDisplayManager($entityType, $required = TRUE);

  /**
   * @param string $entityType
   * @param string $bundleName
   * @param bool $required
   *
   * @return \Drupal\entdisp\EntdispConfigurator\EntdispConfigurator|\Drupal\entdisp\EntdispConfigurator\EntdispConfiguratorInterface
   */
  function etBundleGetDisplayManager($entityType, $bundleName, $required = TRUE);
}
