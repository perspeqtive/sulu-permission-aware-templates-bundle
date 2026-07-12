# Sulu Permission Aware Templates Bundle

[![Latest Stable Version](https://poser.pugx.org/perspeqtive/sulu-permission-aware-templates-bundle/v/stable)](https://packagist.org/packages/perspeqtive/sulu-permission-aware-templates-bundle)
[![License](https://poser.pugx.org/perspeqtive/sulu-permission-aware-templates-bundle/license)](https://packagist.org/packages/perspeqtive/sulu-permission-aware-templates-bundle)

This bundle adds permission-aware page templates to the Sulu admin.

It registers a security context for every available Sulu page template and uses these permissions to restrict the template dropdown and the page toolbar actions. This makes it possible to decide per role which templates can be selected, changed, saved, published, unpublished, or deleted.

## ✨ Features

- Adds a dedicated Sulu security context for page templates.
- Restricts the page template dropdown to templates the current user is allowed to use.
- Keeps the currently selected template visible, even if the user is not allowed to switch to another one.
- Disables save, publish and unpublish actions when the current template is not writable for the current user.
- Disables delete actions when the current template is not deletable for the current user.
- Supports toolbar actions inside Sulu dropdown toolbar actions.

## 📋 Requirements

- PHP 8.2 or higher
- Sulu 2.6 or higher

## ⚙️ Installation

### Install the bundle via composer:

```bash
composer require perspeqtive/sulu-permission-aware-templates-bundle
```

### Enable the bundle

If your application does not use Symfony Flex, register the bundle manually:

```php
// config/bundles.php

return [
    // ...
    PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\SuluPermissionAwareTemplatesBundle::class => ['all' => true],
];
```

### Add the bundle to package.json

In order to enable visibility- and disabled-checks for toolbar actions, you need to register the bundle in your package.json:

```js
// assets/admin/package.json
{
    // ...
    "dependencies":
    {
        // ...
        "sulu-permission-aware-templates-bundle": "file:../../vendor/perspeqtive/sulu-permission-aware-templates-bundle/assets/js"
    }
},
```

### Import the bundle

```js
// ...
import 'sulu-permission-aware-templates-bundle';
```

### Rebuild the admin JS

```bash
cd assets/admin
npm install
npm run build
```


### Clear the cache

Clear the cache after installing the bundle:

```bash
bin/console cache:clear
```

## 🔐 Permissions

After installation, Sulu contains a new `Templates` security section. Every page template is registered with its own security context:

```text
Sulu
`-- Templates
    |-- templates.default
    |-- templates.homepage
    `-- templates.article
```

The permission behavior is:

| Permission | Behavior |
| --- | --- |
| `add` | The user may edit a content page that already uses the template, but cannot change the template. |
| `write` | The user may select the template in the dropdown and may change an existing page to this template. The user may also save, publish and unpublish pages using this template. |
| `delete` | The user may delete a content page that uses this template. |

⚠️ **Important**: Users without the required template permission can still edit content pages they are allowed to access, but the template-related actions are restricted by the permissions above.

Internally the bundle maps these checks to Sulu's template security permissions:

| Sulu permission | Used for |
| --- | --- |
| `add` | Template dropdown availability |
| `edit` | Save, publish and unpublish toolbar actions |
| `delete` | Delete toolbar action |

## 🧩 Usage

No additional configuration is required. The bundle reads the available page templates from Sulu's `page` form metadata and registers matching security contexts automatically.

Assign the permissions in the Sulu role settings:

1. Open the Sulu admin.
2. Go to `Settings` > `Roles`.
3. Select a role.
4. Configure the permissions in `Sulu` > `Templates`.
5. Save the role.

The page editor then applies the permissions automatically:

- Users without `write` permission only see templates they may keep or select.
- Users with access to a page may still edit the page content.
- Users without `write` permission for the current template cannot save, publish or unpublish that page.
- Users without `delete` permission for the current template cannot delete that page.


## 🤝 Contribution

Please feel free to fork and extend existing or add new features and send a pull request with your changes. To establish a consistent code quality, please provide unit tests for all your changes and adapt the documentation.
