<?php
# use Drupal\entdisp\CtoolsPlugin\content_types\EntdispContentPane;

$plugin = array(
  'title' => t('Entity display plugin'),
  # 'class' => EntdispContentPane::class,
  'single' => TRUE,
  'category' => t('Entity display plugin'),
  /* @see _entdisp_ctools_content_type_edit_form() */
  'edit form' => '_entdisp_ctools_content_type_edit_form',
  /* @see _entdisp_ctools_content_type_render() */
  'render callback' => '_entdisp_ctools_content_type_render',
);

/**
 * @param array $form
 * @param array $form_state
 *
 * @return null|array
 */
function _entdisp_ctools_content_type_edit_form(array $form, array  &$form_state) {

  // Grab entity information.
  foreach ($form_state['contexts'] as $ctools_context) {
    if ('entity' !== $ctools_context->type[1]) {
      return $form;
    }
    $entityType = $ctools_context->type[2];
    break;
  }

  if (!isset($entityType)) {
    return NULL;
  }

  if (isset($form_state['values'][ENTDISP_PLUGIN_KEY])) {
    $conf = $form_state['values'][ENTDISP_PLUGIN_KEY];
  }
  elseif (isset($form_state['input'][ENTDISP_PLUGIN_KEY])) {
    $conf = $form_state['input'][ENTDISP_PLUGIN_KEY];
  }
  else {
    $conf = array();
  }

  $displayManager = entdisp()->etGetDisplayManager($entityType);

  $form[ENTDISP_PLUGIN_KEY] = $displayManager->confGetForm($conf, t('Entity display plugin'));

  return $form;
}

/**
* Submit function, note anything in the formstate[conf] automatically gets saved
*/
function _entdisp_ctools_content_type_edit_form_submit(&$form, &$form_state) {
  $form_state['conf'][ENTDISP_PLUGIN_KEY] = $form_state['values'][ENTDISP_PLUGIN_KEY];
}

/**
 * @param $subtype
 * @param $conf
 * @param $args
 * @param array|null $context
 *
 * @return null|\stdClass
 *
 * @throws \InvalidArgumentException
 */
function _entdisp_ctools_content_type_render($subtype, $conf, $args, $context = NULL) {

  // Grab entity information.
  foreach ($context as $ctools_context) {
    if ('entity' !== $ctools_context->type[1]) {
      throw new \InvalidArgumentException("Context must be an entity.");
    }
    $entityType = $ctools_context->type[2];
    if (empty($ctools_context->data)) {
      throw new \InvalidArgumentException("Context entity must not be empty.");
    }
    $entity = $ctools_context->data;
    break;
  }

  if (!isset($entityType)) {
    return NULL;
  }

  if (!isset($entity)) {
    return NULL;
  }

  $displayManager = entdisp()->etGetDisplayManager($entityType);
  $entityDisplay = $displayManager->confGetEntityDisplay($conf);

  $block = new \stdClass();
  $block->content = $entityDisplay->buildEntity($entityType, $entity);
  return $block;
}
