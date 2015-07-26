# EntDisH ("Entity Display Handler")

Entdish is a compositional framework for displaying entities.

An "entity display handler" is an object that builds render arrays from entities. This may be for a specific field, an entity title link, or an entire entity displayed in a view mode with entity_view().

Some entity display handler classes take one or more other edhs as constructor arguments, e.g. to concatenate the output, or wrap it in a container element.

There is a plugin layer added on top of this, which allows to expose specific edh combinations as "plugins" with a machine key and human name. In the future this API might be extended, so that some plugins may have configuration options.
 
Entdish does provide views row plugins, and may soon provide an entityreference formatter, panel panes and other things, to make these plugins available whereever an entity or a part of an entity is meant to be displayed.

## Recommended use

The idea is that developers write handler classes for low-level stuff like "wrap something in a div container with a class". And then implement hook_entdish_info() to make some combinations of display handlers available as plugins. E.g. "Frontpage highlight picture", which could be a link wrapper, using an image style decorator, using an image field.

## Why?

A lot of what EntDisH does could also be achieved with entity view modes, Field UI and Display Suite, or with theme preprocessors and entity or node templates. These systems do have limitations, however.

Preprocessors and templates can become messy after some time. Especially, they usually cause your theme and/or modules to depend on site-specific configuration, such as bundles (node types) and field names.

With view modes and field UI, on the other hand, you can end up with dozens or more view modes, which all need to be manually configured (and then exported with Features) for each bundle (node type).

Reuse between bundles, view modes or even between different sites is really limited.

## Background: Existing plugin systems in Drupal 7 and 8

EntDisH is not the only system in Drupal (contrib) that ships with a plugin API.

Most of the plugins in Drupal 7 core (field types, field formatters, text filters, image style filters..) are based on info hooks, and definition arrays with procedural callbacks. A lot of the plugins APIs in Drupal 7 contrib are based on ctools. Most or all of the plugin APIs in Drupal 8 are based on the new plugin API in D8 core. While these systems are different, they do have some things in common.

The term "plugin" itself is a bit fuzzy. It is useful to distinguish between an "available plugin", a "configured plugin" (*), an "instantiated plugin", and a "plugin class".

(*) Not happy with that name. Suggestions welcome.

### "Available" plugin

In most or all of the plugin systems mentioned above, modules can register "available plugins" by registering a machine key ("plugin id") with a plugin definition array. Registration may happen with an info hook (Drupal 7), or it may be based on class discovery and docblock comment parsing (Drupal 8).

Definition arrays for "available plugins" typically contain
- A human label for the plugin.
- Optionally, an array of available configuration options and their defaults.
- Possibly some options unique to the specific plugin API.
- Depending on the specific plugin API, a class names or one or more function names.
  (Sometimes these function names are implicitly derived from the machine key (plugin id), and don't need to be explicitly specified.)

Typically, a list of "available plugins" is displayed as e.g. a select element, in places where a plugin can be used. E.g. in Field UI the user can choose with which formatter a field should be displayed. And then once the plugin is chosen, a sub-form allows to configure the options for this plugin instance.

### "Configured" plugin

The choices are stored for each place where a plugin is used. E.g. Field UI does allow an independent choice and configuration of formatters for every combination of entity type, bundle, field and view mode. Views does allow a different choice and configuration of e.g. row plugins for each "views display".

For this document we refer to this stored configuration as a "configured plugin". For every "thing" that uses plugins (a views display, a field in an entity view mode), we could speak of "plugin slots", that is, slots that can or have to be filled with a plugin (or more than one) of a specific type.

### "Instantiated" plugin

Whenever the "thing" (views display, etc) that uses a plugin is being used, the plugin choice ("configured plugin") needs to be loaded, and based on this the thing may call functions specified in the plugin definition.

If the plugin definition contains a class name (e.g. Drupal 7 ctools, or Drupal 8 core plugin API), an instance of that class will be created together with the "thing" that uses the plugin, based on the "configured plugin". We refer to this object as the "instantiated plugin".

In fact, an "instantiated plugin" could be created programmatically with hard-coded options, even if these options are not stored or manually configured anywhere, and without being based on an "available plugin" definition. E.g. you could use one of the hooks provided in views to replace the row plugin instance with an instance of a custom row plugin class. You could do so without registering this custom row plugin class anywhere in views_row_plugins().

This means, a plugin system does technically not need all the definition, UI and storage part. On the other hand, we would not call this a "plugin system" anymore, because it would be simply classes and interfaces.

### "Plugin class"

While the concepts described above could be technically independent, there is often a 1:1 relationship between the plugin class and the "available plugin" definition. Especially in Drupal 8, if the plugin definition is in the class docblock of the plugin class, we automatically get a 1:1 match. Also, the callbacks for configuration form, and the array of available options, are now all part of the plugin class.

In an architecture like this, sharing of reusable functionality between plugins is often based on inheritance, instead of composition. 

## EntDisH: Separation of Handler vs Plugin

The EntDisH plugin API has a lot in common with the above, but it does make a strong distinction between "available plugin" and "plugin class". In fact we don't even use the term "plugin class", instead we call it the "(entity display) handler class". (And on a lazy day, we simply say "display handler" and mean both the class and the object/instance). 

The system of entity display handlers (handler classes) is entirely independent of the plugin system. A handler class is simply a class that implements `EntityDisplayInterface`. Most of these classes shipped with EntDisH core are very light-weight and do just one thing.

An entity display plugin, on the other hand, is defined with an entry in hook_entdish_info(). These plugin definition arrays define how to obtain the display handler instance for the given machine key (plugin id). But this is in no way a 1:1 thing: The same handler class could be used by more than one plugin definition. Some handler classes might only be used as arguments for other handlers, but not have their own plugin definitions. And one display plugin could use a different handler class depending on some external criteria.

In fact, most of the handler classes provided in EntDisH core do not even have a corresponding entry in `hook_entdish_info()`. This may change in future versions, but for now the plugin layer is simply less important than the handler layer.

And besides, a lot of the handler classes are not meaningful on their own. E.g. an `ImageStyle` requires an `EntityImageInterface` object, to specify where the image should come from. This could be an image field, a user picture, or something else.

### Handler object in the plugin definition?

So, a plugin definition describes how to obtain a plugin handler instance. This could be done by providing a class name and constructor parameters, a factory callback, etc.

However, in the current version of EntDisH, the only way to do so is by directly putting the handler object into the definition, e.g. `array('label' => 'My plugin', 'handler' => new MyHandler())`. Some may say "ouch". But this is the cheapest-to-implement solution. And it allows the IDE to recognize the class name for "find usages" and refactor/rename.

### Serialized objects?

At some point we may want to cache the array of available plugin definitions. This means we will have serialized objects. This is ok only because we are in Drupal 7, and don't use dependency injection. The handler objects don't have properties that reference services or other dependencies. Instead, they do everything with global function calls, in good old Drupal 7 fashion. E.g. just `l()` instead of `$this->linkService->l()`. This makes it impossible to unit-test these classes outside of a functional Drupal 7 environment, but we are already used to that and don't care.

Another typical problem with serialized objects is that the class might no longer be defined on `unserialize()`. In this case, a fallback handler object needs to be created instead. E.g. one that only display the entity title, or one that displays nothing at all.
