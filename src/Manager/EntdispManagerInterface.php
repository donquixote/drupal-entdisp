<?php

namespace Drupal\entdisp\Manager;

use Drupal\uniplugin\Manager\UniManagerInterface;

interface EntdispManagerInterface extends UniManagerInterface {

  /**
   * @param array $conf
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   */
  function confGetEntityDisplay(array $conf);

}
