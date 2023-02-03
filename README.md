# Installation

Based on https://daniellockyer.com/php-flame-graphs/ and https://github.com/brendangregg/FlameGraph .

Configuration is for the Docker Debian/Ubuntu container. Please adjust, if your container's OS is different.

1. Install the XDebug 2 PHP extension:

```Docker
RUN pecl install xdebug-2.9.8 \
 && docker-php-ext-enable xdebug \
 && echo "xdebug.mode=debug,trace" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.discover_client_host=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.remote_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.remote_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.remote_connect_back=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
```

2. Install Perl for FlameGraph:

```Docker
RUN apt install perl
```

3. Add FlameGraph configuration to XDebug:

```Docker
RUN echo "xdebug.trace_output_name=xdebug.trace.%t.%s" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.trace_enable_trigger=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.trace_output_dir=/tmp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.trace_enable_trigger_value=secret4565467567" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.trace_format=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
```

4. Clone https://github.com/AlexeyPlodenko/FlameGraph to your project web root directory.
5. Copy-paste the `flamegraph.php` file from this repository to your project web directory.
6. Access the URL that your want to have a flame graph for with a query parameter `XDEBUG_TRACE=secret4565467567`
7. Once the page from the previous step loads, access the `/flamegraph.php` script from your browser to see the flame graph.
