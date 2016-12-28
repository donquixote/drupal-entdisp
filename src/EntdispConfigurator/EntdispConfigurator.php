<?php

namespace Drupal\entdisp\EntdispConfigurator;

use Drupal\cfrapi\Exception\InvalidConfigurationException;
use Drupal\cfrapi\RawConfigurator\RawConfigurator_CfrDecoratorTrait;
use Drupal\renderkit\EntityDisplay\EntityDisplayInterface;

class EntdispConfigurator implements EntdispConfiguratorInterface {

  use RawConfigurator_CfrDecoratorTrait;

  /**
   * @param array $conf
   *
   * @param string $label
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
   *
   * @throws \Drupal\cfrapi\Exception\InvalidConfigurationException
   */
  public function confGetEntityDisplay(array $conf) {

    $handlerCandidate = $this->decorated->confGetValue($conf);

    if ($handlerCandidate instanceof EntityDisplayInterface) {
      return $handlerCandidate;
    }

    throw new InvalidConfigurationException("The configurator returned something other than an entity display");
  }
}
