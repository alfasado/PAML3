<?php
    $pfx = $this->prefix;
    $this->pre_fetch = ['/\{#\s*.*?\s*#\}/si'
                          => '',
                       '/\{%\s*raw\s*%\}(.*?)\{%\s*endraw\s*%\}/si'
                          => '__raw__',
                       '/\{%\s*set\s{1,}([^=\s]*)?\s*%\}(.*?)\{%\s*endset\s*%\}/si'
                          => '<' . $pfx . ':setvarblock name="$1">$2</' . $pfx . ':setvarblock>',
                       '/\{%\s*let\s{1,}([^=\s]*)?\s*%\}(.*?)\{%\s*endlet\s*%\}/si'
                          => '<' . $pfx . ':setvarblock name="$1" scope="local">$2</' . $pfx . ':setvarblock>',
                       '/\{%\s*end(.*?)\s*%\}/si'
                          => '</' . $pfx . ':$1>',
                       '/\{%\s*(.*?)?\s*%\}/si'
                          => '<' . $pfx . ':$1>',
                       '/\{\{\s*([^\}"\']*?)\|[a-zA-Z0-9|]+?\s*\}\}/si'
                          => ['/\|/' => ' '],
                       '/\{\{\s*(.*?)?\s*\}\}/si'
                          => '<' . $pfx . ':$1>'];
