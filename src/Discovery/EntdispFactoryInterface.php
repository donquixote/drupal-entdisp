<?php

namespace Drupal\entdisp\Discovery;

interface EntdispFactoryInterface {

  /**
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   */
  public function createHandler();
}
