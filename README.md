# EntDisP ("Entity Display Handler")

Entdisp is a plugin system for entity display handlers, based on the [UniPlugin](https://github.com/donquixote/drupal-uniplugin) API.

An "entity display handler" is an object that builds render arrays from entities.
The interface, `Drupal\renderkit\EntityDisplay\EntityDisplayInterface`, and most implementations, live in the [Renderkit](https://github.com/donquixote/drupal-uniplugin) module.

EntDisP provides a way to expose these handlers as plugins.

## Integration

So far, there is..

* A views row plugin, [EntdispRowPlugin](src/Plugin/views/row/EntdispRowPlugin.php).
* A views field handler, [EntdispRowPlugin](src/Plugin/views/field/EntdispViewsFieldHandler.php).
* An entityreference field formatter, see [entdisp.field.inc](entdisp.field.inc).
