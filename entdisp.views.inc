<?php

/**
 * Implements hook_views_plugins().
 *
 * @see entity_views_plugins()
 */
function entdisp_views_plugins() {
  // Have views cache the table list for us so it gets
  // cleared at the appropriate times.
  $data = views_cache_get('entity_base_tables', TRUE);
  if (!empty($data->data)) {
    $base_tables = $data->data;
  }
  else {
    $base_tables = [];
    foreach (views_fetch_data() as $table => $data) {
      if (!empty($data['table']['entity type']) && !empty($data['table']['base'])) {
        $base_tables[] = $table;
      }
    }
    views_cache_set('entity_base_tables', $base_tables, TRUE);
  }
  return [
    'module' => 'entity',
    'row' => [
      'entdisp' => [
        'title' => t('Entity display plugin'),
        'help' => t('Renders the entity with one of the available entity display plugins.'),
        'handler' => \Drupal\entdisp\ViewsPlugin\row\EntdispViewsRowPlugin::class,
        'uses fields' => FALSE,
        'uses options' => TRUE,
        'type' => 'normal',
        'base' => $base_tables,
      ],
    ],
  ];
}

/**
 * Implements hook_views_data_alter().
 *
 * See http://drupal.stackexchange.com/questions/166734/custom-views-field-handler-with-relationship-support-but-without-table
 *
 * @param array $data
 *   The definitions to be altered.
 */
function entdisp_views_data_alter(array &$data) {

  foreach (entity_get_info() as $entity_type => $info) {
    if (!isset($data['views_entity_' . $entity_type]['rendered_entity'])) {
      continue;
    }
    $data['views_entity_' . $entity_type]['entdisp'] = [
      'title' => t('Entity display plugin'),
      'help' => t(
        'The @entity-type of the current relationship rendered using an entity display plugin.',
        ['@entity-type' => $info['label']]
      ),
      'field' => [
        'handler' => \Drupal\entdisp\ViewsPlugin\field\EntdispViewsFieldHandler::class,
        'type' => $entity_type,
        // The EntityFieldHandlerHelper treats the 'entity object' data
        // selector as special case for loading the base entity.
        // @todo Not sure if we really need this.
        'real field' => 'entity object',
      ],
    ];
  }
}
