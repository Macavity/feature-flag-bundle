# FeatureFlagBundle
Easily toggle features in your Symfony app. Built-in providers are .env, cookies and userAgent. But you can extend the system with own providers (f.e. for A/B testing).

## Install
Install the package via composer:
```
composer require check24/feature-flag-bundle
```

## Usage

### Accessing feature flag states

#### Limit access to controller actions
You can use the `IsActive` annotation to let the action only be accessible if the given feature is active. Otherwise a 403 is thrown:
```php
use Shopping\FeatureFlagBundle\Annotation\IsActive;

/**
 * @Route("test")
 * @IsActive("foobar")
 * @return Response
 */
public function test(): Response
{
    return new Response('I can see you.');
}
```

#### As a dependency injection
To access the state of a feature inside a service, you can inject the `FeatureFlagInterface`
```php
use Shopping\FeatureFlagBundle\Service\FeatureFlagInterface;

class SomeService
{
    private $featureFlag;

    public function __construct(FeatureFlagInterface $featureFlag)
    {
        $this->featureFlag = $featureFlag;
    }

    public function getNumber(): int
    {
        if ($this->featureFlag->isActive('foobar')) {
            return 2;
        }

        return 1;
    }
}
```

#### In Twig
Use the twig function `is_active` to check if a feature is enabled:
```twig
{% if is_active('foobar') %}
    Awesome!
{% else %}
    Default...
{% endif %}
```

### Configuring feature flag providers

In your `config/packages/shopping_feature_flag.yaml` configuration, you can configure the built-in providers to define, which feature flag (key) should be active, when the user inputs on of the values:
```yaml
shopping_feature_flag:
    providers:
        cookie:
            values:
                test1: 1234
                test2: [5678,9999]
        userAgent:
            values:
                test: foobar/chrome
                test2: [foobar/chrome, foobar/firefox]
```

You can enable/disable some of the built in providers.

### Toggling features
Feature flags can be toggled by the configured providers. If at least one of the providers reports the feature as active, it is active.

#### .env
Toggle a feature environment wide in your `.env` file. You could f.e. disable the feature in your productive `.env` file but enable it in your staging `.env` file.
```bash
FEATUREFLAG_FOOBAR=1
```
(Prefix "FEATUREFLAG_" and uppercase feature flag name)

#### Cookie
Activate a feature only for yourself by setting a cookie in your browser, f.e. in Chrome with F12 -> Application -> Cookies
```
featureFlag_foobar=1
```
(Prefix "featureFlag_" and feature flag name)

If you haven't specified a specific value in the config for the feature flag, any value will activate the feature. Use the config to make the features more secure.

#### User-Agent
Activate a feature only for yourself by setting a custom User-Agent in your browser (f.e. with the Chrome plugin "User-Agent Switcher").

Configure in the `shopping_feature_flag.yaml`, what parts of the User-Agent activates what feature:
```yaml
shopping_feature_flag:
    providers:
        userAgent:
            values:
                foobar: foobar/chrome
```
-> When using a User-Agent containing the string `foobar/chrome`, the Feature "foobar" is active. This way it is possible to activate multiple features with one User-Agent, which contains all needed keys for the features while preserving the browser/device detection of the website.

### Create own provider
If you want to activate features in a custom way, you want to write your own provider.

In this example we create a time-based provider, so the specific feature "morningShow" is only activated between 8 and 10 a.m. .

First create a provider class, implement the `FeatureFlagInterface` with the `isActive()` method. This method should return true if the given feature is active. Otherwise return false.
```php
//src/FeatureFlagProvider/MorningProvider.php

namespace App\FeatureFlagProvider;

class MorningProvider implements FeatureFlagInterface
{
    /**
     * @param string $featureFlag
     *
     * @return bool
     */
    public function isActive(string $featureFlag): bool
    {
        if ($featureFlag !== 'morningShow') {
            return false;
        }

        $hour = (new \DateTime())->format('G');

        return $hour >= 8 && $hour <= 10;
    }
}
```
Remember: A feature is active if any provider reports it as being active, so returning false doesn't deactivate the feature completely, but instead looks into all other providers if they report it as active.

Next, tag the service with the `featureFlag.provider` tag:
```yaml
# config/services.yaml

services:
    App\FeatureFlagProvider\MorningProvider:
        tags:
            - { name: featureFlag.provider }
```

And you are done. The feature "morningShow" will now always be active between 8 and 10 a.m.

You can use this feature to realise an A/B testing, so you activate the feature only for a specific group of users.

### Env Var Processor

To use a feature flag's value within a bundle's configuration you may use the `feature_flag`
env var processor. This is especially useful when used in conjunction with custom feature
flag providers, f.e. when you're loading flags from a database via doctrine.

```yaml
# config/packages/example_foo_bundle.yaml
example_foo_bundle:
    enable_baz: '%env(feature_flag:foo_bar)%'
```

The above snippet will set the `enable_baz` config of ExampleBundle to the value of 
a feature flag called `foo_bar`.
You can use the same technique when you'd like to register a specific feature flag as
container parameter:

```yaml
# config/services.yaml

parameters:
    maintenance_mode: '%env(feature_flag:maintenance_mode)%'
```

`$container->getParameter('maintenance_mode')` will now contain either true or false, 
depending on whether the feature flag is active or not.

