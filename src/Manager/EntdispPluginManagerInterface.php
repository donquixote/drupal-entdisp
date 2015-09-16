<?php
namespace Drupal\entdisp\Manager;

interface EntdispPluginManagerInterface {

  /**
   * Gets the element type object to be used in a uikit form element, that
   * allows to choose and configure a plugin of this type.
   *
   * @return \Drupal\uikit\FormElement\UikitElementTypeInterface
   */
  function getUikitElementType();

  /**
   * @param array $settings
   *   Format: array('plugin_id' => :string, 'plugin_options' => :array)
   *
   * @return string
   *   A label describing the plugin.
   */
  function settingsGetLabel(array $settings);

  /**
   * @param array $settings
   *   Format: array('plugin_id' => :string, 'plugin_options' => :array)
   *
   * @return string
   */
  function settingsGetSummary(array $settings);

  /**
   * @param array $settings
   *   Format: array('plugin_id' => :string, 'plugin_options' => :array)
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   */
  function settingsGetEntityDisplay(array $settings);

}
