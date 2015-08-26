<?php
use Drupal\renderkit\EntityDisplay\Decorator\RecursionDetectionEntityDisplayDecorator;

/**
 * Implements hook_field_formatter_info().
 */
function entdisp_field_formatter_info() {

  return array(
    'entdisp_entityreference' => array(
      'label' => t('Entity display plugin'),
      'description' => t('Display the referenced entities using an entity display plugin.'),
      'field types' => array('entityreference'),
      'settings' => array(
        'entity_display_plugin' => NULL,
      ),
    ),
  );
}

/**
 * Implements hook_field_formatter_settings_form().
 *
 * @param array $field
 * @param array $instance
 * @param string $view_mode
 * @param array $form
 * @param array $form_state
 *
 * @return array
 *
 * @see entityreference_field_formatter_settings_form()
 */
function entdisp_field_formatter_settings_form(array $field, array $instance, $view_mode, $form, &$form_state) {
  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];

  $element = array();

  if ($display['type'] == 'entdisp_entityreference') {
    $element['entity_display_plugin'] = array(
      '#type' => UNIPLUGIN_ELEMENT_TYPE,
      '#plugin_type' => 'entdisp',
      '#default_value' => $settings['entity_display_plugin'],
    );
  }

  return $element;
}

/**
 * Implements hook_field_formatter_settings_summary().
 *
 * @param array $field
 * @param array $instance
 * @param string $view_mode
 *
 * @return string|null
 *
 * @see entityreference_field_formatter_settings_summary()
 */
function entdisp_field_formatter_settings_summary($field, $instance, $view_mode) {
  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];

  if ($display['type'] == 'entdisp_entityreference') {
    return entdisp()->legend->settingsKeyGetLabel($settings, 'entity_display_plugin', TRUE);
  }

  return NULL;
}

/**
 * Implements hook_field_formatter_prepare_view().
 *
 * @param string $entity_type
 *   The type of $entity.
 * @param object[] $entities
 *   Array of entities being displayed, keyed by entity ID.
 * @param array $field
 *   The field structure for the operation.
 * @param array[] $instances
 *   Array of instance structures for $field for each entity, keyed by entity
 *   ID.
 * @param string $langcode
 *   The language the field values are to be shown in. If no language is
 *   provided the current language is used.
 * @param array[] $items
 *   Array of field values for the entities, keyed by entity ID.
 * @param array[] $displays
 *   Array of display settings to use for each entity, keyed by entity ID.
 *
 * @see entityreference_field_formatter_prepare_view()
 */
function entdisp_field_formatter_prepare_view($entity_type, $entities, $field, $instances, $langcode, &$items, $displays) {
  $display = reset($displays);
  if ($display['type'] === 'entdisp_entityreference') {
    entityreference_field_formatter_prepare_view($entity_type, $entities, $field, $instances, $langcode, $items, $displays);
  }
}

/**
 * Implements hook_field_formatter_view().
 *
 * @param string $entity_type
 *   The type of $entity.
 * @param object $entity
 *   The entity being displayed.
 * @param array $field
 *   The field structure.
 * @param array $instance
 *   The field instance.
 * @param string $langcode
 *   The language associated with $items.
 * @param array[] $items
 *   Array of values for this field.
 * @param array $display
 *   The display settings to use, as found in the 'display' entry of instance
 *   definitions. The array notably contains the following keys and values;
 *   - type: The name of the formatter to use.
 *   - settings: The array of formatter settings.
 *
 * @return array
 *   A renderable array for the $items, as an array of child elements keyed
 *   by numeric indexes starting from 0.
 */
function entdisp_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {

  $settings = $display['settings'];
  $target_type = $field['settings']['target_type'];

  // Rebuild the items list to contain only those with access.
  foreach ($items as $key => $item) {
    if (empty($item['access'])) {
      unset($items[$key]);
    }
  }

  switch ($display['type']) {

    case 'entdisp_entityreference':
      $display = entdisp()->handlerManager->settingsKeyGetHandler($settings, 'entity_display_plugin');
      $display = new RecursionDetectionEntityDisplayDecorator($display);
      $target_entities = array();
      foreach ($items as $delta => $item) {
        $target_entities[$delta] = $item['entity'];
      }
      return $display->buildEntities($target_type, $target_entities);
  }

  return array();
}
