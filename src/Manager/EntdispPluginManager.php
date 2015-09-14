<?php

namespace Drupal\entdisp\Manager;

use Drupal\entdisp\EntityDisplay\EntdispBrokenEntityDisplay;
use Drupal\renderkit\EntityDisplay\EntityDisplayInterface;
use Drupal\uniplugin\Manager\UniPluginManagerInterface;

class EntdispPluginManager implements EntdispPluginManagerInterface {

  /**
   * @var \Drupal\uniplugin\Manager\UniPluginManagerInterface
   */
  private $uniPluginManager;

  /**
   * EntdispPluginManager constructor.
   *
   * @param \Drupal\uniplugin\Manager\UniPluginManagerInterface $uniPluginManager
   */
  function __construct(UniPluginManagerInterface $uniPluginManager) {
    $this->uniPluginManager = $uniPluginManager;
  }

  /**
   * Gets the element type object to be used in a uikit form element, that
   * allows to choose and configure a plugin of this type.
   *
   * @return \Drupal\uikit\FormElement\UikitElementTypeInterface
   */
  function getUikitElementType() {
    // Pass-through.
    return $this->uniPluginManager->getUikitElementType();
  }

  /**
   * @param array $settings
   *   Format: array('plugin_id' => :string, 'plugin_options' => :array)
   *
   * @return string
   *   A label describing the plugin.
   */
  function settingsGetLabel(array $settings) {
    // Pass-through.
    return $this->uniPluginManager->settingsGetLabel($settings);
  }

  /**
   * @param array $settings
   *   Format: array('plugin_id' => :string, 'plugin_options' => :array)
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   */
  function settingsGetEntityDisplay(array $settings) {
    $handler = $this->uniPluginManager->settingsGetHandler($settings);
    return $handler instanceof EntityDisplayInterface
      ? $handler
      : EntdispBrokenEntityDisplay::create()->setInvalidHandler($handler);
  }
}
