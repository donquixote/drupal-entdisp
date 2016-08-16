<?php
use Drupal\cfrapi\SummaryBuilder\SummaryBuilder_Static;

/**
 * Implements hook_field_formatter_info().
 */
function entdisp_field_formatter_info() {

  return array(
    ENTDISP_ENTITYREFERENCE_FORMATTER => array(
      'label' => t('Entity display plugin'),
      'description' => t('Display the referenced entities using an entity display plugin.'),
      'field types' => array('entityreference'),
      'settings' => array(
        ENTDISP_PLUGIN_KEY => array(),
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
function entdisp_field_formatter_settings_form(
  array $field,
  array $instance,
  $view_mode,
  /** @noinspection PhpUnusedParameterInspection */ $form,
  /** @noinspection PhpUnusedParameterInspection */ &$form_state
) {
  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];
  $target_type = $field['settings']['target_type'];

  $element = array();

  if ($display['type'] === ENTDISP_ENTITYREFERENCE_FORMATTER) {
    $element[ENTDISP_PLUGIN_KEY] = entdisp()->etGetDisplayManager($target_type)->confGetForm($settings[ENTDISP_PLUGIN_KEY], t('Entity display'));
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
  $target_type = $field['settings']['target_type'];

  if ($display['type'] === ENTDISP_ENTITYREFERENCE_FORMATTER) {
    return entdisp()->etGetDisplayManager($target_type)->confGetSummary($settings[ENTDISP_PLUGIN_KEY], new SummaryBuilder_Static());
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
  if ($display['type'] === ENTDISP_ENTITYREFERENCE_FORMATTER) {
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
function entdisp_field_formatter_view(
  $entity_type,
  /** @noinspection PhpUnusedParameterInspection */ $entity,
  $field,
  /** @noinspection PhpUnusedParameterInspection */ $instance,
  /** @noinspection PhpUnusedParameterInspection */ $langcode,
  $items,
  $display
) {
  $settings = $display['settings'];
  $target_type = $field['settings']['target_type'];

  // Rebuild the items list to contain only those with access.
  foreach ($items as $key => $item) {
    if (empty($item['access'])) {
      unset($items[$key]);
    }
  }

  switch ($display['type']) {

    case ENTDISP_ENTITYREFERENCE_FORMATTER:
      $entity_display = entdisp()->etGetDisplayManager($entity_type)->confGetEntityDisplay($settings[ENTDISP_PLUGIN_KEY]);
      $target_entities = array();
      foreach ($items as $delta => $item) {
        $target_entities[$delta] = $item['entity'];
      }
      return $entity_display->buildEntities($target_type, $target_entities);
  }

  return array();
}
