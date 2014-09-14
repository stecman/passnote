.PHONY: less less-dev install

less-dev:
	lessc --include-path=./:./source/components ./source/less/style.less public/css/style.css

less:
	lessc -x --include-path=./:./source/components ./source/less/style.less public/css/style.css

install:
	cd source && bower install