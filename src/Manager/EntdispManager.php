<?php

namespace Drupal\entdisp\Manager;

use Drupal\entdisp\EntityDisplay\EntdispBrokenEntityDisplay;
use Drupal\renderkit\EntityDisplay\EntityDisplayInterface;
use Drupal\uniplugin\HandlerMap\UniHandlerMapInterface;

class EntdispManager implements EntdispManagerInterface {

  /**
   * @var \Drupal\uniplugin\HandlerMap\UniHandlerMapInterface
   */
  private $handlerMap;

  /**
   * EntdispManager constructor.
   *
   * @param \Drupal\uniplugin\HandlerMap\UniHandlerMapInterface $handlerMap
   */
  function __construct(UniHandlerMapInterface $handlerMap) {
    $this->handlerMap = $handlerMap;
  }

  /**
   * @param array $conf
   *
   * @return array
   */
  function confGetForm(array $conf) {
    $form = $this->handlerMap->confGetForm($conf);
    $form['plugin_id']['#title'] = t('Entity display plugin');
    return $form;
  }

  /**
   * @param array $conf
   *
   * @return string
   */
  function confGetSummary(array $conf) {
    return $this->handlerMap->confGetSummary($conf);
  }

  /**
   * @param array $conf
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   */
  function confGetEntityDisplay(array $conf) {
    $handlerCandidate = $this->handlerMap->confGetHandler($conf);
    if ($handlerCandidate instanceof EntityDisplayInterface) {
      return $handlerCandidate;
    }
    return EntdispBrokenEntityDisplay::create()->setInvalidHandler($handlerCandidate);
  }
}
