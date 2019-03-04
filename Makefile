BIN := $(shell npm bin)
LESSC := $(BIN)/lessc

all: app.css

app.css: less/*.less
	$(LESSC) less/app.less > $@

watch: all
	while inotifywait -e MODIFY -r less ; do make $^ ; done

.PHONY: all watch