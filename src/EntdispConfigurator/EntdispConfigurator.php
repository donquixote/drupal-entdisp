<?php

namespace Drupal\entdisp\EntdispConfigurator;

use Drupal\entdisp\EntityDisplay\EntdispBrokenEntityDisplay;
use Drupal\renderkit\EntityDisplay\EntityDisplayInterface;
use Drupal\cfrapi\RawConfigurator\RawConfigurator_CfrDecoratorTrait;

class EntdispConfigurator implements EntdispConfiguratorInterface {

  use RawConfigurator_CfrDecoratorTrait;

  /**
   * @param array $conf
   *
   * @param $label
   *
   * @return array
   */
  public function confGetForm($conf, $label) {
    $form = $this->decorated->confGetForm($conf, $label);
    $form['plugin_id']['#title'] = t('Entity display plugin');
    return $form;
  }

  /**
   * @param array $conf
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   */
  public function confGetEntityDisplay(array $conf) {
    $handlerCandidate = $this->decorated->confGetValue($conf);
    if ($handlerCandidate instanceof EntityDisplayInterface) {
      return $handlerCandidate;
    }
    return EntdispBrokenEntityDisplay::create()->setInvalidHandler($handlerCandidate);
  }
}
