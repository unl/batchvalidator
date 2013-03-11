# UNL Site Validator

## A Note on LESS/CSS
All CSS is created with the LESS pre-processor. Do not modify the CSS files, as they will be overwritten by the LESS builds.

WDN Template _mixins are required:
`ln -s /path/to/UNL_WDNTemplates/wdn/templates_3.1/less/_mixins wdn_mixins`

### CSS Building
To build CSS files, you must have LESS installed:
`npm install -g less`

Then, to build the `main.css` file:
`lessc www/less/main.less www/css/main.css --compress`

## Javascript
Compression and mangling handled with uglify:
`npm install uglify-js -g`

Combine, compress and mangle:
`uglifyjs www/js/lib/handlebars-1.0.0-rc3.js www/js/main.js -o www/js/main.min.js -c -m`

#### Sublime Text 2 Build
1. Use the `(less2css)[https://github.com/timdouglas/sublime-less2css]` Build Process
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