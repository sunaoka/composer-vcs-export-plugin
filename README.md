# Composer VCS Export Plugin

[![Latest](https://poser.pugx.org/sunaoka/composer-vcs-export-plugin/v)](https://packagist.org/packages/sunaoka/composer-vcs-export-plugin)
[![License](https://poser.pugx.org/sunaoka/composer-vcs-export-plugin/license)](https://packagist.org/packages/sunaoka/composer-vcs-export-plugin)
[![PHP](https://img.shields.io/packagist/php-v/sunaoka/composer-vcs-export-plugin)](composer.json)
[![Test](https://github.com/sunaoka/composer-vcs-export-plugin/actions/workflows/test.yml/badge.svg)](https://github.com/sunaoka/composer-vcs-export-plugin/actions/workflows/test.yml)
[![codecov](https://codecov.io/github/sunaoka/composer-vcs-export-plugin/graph/badge.svg)](https://codecov.io/github/sunaoka/composer-vcs-export-plugin)

## Overview

This Composer plugin ensures that when installing packages from VCS repositories (such as Git) via the `repositories` configuration, only the files allowed by `.gitattributes` `export-ignore` rules are placed in the `vendor` directory.  
It enables clean, distribution-like installs even when directly referencing VCS sources, helping maintain a tidy `vendor/` with only the intended files from each package.

## Features

- Applies `.gitattributes` `export-ignore` rules to packages installed from VCS repositories
- Ensures only distribution-ready files are present in `vendor/`
- Works automatically during Composer install/update
- No configuration required for standard usage

## Installation

Add the plugin to your project using Composer:

```bash
composer require --dev sunaoka/composer-vcs-export-plugin
```

Or

```bash
composer global require sunaoka/composer-vcs-export-plugin
```

## Usage

No additional configuration is needed.  
When you install or update packages from VCS repositories (e.g., via the `repositories` section in your `composer.json`), this plugin will:

- Detect if the package was installed from a Git repository
- Use `git archive` to export files, respecting `.gitattributes` `export-ignore` rules
- Replace the package directory in `vendor/` with the exported contents

## Example

**composer.json:**

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://example.com/your-vendor/your-library.git"
        }
    ],
    "require": {
        "your-vendor/your-library": "^1.0",
        "sunaoka/composer-vcs-export-plugin": "^1.0"
    },
    "config": {
        "allow-plugins": {
          "sunaoka/composer-vcs-export-plugin": true
        }
    }
}
```

With this setup, only the files not marked with `export-ignore` in `.gitattributes` will be present in `vendor/your-vendor/your-library`.

## Requirements

- PHP 7.2.5 or later
- Composer 2.x
- `git` and `unzip` command available in the system environment

## Limitations

- Only works with Git repositories
- Requires the `.git` directory to be present in the installed package (i.e., VCS install, not dist)
- May not work on environments where `git archive` or `unzip` are unavailable (e.g., some Windows setups)
- Does not affect packages installed via dist (zip/tarball)
