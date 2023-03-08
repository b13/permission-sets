# Repeatable and deployable Permission Sets for TYPO3

*** Please note that this extension is still in testing phase ***

This extension allows to attach a set of permissions to TYPO3 Backend UserGroups
based on files.

These sets of permissions are put in files and can be stored on a per-project
basis or in extensions.

Currently, the only supported configuration file for permission sets
is YAML.

This extension works with TYPO3 v11+.

## Installation

You can install this extension by using composer:

    composer req b13/permission-sets

## Location of permission set resources

* `config/permission-sets/*.yaml`
* `EXT:my-extension/Configuration/PermissionSets/*.yaml`

## Available options in a Permission Set

### Module Access

Give access to a module, or to all modules of a main module.

```
modules:
  # enable access to a specific module
  web_info: true
  # enable access to all submodules of a module
  web: "*"
```

### TCA-related settings

Enable access to TCA tables

```
resources:
  pages:
    fields: "*"
    types: "*"
  tt_content:
    fields: "*"
    types: ["textpic"]
```

Special options:
* `_languageFields`
* `_types`
* `_starttime`


### Languages

```
# Allow any language
languages: "*"

# Allow any locale (used from site configuration)
languages: ["de_DE", "en_UK"]
```

### File Permissions

```
files:
  # file-related permissions
  - readFile
  - writeFile
  - addFile
  - renameFile
  - replaceFile
  - moveFile
  - copyFile
  - deleteFile
  # folder-related permissions
  - readFolder
  - writeFolder
  - addFolder
  - renameFolder
  - moveFolder
  - copyFolder
  - deleteFolder
  - recursiveDeleteFolder
```

### Sites

```
sites:
  # Grant Permissions to the DB mounts of a specific site
  - main
  - landingpage1
  # Grant permission to a specific page within the installation
  - 13
```

### UserTsConfig

UserTsConfig is merged automatically with the users' / user group
configuration.

```
settings:
    options:
        createFoldersInEB: true
    TCEMAIN:
        clearCache: all
```

### Dashboard Widgets

```
widgets: ['*']

widgets:
    - 'sysLogErrors'
    - 't3news'
```

## ToDo

* Extensive tests
* Add default permission sets as examples
* Enhance documentation

## License

The extension is licensed under GPL v2+, same as the TYPO3 Core. For details see the LICENSE file in this repository.

## Open Issues

If you find an issue, feel free to create an issue on GitHub or a pull request.

## Credits

This extension was created by [Benni Mack](https://github.com/bmack) in 2021 for [b13 GmbH](https://b13.com).

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you) that help us deliver value in client projects. As part of the way we work, we focus on testing and best practices to ensure long-term performance, reliability, and results in all our code.
