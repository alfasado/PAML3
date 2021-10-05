<?php
    $pfx = $this->prefix;
    $this->pre_fetch = ['/\{\{\s*#set\s{1,}([^=\s]*)?\s*\}\}(.*?)\{\{\/\s*set\s*\}\}/si'
                          => '<' . $pfx . ':setvarblock name="$1">$2</' . $pfx . ':setvarblock>',
                       '/\{\{\s*#let\s{1,}([^=\s]*)?\}\}(.*?)\{\{\/\s*let\s*\}\}/si'
                          => '<' . $pfx . ':setvarblock name="$1" scope="local">$2</' . $pfx . ':setvarblock>',
                       '/\{\{\/\s*(.*?)\s*\}\}/s'
                          => '</' . $pfx . ':$1>',
                       '/\{\{\s*#(.*?)?\s*\}\}/s'
                          => '<' . $pfx . ':$1>',
                       '/\{\{\s*([^\}"\']*?)\|[a-zA-Z0-9|]+?\s*\}\}/si'
                          => ['/\|/' => ' '],
                       '/\{\{\s*(.*?)?\s*\}\}/s'
                          => '<' . $pfx . ':$1>'];

    function mustache_pre_fetch ( $ctx, &$content, &$map ) {
        if ( strpos( $content, '{{' ) === false ) {
            return;
        }
        $pfx = $ctx->prefix;
        if ( strpos( $content, '{{!' ) !== false || strpos( $content, '{{ !' ) !== false ) {
            // Comment
            $content = preg_replace( '/\{\{\s*!.*?\s*\}\}/si', '', $content );
        }
        if ( stripos( $content, '{{raw' ) !== false || stripos( $content, '{{ raw' ) !== false ) {
            // Literal
            $regex = '/\{\{\s*raw.*?\s*\}\}(.*?)\{\{\/\s*raw\s*\}\}/si';
            if ( preg_match_all( $regex, $content, $mts ) ) {
                $raws = $mts[1];
                $mts = $mts[0];
                foreach ( $mts as $idx => $mt ) {
                    $magic = $ctx->magic( $content );
                    $map[ $magic ] = $raws[ $idx ];
                    $mt = preg_quote( $mt, '/' );
                    $content = preg_replace( "/{$mt}/", $magic, $content, 1 );
                }
            }
        }
        if ( strpos( $content, '{{{' ) !== false ) {
            // Not escape
            $content = preg_replace( '/\{\{\{\s*(.*?)\s*\}\}\}/s', '<'
            . $pfx .':var name="' . '$1' . '" raw="1">', $content );
        }
        if ( strpos( $content, '{{^' ) !== false || strpos( $content, '{{ ^' ) !== false ) {
            // Unless
            if ( preg_match_all( "/\{\{\s*\^(.*?)\s*\}\}/s", $content, $mts ) ) {
                foreach ( $mts[0] as $idx => $mt ) {
                    $name = preg_quote( $mts[1][ $idx ], '/' );
                    $regex = preg_quote( $mt, '/' ) . '(.*?)\{\{\/' . $name . '\}\}';
                    $content = preg_replace(
                      "/$regex/si", '<' . $pfx .':unless name="' . $name . '">'
                      . '$1' . '</'. $pfx . ':unless>', $content );
                }
            }
        }
        if ( strpos( $content, '{{/' ) !== false || strpos( $content, '{{ /' ) !== false ) {
            // If
            $blocks = array_merge( $ctx->tags['block'],
                      $ctx->tags['block_once'], $ctx->tags['conditional'], ['set', 'let'] );
            if ( preg_match_all( "/\{\{\s*\/(.*?)\s*\}\}/s", $content, $mts ) ) {
                foreach ( $mts[1] as $tag ) {
                    if (!in_array( $tag, $blocks ) ) {
                        $regex = '\{\{\s*#{0,1}' . preg_quote( $tag, '/' ) . '\s*\}\}(.*?)\{\{\s*\/' . $tag . '\s*\}\}';
                        $content = preg_replace(
                          "/$regex/si", '<' . $pfx .':if name="' . $tag . '">'
                          . '$1' . '</'. $pfx . ':if>', $content );
                    }
                }
            }
        }
    }