<?php
require_once( '../class.paml3.php' );
$base_path = './contents/';
$ctx = new PAML ();
$base_path = './contents/';
$end_point = 'https://powercmsx.jp/api/v1/1/entry/list.json?cols=title,text,text_format,basename,categories,Path,Permalink&limit=10';
// Set to Smarty compatible delimiter.
$ctx->compatible = 'Smarty';
$ctx->force_compile = false;
$ctx->compile_dir = './compiled/';
$ctx->allow_fileput = true;
$ctx->use_plugin = true;
$ctx->init();
$params = ['base_path' => $base_path, 'end_point' => $end_point, 'sapi' => php_sapi_name() ];
echo $ctx->build_page( 'tmpl/smarty.tpl', $params );
