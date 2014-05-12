.PHONY: less less-dev js js-dev

less-dev:
	lessc --include-path=./:./source/components ./source/less/style.less public/css/style.css
	lessc --include-path=./:./source/components ./source/less/ace.less public/css/ace.css

less:
	lessc -x --include-path=./:./source/components ./source/less/style.less public/css/style.css
	lessc -x --include-path=./:./source/components ./source/less/ace.less public/css/ace.css

js-dev:
	ln -sf '../source/components' public/
	jsx --watch source/js public/js