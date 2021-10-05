<?php
use Michelf\Markdown;
function smarty_modifier_convert_breaks ( $text, $arg = null ) {
    if ( $arg === 'markdown' ) {
        $autoload = __DIR__ . DS . 'lib' . DS . 'vendor' . DS . 'autoload.php';
        if (! file_exists( $autoload ) ) {
            return $text;
        }
        require_once( $autoload );
        $text = Markdown::defaultTransform( $text );
    } else if ( $arg === 'convert_breaks' ) {
        $results = [];
        $lines = preg_split( '/[\r?\n]{2}/', $text );
        $perttern = '/^<\/?(?:h|table|tr|dl|ul|ol|div|p|pre|blockquote|address|hr)/';
        $in_paragraph = false;
        foreach ( $lines as $line ) {
            if ( preg_match( "/<p/i", $line ) ) {
                $after = preg_replace( "/.*<p[^>]*>/i", '', $line );
                if (! preg_match( "/<\/p/i", $after ) ) {
                    $in_paragraph = true;
                }
            }
            if (!preg_match( $perttern, $line ) ) {
                $line = preg_replace( '/\r?\n/', '<br />' . PHP_EOL, $line );
                if (! $in_paragraph ) {
                    $line = "<p>{$line}</p>";
                } else {
                    $line = "<br /><br />{$line}";
                }
            }
            if ( preg_match( "/<\/p/i", $line ) ) {
                $after = preg_replace( "/.*<\/p[^>]*>/i", '', $line );
                if (! preg_match( "/<p/i", $after ) ) {
                    $in_paragraph = false;
                }
            }
            $results[] = $line;
        }
        return implode( PHP_EOL . PHP_EOL, $results );
    }
    return $text;
}