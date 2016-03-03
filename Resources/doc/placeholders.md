# Placeholders

In order to improve layouts and make them more flexible a new Twig token `placeholder` is implemented. It allows us to combine several blocks (templates or actions) and output them in different places in Twig templates. This way we can customize layouts without modifying Twig templates.


## Placeholder declaration in YAML

Placeholders can be defined in any bundle under `/SomeBundleName/Resource/placeholders.yml`

```yaml
items:                                 # items to use in placeholders (templates or actions)
    <item_name>:                       # any unique identifier
        template: <template>           # path to custom template for renderer
    <another_item_name>:
        action: <action>               # action name (e.g. AcmeCarBundle:Car:index)

placeholders:
    <placeholder_name>:
        items:
            <item_name>:
                order: 100              # sort order in placeholder
            <another_item_name>:
                order: 200
            <one_more_item_name>: ~     # sort order will be set to 0
```

Any configuration defined in bundle `placeholders.yml` file can be overridden in `app/config/config.yml` file.

```yaml
z38_ui:
    placeholders:
        <placeholder_name>:
            items:
                <item_name>:
                    remove: true   # remove item from placeholder
        <another_placeholder_name>:
            items:
                <item_name>:
                    order: 200     # change item order in placeholder
```

Each placeholder item can have the following properties:

 - **template** or **action** - The path to Twig template or controller action is used to rendering the item.
 - **applicable** - The condition indicates whether the item can be rendered or not.
 - **secure** - An expression which must evaluate to true. It has access to the following variables: `token`, `user`, `request`, `roles` and all passed options. It works like the [`@Security` annotation](http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/security.html).
 - **data** - An additional data to be passed to the Twig template or controller.


## Rendering placeholders

To render placeholder content in Twig template we need to put

```html
{% placeholder <placeholder_name> %}
```

Additional options can be passed to all placeholder child items using `with` e.g.

```html
{% placeholder <placeholder_name> with {'form' : form} %}
```
