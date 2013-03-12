# UNL Site Validator

## Install
To install, run the script `scripts/install.php` via command line

Example - normal install
```
php scripts/install.php
```

Example - Force install (replace tables)
```
php scripts/install.php -f
```

## A Note on LESS/CSS
All CSS is created with the LESS pre-processor. Do not modify the CSS files, as they will be overwritten by the LESS builds.

WDN Template _mixins are required:
```
ln -s /path/to/UNL_WDNTemplates/wdn/templates_3.1/less/_mixins wdn_mixins
```

## Build Dependencies
```
make
```
That's all.

#### Sublime Text 2 Build
1. Use the (`less2css`)[https://github.com/timdouglas/sublime-less2css] Build Process
2. Local Sublime Text 2 Settings (via "Preferences" -> "Package Settings" -> "Less2Css" -> "Settings - User")

```
    {
      "lessBaseDir": "./www/less",
      "outputDir": "./www/css",
      "minify": true,
      "autoCompile": true,
      "showErrorWithWindow": true,
      "main_file": "main.less"
    }
```

## API
All api data is returned as JSON

Base URL: `api.php`

Required GET Arguments for ALL requests:
* `uri`
  * The URL of the site (url encoded)

### GET site statisitcs
A GET request to the base api url with the `uri` argument set will return a JSON result set

`last_scan` will be `false` if the site has never been scaned or a scan is currently not complete.
`queued` will be `true` if the site is currently in the in the process of being scanned or is in the queue.

In the case that a site is `queued` and in the process of being scanned, current results of the scan will be returned.

Optional GET Arguments
* `page`
  * The URL of a specific sub-page (url encoded) - this will return results for only that sub-page

Example (all pages):
```
GET http://validator.unl.edu/site/api.php?uri=http%3A%2F%2Fwdn.unl.edu%2F
```

Example (single page):
```
GET http://validator.unl.edu/site/api.php?uri=http%3A%2F%2Fwdn.unl.edu%2F&page=http%3A%2F%2Fwdn.unl.edu%2Fdocumentation%2F
```

### GET page html errors
Will return a list of HTML errors for a given page within a site.

When the page is checked with this method, the API will recheck that page for HTML errors, update the DB with the new error count and return the errors in JSON format.

Additional required GET arguments:
* `action=html_errors`
* `page`
  * The URL of the page that you want to request an error list for (url encoded)

Example:
```
GET http://validator.unl.edu/site/api.php?action=html_errors&uri=http%3A%2F%2Fwdn.unl.edu%2F&page=http%3A%2F%2Fwdn.unl.edu%2F
```

### POST a request to check a site
Will re-crawl and run all tests against a site.  This will usually take a long time.  When finished, it will return the JSON for the site statisitcs 

Additional required POST arguments:
* `action=check`

Optional GET Arguments
* `page`
  * The URL of a specific sub-page (url encoded) - this will check ONLY that sub-page and return results for only that sub-page

Example (all pages):
```
POST http://validator.unl.edu/site/api.php?uri=http%3A%2F%2Fwdn.unl.edu%2F
POST-DATA: 'action=check'
```

Example (single page):
```
POST http://validator.unl.edu/site/api.php?uri=http%3A%2F%2Fwdn.unl.edu%2F%2F&page=http%3A%2F%2Fwdn.unl.edu%2Fdocumentation%2F
POST-DATA: 'action=check'
```
