# Transformer Bundle

This is a Symfony integration package for the <a href="https://github.com/Crell/Transformer">Crell/Transformer</a>
library.

## Usage

See the Transformer library README file for full usage information. This bundle
adds the ability to register Symfony services and string-class-method names, ie, from
a service container, as transformers.

```php
class TransformerService 
{
    public function transform(HtmlPage $h)
    {
        // .. Do stuff.
        return new Response();
    }
}

// Register TransformerService in the Container as "page_transformer".

$bus = new ContainerAwareTransformerBus(Response::class);
$bus->setContainer($container);
$bus->setTransformer(HtmlPage::class, 'page_transformer:transform');

$bus->transform(new HtmlPage());
```
In practice of course you would want to call setTransformer from your container
configuration, not inline.

This bundle does *not* automatically register a transformer with the container.
You will have to do that yourself, along with whatever configuration is appropriate
for your application.  (You can also, of course, register several different
transformation pipelines for different use cases if appropriate.)

A suggested usage is to register a single transformation bus as a `kernel::VIEW`
listener.  That way, Controllers may return any number of different objects and
they can all get folded down to a Response object with less overhead than
registering a whole bunch of manually-ordered view listeners.

## Installation

The preferred method of installation is via Composer with the following command:

    composer require crell/transformer-bundle

See the [Composer documentation][2] for more details.

Alternatively, clone the project and install into your project manually.

## License

This library is released under the MIT license.  In short, "leave the copyright
statement intact, otherwise have fun."  See LICENSE for more information.
