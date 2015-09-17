<?php

namespace Drupal\entdisp\Discovery;

use Drupal\renderkit\EntityDisplay\EntityDisplayInterface;

class EntdispFactoryDiscovery {

  /**
   * @var string
   */
  private $prefix;

  function __construct($module) {
    $this->prefix = $module . '.';
  }

  /**
   * @param string $module
   * @param string $subnamespace
   */
  function discoverInModulePsr4($module, $subnamespace) {

    $path = drupal_get_path('module', $module) . '/src';
    $namespaceOrClass = 'Drupal\\' . $module;
    if (null !== $subnamespace && '' !== $subnamespace) {
      $path .= '/' . str_replace('\\', '/', $subnamespace);
      $namespaceOrClass .= '\\' . $subnamespace;
    }
    $this->discoverInPath($path, $namespaceOrClass);
  }

  /**
   * @param string $path
   * @param string $namespaceOrClass
   *
   * @throws \InvalidArgumentException
   */
  function discoverInPath($path, $namespaceOrClass) {
    $path_lastchar = substr($path, -1);
    if ('/' === $path_lastchar || '\\' === $path_lastchar) {
      throw new \InvalidArgumentException('Path must be provided without trailing slash or backslash.');
    }
    if ('\\' === substr($namespaceOrClass, -1)) {
      throw new \InvalidArgumentException('Namespace must be provided without trailing backslash.');
    }
    if (!empty($namespaceOrClass) && '\\' === $namespaceOrClass[0]) {
      throw new \InvalidArgumentException('Namespace must be provided without preceding backslash.');
    }
    if (is_file($path)) {
      $this->discoverInClass($namespaceOrClass);
      return;
    }
    elseif (!is_dir($path)) {
      throw new \InvalidArgumentException('Not a directory: ' . check_plain($path));
    }
    if ('' !== $namespaceOrClass) {
      $namespaceOrClass .= '\\';
    }
    $this->discoverInDir($path . '/', $namespaceOrClass);
  }

  /**
   * @param string $parentDir
   * @param string $parentNamespace
   */
  protected function discoverInDir($parentDir, $parentNamespace) {
    foreach (scandir($parentDir) as $candidate) {
      if ('.' === $candidate[0]) {
        continue;
      }
      $path = $parentDir . $candidate;
      if ('.php' === substr($candidate, -4)) {
        $name = substr($candidate, 0, -4);
        $class = $parentNamespace . $name;
        $this->discoverInClass($class);
      }
      else {
        $this->discoverInDir($path . '/', $parentNamespace . $candidate . '\\');
      }
    }
  }

  /**
   * @param string $class
   *
   * @return \array[]
   */
  function discoverInClass($class) {
    $reflectionClass = new \ReflectionClass($class);
    $definitions = array();
    foreach ($methods = $reflectionClass->getMethods() as $method) {
      if ($definition = $this->discoverInMethod($method)) {
        $definitions[$this->prefix . $method->name] = $definition;
      }
    }

    return $definitions;
  }

  /**
   * @param \ReflectionMethod $method
   *
   * @return array|null
   */
  protected function discoverInMethod(\ReflectionMethod $method) {
    if (!$method->isStatic()) {
      return NULL;
    }
    $comment = $this->clearDocComment($method->getDocComment());
    $comment_parts = explode('@', $comment);
    $first_part = array_shift($comment_parts);
    $doc_param_types = array();
    $is_plugin = FALSE;
    foreach ($comment_parts as $part) {
      list($part_line0, $rest) = explode("\n", $part . "\n");
      list($piece0, $type, $name) = $pieces = explode(' ', $part_line0 . '  ');
      if (!empty($type) && '\\' === $type{0}) {
        $type = substr($type, 1);
      }
      if ('plugin' === $piece0) {
        $is_plugin = TRUE;
      }
      if ('param' === $piece0) {
        if (empty($name) || '$' !== $name{0}) {
          continue;
        }
        $doc_param_types[$name] = $type;
      }
      elseif ('return' === $piece0) {
        $doc_return_type = trim($type);
      }
    }
    if (!$is_plugin) {
      return NULL;
    }
    if (!isset($doc_return_type)) {
      return NULL;
    }
    if (1
      && $doc_return_type !== EntityDisplayInterface::class
      && !is_subclass_of($doc_return_type, EntityDisplayInterface::class)
    ) {
      return NULL;
    }
    return array(
      'label' => $this->getLabel($first_part, $method->class . '::' . $method->name),
      'factory' => $method->class . '::' . $method->name,
    );
  }

  /**
   * @param string $comment
   *
   * @return string
   */
  protected function clearDocComment($comment) {
    $comment = substr($comment, 3, -2);
    $lines = explode("\n", $comment);
    foreach ($lines as &$line) {
      if (preg_match('#^ *\*(.*)$#', $line, $m)) {
        $line = $m[1];
      }
    }
    return implode("\n", $lines);
  }

  /**
   * @param string $comment
   * @param string $else
   *
   * @return string
   */
  protected function getLabel($comment, $else) {
    foreach (explode("\n", $comment) as $line) {
      $line = trim($line);
      if (empty($line)) {
        continue;
      }
      return $line;
    }
    return $else;
  }
}
