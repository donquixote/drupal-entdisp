<?php
namespace Drupal\entdisp\Manager;

use Drupal\uniplugin\Manager\UniPluginManagerBaseInterface;

interface EntdispPluginManagerInterface extends UniPluginManagerBaseInterface {

  /**
   * @param array $settings
   *   Format: array('plugin_id' => :string, 'plugin_options' => :array)
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   */
  function settingsGetEntityDisplay(array $settings);

}
