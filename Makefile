all:
	lessc www/less/main.less www/css/main.css --compress
	uglifyjs www/js/lib/handlebars-1.0.0-rc3.js www/js/main.js -o www/js/main.min.js -c -m