<?php

namespace Drupal\entdisp\EntdispConfigurator;

use Drupal\cfrapi\RawConfigurator\RawConfiguratorInterface;

interface EntdispConfiguratorInterface extends RawConfiguratorInterface {

  /**
   * @param array $conf
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   */
  public function confGetEntityDisplay(array $conf);

}
