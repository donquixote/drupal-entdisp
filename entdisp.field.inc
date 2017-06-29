<?php
use Drupal\cfrapi\SummaryBuilder\SummaryBuilder_Static;

/**
 * Implements hook_field_formatter_info().
 */
function entdisp_field_formatter_info() {

  return [
    ENTDISP_ENTITYREFERENCE_FORMATTER => [
      'label' => t('Entity display plugin'),
      'description' => t('Display the referenced entities using an entity display plugin.'),
      'field types' => ['entityreference', 'taxonomy_term_reference', 'paragraphs', 'field_collection'],
      'settings' => [
        ENTDISP_PLUGIN_KEY => [],
      ],
    ],
  ];
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

  // @todo Grab settings from $form_state?

  $element = [];

  if ($display['type'] === ENTDISP_ENTITYREFERENCE_FORMATTER) {

    if (NULL === $target_type = _entdisp_reference_field_get_target_type($field)) {
      return array();
    }

    $element[ENTDISP_PLUGIN_KEY] = [
      /* @see entdisp_element_info() */
      '#type' => 'entdisp',
      '#title' => t('Entity display'),
      '#default_value' => $settings[ENTDISP_PLUGIN_KEY],
      '#entity_type' => $target_type,
    ];
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

  if ($display['type'] === ENTDISP_ENTITYREFERENCE_FORMATTER) {

    if (NULL === $target_type = _entdisp_reference_field_get_target_type($field)) {
      return NULL;
    }

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

    switch ($field['type']) {

      case 'entityreference':
        entityreference_field_formatter_prepare_view($entity_type, $entities, $field, $instances, $langcode, $items, $displays);
        break;

      case 'taxonomy_term_reference':
        taxonomy_field_formatter_prepare_view($entity_type, $entities, $field, $instances, $langcode, $items, $displays);
        break;

      case 'paragraphs':
        // Does not have a prepare_view step.
        break;

      case 'field_collection':
        // Does not have a prepare_view step.
        break;
    }
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
  if (ENTDISP_ENTITYREFERENCE_FORMATTER !== $display['type']) {
    return [];
  }

  if ([] === $items) {
    return [];
  }

  $settings = $display['settings'];

  $target_entities = [];

  switch ($field['type']) {

    case 'entityreference':
      /* @see entityreference_field_formatter_view() */
      $target_type = $field['settings']['target_type'];
      foreach ($items as $delta => $item) {
        if (!empty($item['access']) && !empty($item['entity'])) {
          $target_entities[$delta] = $item['entity'];
        }
      }
      break;

    case 'taxonomy_term_reference':
      /* @see taxonomy_field_formatter_view() */
      $target_type = 'taxonomy_term';
      foreach ($items as $delta => $item) {
        if (!empty($item['taxonomy_term'])) {
          $target_entities[$delta] = $item['taxonomy_term'];
        }
      }
      break;

    case 'paragraphs':
      /* @see paragraphs_field_formatter_view() */
      $target_type = 'paragraphs_item';
      foreach ($items as $delta => $item) {
        if ($paragraph = paragraphs_field_get_entity($item)) {
          $paragraph->setHostEntity($entity_type, $entity, $langcode);
          if (entity_access('view', 'paragraphs_item', $paragraph)) {
            $target_entities[$delta] = $paragraph;
          }
        }
      }
      break;

    case 'field_collection':
      /* @see field_collection_field_formatter_view() */
      $target_type = 'field_collection_item';
      foreach ($items as $delta => $item) {
        if ($field_collection = field_collection_field_get_entity($item)) {
          $target_entities[$delta] = $field_collection;
        }
      }
      break;

    default:
      return [];
  }

  if ([] === $target_entities) {
    return [];
  }

  try {
    return entdisp()
      ->etGetDisplayManager($target_type)
      ->confGetEntityDisplay($settings[ENTDISP_PLUGIN_KEY])
      ->buildEntities($target_type, $target_entities);
  }
  catch (\Exception $e) {
    watchdog('cfrplugin',
      'Broken entity display plugin in field formatter for %field at %entity_type %entity_id.'
      . "\n" . 'Exception message: %message',
      [
        '%field' => $field['field_name'],
        '%entity_type' => $entity_type,
        '%view_mode' => entity_id($entity_type, $entity),
      ],
      WATCHDOG_WARNING);

    return [];
  }
}

/**
 * @param array $field
 *   A field definition.
 *
 * @return string|null
 *   The target entity type.
 */
function _entdisp_reference_field_get_target_type(array $field) {

  switch ($field['type']) {

    case 'entityreference':
      return $field['settings']['target_type'];

    case 'taxonomy_term_reference':
      return 'taxonomy_term';

    case 'paragraphs':
      return 'paragraphs_item';

    case 'field_collection':
      return 'field_collection_item';

    default:
      return NULL;
  }
}
