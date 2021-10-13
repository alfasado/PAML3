<?php

/**
 * PAML : PHP Alternative Markup Language
 *
 * @version    3.0
 * @package    PAML
 * @author     Alfasado Inc. <webmaster@alfasado.jp>
 * @copyright  2021 Alfasado Inc. All Rights Reserved.
 */
if (! defined( 'DS' ) ) {
    define( 'DS', DIRECTORY_SEPARATOR );
}
if (! defined( 'PAMLDIR' ) ) {
    define( 'PAMLDIR', __DIR__ . DS );
}
if (! defined( 'EP' ) ) {
    define( 'EP', '?>' . PHP_EOL );
}

/**
 * PAMLVSN = Compile format version.
 */
define( 'PAMLVSN', '3.0' );

class PAML {
    private   $version       = 3.0;

/**
 * $prefix        : Tag prefix.
 * $tag_block     : Tag delimiters.
 * $html_block    : Replace HTML tags temporarily.
 * $ldelim,$rdelim: Alias for $tag_block.
 */
    public    $id;
    public    $app_name      = 'PAML';
    public    $prefix        = 'paml';
    public    $tag_block     = ['{%', '%}'];
    public    $html_block    = ['%[[', ']]%'];
    public    $ldelim, $rdelim;
    public    $html_ldelim, $html_rdelim;
    public    $compatible    = null;
    public    $cache_ttl     = 86400;
    public    $use_plugin    = false;
    public    $force_compile = true;
    public    $build_compile = false;
    public    $compile_check = true;
    public    $keep_vars     = false;
    public    $cached        = false;
    public    $request_cache = true;
    public    $advanced_mode = true;
    public    $language      = 'ja';
    public    $compile_dir;
    public    $unify_breaks  = false;
    public    $trim_tmpl     = false;
    public    $logging       = false;
    public    $build_start   = false;
    public    $log_path;
    public    $csv_delimiter = ',';
    public    $csv_enclosure = "'";
    public    $plugin_compat = 'smarty_';
    public    $literal_compat= false;
    public    $keys_lower    = false;
    public    $path          = __DIR__;
    public    $esc_trans     = false;
    public    $app           = null;
    public    $meta;
    public    $inited;
    private   $call_break    = false;
    public    $sethashvar_compat = true;
    public    $regex_compat  = true;
    public    $tmpl_mtime    = null;
    public    $user_agent    = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36';
    public    $allow_fileget = false;
    public    $allow_fileput = false;
    public    $allow_unlink  = false;
    public    $dir_perms     = null;
    public    $file_perms    = null;
    public    $update_paths  = [];
    public    $allowed_paths = [];
    public    $pre_fetch     = [];
    public    $replaced_map  = [];
    public    $extends_meth  = [];
    public    $else_in_block = true;

/**
 * $autoescape: Everything is escaped(When there is no designation of 'raw' modifier).
 */
    public    $autoescape    = false;

/**
 * $includes: Array of file extensions that allow include.
 */
    public    $includes      =  ['txt', 'tpl', 'tmpl', 'inc', 'html'];

/**
 * $vars           :  Global variables.
 * $__stash['vars']:  Alias for $vars.
 * $local_vars     :  Local variables in block scope.
 * $params         :  Global parameters.
 * $local_params   :  Local parameters in block scope.
 */
    public    $vars          = [];
    public    $__stash       = [];
    public    $local_vars    = [];
    public    $params        = [];
    public    $local_params  = [];
    public    $literal_vars  = [];

/**
 * $include_paths : Path(s) of php file called $this([path=>true...]).
 * $template_paths: Template or included template path(s)([path=>true...]).
 * $plugin_paths  : Array of plugin directories([path1,path2...]).
 */
    public    $include_paths = [];
    public    $template_paths= [];
    public    $plugin_paths  = [];
    public    $dictionary    = [];
    public    $include_files = [];

/**
 * $cache_driver = 'Memcached' or null. If null, use simple file cache.
 */
    public    $cache_driver  = null;
    public    $request_time  = null;
    public    $plugin_order  = 0; // 0=asc, 1=desc
    public    $components    = [];
    protected $all_tags      = [];
    protected $ids           = [];
    protected $old_vars      = [];
    protected $old_params    = [];
    protected $cached_vars   = [];
    protected $cached_stash  = [];
    protected $func_map      = [];
    protected $block_vars    = [];
    public    $dom;
    public    $compiled      = [];

    public $default_component= null;

/**
 * $tags: Array of Core Template Tags.
 */
    public   $tags = [
      'block'       => ['block', 'loop', 'foreach', 'for','section', 'literal', 'queries',
                        'sethashvar', 'dynamicmtml'],
      'block_once'  => ['ignore', 'setvars', 'sethashvars', 'capture', 'setvarblock', 'assignvars',
                        'setvartemplate', 'nocache', 'isinchild'],
      'conditional' => ['else', 'elseif', 'if', 'unless', 'ifgetvar', 'elseifgetvar', 'ifinarray',
                        'isarray', 'isset'],
      'modifier'    => ['escape' ,'setvar', 'assign', 'format_ts', 'zero_pad', 'trim_to', 'eval',
                        'add_slash', 'strip_linefeeds', 'sprintf', 'encode_js', 'encode_json', 'truncate',
                        'wrap', 'encode_url', 'trim_space', 'regex_replace', 'setvartemplate',
                        'replace', 'translate', 'count_chars', 'to_json', 'from_json', 'encode_html',
                        'nocache', 'split', 'join', 'format_size', 'encode_xml', 'encode_php',
                        'instr', 'mb_instr', 'relative', 'numify', 'merge_linefeeds', 'array_pop',
                        'default', 'normalize', 'preg_quote', 'remove_blank', 'increment',
                        'decrement', 'cast_to', 'sanitize', 'count_characters', 'set', 'let'],
      'function'    => ['getvar', 'trans', 'setvar', 'ldelim', 'include', 'math', 'break', 'rdelim', 
                        'fetch', 'var', 'date', 'assign', 'count', 'vardump', 'unsetvar', 'gethashvar', 
                        'query', 'ml', 'arrayshuffle', 'arrayslice', 'arrayrand', 'unset',
                        'triggererror', 'constant', 'fileput', 'unlink', 'set', 'let'],
      'include'     => ['include', 'includeblock', 'extends'] ];

/**
 * $modifier_funcs: Mappings of modifier and PHP functions.
 */
    public    $modifier_funcs = [
      'lower_case' => 'strtolower', 'upper_case' => 'strtoupper', 'trim' => 'trim',
      'ltrim' => 'ltrim',  'remove_html' => 'strip_tags', 'rtrim' => 'rtrim',
      'nl2br' => 'nl2br', 'base64_encode' => 'base64_encode', 'strtotime' => 'strtotime',
      'title_case' => 'ucwords', 'array_unique' => 'array_unique', 'urldecode' => 'urldecode',
      'decode_html' => 'html_entity_decode', 'unescape' => 'html_entity_decode'];

/**
 * $callbacks: Array of Callbacks.
 */
    public    $callbacks = [
      'input_filter'=> [], 'pre_parse_filter'   => [], 'output_filter'=> [],
      'dom_filter'  => [], 'post_compile_filter'=> [] ];

/**
 * Initialize a PAML.
 *
 * @param array $config: Array for set class properties.
 *                          or properties to JSON file.
 */
    function __construct ( $config = [] ) {
        set_error_handler( [ $this, 'errorHandler'] );
        if ( ( $cfg_json = PAMLDIR . 'config.json' ) 
            && file_exists( $cfg_json ) ) $this->configure_from_json( $cfg_json );
        foreach( $config as $k => $v ) $this->$k = $v;
        $this->__stash['vars'] =& $this->vars;
        $this->components['paml'] = $this;
        $this->core_tags = $this->tags;
    }

    function __call( $name, $args ) {
        if (!isset( $this->functions[ $name ] ) ) return;
        return call_user_func_array( $this->functions[ $name ], $args );
    }

    function __get ( $name ) {
        return isset( $this->$name ) ? $this->$name : null;
    }

    function init () {
        if ( $this->inited ) return;
        if (!$this->language && isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) )
            $this->language = substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 );
        $this->tags['modifier'] = array_merge(
            array_keys( $this->modifier_funcs ), $this->tags['modifier'] );
        $tags = $this->tags;
        foreach ( $tags as $kind => $tags_arr )
            $this->all_tags[ $kind ] = array_flip( $tags_arr );
        if ( debug_backtrace()[0] && $f = debug_backtrace()[0]['file'] )
            $this->include_paths[ dirname( $f ) ] = true;
        if ( $this->use_plugin ) {
            if ( ( $plugin_d = PAMLDIR . 'plugins' ) && is_dir( $plugin_d ) )
                $this->plugin_paths[] = $plugin_d;
            $this->init_plugins();
        }
        if ( $this->ldelim && $this->rdelim )
            $this->tag_block = [ $this->ldelim, $this->rdelim ];
        if (! $this->request_time ) {
            $this->request_time = isset( $_SERVER['REQUEST_TIME'] ) ? $_SERVER['REQUEST_TIME'] : time();
        }
        if ( $this->compatible ) {
            $this->compatible = strtolower( $this->compatible );
            // smarty, twig or mustache
            $pre_fetch = __DIR__ . DS . 'filters' . DS . $this->compatible . '.php';
            if ( file_exists( $pre_fetch ) ) {
                include_once( $pre_fetch );
            }
        }
        $this->inited = true;
    }

/**
 * Load plugins.
 */
    function init_plugins () {
        $plugin_paths = $this->plugin_paths;
        foreach ( $plugin_paths as $dir ) {
            $items = scandir( $dir, $this->plugin_order );
            foreach ( $items as $plugin ) {
                if ( strpos( $plugin, '.' ) === 0 ) continue;
                $plugin = $dir . DS . $plugin;
                if (! is_dir( $plugin ) ) continue;
                $plugins = scandir( $plugin, $this->plugin_order );
                foreach ( $plugins as $f ) {
                    if ( ( $_plugin = $plugin . DS . $f ) && ( is_file( $_plugin ) )
                      && ( pathinfo( $_plugin )['extension'] === 'php' ) ) {
                        if (!include( $_plugin ) )
                            trigger_error( "Plugin '{$f}' load failed!" );
                        if ( preg_match ("/^class\.(.*?)\.php$/", $f, $mts ) ) {
                            if (!class_exists( $mts[1] ) ) continue;
                            $obj = new $mts[1]();
                            $registry = property_exists( $obj, 'registry' )
                                      ? $obj->registry : [];
                            $this->register_component(
                                $obj, dirname( $_plugin ), $registry );
                        }
                    }
                }
            }
        }
    }

/**
 * Register Smarty2(BC) style plugins.
 */
    function init_functions () {
        $this->functions = [];
        $_pfx = $this->prefix;
        $pfx = preg_quote( $_pfx );
        $plugin_paths = $this->plugin_paths;
        foreach ( $plugin_paths as $dir ) {
            $items = scandir( $dir, $this->plugin_order );
            foreach ( $items as $plugin ) {
                if ( strpos( $plugin, '.' ) === 0 ) continue;
                $plugin = $dir . DS . $plugin;
                if (!preg_match (
                    "/(^.*?)\.(.*?)\.php$/", basename( $plugin ), $mts ) ) continue;
                list( $all, $kind, $tag ) = $mts;
                if ( $kind != 'modifier' ) $tag = preg_replace( "/^$pfx/", '', $tag );
                $this->functions[ "{$kind}_{$tag}" ] =
                    [ $this->plugin_compat . "{$kind}_{$tag}", $plugin ];
                if ( $kind === 'block' ) if ( strpos( $tag, 'if' ) === 0
                    || strpos( $tag, $_pfx . 'if' ) === 0 ) $kind = 'conditional';
                $this->tags[ $kind ][] = $tag;
                $this->all_tags[ $kind ][ $tag ] = true;
            }
        }
    }

/**
 * Set properties from JSON.

 * @param string $json: JSON file path.
 */
    function configure_from_json ( $json ) {
        if (!is_readable( $json ) ) return;
        $config = json_decode( file_get_contents( $json ), true );
        foreach ( $config as $k => $v ) $this->$k = $v;
    }

/**
 * Autoload Smarty2(BC) style modifier function.
 *
 * @param  string $name: Modifier name.
 * @return string $func: Modifier name if function_exists.
 */
    function autoload_modifier ( $name ) {
        $funcs = $this->functions;
        if (!isset( $funcs[ $name ] ) ) {
            $plugin_paths = $this->plugin_paths;
            $f = str_replace( '_', '.', $name ).'.php';
            list( $kind, $tag ) = explode( '_', $name );
            $plugin = '';
            $func = '';
            foreach ( $plugin_paths as $dir ) {
                $f = $dir . DS . $f;
                if ( file_exists( $f ) ) {
                    $plugin = $f;
                    $func = $this->plugin_compat . $name; break;
                }
            }
            if ( $plugin && $func ) $funcs[ $name ] = [ $func, $plugin ];
        }
        if (!isset( $funcs[ $name ] ) ) return null;
        list( $func, $plugin ) = $funcs[ $name ];
        if ( function_exists( $func ) ) return $func;
        $this->functions = $funcs;
        if (!include( $plugin ) ) trigger_error ( "Plugin '$plugin' load failed!" );
        if ( $this->in_nocache ) $this->cache_includes[] = $plugin;
        return function_exists( $func ) ? $func : null;
    }

/**
 * stash: Where the variable is stored.
 *
 * @param  string $name : Name of set or get variable to(from) stash.
 * @param  mixed  $value: Variable for set to stash.
 * @return mixed  $var  : Stored data.
 */
    function stash ( $name, $value = false, $var = null ) {
        if ( isset( $this->__stash[ $name ] ) ) $var = $this->__stash[ $name ];
        if ( $value !== false ) $this->__stash[ $name ] = $value;
        return $var;
    }

/**
 * Set variables.
 *
 * @param  string or array $name : Name of set variables or array variables.
 * @param  array $value          : Variable for set variables.
 */
    function assign ( $name, $value = null ) {
        $assign = !is_array( $name ) ? $this->vars[ $name ] = $value : false;
        if ( $assign === false ) {
            if (!$value ) $value = $name;
            foreach ( $value as $k => $v ) $this->vars[ $k ] = $v;
        }
    }

/**
 * Register plugin component(s).
 *
 * @param  object $obj     : Plugin class object.
 * @param  string $path    : Path of plugin directory.
 * @param  array  $registry: Array of template tags and callbacks.
 *                           Or $registry to file 'config.json'.
 */
    function register_component ( $obj, $path = '', $registry = [] ) {
        $obj->dictionary = [];
        $this->components[ strtolower( get_class( $obj ) ) ] = $obj;
        if ( $path ) $obj->path = $path;
        if ( empty( $registry ) && file_exists( $path . DS . 'config.json' ) )
            $registry = json_decode( file_get_contents( $path . DS . 'config.json' ), 1 );
        foreach ( $registry as $key => $funcs ) {
            if ( $key === 'tags' ) {
                foreach ( $funcs as $kind => $meths ) {
                    $tag_kind = $kind == 'block_once' ? 'block' : $kind;
                    $tags = array_keys( $funcs[ $kind ] );
                    foreach ( $tags as $name ) {
                        $this->tags[ $kind ][] = $name;
                        $this->all_tags[ $kind ][ $name ] = true;
                        $this->func_map[ $tag_kind . '_' . $name ] 
                            = [ $obj, $meths[ $name ] ];
                    }
                }
            } elseif ( $key === 'callbacks' ) {
                foreach ( $funcs as $kind => $meths ) {
                    $callbacks = array_keys( $funcs[ $kind ] );
                    foreach ( $callbacks as $name ) {
                        $method = $meths[ $name ];
                        $this->callbacks[ $kind ][] = $name;
                        $this->func_map[ $name ] = [ $obj, $method ];
                    }
                }
            }
        }
    }

/**
 * You can also register tags respectively.
 */
    function register_tag ( $name, $kind, $method, $obj ) {
        $tag_kind = $kind == 'block_once' ? 'block' : $kind;
        $this->tags[ $kind ][] = $name;
        $this->all_tags[ $kind ][ $name ] = true;
        $this->func_map[ $tag_kind . '_' . $name ] = [ $obj, $method ];
        $this->components[ strtolower( get_class( $obj ) ) ] = $obj;
    }

/**
 * You can also register callbacks respectively.
 */
    function register_callback ( $name, $kind, $method, $obj ) {
        $this->callbacks[ $kind ][] = $name;
        $this->func_map[ $name ] = [ $obj, $method ];
    }

    function unregister_callback ( $name, $kind ) {
        $callbacks = $this->callbacks[ $kind ];
        $registerd = [];
        foreach ( $callbacks as $callback ) {
            if ( $callback != $name ) {
                $registerd[] = $callback;
            }
        }
        $this->callbacks[ $kind ] = $registerd;
        unset( $this->func_map[ $name ] );
    }

/**
 * Get plugin component.
 *
 * @param  string $name    : Plugin class name.
 * @return object $obj     : Plugin class object.
 */
    function component ( $name ) {
        if ( isset( $this->components[ strtolower( $name ) ] ) )
            return $this->components[ strtolower( $name ) ];
    }

    function component_method ( $name ) {
        if (!isset( $this->func_map[ $name ] ) ) return null;
        list( $obj, $method ) = $this->func_map[ $name ];
        if ( method_exists( $obj, $method ) ) return $this->func_map[ $name ];
    }

/**
 * Do callbacks.
 */
    function call_filter
    ( $res, $type, &$args1 = null, &$args2 = null, &$args3 = null, &$args4 = null ) {
        $args0 = $this;
        $args = $args1;
        $filters = $this->callbacks[ $type ];
        foreach ( $filters as $key )
            if ( list( $obj, $method ) = $this->component_method( $key ) )
                $res = $obj->$method( $res, $args0, $args, $args2, $args3, $args4 );
        return $res;
    }

/**
 * Return a unique string characters not used in the template.
 */
    function magic ( $content = '' ) {
        $magic = '_' . substr( md5( uniqid( mt_rand(), true ) ), 0, 6 );
        if ( isset( $this->ids[ $magic ] ) || strpos( $content, $magic ) !== false )
            return $this->magic( $content );
        if ( $this->ids[ $magic ] = true ) return $magic;
    }

/**
 * Build template file and display or return result.
 *
 * @param   string $path    : Template file path.
 * @param   array  $params  : Array of template variables to set.
 * @param   string $cache_id: Template cache id.
 * @param   bool   $disp    : Display result or return result.
 * @param   string $src     : Template source text.
 * @return  string $content : After processing $content.
 */
    function build_page ( $path, $params = [], $cache_id = '', $disp = false, $src = '' ) {
        $force_compile = $this->force_compile;
        $filemtime = $path ? @filemtime( $path ) : $this->tmpl_mtime;
        if (!$src && $filemtime === false ) return;
        $ttl = $this->cache_ttl;
        $compile_key = '';
        $compile_path = '';
        $this->restore_vars = $this->vars;
        $this->include_paths[ dirname( $path ) ] = true;
        $this->template_paths[ $path ] = $filemtime;
        $this->template_file = $path;
        $compile_key = $cache_id ? $cache_id : md5( $path );
        $this->init();
        $request_time = $this->request_time;
        $ttl = $request_time - $filemtime;
        $old_vars = $this->vars;
        $old_local_vars = $this->local_vars;
        $old_old_vars = $this->old_vars;
        $old_old_params = $this->old_params;
        $old_literal_vars = $this->literal_vars;
        $this->re_compile = false;
        if (!$force_compile ) { // Compile cache.
            if (!empty( $params ) ) foreach ( $params as $k => $v ) $this->vars[ $k ] = $v;
            $compile_path = $this->get_cache( $compile_key, $ttl, true );
            if ( $this->out !== null ) {
                $out = $this->out;
                if (!empty( $this->callbacks['output_filter'] ) )
                    $out = $this->call_filter( $out, 'output_filter' );
                if ( $disp ) echo $out;
                unset( $this->out, $this->meta, $this->literal_vars, $this->template_file );
                $this->old_vars = $old_old_vars;
                $this->old_params = $old_old_params;
                $this->literal_vars = $old_literal_vars;
                return $out;
            }
        }
        $this->vars = $old_vars;
        $this->local_vars = $old_local_vars;
        $this->literal_vars = [];
        $this->id = $this->magic();
        $this->compile_path = $compile_path;
        $this->compile_key = $compile_key;
        if (! $src ) {
            $src = file_get_contents( $path );
        }
        if ( $this->use_plugin && !$this->functions ) $this->init_functions();
        $this->cache_includes = [];
        $out = $this->compile( $src, $disp, null, null, $params );
        if ( $this->unify_breaks || $this->trim_tmpl ) {
            $out = $this->finalize( $out );
        }
        $this->old_vars = $old_old_vars;
        $this->old_params = $old_old_params;
        $this->literal_vars = $old_literal_vars;
        return $out;
    }

/**
 * Do not display result.
 */
    function fetch ( $path, $cache_id = '', $params = [] ) {
        return $this->build_page( $path, $params, $cache_id, false );
    }

/**
 * Display result.
 */
    function display ( $path, $cache_id = '', $params = [] ) {
        return $this->build_page( $path, $params, $cache_id, true );
    }

/**
 * Build template from content.
 */
    function render ( $src, $params = [], $cache_id = '' ) {
        if ( $cache_id ) $this->compile_key = $cache_id;
        return $this->build_page( '', $params, $cache_id, false, $src );
    }

/**
 * Build from source text.
 *
 * @param  string $src     : Template source text.
 * @param  bool   $compiled: Get compiled PHP code.
 * @param  string $cache_id: md5 hash value of $src.
 * @return string $build: After processing $src.
 */
    function build ( $src, $compiled = false, $cache_id = null, $force = false ) {
        $this->cached = false;
        $metadata = $this->meta;
        if (! isset( $this->inited ) ) $this->init();
        $old_literal = $this->literal_vars;
        $this->literal_vars = [];
        if ( $this->use_plugin && !$this->functions ) $this->init_functions();
        if (!$this->id ) $this->id = $this->magic();
        $force_compile = $this->force_compile;
        $compile_path = '';
        if ( $this->literal_compat && ! $force_compile ) {
            if ( stripos( $src, 'literal' )!== false || stripos( $src, 'dynamicmtml' )!== false ) {
                $ctx = clone $this;
                list( $t_sta, $t_end, $sta_h, $end_h )
                    = array_merge( $this->tag_block, $this->html_block );
                list( $tag_s, $tag_e, $h_sta, $h_end, $pfx )
                    = $ctx->get_quoted( [ $t_sta, $t_end, $sta_h, $end_h, $this->prefix ] );
                $ctx->parse_literal( $src );
                if (!empty( $ctx->literal_vars ) ) {
                    $force_compile = true;
                }
            }
        }
        if (!$force_compile && !$force && !$compiled && !$this->build_compile ) {
            if (! $cache_id ) $cache_id = md5( $src );
            $compile_path = $this->get_cache( $cache_id );
            if ( $this->out !== null ) {
                $this->literal_vars = $old_literal;
                $out = $this->out;
                $this->out = null;
                if ( $this->unify_breaks || $this->trim_tmpl ) {
                    $out = $this->finalize( $out );
                }
                $this->meta = $metadata;
                $this->cached = true;
                return $out;
            }
        }
        $old_vars = $this->vars;
        $old_params = $this->params;
        $this->in_build = true;
        $this->force_compile = true;
        $build = $this->compile( $src, false, null, [], [], $compiled, false, $cache_id );
        $this->force_compile = $force_compile;
        $this->in_build = false;
        $this->literal_vars = $old_literal;
        if ( $compile_path && isset( $this->compiled[ $cache_id ] ) ) {
            $require = isset( $this->compiled["{$cache_id}_require"] )
                     ? $this->compiled["{$cache_id}_require"] : '';
            // template_paths is invalid?
            $this->set_cache( $cache_id, $this->compiled[ $cache_id ], $compile_path, $require );
        }
        $build = preg_replace( "/\n$/s", '', $build );
        if ( $this->unify_breaks || $this->trim_tmpl ) {
            $build = $this->finalize( $build );
        }
        $this->vars = array_merge( $old_vars, $this->vars );
        $this->params = array_merge( $old_params, $this->old_params );
        $this->meta = $metadata;
        return $build;
    }

/**
 * Quote all values passed to the array.
 *
 * @param   array $values: Array for quote strings.
 * @param   bool  $force : Always quote values(no cache).
 * @return  array $quoted: Array of quoted strings.
 */
    function get_quoted ( $values, $force = false ) {
        if ( $this->quoted_vars && ! $force ) return $this->quoted_vars;
        foreach ( $values as $v ) $quoted[] = preg_quote( $v, '/' );
        if (!$force ) $this->quoted_vars = $quoted;
        return $quoted;
    }

/**
 * Search template file from $template_paths and $include_paths.
 *
 * @param  string $path: Path of template file.
 * @return string $path: Real path of specified file.
 */
    function get_template_path ( $path, $continue = false ) {
        if ( isset( $this->include_files[ $path ] ) ) {
            return $this->include_files[ $path ];
        }
        $tmpl_paths = array_keys( $this->template_paths );
        $incl_paths = array_keys( $this->include_paths );
        $extension = strtolower( pathinfo( $path )['extension'] );
        if (!in_array( $extension, $this->includes ) ) return;
        if ( is_readable( $path ) ) {
            $realpath = realpath( $path );
            $this->template_paths[ $realpath ] = filemtime( $realpath );
            $this->include_files[ $path ] = $realpath;
            return $realpath;
        } else if (!$continue ) {
            $incl_paths = array_unique( $incl_paths );
            foreach ( $incl_paths as $tmpl ) {
                $tmpl = rtrim( $tmpl, DS );
                if ( ( $f = $tmpl . DS . $path ) && is_readable( $f ) ) {
                    $realpath = realpath( $f );
                    $this->template_paths[ $realpath ] = filemtime( $realpath );
                    $this->include_files[ $path ] = $realpath;
                    return $realpath;
                }
            }
            $tmpl_paths = array_unique( $tmpl_paths );
            foreach ( $tmpl_paths as $tmpl ) {
                $tmpl = rtrim( $tmpl, DS );
                $f = dirname( $tmpl ) . DS . $path;
                if ( is_readable( $f ) ) {
                    $realpath = realpath( $f );
                    $this->template_paths[ $realpath ] = filemtime( $realpath );
                    $this->include_files[ $path ] = $realpath;
                    return $realpath;
                }
            }
        }
        $this->include_files[ $path ] = '';
        return;
    }

/**
 * Setup tag attributes. value, variable, or array(CSV).
 *
 * @param  array  $args: Array args for setup.
 * @param  string $name: Template tag name.
 * @param  object $ctx : Class PAML.
 * @param  string $vars  : 'vars' and 'local_vars'.
 * @return array  $args: Set-uped $args.
 */
    function setup_args ( $args, $name = '', $ctx = null, $vars = null ) {
        $string = false;
        if (! $ctx ) $ctx = $this;
        if (! is_array( $args ) ) {
            $args = is_string( $args ) ?
                ['__key__' => $args ] : ['__key__' => (string) $args ];
            $string = true;
        }
        $encl = preg_quote( $ctx->csv_enclosure );
        $delim = preg_quote( $ctx->csv_delimiter );
        foreach ( $args as $k => $v ) {
            if (! is_string( $v ) ) {
                // do nothing
            } elseif ( strpos( $v, '$' ) === 0 ) { // Variable
                if (!$vars ) $vars = array_merge( $ctx->vars, $ctx->local_vars );
                $v = ltrim( $v, '$' );
                if ( preg_match( "/(.{1,})\[(.*?)\]$/", $v, $mts ) ) {
                    list( $v, $idx ) = [ trim( $mts[1] ), trim( $mts[2] ) ];
                } else if ( strpos( $v, '.' ) !== false ) {
                    list( $v, $idx ) = explode( '.', $v );
                }
                if ( isset( $idx ) && isset( $args['this_tag'] ) 
                    && $args['this_tag'] === 'if' && $k == 'name' ) {
                    $v = [ $v => $idx ];
                    $args['name'] = $v;
                } else {
                    $v = isset( $vars[ $v ] ) ? $vars[ $v ] : '';
                    if ( isset( $idx ) ) {
                        $args[ $k ] = isset( $v[ $idx ] ) ? $v[ $idx ] : '';
                        if ( strpos( $idx ,'$' ) === 0 ) {
                            $idx = ltrim( $idx, '$' );
                            $idx = isset( $vars[ $idx ] ) ? $vars[ $idx ] : '';
                            if ( is_array( $v ) && isset( $v[ $idx ] ) ) {
                                $args[ $k ] = ['__array__' => $v[ $idx ] ];
                                $args[ "<{$k}>" ] = $args[ $k ];
                            }
                        }
                        if ( isset( $idx ) && isset( $args['this_tag'] ) 
                            && $args['this_tag'] === 'var' && $k == 'name' ) {
                            $args['setuped_var'] = $args[ $k ];
                        }
                    } else {
                        $args[ $k ] = $this->setup_args( $v );
                    }
                }
            } elseif ( strpos( $v, $delim ) !== false
                && preg_match( "/^{$encl}.*?{$delim}.*{$encl}$/", $v ) ) {
                $arr = $ctx->parse_csv( $v ); // CSV
                $args[ $k ] = $arr;
                if ( strpos( $name, 'regex' ) !== false ) continue;
                $array = [];
                foreach ( $arr as $var )
                    $array[] = strpos( $var ,'$' ) === 0
                        ? $this->setup_args( $var, $name, $ctx, $vars ) : $var;
                $args[ $k ] = $array;
            } elseif ( ( $k === 'name' || $k === 'from' ) && strpos( $v, '.' ) !== false ) { // Array
                if (!$vars ) $vars = array_merge( $ctx->vars, $ctx->local_vars );
                $params = explode( '.', $v );
                $var = array_shift( $params );
                if ( isset( $vars[ $var ] ) && $v = $vars[ $var ] ) {
                    if ( is_array( $v ) ) foreach ( $params as $__key ) {
                        if ( isset( $v[ $__key ] ) ) {
                            $v = $v[ $__key ];
                        } else {
                            return $args;
                        }
                    }
                    if ( isset( $v ) ) {
                        $args["<{$k}>"] = $args[ $k ];
                        $args[ $k ]= ['__array__' => $v ];
                    }
                }
            }
        }
        return $string ? $args['__key__'] : $args;
    }

/**
 * Localize variables in block scope.
 *
 * @param array $vars: Array for localize and restore variables names.
 * @param array $stashes: Array for localize and restore stashes names.
 */
    function localize ( $vars = [] ) {
        $cached_stash = [];
        $cached_vars = [];
        foreach ( $vars as $var ) {
            if ( is_array( $var ) ) {
                foreach ( $var as $v ) {
                    if ( isset( $this->__stash['vars'][ $v ] ) ) {
                        $cached_stash['vars'][ $v ] = $this->__stash['vars'][ $v ];
                    }
                }
            } elseif ( isset( $this->__stash[ $var ] ) ) {
                $cached_stash[ $var ] = $this->__stash[ $var ];
            } else {
                $cached_stash[ $var ] = false;
            }
            if ( is_string( $var ) ) {
                $cached_vars[ $var ] = isset( $this->vars[ $var ] ) ? $this->vars[ $var ] : null;
            }
        }
        array_unshift( $this->cached_stash , $cached_stash );
        array_unshift( $this->cached_vars , $cached_vars );
        if ( empty( $vars ) ) {
            $this->restore_vars = $this->vars;
        }
    }

/**
 * Restore variables in block scope.
 *
 * @param array $vars: Array for localize and restore variables names.
 * @param array $stashes: Array for localize and restore stashes names.
 */
    function restore ( $vars = [] ) {
        $cached_stash = array_shift( $this->cached_stash );
        $cached_vars = array_shift( $this->cached_vars );
        foreach ( $vars as $var ) {
            if ( is_array( $var ) ) {
                foreach ( $var as $v )
                    $this->__stash['vars'][ $v ] = isset( $cached_stash[ $v ] )
                        ? $cached_stash['vars'][ $v ] : null;
            } else {
                if ( isset( $cached_stash[ $var ] ) ) {
                    if ( $cached_stash[ $var ] === false ) {
                        unset( $this->__stash[ $var ] );
                    } else {
                        $this->__stash[ $var ] = $cached_stash[ $var ];
                    }
                } else if ( isset( $cached_vars[ $var ] ) ) {
                    $this->vars[ $var ] = $cached_vars[ $var ];
                }
            }
        }
        if ( empty( $vars ) ) {
            $this->vars = $this->restore_vars;
        }
    }

/**
 * Skip block tags.
 */

    function false () {
        $this->local_vars['__total__'] = 0;
        $this->local_vars['__counter__'] = 1;
        return false;
    }

/**
 * Easily get the value of the variable.
 *
 * @param  string $name : Name of variable.
 * @param  string $var  : 'vars' or 'local_vars'.
 * @param  bool   $recursive : Retry get when keys_lower specified.
 * @return string $value: Value of variable.
 */
    function get_any ( $name, $var = null, $recursive = false ) {
        if ( is_array( $name ) ) return null;
        if ( preg_match( "/(.{1,})\[(.*?)]$/", $name, $mts ) ) {
            list( $name, $idx ) = [ trim( $mts[1] ), trim( $mts[2] ) ];
            if ( strpos( $name, '.' ) !== false ) {
                $name .= '.' . $idx;
            }
        }
        if ( strpos( $name, '.' ) !== false ) {
            if ( mb_substr_count( $name, '.' ) == 1 ) {
                list( $name, $idx ) = explode( '.', $name );
            } else {
                $names = explode( '.', $name );
                $v_name = array_shift( $names );
                $arr = ["['$v_name']"];
                foreach ( $names as $key ) {
                    $key = addslashes( $key );
                    $arr[] = "['$key']";
                }
                $arr = implode( $arr );
                if ( $var == 'local_vars' ) {
                    $code = "\$v = isset( \$this->local_vars{$arr} ) ? \$this->local_vars{$arr} : null;";
                } else {
                    $code = "\$v = isset( \$this->local_vars{$arr} ) ? \$this->local_vars{$arr}" .
                            " : ( isset( \$this->vars{$arr} ) ? \$this->vars{$arr} : null );";
                }
                eval( $code );
                if ( $v === null && $this->keys_lower && ! $recursive ) {
                    $name = strtolower( $name );
                    return $this->get_any( $name, $var, true );
                }
                return $v;
            }
        }
        if ( $var == 'local_vars' ) {
            $v = isset( $this->local_vars[ $name ] ) ? $this->local_vars[ $name ] : null;
            if ( $v === null && $this->keys_lower ) {
                $name = strtolower( $name );
                $v = isset( $this->local_vars[ $name ] ) ? $this->local_vars[ $name ] : null;
            }
        } else {
            $v = isset( $this->local_vars[ $name ] ) ? $this->local_vars[ $name ]
               : ( isset( $this->vars[ $name ] ) ? $this->vars[ $name ] : null );
            if ( $v === null && $this->keys_lower ) {
                $name = strtolower( $name );
                $v = isset( $this->local_vars[ $name ] ) ? $this->local_vars[ $name ]
                   : ( isset( $this->vars[ $name ] ) ? $this->vars[ $name ] : null );
            }
        }
        if ( isset( $idx ) && is_array( $v ) )
            return isset( $v[ $idx ] ) ? $v[ $idx ] : null;
        if ( isset( $v ) && is_array( $v ) && isset( $v['__eval__'] ) ) {
            $force_compile_old = $this->force_compile;
            $this->force_compile = true;
            if (! $this->in_build ) {
                $v = $this->build( $v['__eval__'] );
            } else {
                // Why It works
                $v = $this->build_page( null, [], '', false, $v['__eval__'] );
            }
            $this->force_compile = $force_compile_old;
        }
        return isset( $v ) ? $v : null;
    }

    function do_modifier ( $name, $out, $arg, $ctx ) {
        $func = $this->plugin_compat . $name;
        if (!function_exists( $func ) ) {
            if (!isset( $ctx->functions[ $name ] ) ) return $out;
            $func = $ctx->functions[ $name ][0];
        }
        $arg = $this->setup_args( $arg, $name, $ctx );
        if (!is_array( $arg ) ) $arg = $ctx->parse_csv( $arg );
        array_unshift( $arg, $out );
        return $func( ...$arg );
    }

/**
 * Core template tags.
 */
    function block_block ( $args, $content, $ctx, &$repeat, $counter ) {
        $name = isset( $args['name'] ) ? $args['name'] : null;
        if ( $name && ( $old_var = $ctx->get_any( $name ) ) ) {
            $old_var = $ctx->get_any( $name );
            if ( $old_var !== null ) {
                $repeat = $ctx->false();
                return $old_var;
            }
        }
        $modifier = $ctx->all_tags['modifier'];
        $modifier['assign'] = true;
        if (!$counter ) {
            $keys = [];
            foreach ( $args as $k => $v )
                if(!isset( $modifier[ $k ] ) ) $keys[] = $k;
            if (!empty( $keys ) ) {
                $ctx->localize( [ $keys, ['block_keys'] ] );
                $ctx->local_vars['block_keys'] = $keys;
                foreach ( $keys as $key ) $ctx->vars[ $key ] = $args[ $key ];
            }
        } else {
            if ( isset( $ctx->local_vars['block_keys'] ) )
                $ctx->restore( [ $ctx->local_vars['block_keys'], ['block_keys'] ] );
        }
        if ( $name && $counter && isset( $ctx->local_vars['__child_context__'] ) ) {
            $append = isset( $args['append'] ) ? $args['append'] : '';
            $prepend = isset( $args['prepend'] ) ? $args['prepend'] : '';
            if ( $append || $prepend ) {
                if (!isset( $ctx->block_vars[ $name ] ) ) $ctx->block_vars[ $name ] = [];
                if ( $append )
              {
                if (!isset( $ctx->block_vars[ $name ]['append'] ) )
                    $ctx->block_vars[ $name ]['append'] = [];
                array_unshift( $ctx->block_vars[ $name ]['append'], $content );
              } elseif ( $prepend ) {
                if (!isset( $ctx->block_vars[ $name ]['prepend'] ) )
                    $ctx->block_vars[ $name ]['prepend'] = [];
                $ctx->block_vars[ $name ]['prepend'][] = $content;
              }
            } else {
                $ctx->vars[ $name ] = $content;
            }
            return;
        }
        if ( $counter && $name && isset( $ctx->block_vars[ $name ] ) ) {
            if ( isset( $ctx->block_vars[ $name ]['append'] ) ) {
                $content .= join( '', $ctx->block_vars[ $name ]['append'] );
            }
            if ( isset( $ctx->block_vars[ $name ]['prepend'] ) ) {
                $content = join( '', $ctx->block_vars[ $name ]['prepend'] ) . $content;
            }
        }
        return $content;
    }

    function block_foreach ( $args, &$content, $ctx, &$repeat, $counter, $id ) {
        $name = array_slice( $args, 2, 1, true );
        $array = array_slice( $args, 0, 1, true );
        if ( isset( $args['as'] ) && !$args['as'] && count( $args ) == 4
            && key( array_slice( $args, 1, 1, true ) ) == 'as'
            && ! $name[ key( $name ) ] && ! $array[ key( $array ) ] ) {
        } else {
            return $this->block_loop( $args, $content, $ctx, $repeat, $counter, $id );
        }
        if (!$counter ) {
            $params = $ctx->get_any( key( $array ) );
            if (! $params || empty( $params ) ) {
                $repeat = $ctx->false();
                return;
            }
            $ctx->local_params = $params;
        }
        if (!isset( $params ) ) $params = $ctx->local_params;
        $ctx->set_loop_vars( $counter, $params );
        $vars = isset( $params[ $counter ] ) ? $params[ $counter ]
              : array_slice( $params, $counter, 1, true );
        if ( $vars ) {
            $repeat = true;
            $ctx->local_vars['__value__'] = $vars;
            if ( is_array( $vars ) && count( $vars ) == 1 ) {
                $ctx->local_vars['__key__'] = key( $vars );
                $vars = $vars[ key( $vars ) ];
            } else {
                $ctx->local_vars['__key__'] = $counter + 1;
            }
            $ctx->local_vars[ key( $name ) ] = $vars;
        } else if (!isset( $params[ $counter + 1] ) && count( $params ) <= $counter ) {
            $repeat = $ctx->false();
        }
        return ( $counter > 1 && isset( $args['glue'] ) )
            ? $args['glue'] . $content : $content;
    }

    function block_loop ( $args, &$content, $ctx, &$repeat, $counter, $id ) {
        if (!$counter ) {
            if (!isset( $args['name'] ) && !isset( $args['from'] ) ) {
                $repeat = $ctx->false();
                return;
            }
            $from = isset( $args['name'] ) ? $args['name'] : $args['from'];
            $params = is_array( $from ) ? $from : null;
            if (!$params ) $params = isset( $ctx->vars[ $from ] ) ? $ctx->vars[ $from ] : '';
            if (!$params ) $params = isset( $ctx->local_vars[ $from ] ) ?
                                            $ctx->local_vars[ $from ] : '';
            if (!$params ) {
                $repeat = $ctx->false();
                return;
            }
            if ( is_object( $params ) ) $params = (array) $params;
            if (!is_array( $params ) ) return;
            if ( count( $params ) === 1 & key( $params ) == '__array__' ) {
                $params = $params['__array__'];
            }
            if ( isset( $args['unique'] ) && $args['unique'] ) {
                $params = array_unique( $params );
            }
            if ( isset( $args['sort_by'] ) ) {
                $sort = $args['sort_by'];
                $sorts = ['key'];
                if ( is_array( $sort ) ) {
                    $sorts = $sort;
                } else if ( strpos( $sort, ' ' ) !== false && strpos( $sort, ',' ) === false ) {
                    $sorts = preg_split( "/\s+/", $sort, 2, PREG_SPLIT_NO_EMPTY );
                } else {
                    $sorts = preg_split( "/\s*,\s*/", $sort, 2, PREG_SPLIT_NO_EMPTY );
                }
                $sort = $sorts[0];
                if ( $sort == 'key' || $sort == 'value' ) {
                    $reverse = false;
                    $numeric = false;
                    if ( isset( $sorts[1] ) ) {
                        $opt = $sorts[1];
                        if ( stripos( $opt, 'reverse' ) !== false ) {
                            $reverse = true;
                        }
                        if ( stripos( $opt, 'numeric' ) !== false ) {
                            $numeric = true;
                        }
                    }
                    $sort_func = $reverse ? 'krsort' : 'ksort'; // key
                    if ( $sort == 'value' ) {
                        if ( isset( $args['hash'] ) ) {
                            $sort_func = $reverse ? 'arsort' : 'asort';
                        } else {
                            $sort_func = $reverse ? 'rsort' : 'sort';
                        }
                    }
                    if ( $numeric ) {
                        $sort_func( $params, SORT_NUMERIC );
                    } else {
                        $sort_func( $params );
                    }
                }
            }
            $item = ( isset( $args['item'] ) ) ? $args['item'] : '__value__';
            $key  = ( isset( $args['key'] ) ) ? $args['key'] : '__key__';
            if ( isset( $params[0] ) && !isset( $args['hash'] ) ) {
                if (!is_array( $params[0] ) ) {
                    $i = 0; foreach ( $params as $param )
                        $arr[] = array( $key => $i++, $item => $param );
                }
            } else {
                foreach( $params as $name => $param ) {
                    $arr[] = [ $key => $name, $item => $param ];
                }
            }
            if ( isset( $arr ) ) $params = $arr;
            if (! isset( $args['sort_by'] ) && isset( $args['shuffle'] ) ) {
                if ( $args['shuffle'] !== '0' ) {
                    shuffle( $params );
                }
            }
            if ( isset( $args['offset'] ) || isset( $args['limit'] ) ) {
                $offset = ( isset( $args['offset'] ) ) ? (int) $args['offset'] : 0;
                $limit = ( isset( $args['limit'] ) ) ? (int) $args['limit'] : 0;
                if ( count( $params ) < $offset ) {
                    $params = [];
                } else {
                    $params = array_slice( $params, $offset, $limit );
                }
            }
            $ctx->local_params = $params;
        }
        if (!isset( $params ) ) $params = $ctx->local_params;
        $ctx->set_loop_vars( $counter, $params );
        $vars = isset( $params[ $counter ] ) ? $params[ $counter ]
              : array_slice( $params, $counter, 1, true );
        if ( $vars ) {
            $repeat = true;
            if ( is_object( $vars ) ) $vars = (array) $vars;
            foreach ( $vars as $key => $value )
                $ctx->local_vars[ $key ] = $value;
        } else if (!isset( $params[ $counter + 1] ) && count( $params ) <= $counter ) {
            $repeat = $ctx->false();
        }
        return ( $counter > 1 && isset( $args['glue'] ) )
            ? $args['glue'] . $content : $content;
    }

    function block_for ( $args, $content, $ctx, &$repeat, $counter, $id ) {
        if (!$counter ) {
            $ctx->local_vars['loop_type'] = 1;
            $array = array_slice( $args, 2, 1, true );
            $name = array_slice( $args, 0, 1, true );
            if ( isset( $args['in'] ) && !$args['in'] && count( $args ) == 4
                && key( array_slice( $args, 1, 1, true ) ) == 'in'
                && ! $name[ key( $name ) ] && ! $array[ key( $array ) ] ) {
                $params = $ctx->get_any( key( $array ) );
                if (! $params || empty( $params ) ) {
                    $repeat = $ctx->false();
                    return;
                }
                $ctx->local_vars['var_name'] = key( $name );
                $ctx->local_vars['loop_type'] = 2;
                $ctx->local_params = $params;
            } else {
                if ( isset( $args['start'] ) ) $args['from'] = $args['start'];
                if ( isset( $args['end'] ) ) $args['to'] = $args['end'];
                if ( isset( $args['loop'] ) ) $args['to'] = $args['loop'];
                if ( isset( $args['step'] ) ) $args['increment'] = $args['step'];
                $from = isset( $args['from'] ) ? $args['from'] : 1;
                $to = isset( $args['to'] ) ? $args['to'] : 1;
                $increment = isset( $args['increment'] ) ? $args['increment'] : 1;
                $from = (int) $from;
                $to = (int) $to;
                if ( $from > $to ) {
                    $repeat = $ctx->false();
                    return;
                }
                $increment = (int) $increment;
                $range = $to - $from;
                if ( $range && ( $increment > $range ) ) $increment = $range;
                $params = range( $from, $to, $increment );
                $ctx->local_params = $params;
            }
        }
        if (!isset( $params ) ) $params = $ctx->local_params;
        $ctx->set_loop_vars( $counter, $params );
        $vars = isset( $params[ $counter ] ) ? $params[ $counter ]
              : array_slice( $params, $counter, 1, true );
        if ( $vars ) {
            $repeat = true;
            if ( isset( $ctx->local_vars['loop_type'] ) && $ctx->local_vars['loop_type'] == 2 ) {
                $ctx->local_vars['__value__'] = $vars;
                if ( is_array( $vars ) && count( $vars ) == 1 ) {
                    $ctx->local_vars['__key__'] = key( $vars );
                    $vars = $vars[ key( $vars ) ];
                } else {
                    $ctx->local_vars['__key__'] = $counter + 1;
                }
                $ctx->local_vars[ $ctx->local_vars['var_name'] ] = $vars;
            } else {
                $var = isset( $args['var'] ) ? $args['var'] : '__value__';
                $ctx->local_vars[ $var ] = $params[ $counter ];
            }
        } else {
            $repeat = $ctx->false();
        }
        return ( $counter > 1 && isset( $args['glue'] ) )
            ? $args['glue'] . $content : $content;
    }

    function block_setvarblock ( $args, &$content, $ctx, &$repeat, $counter ) {
        $name = $args['this_tag'] === 'setvarblock' ? 'name' : 'var';
        if ( isset( $args[ $name ] ) && $args[ $name ] ) {
            $var = 'vars';
            if ( isset( $args['scope'] ) && $args['scope'] === 'local' ) {
                $var = 'local_vars';
            }
            if ( is_array( $args[ $name ] ) && isset( $args["<{$name}>"] ) ) {
                $name = "<{$name}>";
            }
            if ( is_string( $args[ $name ] ) && strpos( $args[ $name ], '.' ) !== false ) {
                $names = explode( '.', $args[ $name ] );
                $v_name = array_shift( $names );
                $arr = ["['$v_name']"];
                $last = count( $names ) - 1;
                foreach ( $names as $idx => $key ) {
                    $key = addslashes( $key );
                    if ( strpos( $key, '[]' ) !== false && $idx === $last ) {
                        if ( preg_match( '/(^.{1,})\[\]$/', $key, $mts ) ) {
                            $key = $mts[1];
                            $arr[] = "['$key'][]";
                            break;
                        }
                    }
                    $arr[] = "['$key']";
                }
                $arr = implode( $arr );
                if ( isset( $args['op'] ) ) {
                    $value = $this->get_any( $name, $var );
                    if ( $value !== null ) {
                        $content = static::math_operation( $args['op'], $value, $content, $args );
                    }
                }
                $code = " \$ctx->{$var}{$arr} = \$ctx->append_prepend( \$content, \$args, \$name, \$var );";
                eval( $code );
            } else if ( is_string( $args[ $name ] ) && preg_match( '/(^.{1,})\[\]$/', $args[ $name ], $mts ) ) {
                $name = $mts[1];
                if ( isset( $args['op'] ) ) {
                    $value = $this->get_any( $name, $var );
                    if ( $value !== null ) {
                        $content = static::math_operation( $args['op'], $value, $content, $args );
                    }
                }
                $ctx->$var[ $name ][] = $ctx->append_prepend( $content, $args, $name, $var );
            } else {
                if (! $ctx->sethashvar_compat ) {
                    $hash = $ctx->stash( '__inside_sethashvar' );
                    if ( $hash !== null ) {
                        $args['key'] = $args[ $name ];
                        $args[ $name ] = $hash;
                    }
                }
                if ( isset( $args['op'] ) ) {
                    $value = $this->get_any( $args[ $name ], $var );
                    if ( $value !== null ) {
                        $content = static::math_operation( $args['op'], $value, $content, $args );
                    }
                }
                $ctx->$var[ $args[ $name ] ] = $ctx->append_prepend( $content, $args, $name, $var );
            }
        }
        return '';
    }

    function block_setvars ( $args, $content, $ctx, &$repeat, $counter ) {
        $name = ! isset( $args['name'] ) ? '' : $args['name'];
        if ( $name && strpos( $name, '.' ) ) {
            $names = explode( '.', $name );
            $v_name = array_shift( $names );
            $arr = ["['$v_name']"];
            foreach ( $names as $key ) {
                $key = addslashes( $key );
                $arr[] = "['$key']";
            }
            $arr = implode( $arr );
        }
        $var = 'vars';
        if ( isset( $args['scope'] ) && $args['scope'] === 'local' ) {
            $var = 'local_vars';
        }
        $lines = array_map( 'ltrim', preg_split( "/\r?\n/", trim( $content ) ) );
        foreach ( $lines as $line ) {
            if ( strpos( $line, '=' ) === false ) continue;
            list( $k, $v ) = preg_split( '/\s*=/', $line, 2 );
            if ( isset( $k ) ) {
                if ( $name ) {
                    if ( isset( $arr ) ) {
                        $code = "\$ctx->{$var}{$arr}[ \$k ] = \$v;";
                        eval( $code );
                    } else {
                        $ctx->$var[ $name ][ $k ] = $v;
                    }
                } else {
                    $ctx->$var[ $k ] = $v;
                }
            }
        }
        return '';
    }

    function block_sethashvars ( $args, &$content, $ctx, &$repeat, $counter ) {
        if ( isset( $args['name'] ) ) $name = $args['name'];
        if ( $name ) {
            if ( strpos( $name, '.' ) ) {
                $names = explode( '.', $name );
                $v_name = array_shift( $names );
                $arr = ["['$v_name']"];
                foreach ( $names as $key ) {
                    $key = addslashes( $key );
                    $arr[] = "['$key']";
                }
                $arr = implode( $arr );
            }
            $var = 'vars';
            if ( isset( $args['scope'] ) && $args['scope'] === 'local' ) {
                $var = 'local_vars';
            }
            $lines = array_map( 'ltrim', preg_split( "/\r?\n/", trim( $content ) ) );
            foreach ( $lines as $line ) {
                if ( strpos( $line, '=' ) === false ) continue;
                list( $k, $v ) = preg_split( '/\s*=/', $line, 2 );
                if ( isset( $arr ) ) {
                    $code = "\$ctx->{$var}{$arr}[ \$k ] = \$v;";
                    eval( $code );
                } else {
                    $ctx->$var[ $name ][ $k ] = $v;
                }
            }
        }
        return '';
    }

    function block_sethashvar ( $args, &$content, $ctx, &$repeat, $counter ) {
        if ( isset( $args['name'] ) ) $name = $args['name'];
        if (! $name ) return '';
        $ctx->stash( '__inside_sethashvar', $name );
        if ( isset( $content ) ) {
            $ctx->stash( '__inside_sethashvar', null );
            $repeat = $ctx->false();
        }
        return $content;
    }

    function block_literal ( $args, &$content, $ctx, &$repeat, $counter ) {
        if (!$counter ) return;
        if ( isset( $args['nocache'] ) ) return $content;
        $var = isset( $ctx->literal_vars[ $args['index'] ] )
             ? $ctx->literal_vars[ $args['index'] ] : '';
        return $var;
    }

    function block_queries ( $args, &$content, $ctx, &$repeat, $counter ) {
        if (!$counter ) {
            $excludes = isset( $args['excludes'] ) ? $args['excludes'] : [];
            $params = $_GET;
            if ( is_string( $excludes ) ) {
                unset( $params[ $excludes ] );
            } else if (! empty( $excludes ) ) {
                foreach ( $excludes as $exclude ) {
                    unset( $params[ $exclude ] );
                }
            }
            unset( $params['request_id'] ); // for Prototype
            $queries = [];
            foreach ( $params as $key => $value ) {
                $queries[] = [ $key, $value ];
            }
            $params = $queries;
            $ctx->local_params = $queries;
        }
        if (!isset( $params ) ) $params = $ctx->local_params;
        $ctx->set_loop_vars( $counter, $params );
        if ( isset( $params[ $counter ] ) ) {
            $repeat = true;
            $key = isset( $args['key'] ) ? $args['key'] : '__key__';
            $var = isset( $args['var'] ) ? $args['var'] : '__value__';
            list( $k, $v ) = $params[ $counter ];
            $ctx->local_vars[ $key ] = $k;
            $ctx->local_vars[ $var ] = $v;
        } else {
            $repeat = $ctx->false();
        }
        return ( $counter > 1 && isset( $args['glue'] ) )
            ? $args['glue'] . $content : $content;
    }

    function conditional_if ( $args, $content, $ctx, $repeat, $context = true ) {
        $vars = array_merge( $ctx->vars, $ctx->local_vars );
        list( $true, $false ) = $context ? [ true, false ] : [ false, true ];
        $v = null;
        if ( ( isset( $args['test'] ) ) && ( $test = $args['test'] ) ) {
            if ( preg_match_all( "/\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/",
                $test, $mts ) ) {
                $mts = $mts[0];
                foreach ( $mts as $val ) {
                    $variable = preg_replace( '/^\$/', '', $val );
                    $variable = ( isset( $vars[ $variable ] ) ) ? $vars[ $variable ] : '';
                    $test = str_replace( $val, "'{$variable}'", $test );
                }
            }
            $test = str_replace( '"', '', str_replace( '\\', '', $test ) );
            $alloweds = ['int','abs','ceil','cos','exp','floor','log','sin','log10','pi','max'
          ,'min','pow','rand','round','sqrt','srand','tan','strlen','mb_strlen',')','(','=='
          ,'===','!=','!==','<','>','>=','<=','and','or','true','false','{','}','&&','||', '%', '+', '-', '*'];
            foreach ( $alloweds as $allowed ) {
                $result = preg_match( '/[a-z]/', $allowed ) ? ' $1 ' : '$1';
                $allowed = preg_quote( $allowed, '/' );
                $test = preg_replace( "/($allowed)/", $result, $test );
            }
            $test = str_replace( '&gt;', '>', $test );
            $test = str_replace( '&lt;', '<', $test );
            $funcs = strtolower( preg_replace( "/'.*?'/", ' ', $test ) );
            $funcs = preg_replace( '/[0-9]{1,}/', '', $funcs );
            $funcs = preg_split( '/\s{1,}/', $funcs );
            foreach ( $funcs as $func ) {
                if ( $func and ! in_array( $func, $alloweds ) ) {
                    trigger_error( "error in expression '{$test}'" );
                    return $false;
                }
            }
            unset( $args['test'], $args['this_tag'] );
            if (! empty( $args ) ) {
                $test = "return {$test};";
                $result = eval( $test );
                if( $result === false ) trigger_error( "error in expression '{$test}'" );
            } else {
                $test = "return {$test} ? 1 : 0;";
                $result = eval( $test );
                if( $result === false ) trigger_error( "error in expression '{$test}'" );
                return ( $result ) ? $true : $false;
            }
            $v = $result;
        } else {
            $encl = preg_quote( $ctx->csv_enclosure );
            $delim = preg_quote( $ctx->csv_delimiter );
            if ( ( isset( $args['tag'] ) ) && ( $tag = $args['tag'] ) ) {
                $tag = strtolower( $tag );
                $functions = $ctx->all_tags['function'];
                if ( isset( $functions[ $tag ] ) ) {
                    list( $obj, $meth ) = [ null, null ];
                    if ( isset( $ctx->func_map['function_' . $tag ] ) ) {
                        list( $obj, $meth ) = $ctx->func_map['function_' . $tag ];
                    }
                    if ( is_object( $obj ) && method_exists( $obj, $meth ) ) {
                        $tag_args = $args;
                        unset( $tag_args['eq'], $tag_args['not'], $tag_args['ne'],
                               $tag_args['gt'], $tag_args['lt'], $tag_args['ge'],
                               $tag_args['le'], $tag_args['like'], $tag_args['match'],
                               $tag_args['tag'] );
                        foreach ( $tag_args as $key => $arg ) {
                            if ( $key !== 'this_tag' ) {
                                unset( $args[ $key ] );
                            }
                        }
                        $tag_args['this_tag'] = $tag;
                        $v = $obj->$meth( $tag_args, $ctx );
                    } else {
                        $tag = $ctx->prefix . $tag;
                        $v = $ctx->build( "<{$tag}>" );
                    }
                } else {
                    $tag = $ctx->prefix . $tag;
                    $v = $ctx->build( "<{$tag}>" );
                }
                unset( $args['tag'] );
            } else if ( isset( $args['value'] ) ) {
                if ( isset( $args['op'] ) ) {
                    $v = isset( $vars[ $args['name'] ] )
                       ? $vars[ $args['name'] ] : $this->function_var( $args, $ctx );
                    $v = static::math_operation( $args['op'], $v, $args['value'] );
                } else {
                    $v = $args['value'];
                    unset( $args['value'] );
                }
            } else if ( ( count( $args ) == 4 || count( $args ) == 5 )
                && isset( $args['is'] ) && isset( $args['defined'] )
                && !$args['is'] && !$args['defined'] ) {
                $name = array_slice( $args, 0, 1, true );
                $v = $this->get_any( key( $name ) );
                $match = false;
                if ( is_string( $v ) && $v ) {
                    $match = isset( $args['not'] ) ? false : true;
                } else if ( is_array( $v ) && !empty( $v ) ) {
                    $match = isset( $args['not'] ) ? false : true;
                }
                return $match ? $true : $false;
            } else if (! isset( $args['name'] ) && count( $args ) == 2 ) {
                $name = array_slice( $args, 0, 1, true );
                $comp = $name[ key( $name ) ];
                $v = $this->get_any( key( $name ) );
                $match = false;
                if ( is_string( $v ) && $v ) {
                    $match = $comp ? $v == $comp : true;
                } else if ( is_array( $v ) && !empty( $v ) ) {
                    $match = true;
                }
                return $match ? $true : $false;
            } else {
                if (!isset( $args['name'] ) ) return $false;
                if ( is_array( $args['name'] ) ) {
                    $key = key( $args['name'] );
                    $value = $args['name'][ $key ];
                    $v = isset( $vars[ $key ][ $value ] ) ? $vars[ $key ][ $value ] : '';
                    if (! $v && $key === '__array__' && is_array( $value ) && !empty( $value ) ) {
                        if ( isset( $args['<name>'] ) ) {
                            $name = $args['<name>'];
                            if ( strpos( $name, '.' ) !== false ) {
                                $names = explode( '.', $name );
                                $v = array_shift( $names );
                                $v = isset( $vars[ $v ] ) ? $vars[ $v ] : null;
                                if ( $v === null ) return $false;
                                foreach ( $names as $chain ) {
                                    $v = is_array( $v ) && isset( $v[ $chain ] ) ? $v[ $chain ] : null;
                                    if ( $v === null ) return $false;
                                }
                                return $v ? $true : $false;
                            } else {
                                $v = $this->get_any( $name );
                                return $v ? $true : $false;
                            }
                        }
                        return $true;
                    }
                } elseif ( strpos( $args['name'], 'request.' ) === 0 ) {
                    $v = $ctx->request_var( $args['name'], $args );
                } elseif ( strpos( $args['name'], 'cookie.' ) === 0 ) {
                    $cookie = preg_replace( '/^cookie\./', '', $args['name'] );
                    $v = isset( $_COOKIE[ $cookie ] ) ? $_COOKIE[ $cookie ] : '';
                } else {
                    $v = isset( $vars[ $args['name'] ] )
                       ? $vars[ $args['name'] ] : $this->function_var( $args, $ctx );
                }
                if ( isset( $args['op'] ) ) {
                    $v = static::math_operation( $args['op'], $v, null );
                }
            }
            unset( $args['name'], $args['this_tag'] );
        }
        if ( ( isset( $v ) && $v ) || !empty( $args ) ) {
            $v = !isset( $v ) ? '' : $v;
            if ( empty( $args ) ) return $true;
            elseif (is_array( $v ) && !empty( $v ) ) return $true;
            elseif( isset( $args['eq'] ) ) return $v ==$args['eq'] ? $true : $false;
            elseif( isset( $args['not']) ) return $v !=$args['not']? $true : $false;
            elseif( isset( $args['ne'] ) ) return $v !=$args['ne'] ? $true : $false;
            elseif( isset( $args['gt'] ) ) return $v > $args['gt'] ? $true : $false;
            elseif( isset( $args['lt'] ) ) return $v < $args['lt'] ? $true : $false;
            elseif( isset( $args['ge'] ) ) return $v >=$args['ge'] ? $true : $false;
            elseif( isset( $args['le'] ) ) return $v <=$args['le'] ? $true : $false;
            elseif( isset( $args['like'] ) ) {
                if ( !is_string( $args['like'] ) || $args['like'] === '' ) return $false;
                return strpos( $v, $args['like'] ) !== false ? $true : $false;
            } elseif( isset( $args['match'])) {
                $pattern = $args['match'];
                if ( strpos( $pattern, '/' ) !== 0 ) {
                    $pattern = "/{$pattern}/";
                }
                return preg_match( $pattern, $v ) ? $true : $false;
            }
            return $true;
        }
        return $false;
    }

    function conditional_isset ( $args, $content, $ctx, $repeat ) {
        if (!isset( $args['name'] ) ) return false;
        $name = $args['name'];
        $vars = array_merge( $ctx->vars, $ctx->local_vars );
        if ( strpos( $name, '[' ) !== false && strpos( $name, ']' ) !== false ) {
            if ( preg_match( '/(^.*)\[(.*?)\]$/', $name, $matchs ) ) {
                return isset( $vars[ $matchs[1] ][ $matchs[2] ] );
            }
        }
        return isset( $vars[ $name ] );
    }

    function conditional_unless ( $args, $content, $ctx, $repeat ) {
        return $ctx->conditional_if( $args, $content, $ctx, $repeat, false );
    }

    function _hdlr_if ( $args, $content, $ctx, $repeat, $context ) {
        return $ctx->conditional_if( $args, $content, $ctx, $repeat, $context );
    }

    function conditional_elseif ( $args, $content, $ctx, $repeat, $context ) {
        return $ctx->conditional_if( $args, $content, $ctx, $repeat, $context );
    }

    function conditional_ifgetvar ( $args, $content ) {
        if (!isset( $args['name'] ) ) return false;
        return isset( $this->local_vars[ $args['name'] ] ) &&
            $this->local_vars[ $args['name'] ] ? true :
          ( isset( $this->vars[ $args['name'] ] ) &&
            $this->vars[ $args['name'] ] ? true : false );
    }

    function conditional_ifinarray ( $args, $content, $ctx, $repeat, $counter ) {
        $value = $args['value'];
        $array = isset( $args['array'] ) ? $args['array'] : $args['name'];
        $name = $array;
        if ( is_string( $array ) ) $array = $ctx->get_any( $array );
        if ( $array === false ) {
            $array = isset( $ctx->vars[ $name ] ) ? $ctx->vars[ $name ] : null;
        }
        if (! is_array( $array ) ) return false;
        return in_array( $value, $array ) ? true : false;
    }

    function conditional_isarray ( $args, $content, $ctx, $repeat, $counter ) {
        $array = isset( $args['array'] ) ? $args['array'] : $args['name'];
        if ( is_string( $array ) ) $array = $ctx->get_any( $array );
        return is_array( $array ) ? true : false;
    }

    function function_var ( $args, $ctx ) {
        if (! isset( $args['name'] ) ) {
            return $this->function_let( $args, $ctx );
        }
        if ( isset( $args['setuped_var'] ) && $args['setuped_var'] )
            return $args['setuped_var'];
        if ( isset( $args['value'] ) ) {
            $args['this_tag'] = 'setvar';
            return $ctx->function_setvar( $args, $ctx );
        }
        $name = $args['name'];
        if ( is_array( $name )&& isset( $name['__array__'] ) ) {
            return $name['__array__'];
        } else if ( is_string( $name ) && strpos( $name, 'request.' ) === 0 ) {
            return $ctx->request_var( $args['name'], $args );
        } else if ( is_string( $name ) && strpos( $name, 'cookie.' ) === 0 ) {
            $name = preg_replace( '/^cookie\./', '', $name );
            return isset( $_COOKIE[ $name ] ) ? $_COOKIE[ $name ] : '';
        }
        if ( isset( $args['key'] ) ) $args['index'] = $args['key'];
        if ( isset( $args['index'] ) ) {
            if ( is_array( $name ) && isset( $name[ $args['index'] ] ) )
                return $name[ $args['index'] ];
            if ( is_string( $name ) ) $name .= '[' . $args['index'] . ']';
        }
        $var = $ctx->get_any( $name );
        if ( isset( $args['callback'] ) ) {
            $component = isset( $args['component'] ) 
                       ? $ctx->component( $args['component'] )
                       : $ctx->default_component;
            if ( $component ) {
                $callback = $args['callback'];
                if ( method_exists( $component, $callback ) ) {
                    $component->$callback( $var, $args, $ctx );
                }
            }
        }
        return $var;
    }

    function function_gethashvar ( $args, &$ctx ) {
        if ( isset( $args['name'] ) ) $name = $args['name'];
        if ( isset( $args['index'] ) ) $args['key'] = $args['index'];
        if ( isset( $args['key'] ) ) $key = $args['key'];
        if ( is_array( $name ) ) return $ctx->function_var( $args, $ctx );
        if ( (! $name ) || (! isset( $key )
                        || $key === '' ) ) return '';
        $hash = $ctx->vars[ $name ] ?? null;
        if (! $hash && $this->keys_lower ) {
            $hash = $ctx->vars[ strtolower( $name ) ] ?? null;
        }
        if (! $hash ) {
            return '';
        }
        if ( is_array( $hash ) ) {
            if ( is_array( $key ) ) {
                $keys = $key;
                $value = $hash;
                foreach( $keys as $key ) {
                    if ( strpos( $key, 'Array.' ) === 0 ) {
                        $key = str_replace( 'Array.', '', $key );
                        $key = $ctx->vars[ $key ];
                    }
                    if ( isset( $value[ $key ] ) ) {
                        $value = $value[ $key ];
                    } else if ( $this->keys_lower && isset( $value[ strtolower( $key ) ] ) ) {
                        $value = $value[ strtolower( $key ) ];
                    }
                }
                return $value;
            }
            if ( isset( $hash[ $key ] ) ) {
                return $hash[ $key ];
            } else if ( $this->keys_lower && isset( $hash[ strtolower( $key ) ] ) ) {
                return $hash[ strtolower( $key ) ];
            }
        }
        return '';
    }

    function function_include ( $args, $ctx ) {
        $f = isset( $args['file'] ) ? $args['file'] : '';
        if (! $f ) return '';
        if (!$f = $ctx->get_template_path( $f, true ) ) return '';
        if (!$incl = file_get_contents( $f ) ) return '';
        return $ctx->build( $incl );
    }

    function function_vardump ( $args, $ctx ) {
        $filter = false;
        $vars = null;
        if ( isset( $args['key'] ) && $args['key'] && ! isset( $args['name'] ) ) {
            $args['name'] = $args['key'];
            unset( $args['key'] );
        }
        if ( isset( $args['name'] ) && $args['name'] ) {
            if ( $this->keys_lower ) {
                $args['name'] = strtolower( $args['name'] );
            }
            if ( is_array( $args['name'] ) && isset( $args['name']['__array__'] ) ) {
                $vars = $args['name']['__array__'];
            } else {
                $vars = $this->get_any( $args['name'] );
            }
            if ( is_array( $vars ) && isset( $args['key'] ) && $args['key'] ) {
                $vars = isset( $vars[ $args['key'] ] ) ? $vars[ $args['key'] ] : null;
            }
            $filter = true;
        }
        if (! $filter && $args !== null ) {
            $vars = ['vars' => $ctx->vars, 'local_vars' => $ctx->local_vars ];
        }
        ob_start();
        var_dump( $vars );
        $result = ob_get_clean();
        if (isset( $args['preformat'] ) ) {
            $result = htmlspecialchars( $result );
            $result = "<pre>{$result}</pre>";
        }
        return $result;
    }

    function function_count ( $args, $ctx ) {
        $name = isset( $args['name'] ) ? $args['name'] : '';
        if (!$name ) return 0;
        if ( is_array( $name ) ) return count( $name );
        $v = $ctx->get_any( $name )
           ? $ctx->get_any( $name )
           : $this->function_var( ['name' => $name ], $ctx );
        return ( $v ) ? count( $v ) : 0;
    }

    function function_arrayshuffle ( $args, $ctx ) {
        $name = isset( $args['name'] ) ? $args['name'] : '';
        if (!$name ) $name = isset( $args['array'] ) ? $args['array'] : '';
        if (!$name ) return;
        $v = $ctx->get_any( $name )
           ? $ctx->get_any( $name )
           : $this->function_var( ['name' => $name ], $ctx );
        if (!is_array( $v ) ) return;
        shuffle( $v );
        if ( isset( $ctx->local_vars[ $name ] ) ) {
            $ctx->local_vars[ $name ] = $v;
        } else if ( isset( $ctx->vars[ $name ] ) ) {
            $ctx->vars[ $name ] = $v;
        }
    }

    function function_arrayslice ( $args, $ctx ) {
        $name = isset( $args['name'] ) ? $args['name'] : '';
        if (!$name ) $name = isset( $args['array'] ) ? $args['array'] : '';
        if (!$name ) return;
        $v = $ctx->get_any( $name )
           ? $ctx->get_any( $name )
           : $this->function_var( ['name' => $name ], $ctx );
        if (!is_array( $v ) ) return;
        $offset = isset( $args['offset'] ) ? (int)$args['offset'] : 0;
        $length = isset( $args['length'] ) ? (int)$args['length'] : null;
        if (! $length && isset( $args['limit'] ) ) {
            $length = (int) $args['limit'];
        }
        if ( count( $v ) < $offset ) {
            $v = [];
        } else {
            $v = array_slice( $v, $offset, $length );
        }
        if ( isset( $ctx->local_vars[ $name ] ) ) {
            $ctx->local_vars[ $name ] = $v;
        } else if ( isset( $ctx->vars[ $name ] ) ) {
            $ctx->vars[ $name ] = $v;
        }
    }

    function function_arrayrand ( $args, $ctx ) {
        $name = isset( $args['name'] ) ? $args['name'] : '';
        if (!$name ) $name = isset( $args['array'] ) ? $args['array'] : '';
        if (!$name ) return;
        $v = $ctx->get_any( $name )
           ? $ctx->get_any( $name )
           : $this->function_var( ['name' => $name ], $ctx );
        if (!is_array( $v ) ) return;
        $num = isset( $args['num'] ) ? (int)$args['num'] : 1;
        if (! isset( $args['num'] ) && isset( $args['limit'] ) ) {
            $num = (int) $args['limit'];
        } else if (! isset( $args['num'] ) && isset( $args['length'] ) ) {
            $num = (int) $args['length'];
        }
        if ( count( $v ) < $num ) {
            // E_WARNING
        } else {
            $keys = array_rand( $v, $num );
            if ( is_array( $keys ) ) {
                $arr = [];
                foreach ( $keys as $key ) {
                    $arr[ $key ] = $v[ $key ];
                }
                $v = $arr;
            } else {
                $v = [ $keys => $v[ $keys ] ];
            }
        }
        if ( isset( $ctx->local_vars[ $name ] ) ) {
            $ctx->local_vars[ $name ] = $v;
        } else if ( isset( $ctx->vars[ $name ] ) ) {
            $ctx->vars[ $name ] = $v;
        }
    }

    function function_unset ( $args, $ctx ) {
        $name = isset( $args['name'] ) ? $args['name'] : '';
        if ( is_array( $name ) && isset( $args['<name>'] ) ) {
            $name = $args['<name>'];
            if ( strpos( $name, '.' ) !== false ) {
                $names = explode( '.', $name );
                $v_name = array_shift( $names );
                $var = $this->get_any( $v_name );
                if (! $var || !is_array( $var ) ) {
                    return '';
                }
                $arr = ["['$v_name']"];
                foreach ( $names as $key ) {
                    $var = isset( $var[ $key ] ) ? $var[ $key ] : null;
                    if ( $var === null ) {
                        return;
                    }
                    $key = addslashes( $key );
                    $arr[] = "['$key']";
                }
                $arr = implode( $arr );
                if (! isset( $args['scope'] ) ) {
                    $code = "unset( \$ctx->vars{$arr}, \$ctx->local_vars{$arr} );";
                } else if ( $args['scope'] == 'local' ) {
                    $code = "unset( \$ctx->local_vars{$arr} );";
                } else {
                    $code = "unset( \$ctx->vars{$arr} );";
                }
                eval( $code );
            }
            return '';
        }
        if (!$name ) return '';
        if ( strpos( $name, '[' ) !== false && strpos( $name, ']' ) !== false ) {
            if ( preg_match( '/(^.*)\[(.*?)\]$/', $name, $matchs ) ) {
                if (! isset( $args['scope'] ) ) {
                    unset( $ctx->vars[ $matchs[1] ][ $matchs[2] ], $ctx->local_vars[ $matchs[1] ][ $matchs[2] ] );
                } else if ( $args['scope'] == 'local' ) {
                    unset( $ctx->local_vars[ $matchs[1] ][ $matchs[2] ] );
                } else {
                    unset( $ctx->vars[ $matchs[1] ][ $matchs[2] ] );
                }
                return '';
            }
        }
        if (! isset( $args['scope'] ) ) {
            unset( $ctx->vars[ $name ], $ctx->local_vars[ $name ] );
        } else if ( $args['scope'] == 'local' ) {
            unset( $ctx->local_vars[ $name ] );
        } else {
            unset( $ctx->vars[ $name ] );
        }
        return '';
    }

    function function_break ( $args, $ctx ) {
        $this->call_break = true;
        if ( isset( $args['close'] ) && $args['close'] &&
           ( isset( $ctx->local_vars['__last__'] ) && !$ctx->local_vars['__last__'] ) ) {
            return '</' . $args['close'] . '>';
        }
        return '';
    }

    function function_triggererror ( $args, $ctx ) {
        $message = isset( $args['message'] ) ? $args['message'] : 'function_triggererror called.';
        trigger_error( $message );
    }

    function function_constant ( $args, $ctx ) {
        return isset( $args['name'] ) ? constant( $args['name'] ) : '';
    }

    function function_date ( $args, $ctx ) {
        $t = ( isset( $args['ts'] ) && $args['ts'] ) ? strtotime( $args['ts'] ) : time();
        if ( isset( $args['unixtime'] ) && $args['unixtime'] ) $t = (int) $args['unixtime'];
        $format = isset( $args['format'] ) ? $args['format'] : '';
        if (! $format ) {
            if ( isset( $args['format_name'] ) && $args['format_name'] ) {
                $format = $args['format_name'];
            } else {
                $format = 'Y-m-d H:i:s';
            }
        }
        $date = date( 'YmdHis', $t );
        return $this->modifier_format_ts( $date, $format, $ctx, $args );
    }

    function function_ml ( $args, $ctx ) {
        $tag = isset( $args['tag'] ) ? $args['tag'] : '';
        if (! $tag ) return '';
        $params = isset( $args['params'] ) ? $args['params'] : '';
        $prefix = $ctx->prefix;
        $close = '';
        if ( strpos( $tag, '/' ) === 0 ) {
            $close = '/';
            $tag = ltrim( $tag, '/' );
        }
        if ( stripos( $tag, $prefix ) !== 0 ) {
            $tag = "{$prefix}:{$tag}";
        }
        if ( $params ) {
            if ( strpos( $params, '\\' ) !== false ) {
                $params = str_replace( '\\', '', $params );
            }
            $tag .= " {$params}";
        }
        return "<{$close}{$tag}>";
    }

    function function_query ( $args, $ctx ) {
        $excludes = isset( $args['excludes'] ) ? $args['excludes'] : [];
        $params = $_GET;
        if ( is_string( $excludes ) ) {
            unset( $params[ $excludes ] );
        } else if (! empty( $excludes ) ) {
            foreach ( $excludes as $exclude ) {
                unset( $params[ $exclude ] );
            }
        }
        $query = http_build_query( $params );
        if ( isset( $args['values'] ) ) {
            $query = preg_replace( '/%5B.*?%5D/', '%5B%5D', $query );
        }
        return $query;
    }

    function function_setvar ( $args, $ctx ) {
        $nm = $args['this_tag'] === 'setvar' ? 'name' : 'var';
        $var = 'vars';
        if ( isset( $args['scope'] ) && $args['scope'] === 'local' ) {
            $var = 'local_vars';
        }
        if ( is_array( $args[ $nm ] ) && isset( $args["<{$nm}>"] ) ) {
            $nm = "<{$nm}>";
        }
        if ( isset( $args[ $nm ] ) && $args[ $nm ] ) {
            $args['value'] = isset( $args['value'] ) ? $args['value'] : null;
            $hash = $ctx->stash( '__inside_sethashvar' );
            $name = $args[ $nm ];
            if ( $hash !== null ) {
                $args['key'] = $name;
                $args[ $nm ] = $hash;
                $name = $args[ $nm ];
            }
            if ( strpos( $name, '.' ) !== false ) {
                $names = explode( '.', $name );
                $v_name = array_shift( $names );
                $arr = ["['$v_name']"];
                $last = count( $names ) - 1;
                foreach ( $names as $idx => $key ) {
                    $key = addslashes( $key );
                    if ( strpos( $key, '[]' ) !== false && $idx === $last ) {
                        if ( preg_match( '/(^.{1,})\[\]$/', $key, $mts ) ) {
                            $key = $mts[1];
                            $arr[] = "['$key'][]";
                            break;
                        }
                    }
                    $arr[] = "['$key']";
                }
                $arr = implode( $arr );
                if ( isset( $args['op'] ) ) {
                    $value = $this->get_any( $name, $var );
                    if ( $value !== null ) {
                        $args['value'] = static::math_operation( $args['op'], $value, $args['value'], $args );
                    }
                }
                $code = "\$ctx->{$var}{$arr} = \$ctx->append_prepend( \$args['value'], \$args, \$nm );";
                eval( $code );
            } else if ( preg_match( '/(^.{1,})\[\]$/', $name, $mts ) ) {
                $name = $mts[1];
                if ( isset( $args['op'] ) ) {
                    $value = $this->get_any( $name, $var );
                    if ( $value !== null ) {
                        $args['value'] = static::math_operation( $args['op'], $value, $args['value'], $args );
                    }
                }
                $ctx->$var[ $name ][] = $ctx->append_prepend( $args['value'], $args, $nm );
            } else if ( isset( $args['value'] ) ) {
                if ( isset( $args['op'] ) && isset( $ctx->$var[ $args[ $nm ] ] ) ) {
                    $args['value'] = static::math_operation( $args['op'], $ctx->$var[ $args[ $nm ] ], $args['value'], $args );
                }
                $ctx->$var[ $args[ $nm ] ] = $ctx->append_prepend( $args['value'], $args, $nm );
            } else if ( isset( $args['op'] ) && isset( $ctx->$var[ $args[ $nm ] ] ) ) {
                $value = static::math_operation( $args['op'], $ctx->$var[ $args[ $nm ] ], null, $args );
                $ctx->$var[ $args[ $nm ] ] = $ctx->append_prepend( $value, $args, $nm );
            }
        }
    }

    function function_unsetvar ( $args, $ctx ) {
        if ( isset( $args['name'] ) && $args['name'] ) {
            if (! isset( $args['scope'] ) ) {
                unset( $ctx->vars[ $args['name'] ], $ctx->local_vars[ $args['name'] ] );
            } else if ( $args['scope'] == 'local' ) {
                unset( $ctx->local_vars[ $args['name'] ] );
            } else {
                unset( $ctx->vars[ $args['name'] ] );
            }
        }
    }

    function function_assign ( $args, $ctx ) {
        return $ctx->function_setvar( $args, $ctx );
    }

    function function_math ( $args, $ctx ) {
        static $allowed_funcs =
            ['int', 'abs', 'ceil', 'cos', 'exp', 'floor', 'log', 'log10', 'max', 'min',
             'pi', 'pow', 'rand', 'round', 'sin', 'sqrt', 'srand', 'tan', '%'];
        $eq = isset( $args['eq'] ) ? $args['eq'] : '';
        if (! $eq ) return;
        if ( substr_count( $eq, '(' ) != substr_count( $eq, ')' ) ) {
            return;
        }
        preg_match_all( "!(?:0x[a-fA-F0-9]+)|([a-zA-Z][a-zA-Z0-9_]*)!", $eq, $match );
        foreach ( $match[1] as $var ) {
            if ( $var && !isset( $args[ $var ] ) &&
                ! in_array( $var, $allowed_funcs ) ) {
                return;
            }
        }
        $modifiers = isset( $this->all_tags['modifiers'] )
                   ? $this->all_tags['modifiers'] : [];
        $modifiers[] = 'eq';
        foreach ( $args as $key => $var ) {
            if (! in_array( $key, $modifiers ) ) {
                if ( strlen( $var ) == 0 ) {
                    return;
                }
                if ( stripos( $var, '.' ) !== false ) {
                    $var = (float) $var;
                } else {
                    $var = (int) $var;
                }
                $eq = preg_replace( "/\b{$key}\b/", $var, $eq );
            }
        }
        $result = null;
        eval( '$result=' . $eq . ';' );
        return $result;
    }

    function function_trans ( $args, $ctx ) {
        $phrase = ! isset( $args['phrase'] ) ? '' : $args['phrase'];
        $lang = isset( $args['language'] ) ? $args['language'] : $ctx->language;
        if ( $phrase === null || $phrase === '' ) return;
        $component = isset( $args['component'] )
                   ? $ctx->component( $args['component'] ) : $ctx->default_component;
        if ( isset( $args['component'] ) && ! $component && $ctx->default_component ) {
            $component = $ctx->default_component->component( $args['component'] )
                       ? $ctx->default_component->component( $args['component'] )
                       : $ctx->default_component;
        }
        if (! $component ) $component = $ctx;
        if ( $lang && $component ) {
            $dict = isset( $component->dictionary ) ? $component->dictionary : null;
            if ( ( empty( $dict ) || !isset ( $component->dictionary[ $lang ] ) )
            && $path = $component->path ) {
                $locale_dir = $path . DS . 'locale';
                if ( is_dir( $locale_dir ) ) {
                    $locale = $locale_dir . DS . $lang . '.json';
                    if ( file_exists( $locale ) ) $component->dictionary[ $lang ]
                        = json_decode( file_get_contents( $locale ), true );
                }
            }
        }
        if ( $component && ( $dict = $component->dictionary )
            && isset( $dict[ $lang ] )
            && ( $dict = $dict[ $lang ] ) )
            $phrase = isset( $dict[ $phrase ] ) ? $dict[ $phrase ] : $phrase;
        $params = isset( $args['params'] ) ? $args['params'] : '';
        if (!is_array( $params ) && !is_string( $params ) ) {
            $params = (string) $params;
        }
        $phrase = ! is_array( $params )
            ? sprintf( $phrase, $params ) : vsprintf( $phrase, $params );
        if ( $ctx->esc_trans ) {
            return !isset( $args['noescape'] ) ? htmlspecialchars( $phrase, ENT_COMPAT, 'UTF-8', false ) : $phrase;
        }
        return $phrase;
    }

    function function_fileput ( $args, $ctx ) {
        if (! $this->allow_fileput ) return;
        $path = isset( $args['path'] ) ? $args['path'] : null;
        if (! $path ) return;
        if (! $this->allowed_path( $path ) ) return;
        $contents = isset( $args['contents'] ) ? $args['contents'] : '';
        if (! is_dir( dirname( $path ) ) ) {
            $mode = $this->dir_perms ? $this->dir_perms : 0755;
            $mask = umask();
            umask( 000 );
            $res = @mkdir( dirname( $path ), $mode, true );
            @umask( $mask );
        }
        $result = true;
        if ( file_exists( $path ) ) {
            if (! is_writable( $path ) ) {
                return;
            }
            $result = 1;
        }
        $append = isset( $args['append'] ) ? true : false;
        if ( $append ) {
            $res = @file_put_contents( $path, $contents, LOCK_EX|FILE_APPEND );
        } else {
            $res = @file_put_contents( $path, $contents, LOCK_EX );
        }
        if ( $res !== false ) {
            $this->update_paths[ $path ] = $result;
            $file_perms = $this->file_perms;
            if ( $file_perms ) @chmod( $path, $file_perms );
        }
        return $res !== false ? 1 : '';
    }

    function function_unlink ( $args, $ctx ) {
        if (! $this->allow_unlink ) return;
        $path = isset( $args['path'] ) ? $args['path'] : null;
        if (! $path ) return;
        if (! $this->allowed_path( $path ) ) return;
        if ( file_exists( $path ) && is_readable( $path ) ) {
            $res = @unlink( $path );
            if ( $res ) {
                $this->update_paths[ $path ] = false;
            }
            return $res;
        }
    }

    function function_set ( $args, $ctx ) {
        return $this->function_let( $args, $ctx );
    }

    function function_let ( $args, $ctx ) {
        $var = $args['this_tag'] == 'let' ? 'local_vars' : 'vars';
        $excludes = ['function', 'key', 'append', 'prepend', 'this_tag'];
        foreach ( $args as $key => $value ) {
            if ( in_array( $key, $excludes ) ) {
                continue;
            }
            if ( strpos( $key, '.' ) !== false ) {
                $names = explode( '.', $key );
                $v_name = array_shift( $names );
                $arr = ["['$v_name']"];
                foreach ( $names as $key ) {
                    $key = addslashes( $key );
                    $arr[] = "['$key']";
                }
                $arr = implode( $arr );
                $code = " \$ctx->{$var}{$arr} = \$ctx->append_prepend( \$value, \$args, \$key, \$var );";
                eval( $code );
            } else {
                $ctx->$var[ $key ] = $ctx->append_prepend( $value, $args, $key, $var );
            }
        }
        return '';
    }

    function allowed_path ( $path ) {
        $allowed = true;
        $allowed_paths = $this->allowed_paths;
        if (! empty( $allowed_paths ) ) {
            $allowed = false;
            foreach ( $allowed_paths as $allowed_path ) {
                if ( strpos( $path, $allowed_path ) === 0 ) {
                    $allowed = true;
                    break;
                }
            }
        }
        return $allowed;
    }

    function function_fetch ( $args, $ctx ) {
        $url = isset( $args['url'] ) ? $args['url'] : '';
        if ( $url ) {
            if ( preg_match( '!^https{0,1}://!', $url ) ) {
                $method = isset( $args['method'] ) ? $args['method'] : 'GET';
                $to_encoding = isset( $args['to_encoding'] ) ? $args['to_encoding'] : 'UTF-8';
                $ssl_opt = ['verify_peer' => false, 'verify_peer_name' => false ];
                $options = ['http' => ['method' => $method, 'ignore_errors' => true,
                            'timeout' => 30, 'follow_location' => true ], 'ssl' => $ssl_opt ];
                $headers = isset( $args['headers'] ) ? $args['headers'] : [];
                if ( is_string( $headers ) ) {
                    $headers = [ $headers ];
                }
                $ua = isset( $args['ua'] ) ? $args['ua'] : $this->user_agent;
                $ct = isset( $args['content_type'] ) ? $args['content_type'] : null;
                $at = isset( $args['access_token'] ) ? $args['access_token'] : null;
                foreach ( $headers as $header ) {
                    if ( strpos( $header, 'User-Agent: ' ) === 0 ) {
                        $ua = null;
                    } else if ( strpos( $header, 'Content-Type: ' ) === 0 ) {
                        $ct = null;
                    } else if ( strpos( $header, 'access_token: ' ) === 0 ) {
                        $at = null;
                    }
                }
                if ( $ua ) {
                    $ua = "User-Agent: {$ua}";
                    $headers[] = $ua;
                }
                if ( $ct ) {
                    $headers[] = "Content-Type: {$ct}";
                }
                if ( $at ) {
                    $headers[] = "access_token: {$at}";
                }
                $data = isset( $args['content'] ) ? $args['content'] : null;
                if ( $data ) {
                    if ( is_array( $data ) ) {
                        if ( in_array( 'Content-type: application/json', $headers ) ) {
                            $data = json_encode( $data, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT );
                        } else {
                            $data = http_build_query( $data, '', '&' );
                            if (! $ct ) {
                                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                            }
                        }
                    }
                    $headers[] = 'Content-Length: ' . strlen( $data );
                    $options['http']['content'] = $data;
                }
                if (! empty( $headers ) ) {
                    $options['http']['header'] = implode( "\r\n", $headers );
                }
                $context = stream_context_create( $options );
                if ( $contents = file_get_contents( $url, false, $context ) ) {
                    if ( $contents === false ) {
                        return '';
                    }
                    if ( $to_encoding ) {
                        $from_encoding = mb_detect_encoding( $contents, 'UTF-8,EUC-JP,SJIS,JIS' );
                        $contents = mb_convert_encoding( $contents, $to_encoding, $from_encoding );
                    }
                    return $contents;
                }
            }
        }
        if (! $this->allow_fileget ) return;
        $path = isset( $args['path'] ) ? $args['path'] : '';
        if (! $path ) return;
        if (! $this->allowed_path( $path ) ) return;
        if ( file_exists( $path ) && is_readable( $path ) ) {
            return @file_get_contents( $path );
        }
        return '';
    }

    function function_ldelim ( $args, $ctx ) {
        return $ctx->tag_block[0];
    }

    function function_rdelim ( $args, $ctx ) {
        return $ctx->tag_block[1];
    }

    function modifier_escape ( $str, $arg, $ctx, $name = null ) {
        $arg = strtolower( $arg );
        $meth = 'modifier_encode_' . $arg;
        if ( method_exists( $this, $meth ) ) {
            return $this->$meth( $str, 1, $ctx );
        } else if ( $arg === 'single' ) {
            return htmlspecialchars( $str, ENT_COMPAT, 'UTF-8', false );
        }
        return htmlspecialchars( $str );
    }

    function modifier_array_pop ( $arr, $arg, $ctx ) {
        $array = is_array( $arr ) ? $arr : $ctx->vars[ $arg ];
        if ( is_array( $array ) ) {
            $last = array_pop( $array );
            if ( $arg == 1 ) {
                return $last;
            }
            $ctx->vars[ $arg ] = $array;
            return $last;
        }
    }

    function modifier_setvar ( $str, $arg, $ctx ) {
        $ctx->vars[ $arg ] = $str;
    }

    function modifier_set ( $str, $arg, $ctx ) {
        $ctx->vars[ $arg ] = $str;
    }

    function modifier_let ( $str, $arg, $ctx ) {
        $ctx->local_vars[ $arg ] = $str;
    }

    function modifier_assign ( $str, $arg, $ctx ) {
        $ctx->vars[ $arg ] = $str;
    }

    function modifier_split ( $str, $arg, $ctx ) {
        return is_string( $str ) ? explode( $arg, $str ) : $str;
    }

    function modifier_join ( $arr, $arg, $ctx ) {
        return is_array( $arr )? implode( $arg, $arr ) : $arr;
    }

    function modifier_format_ts ( $date, $format, $ctx, $args = [] ) {
        if ( isset( $args['utc'] ) && preg_match( '/^[0-9]{14}$/', $date ) ) {
            $date = strtotime( $date );
            $old = date_default_timezone_get();
            date_default_timezone_set( 'UTC' );
            $date = date( 'YmdHis', $date );
        }
        $supported = ['ATOM', 'COOKIE', 'ISO8601', 'RFC822', 'RFC850', 'RFC1036', 'RFC1123',
                      'RFC2822', 'RFC3339', 'RFC3339_EXTENDED', 'RSS', 'W3C'];
        $format_uc = strtoupper( $format );
        if ( in_array( $format_uc, $supported ) ) {
            $dateTime = new DateTime( $date );
            ob_start();
            eval("echo \$dateTime->format(DateTimeInterface::$format_uc);");
            return ob_get_clean();
        }
        if ( isset( $args['relative'] ) ) {
            $relative = $args['relative'];
            $ts = strtotime( $date );
            $now = time();
            $delta = $now - $ts;
            if ( $delta >= 0 && $delta <= 60 ) { # last minute
                if ( $relative == 1 ) {
                    $args = ['phrase' => 'moments ago'];
                    return $ctx->function_trans( $args, $ctx );
                } else if ( $relative == 2 ) {
                    $args = ['phrase' => 'less than 1 minute ago'];
                    return $ctx->function_trans( $args, $ctx );
                } else {
                    $format = $delta == 1 ? '%s second ago' : '%s seconds ago';
                    $args = ['phrase' => $format, 'params' => $delta ];
                    return $ctx->function_trans( $args, $ctx );
                }
            } else if ( $delta < 0 && $delta >= -60 ) { # next minute
                if ( $relative == 1 ) {
                    $args = ['phrase' => 'moments from now'];
                    return $ctx->function_trans( $args, $ctx );
                } else if ( $relative == 2 ) {
                    $args = ['phrase' => 'less than 1 minute from now'];
                    return $ctx->function_trans( $args, $ctx );
                } else {
                    $second = -$delta;
                    $format = $second == 1 ? '%s second from now' : '%s seconds from now';
                    $args = ['phrase' => $format, 'params' => $second ];
                    return $ctx->function_trans( $args, $ctx );
                }
            } else if ( $delta > 60 && $delta <= 3600 ) { # last hour
                $min = (int) ( $delta / 60 );
                $sec = $delta % 60;
                if ( $relative == 1 ) {
                    $format = $min == 1 ? '%s minute ago' : '%s minutes ago';
                    $args = ['phrase' => $format, 'params' => $min ];
                    return $ctx->function_trans( $args, $ctx );
                } else {
                    $format = $min == 1 ? '%s minute' : '%s minutes';
                    if ( $min ) {
                        $format .= $sec == 1 ? ', %s second' : ', %s seconds';
                    }
                    $format .=  $relative == 2 ? ' ago' : '';
                    $args = ['phrase' => $format, 'params' => [ $hours, $min ] ];
                    return $ctx->function_trans( $args, $ctx );
                }
            } else if ( $delta < -60 && $delta >= -3600 ) { # next hour
                $delta = -$delta;
                $min = (int) ( $delta / 60 );
                $sec = $delta % 60;
                if ( $relative == 1 ) {
                    $format = $min == 1 ? '%s minute from now' : '%s minutes from now';
                    $args = ['phrase' => $format, 'params' => $min ];
                    return $ctx->function_trans( $args, $ctx );
                } else {
                    $format = $min == 1 ? '%s minute' : '%s minutes';
                    if ( $min ) {
                        $format .= $sec == 1 ? ', %s second' : ', %s seconds';
                    }
                    $format .= ' from now';
                    $args = ['phrase' => $format, 'params' => [ $hours, $min ] ];
                    return $ctx->function_trans( $args, $ctx );
                }
            } else if ( $delta > 3600 && $delta <= 86400 ) { # last day
                $hours = (int) ( $delta / 3600 );
                $min = (int) ( ( $delta % 3600 ) / 60 );
                if ( $relative == 1 ) {
                    $format = $hours == 1 ? '%s hour ago' : '%s hours ago';
                    $args = ['phrase' => $format, 'params' => $hours ];
                    return $ctx->function_trans( $args, $ctx );
                } else {
                    $format = $hours == 1 ? '%s hour' : '%s hours';
                    if ( $min ) {
                        $format .= $min == 1 ? ', %s minute' : ', %s minutes';
                    }
                    $format .=  $relative == 2 ? ' ago' : '';
                    $args = ['phrase' => $format, 'params' => [ $hours, $min ] ];
                    return $ctx->function_trans( $args, $ctx );
                }
            } else if ( $delta < -3600 && $delta >= -86400 ) { # next day
                $delta = -$delta;
                $hours = (int) ( $delta / 3600 );
                $min = (int) ( ( $delta % 3600 ) / 60 );
                if ( $relative == 1 ) {
                    $format = $hours == 1 ? '%s hour from now' : '%s hours from now';
                    $args = ['phrase' => $format, 'params' => $hours ];
                    return $ctx->function_trans( $args, $ctx );
                } else {
                    $format = $hours == 1 ? '%s hour' : '%s hours';
                    if ( $min ) {
                        $format .= $min == 1 ? ', %s minute' : ', %s minutes';
                    }
                    $format .= ' from now';
                    $args = ['phrase' => $format, 'params' => [ $hours, $min ] ];
                    return $ctx->function_trans( $args, $ctx );
                }
            } else if ( $delta > 86400 && $delta <= 604800 ) { # last week
                $days = (int) ( $delta / 86400 );
                $hours = (int) ( ( $delta % 86400 ) / 3600 );
                if ( $relative == 1 ) {
                    $format = $days == 1 ? '%s day ago' : '%s days ago';
                    $args = ['phrase' => $format, 'params' => $days ];
                    return $ctx->function_trans( $args, $ctx );
                } else {
                    $format = $days == 1 ? '%s day' : '%s days';
                    if ( $hours ) {
                        $format .= $hours == 1 ? ', %s hour' : ', %s hours';
                    }
                    $format .=  $relative == 2 ? ' ago' : '';
                    $args = ['phrase' => $format, 'params' => [ $days, $hours ] ];
                    return $ctx->function_trans( $args, $ctx );
                }
            } else if ( $delta < -86400 && $delta >= -604800 ) { # next week
                $delta = -$delta;
                $days = (int) ( $delta / 86400 );
                $hours = (int) ( ( $delta % 86400 ) / 3600 );
                if ( $relative == 1 ) {
                    $format = $days == 1 ? '%s day from now' : '%s days from now';
                    $args = ['phrase' => $format, 'params' => $days ];
                    return $ctx->function_trans( $args, $ctx );
                } else {
                    $format = $days == 1 ? '%s day' : '%s days';
                    if ( $hours ) {
                        $format .= $hours == 1 ? ', %s hour' : ', %s hours';
                    }
                    $format .=  ' from now';
                    $args = ['phrase' => $format, 'params' => [ $days, $hours ] ];
                    return $ctx->function_trans( $args, $ctx );
                }
            }
        }
        $backslash = '\\\\';
        if ( isset( $old ) ) date_default_timezone_set( $old );
        $Japanese = strpos( $format, '年' ) !== false
                 || strpos( $format, '月' ) !== false
                 || strpos( $format, '日' ) !== false
                 || isset( $args['language'] ) && $args['language'] == 'ja';
        $strtotime = strtotime( $date );
        if ( $strtotime === false ) return '';
        $strpos_d = strpos( $format, 'D' );
        $strpos_l = strpos( $format, 'l' );
        if ( $Japanese && ( $strpos_d !== false || $strpos_l !== false ) ) {
            // Japanese Day of the week.
            $number = date( 'w', $strtotime );
            if ( $strpos_d !== false ) {
                $week = ['日', '月', '火', '水', '木', '金', '土'];
                $format = preg_replace( "/([^{$backslash}])D/", '$1' . $week[ $number ], $format );
                if ( $strpos_d === 0 ) {
                    $format = preg_replace( "/^D/", $week[ $number ], $format );
                }
                if ( preg_match( "/{$backslash}{2}D/", $format ) ) {
                    $format = preg_replace( "/{$backslash}{$backslash}D/", 'D', $format );
                }
            }
            if ( $strpos_l !== false ) {
                $week = ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'];
                $format = preg_replace( "/([^{$backslash}])l/", '$1' . $week[ $number ], $format );
                if ( $strpos_l === 0 ) {
                    $format = preg_replace( "/^l/", $week[ $number ], $format );
                }
                if ( preg_match( "/{$backslash}{$backslash}l/", $format ) ) {
                    $format = preg_replace( "/{$backslash}{2}l/", 'l', $format );
                }
            }
        }
        if ( strpos( $format, $backslash ) ) {
            $format = str_replace( $backslash, '\\', $format );
        }
        return date( $format, $strtotime );
    }

    function modifier_zero_pad ( $str, $arg ) {
        return sprintf( '%0' . $arg . 's', $str );
    }

    function modifier_strip_linefeeds ( $str, $arg ) {
        return str_replace( ["\r\n", "\n", "\r"], '', $str );
    }

    function modifier_encode_js ( $str, $arg ) {
        $str = json_encode( $str, JSON_UNESCAPED_UNICODE );
        if ( preg_match( '/^"(.*)"$/', $str, $matches ) ) return $matches[1];
    }

    function modifier_encode_json ( $str, $arg ) {
        $str = json_encode( $str, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT );
        if ( preg_match( '/^"(.*)"$/', $str, $matches ) ) return $matches[1];
    }

    function modifier_encode_json_unescaped_unicode ( $str, $arg ) {
        $str = json_encode( $str, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE );
        if ( preg_match( '/^"(.*)"$/', $str, $matches ) ) return $matches[1];
    }

    function modifier_encode_url ( $str, $arg ) {
        return rawurlencode( $str );
    }

    function modifier_encode_html ( $str, $arg ) {
        if ( strtolower( $arg ) === 'single' ) {
            return htmlspecialchars( $str, ENT_COMPAT, 'UTF-8', false );
        }
        return htmlspecialchars( $str );
    }

    function modifier_encode_xml ( $str, $arg ) {
        if ( strtolower( $arg ) == 'cdata' && strpos( $str, ']]>' ) === false ) {
            return '<![CDATA[' . $str . ']]>';
        }
        return htmlentities( $str, ENT_XML1 );
    }

    function modifier_encode_php ( $str, $arg ) {
        return addslashes( $str );
    }

    function modifier_encode_mail ( $str, $arg ) {
        $str = preg_replace( '/\./', ' [DOT] ', $str );
        $str = preg_replace( '/\@/', ' [AT] ', $str );
        return $str;
    }

    function modifier_sprintf ( $str, $arg ) {
        if (!is_string( $str ) ) {
            $str = (string) $str;
        }
        return sprintf( $arg, $str );
    }

    function modifier_add_slash ( $str, $arg ) {
        if (! preg_match( "/\/$/", $str ) ) $str .= '/';
        return $str;
    }

    function modifier_setvartemplate ( $str, $arg, $ctx ) {
        $ctx->vars[ $arg ] = ['__eval__' => $str ];
    }

    function modifier_nocache ( $str, $arg, $ctx ) {
        $this->in_nocache = true;
        $build = $ctx->build( $str, true );
        $this->in_nocache = false;
        return $build;
    }

    function modifier_trim_to ( $str, $arg, $ctx ) {
        return $ctx->modifier_truncate( $str, $arg, $ctx );
    }

    function modifier_count_chars ( $str, $arg, $ctx ) {
        return $arg == 1 ? mb_strlen( $str ) : strlen( $str );
    }

    function modifier_count_characters ( $str, $arg, $ctx ) {
        return $arg == 1 ? mb_strlen( $str ) : strlen( $str );
    }

    function modifier_instr ( $str, $arg, $ctx ) {
        $instr = strpos( $str, $arg );
        if ( $instr !== false ) return $instr + 1;
    }

    function modifier_mb_instr ( $str, $arg, $ctx ) {
        $instr = mb_strpos( $str, $arg );
        if ( $instr !== false ) return $instr + 1;
    }

    function modifier_relative ( $str, $arg, $ctx ) {
        if ( strpos( $str, 'http' ) === 0 ) {
            $str = preg_replace( "/^https{0,1}:\/\/.*?\//", '/', $str );
        }
        return $str;
    }

    function modifier_numify ( $str, $arg, $ctx ) {
        if ( $arg == '1' || ! $arg ) $arg = ',';
        return number_format( $str, 0, '.', $arg );
    }

    function modifier_merge_linefeeds ( $str, $arg, $ctx ) {
        $previous = '';
        $items = preg_split( '/\r\n|\n|\r/', $str );
        $lines = [];
        foreach ( $items as $line ) {
            if ( $previous == '' && $line == '' ) {
            } else {
                $lines[] = $line;
            }
            $previous = $line;
        }
        return implode( "\n", $lines );
    }

    function modifier_truncate ( $str, $len, $ctx ) {
        list ( $plus, $tail ) = [false, ''];
        if ( strpos( $len, $ctx->csv_delimiter )!== false )
            $len = $ctx->parse_csv( $len );
        if ( is_array( $len ) ) {
            $middle = isset( $len[3] ) ? $len[3] : null;
            $break_words = isset( $len[2] ) ? $len[2] : null;
            $tail = isset( $len[1] ) ? $len[1] : null;
            $len = $len[0];
        }
        if ( strpos( $len, '+' ) !== false ) {
            list( $len, $tail ) = explode( '+', $len );
            $plus = true;
        }
        $len = (int) $len;
        if ( $len === 0 ) return;
        if ( mb_strlen( $str ) > $len ) {
            $len -= min( $len, mb_strlen( $tail ) );
            if (!isset( $plus ) && !isset( $break_words ) && !isset( $middle ) )
                $str = preg_replace( '/\s+?(\S+)?$/u', '',
                    mb_substr( $str, 0, $len + 1, 'UTF-8' ) );
            if ( $plus ) $len += mb_strlen( $tail );
            if (!isset( $middle ) ) return mb_substr( $str, 0, $len, 'utf-8' ) . $tail;
            $str = mb_substr( $str, 0, $len / 2, 'utf-8' )
                . $tail . mb_substr( $str, - $len / 2, 'utf-8' );
        }
        return $str;
    }

    function modifier_wrap ( $str, $len ) {
        $str = preg_replace( "/\r\n|\r|\n/", PHP_EOL, $str );
        $len = (int) $len;
        if (!$len ) return $str;
        $arr = [];
        $lines = explode( PHP_EOL, $str );
        foreach ( $lines as $line ) {
            $cnt = mb_strlen( $line );
            for ( $i = 0; $i <= $cnt; $i += $len ) {
                $arr[] = mb_substr( $line, $i, $len, 'UTF-8' );
            }
        }
        return join( PHP_EOL, $arr );
    }

    function modifier_from_json ( $json, $name, $ctx ) {
        if (! $json ) return '';
        $json = json_decode( $json, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            trigger_error( 'error in modifier from_json(' . json_last_error() . ')' );
        }
        $ctx->vars[ $name ] = $json;
    }

    function modifier_to_json ( $v ) {
        return json_encode( $v, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT );
    }

    function modifier_translate ( $v, $arg, $ctx ) {
        $args = ['phrase' => $v, 'params' => $arg ];
        return $ctx->function_trans( $args, $ctx );
    }

    function modifier_format_size ( $size, $precision, $ctx ) {
        $size = (int) $size;
        $precision = (int) $precision;
        if ( $size >= 1073741824 ) {
            $size = round( $size / 1073741824, $precision ) . 'GB';
        } else if ( $size >= 1048576 ) {
            $size = round( $size / 1048576, $precision ) . 'MB';
        } else if ( $size >= 1024 ) {
            $size = round( $size / 1024, $precision ) . 'KB';
        } else {
            $size .= 'Byte';
        }
        return $size;
    }

    function modifier_eval ( $str, $arg, $ctx ) {
        return ( $arg ) ? $ctx->build( $str ) : $str;
    }

    function modifier_trim_space ( $str, $arg ) {
        if ( $arg == 1 || $arg == 3 ) {
            $ptns = [ ['/^\s{2,}/m', ''],['/\s{1,}$/m', ''],['/\s{2,}/', ' '] ];
            $str = preg_replace(
            array_map( function( $func ) { return $func[0];}, $ptns ),
            array_map( function( $func ) { return $func[1];}, $ptns ), $str );
        }
        if ( $arg == 2 || $arg == 3 ) {
            $list = preg_split( "/[\r\n]/", $str );
            $txt = '';
            foreach ( $list as $out ) if ( $out != '' ) $txt .= $out . PHP_EOL;
            $str = rtrim( $txt );
        }
        return $str;
    }

    function modifier_replace ( $str, $args, $ctx ) {
        if (!is_array( $args ) ) $args = $ctx->parse_csv( $args );
        if (! isset( $args[1] ) ) return $str;
        $args = $this->setup_args( $args );
        return str_replace( $args[0], $args[1], $str );
    }

    function modifier_regex_replace ( $str, $args, $ctx ) {
        if (!is_array( $args ) ) $args = $ctx->parse_csv( $args );
        if (! isset( $args[1] ) ) return $str;
        $i = 0;
        $args = $this->setup_args( $args );
        foreach ( $args as $arg ) {
            if ( strpos( $arg, '\$' ) !== false ) {
                $arg = str_replace( '\$', '$', $arg );
            }
            if ( ( $pos = strpos( $arg, "\0" ) ) !== false ) {
                $arg = substr( $arg, 0, $pos );
            }
            $args[ $i ] = $arg;
            $i += 1;
        }
        $g = isset( $args[2] ) ? $args[2] + 0 : -1;
        $pattern = trim( $args[0] );
        if ( preg_match( '/^[a-zA-Z0-9\\\]/', $pattern ) ) {
            // Delimiter must not be alphanumeric or backslash.
            if ( $ctx->regex_compat ) return '';
            $encl_delimiter = false;
            $delimiters = ['/', '!', '#', '%', '~'];
            foreach ( $delimiters as $delimiter ) {
                if ( strpos( $pattern, $delimiter ) === false ) {
                    $pattern = $delimiter . $pattern . $delimiter;
                    $encl_delimiter = true;
                    break;
                }
            }
            if (! $encl_delimiter ) {
                return $str;
            }
        }
        $delimiter = mb_substr( $pattern, 0, 1 );
        if ( $delimiter !== '[' ) {
            $count = mb_substr_count( $pattern, $delimiter );
            if ( $count === 1 ) {
                // Delimiter must not be alphanumeric or backslash.
                return $ctx->regex_compat ? '' : $str;
            } else if ( $count > 2 ) {
                $start_end = $delimiter === '/' ? '!' : '/';
                preg_match( "{$start_end}^{$delimiter}(.*){$delimiter}(.*)\${$start_end}", $pattern, $matches );
                $regex = $matches[1];
                $opt = $matches[2];
                $regex = preg_replace( $start_end . '[^\\\\]' . $delimiter . $start_end, '$1\\' . $delimiter , $regex );
                if ( strpos( $regex, $delimiter ) === 0 ) {
                    $regex = '\\' . $regex;
                }
                $pattern = "{$delimiter}{$regex}{$delimiter}{$opt}";
            }
        }
        if ( preg_match('!([a-zA-Z0-9]+)$!s', $pattern, $matches ) ) {
            // Unknown modifier x.
            $modifier = $matches[1];
            $modifier = preg_replace( '/[^imsxADSUXJu]/', '', $modifier );
            $pattern = substr( $pattern, 0, - strlen( $matches[1] ) ) . $modifier;
        }
        if (! $g ) $g = -1;
        $result = @preg_replace( $pattern, $args[1], $str, $g );
        return $result === null && !$ctx->regex_compat ? $str : $result;
    }

    function modifier_default ( $str, $args, $ctx ) {
        return $str != '' ? $str : $args;
    }

    function modifier_normalize ( $str, $arg, $ctx ) {
        if ( function_exists( 'normalizer_normalize' ) ) {
            if ( strpos( $arg, 'N' ) === 0 ) {
                $arg = constant( "Normarizer::{$arg}" );
            } else {
                $arg = 5;
            }
            return normalizer_normalize( $str, $arg );
        }
        return $str;
    }

    function modifier_remove_blank ( $str, $arg, $ctx ) {
        $results = [];
        $lines = preg_split( "/[\r\n]/", $str );
        foreach ( $lines as $line ) {
            if ( trim( $line ) ) {
                $results[] = $line;
            }
        }
        return implode( PHP_EOL, $results );
    }

    function modifier_increment ( $str, $arg, $ctx ) {
        $increment = $arg ? (int) $arg : 1;
        $str = (int) $str;
        return $str + $increment;
    }

    function modifier_decrement ( $str, $arg, $ctx ) {
        $decrement = $arg ? (int) $arg : 1;
        $str = (int) $str;
        return $str - $decrement;
    }

    function modifier_cast_to ( $str, $arg, $ctx ) {
        if ( stripos( $arg, 'int' ) === 0 ) {
            return (int) $str;
        } else if ( stripos( $arg, 'bool' ) === 0 ) {
            return (bool) $str;
        } else if ( $arg == 'float' || $arg == 'double' || $arg == 'real' ) {
            return (float) $str;
        } else if ( stripos( $arg, 'str' ) === 0 ) {
            return (string) $str;
        } else if ( stripos( $arg, 'arr' ) === 0 ) {
            return (array) $str;
        } else if ( stripos( $arg, 'obj' ) === 0 ) {
            return (object) $str;
        } else if ( $arg == 'unset' || $arg == 'null' ) {
            return null;
        }
        return $str;
    }

    function modifier_sanitize ( $str, $arg, $ctx ) {
        if ( $arg !== '1' ) {
            $allowable_tags = explode( ',', $arg );
            array_walk( $allowable_tags, function( &$str ) { $str = "<{$str}>"; } );
            return strip_tags( $str, implode( ',', $allowable_tags ) );
        }
        return strip_tags( $str );
    }

    function modifier_preg_quote ( $str, $arg = '/', $ctx = null ) {
        return preg_quote( $str, $arg );
    }

/**
 * Get from predefined variables $_REQUEST.
 */
    function request_var ( $name, $args ) {
        $name = preg_replace( "/^request\./", '', $name );
        if (!isset( $_REQUEST[ $name ] ) ) return;
        $var = $_REQUEST[ $name ];
        if ( isset( $args['setvar'] ) || isset( $args['join'] ) ) return $var;
        return is_array( $var ) ? array_values( $var )[0] : $var;
    }

/**
 * Specified append or prepend attribute for setvar(block) or assign(block).

 * @param  string $str : Content for append or prepend.
 * @param  array  $args: Tag Attributes.
 * @param  string $name: Name of variables.
 * @param  string $var : 'vars' or 'local_vars'.
 * @return string $str : After processing $content.
 */
    function append_prepend ( $str, $args, $name, $var = null ) {
        $var = $var == 'local_vars' ? $var : null;
        if ( ( isset( $args['function'] ) && $args['function'] )
            || ( isset( $args['key'] ) && $args['key'] ) ) {
            $v = $this->get_any( $args[ $name ], $var ) ? $this->get_any( $args[ $name ], $var ) : [];
            if ( is_string( $v ) ) $v = [ $v ];
            if ( isset( $args['function'] ) ) {
                $function = $args['function'];
                if ( $function === 'push' ) {
                    $v[] = $str;
                } else if ( $function === 'unshift' ) {
                    array_unshift( $v, $str );
                }
            } else if ( isset( $args['key'] ) ) {
                $v[ $args['key'] ] = $str;
            }
            return $v;
        } else if ( isset( $args['append'] ) || isset( $args['prepend'] ) ) {
            $v = $this->get_any( $args[ $name ], $var );
            if ( $v ) {
                if ( isset( $args['append'] ) ) {
                    return $v . $str;
                } elseif ( isset( $args['prepend'] ) ) {
                    $str .= $v;
                }
            }
        }
        return $str;
    }

/**
 * Auto set reserved loop variables.
 *
 * @param   int   $cnt   : Loop counter.
 * @param   array $params: Array or object for loop.
 */
    function set_loop_vars ( $cnt, &$params ) {
        if ( empty( $params ) ) {
            $this->local_vars['__first__']  = false;
            $this->local_vars['__last__']   = false;
            $this->local_vars['__even__']   = false;
            $this->local_vars['__odd__']    = false;
            $this->local_vars['__index__']  = false;
            $this->local_vars['__counter__']= false;
            $this->local_vars['__end__']    = false;
            return;
        }
        $this->local_vars['__first__']  = $cnt === 0;
        $this->local_vars['__last__']   = !isset( $params[ $cnt + 1 ] );
        $even = $cnt % 2;
        $this->local_vars['__even__']   = $even;
        $this->local_vars['__odd__']    = !$even;
        $this->local_vars['__index__']  = $cnt;
        $this->local_vars['__counter__']= $cnt + 1;
        $this->local_vars['__end__']    = $cnt >= count( $params );
        $this->local_vars['__total__'] = count( $params );
        if ( $this->call_break ) {
            $params = array_slice( $params, 0, $cnt );
            $this->call_break = false;
        }
    }

    function parse_csv ( $s ) {
    return str_getcsv( stripslashes( $s ), $this->csv_delimiter, $this->csv_enclosure );
    }

/**
 * Parse tag literal, setvartemplate and nocache to array $this->literal_vars,
 *  and convert to literal tag
 *
 * @param string $content : Template source.
 * @param int    $kind    : null(literal), 1(setvartemplate), 2(nocache).
 * @return bool : nocache tag exists or not.
 */
    function parse_literal ( &$content, $kind = null ) {
        $request_cache = $this->request_cache;
        $this->request_cache = false;
        list( $tag_s, $tag_e, $h_sta, $h_end, $pfx ) = $this->quoted_vars;
        list( $ldelim, $rdelim ) = [$this->html_ldelim, $this->html_rdelim];
        if (!$kind ) $tagname = 'literal';
        else $tagname = $kind === 1 ? 'setvartemplate' : 'nocache';
        if ( $tagname == 'literal' && stripos( $content, 'dynamicmtml' ) !== false ) {
            $regex = "/(<$pfx:{0,1}dynamicmtml.*?[{$tag_e}|>])(.*?)"
                   . "([$tag_s|<]\/$pfx:{0,1}dynamicmtml[{$tag_e}|>])/is";
            if ( preg_match_all( $regex, $content, $mts ) ) {
                $count = count( $mts[0] );
                for ( $i = 0; $i < $count; $i++ ) {
                    $start = preg_quote( $mts[1][ $i ], '/' );
                    $body = $mts[2][ $i ];
                    $body_q = preg_quote( $body, '/' );
                    $end = preg_quote( $mts[3][ $i ], '/' );
                    $content = preg_replace( "/{$start}{$body_q}{$end}/", "<{$pfx}:literal>{$body}</{$pfx}literal>", $content );
                }
            }
        }
        if ( stripos( $content, $tagname ) === false ) return false;
        $regex = "/(($tag_s|<)$pfx:{0,1}{$tagname}.*?($tag_e|>))(.*?)"
               . "(($tag_s|<)\/$pfx:{0,1}{$tagname}($tag_e|>))/is";
        if (!preg_match_all( $regex, $content, $mts ) ) return false;
        $count = count( $mts[0] );
        for ( $i = 0; $i < $count; $i++ ) {
            $block = $mts[4][ $i ];
            $tag = preg_quote( $mts[0][ $i ] );
            $cnt = is_array( $this->literal_vars ) ? (string) count( $this->literal_vars ) : '0';
            $idx = " index=\"{$cnt}\"";
            if ( $kind ) $block = str_replace( $this->insert_text, '', $block );
            if (!$kind ) {
                $start = str_replace( 'literal', 'literal' . $idx,
                    strtolower( $mts[1][ $i ] ) );
                $end = $mts[5][ $i ];
            } elseif ( $kind === 1 ) {
                $name = ( preg_match( '/name="(.*?)"/', $mts[1][ $i ], $attr ) )
                      ? $attr[1] : '';
                $name = addslashes( $name );
                $start = preg_replace( "/setvartemplate/i", 'literal setvartemplate="'
                       . $name . '"' . $idx, $mts[1][ $i ], 1 );
                $end = str_replace( 'setvartemplate', 'literal', $mts[5][ $i ] );
            } else {
                $start = preg_replace( "/nocache/i", 'literal nocache="1"' . $idx,
                    $mts[1][ $i ], 1 );
                $end = str_replace( 'nocache', 'literal', $mts[5][ $i ] );
            }
            $content = preg_replace( "!$tag!si", $start . $block . $end, $content, 1 );
            $ids = $this->ids;
            foreach ( $ids as $id => $bool ) {
                $block = str_replace( '%' . $id, '<', $block );
                $block = str_replace( $id . '%', '>', $block );
            }
            $block = str_replace( $ldelim, '<', $block );
            $block = str_replace( $rdelim, '>', $block );
            if ( stripos( $block, 'ignore>' ) !== false ) {
                $block = preg_replace( "/<{$pfx}:{0,1}ignore.*?>.*?<\/{$pfx}:{0,1}ignore>/si", '', $block );
            }
            $this->literal_vars[] = $block;
        }
        $this->request_cache = $request_cache;
        return ( $kind === 2 ) ? true : false;
    }

/**
 * Keep raw block.
 *
 * @param string $content : Template source.
 * @param array  $map     : Array of replace temp string.
 */
    function pre_fetch ( $content, &$map =[] ) {
        $func = $this->compatible . '_pre_fetch';
        if ( function_exists( $func ) ) {
            $func( $this, $content, $map );
        }
        foreach ( $this->pre_fetch as $regex => $replace ) {
            if ( $replace === '__raw__' ) {
                if ( preg_match_all( $regex, $content, $mts ) ) {
                    $raws = $mts[1];
                    $mts = $mts[0];
                    foreach ( $mts as $idx => $mt ) {
                        $magic = $this->magic( $content );
                        $map[ $magic ] = $raws[ $idx ];
                        $mt = preg_quote( $mt, '/' );
                        $content = preg_replace( "/{$mt}/", $magic, $content, 1 );
                    }
                }
            } else if ( is_array( $replace ) ) {
                foreach ( $replace as $key => $value ) {
                    $content = preg_replace( $key, $value, $content );
                }
            } else {
                $content = preg_replace( $regex, $replace, $content );
            }
        }
        return $content;
    }

/**
 * Template compiler.
 *
 * @param string $content : Template source.
 * @param bool   $disp    : Display result or return result.
 * @param array  $tags_arr: Array of all template tags.
 * @param array  $params  : Array of template variables.
 * @param bool   $compiled: Return compiled PHP code.
 * @param bool   $nocache : Whether the nocache tag is used or not.
 * @return string $out    : After processing $content(or compiled PHP code).
 */
    function compile ( $content, $disp = true, $tags_arr = null, $use_tags = [],
        $params = [], $compiled = false, $nocache = false, $orig_key = null ) {
        $orig_content = $content;
        $prefix = $this->prefix;
        $regex = '<\${0,1}' . $prefix;
        if (!empty( $this->pre_fetch ) && !preg_match( "/$regex/i", $content ) ) {
            $content = $this->pre_fetch( $content, $this->replaced_map );
        }
        if (! $orig_key ) $orig_key = md5( $content );
        $this->tags['block'] = array_unique(
            array_merge( $this->tags['block'], $this->tags['block_once'] ) );
        if (!$this->build_start ) {
            $magic = $this->magic( $content );
            $magic2 = $this->magic( $content . $magic );
            $this->html_block = ['%' . $magic, $magic . '%'];
            list( $this->html_ldelim, $this->html_rdelim ) =
                array( '==' . $magic, '==' . $magic2 );
        }
        $this->build_start = true;
        $requires = [];
        foreach ( $params as $k => $v ) $this->vars[ $k ] = $v;
        list( $literals, $templates ) = (!$this->_include )
            ? [ $this->literal_vars, $this->template_paths ] : [ [], [] ];
        $callbacks = $this->callbacks;
        $all_tags = $this->all_tags;
        if ( $this->request_cache && isset( $this->compiled[ $orig_key ] ) ) {
            $out = $this->compiled[ $orig_key ];
        } else {
            if (!$this->allow_php && strpos( $content, '<?php' ) !== false ) $content = self::strip_php( $content );
            if (!empty( $callbacks['input_filter'] ) )
                $content = $this->call_filter( $content, 'input_filter', $orig_content );
            $dom = new DomDocument();
            $this->dom = $dom;
            $id = $this->magic( $content );
            $in_nocache = $this->in_nocache;
            $tags = $this->tags;
            if (!$tags_arr )
          { // Process in descending order of length of the tag names.
            $tags_arr = array_merge( $tags['block'], $tags['function'],
                $tags['conditional'], $tags['include'] );
            //usort( $tags_arr, create_function('$a,$b','return strlen($b)-strlen($a);') );
            usort( $tags_arr, function( $a, $b ) {
                return strlen( $b ) - strlen( $a );
            });
          }
            list( $t_sta, $t_end, $sta_h, $end_h )
                = array_merge( $this->tag_block, $this->html_block );
            list( $tag_s, $tag_e, $h_sta, $h_end, $pfx )
                = $this->get_quoted( [ $t_sta, $t_end, $sta_h, $end_h, $prefix ] );
            if (!$this->_include ) if ( stripos( $content, 'literal' ) !== false
                || stripos( $content, 'dynamicmtml' ) !== false )
                $this->parse_literal( $content );
            if (!$pfx && ( $pfx = $prefix = 'paml' ) )
                $content = preg_replace( '/' . $tag_s . '(\/{0,1})\${0,1}(.*?)\${0,1}' .
                $tag_e . '/si', $t_sta . '$1paml:$2' . $t_end, $content );
            if (! $this->app_name && $prefix == 'mt' && !preg_match( "/$regex/i", $content ) ) {
                // for Prototype.
                return $this->finish( $content, $disp, $callbacks, $literals, $templates, $nocache );
            }
            if ( strpos( $t_sta ,'<' ) === 0 && strpos( $t_end ,'>' ) !== false )
          {
            $content = preg_replace( '/' . $tag_s . '(\/{0,1})\${0,1}(' . $pfx .
                '.*?)\${0,1}' . $tag_e . '/si', '{%$1$2%}', $content );
            list( $t_sta, $t_end, $tag_s, $tag_e ) = ['{%', '%}', '\{%', '%\}'];
          }
            $content = preg_replace( '/' . $tag_s . '\${0,1}(' .
            $pfx . '.*?)\${0,1}' . $tag_e . '/si', '<$1>', $content );
            $content = preg_replace('/' . $tag_s . '(\/' . $pfx . '.*?)' .
            $tag_e . '/si', '<$1>', $content );
            foreach ( $tags_arr as $tag )
          {
            $close = isset( $all_tags['function'][ $tag ] ) || $tag === 'include'
                || $tag === 'extends' || $tag === 'else' || $tag === 'elseif'
                || $tag === 'elseifgetvar' ? ' /' : '';
            $content = preg_replace("/<\\s*\\" . "\${0,1}{$pfx}:{0,1}\s*{$tag}(.*?)\\\${0,1}>"
            . '/si', $t_sta . $pfx . $tag . '$1' . $close . $t_end, $content );
            $content = preg_replace( "!<\\s*{$pfx}:{0,1}\s*{$tag}(.*?)(>)!si", $t_sta . $pfx
            . $tag . '$1' . $close . $t_end, $content );
            $content = preg_replace( "!<\/{$pfx}:{0,1}\s*{$tag}\s*?>!si", $t_sta . '/' . $pfx
            . $tag . '$1' . $t_end, $content );
          }
            $content = preg_replace( '/<([^<|>]*?)>/s', $sta_h . '$1' . $end_h, $content );
            // <> in JavaScript
            $content = str_replace( '<', $this->html_ldelim, $content );
            $content = str_replace( '>', $this->html_rdelim, $content );
            $content = "<{$id}>{$content}</{$id}>";
            $content = preg_replace( '/' . $tag_s . '(\/{0,1}' . $pfx . '.*?)'
            . $tag_e . '/si', '<$1>', $content );
            if ( stripos( $content, 'setvartemplate' ) !== false )
                $this->parse_literal( $content, 1 );
            if ( stripos( $content, 'nocache' ) !== false ) {
                $res = $this->parse_literal( $content, 2 );
                if (!$nocache ) $nocache = $res;
            }
         // Measures against the problem that blank characters disappear.
            $insert = $this->insert_text ? $this->insert_text : "__{$id}__";
            $content = preg_replace( "/(<\/{0,1}$pfx.*?>)/",
            $insert . '$1' . $insert, $content );
         // Double escape HTML entities.
            $content = preg_replace_callback( "/(&#{0,1}[a-zA-Z0-9]{2,};)/is",
            function( $mts ) { return htmlspecialchars( $mts[0], ENT_QUOTES ); }, $content );
            $content = str_replace( '/ />', '/>', $content );
            if (!empty( $callbacks['pre_parse_filter'] ) ) {
                $content = $this->call_filter(
                    $content, 'pre_parse_filter', $insert, $orig_content );
                if ( $content === false ) return;
            }
            libxml_use_internal_errors( true ); // Tag parsing.
            if (!$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES','utf-8' ),
                LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD|LIBXML_COMPACT ) )
                trigger_error( 'loadHTML failed!' );
            $else_in = $this->else_in_block ? $dom->getElementsByTagName( $prefix . 'else' )->length : false;
            $this->_include = false;
            $include_tags = $tags['include'];
            $include_tags = array_unique( $include_tags );
            foreach ( $include_tags as $include )
        {
            $elements = $dom->getElementsByTagName( $prefix . $include );
            if (!$elements->length ) continue;
            $i = $elements->length - 1;
            $use_tags[] = 'include_' . $include;
            while ( $i > -1 )
          {
            $ele = $elements->item( $i );
            $i -= 1;
            $f = $ele->getAttribute( 'file' );
            $incl = null;
            if ( $include === 'extends' && ! $f ) {
                if (! empty( $this->extends_meth ) ) {
                    list( $class, $meth ) = $this->extends_meth;
                    if ( $ele->attributes->length ) {
                        $tag_args = ['this_tag' => 'extends'];
                        foreach ( $ele->attributes as $tag_attr ) {
                            $tag_args[ $tag_attr->name ] = $tag_attr->value;
                        }
                        $incl = $class->$meth( $tag_args, $this );
                    }
                }
            }
            if ( $f || $incl )
            {
            if ( $f ) {
                if ( strpos( $f, '$' ) !== false ) continue;
                if (!$f = $this->get_template_path( $f ) ) continue;
                if (!$incl = file_get_contents( $f ) ) continue;
            }
            if (!empty( $this->pre_fetch ) && !preg_match( "/$regex/i", $incl ) ) {
                $incl = $this->pre_fetch( $incl, $this->replaced_map );
            }
            if ( stripos( $incl, 'literal' ) !== false ) $this->parse_literal( $incl );
            list( $attrs, $t_args, $attributes ) = $this->get_attributes( $ele );
            unset( $attrs['file'] );
            $this->_include = true;
            if ( $include === 'includeblock' )
          {
            $nodeValue = str_replace( $insert, '', $ele->nodeValue );
            $attributes .= ' contents="' . addslashes( $nodeValue ) . '"';
          }
            if (!empty( $attrs ) )
                $incl="<{$prefix}block {$attributes}>{$incl}</{$prefix}block>";
            $parent = $ele->parentNode;
            if ( $include === 'extends' )
              {
                $parent->appendChild( $dom->createTextNode("</{$prefix}isinchild>". $incl ) );
                $parent->replaceChild( $dom->createTextNode("<{$prefix}isinchild>"), $ele );
              } else {
                $parent->replaceChild( $dom->createTextNode( $incl ), $ele );
              }
            }
          }
        }
            if ( $this->_include )
          { // Processed recursively if included.
            $out = mb_convert_encoding( $dom->saveHTML(), 'utf-8', 'HTML-ENTITIES' );
            $out = preg_replace( "!^.*?<$id>(.*?)<\/$id>.*$!si", '$1', $out );
            return $this->compile( str_replace( $insert, '', $out ),
                $disp, $tags_arr, $use_tags, [], $compiled, $nocache );
          }
            $pid = '$' . $this->id . '_';
            $adv = $this->advanced_mode;
            $modifier_funcs = $this->modifier_funcs;
            $functions = $this->functions;
            $modifiers = $all_tags['modifier'];
            $esc = $this->autoescape;
            $func_map = $this->func_map;
            $block_tags = $tags['block'];
            $core_tags = $this->core_tags;
            $cores = $core_tags['block'];
            $core_once  = $core_tags['block_once'];
            $block_tags = array_unique( $block_tags );
            $elseblocks = [];
            $elseTagName = $prefix . 'else';
            foreach ( $block_tags as $block )
        {
            if ( stripos( $content, $block ) === false ) continue;
            if ( $block === 'setvartemplate' || $block === 'nocache' ) continue;
            $elements = $dom->getElementsByTagName( $prefix . $block );
            if (!$elements->length ) continue;
            if ( $block === 'capture' ) $block = 'setvarblock';
            elseif ( $block === 'section' ) $block = 'for';
            elseif ( $block === 'assignvars' ) $block = 'setvars';
            $i = $elements->length - 1;
            $tag_name = 'block_' . $block;
            $use_tags[] = $tag_name;
            $method = $p = isset( $functions[ $tag_name ] ) ? $functions[ $tag_name ][0] : '';
            if ( $method )
          {
            if (!function_exists( $method ) ) include( $functions[ $tag_name ][1] );
            if ( $in_nocache ) $this->cache_includes[] = $functions[ $tag_name ][1];
          }
            if ( isset( $func_map[ $tag_name ] ) )
          {
            list( $class, $name ) = $this->func_map[ $tag_name ];
            $method = '$this->component(\'' . get_class( $class ) . '\')->' . $name;
          } elseif ( in_array( $block, $cores ) || in_array( $block, $core_once ) )
            $method = "\$this->{$tag_name}";
          {
          }
            if (!$method ) continue;
            while ( $i > -1 )
          {
            $ele = $elements->item( $i );
            $i -= 1;
            $bid = $this->magic( $content );
            $containsElse = false;
            if ( $else_in && $ele->childNodes->length ) {
                $blockChild = $ele->childNodes;
                foreach ( $blockChild as $child ) {
                    if ( $child->nodeType == 1 && $child->tagName == $elseTagName ) {
                        $parent = $child->parentNode;
                        $parent->replaceChild( $dom->createTextNode( "<{$prefix}elseblock{$bid}>" ), $child );
                        $elseblocks[] = "{$prefix}elseblock{$bid}";
                        $containsElse = true;
                        break;
                    }
                }
            }
            if ( $block === 'isinchild' )
          {
            $sta = "<?php {$pid}local_vars['__child_context__']=true;ob_start();?>";
            $end = "<?php unset({$pid}local_vars['__child_context__']);ob_end_clean();?>";
          } else
          {
            $restore = $block === 'setvarblock' ? " {$pid}local_params={$pid}old_params['{$bid}'];"
                     : " {$pid}local_params={$pid}old_params['{$bid}'];"
                     . "{$pid}local_vars={$pid}old_vars['{$bid}'];";
            list ( $_args, $_content, $_repeat, $_params, $_param, $_continue ) =
                ['a' . $bid, 'c' . $bid, 'r' . $bid, 'ps' . $bid, 'p' . $bid, 'c' . $bid ];
            list( $attrs, $t_args ) = $this->get_attributes( $ele, $block, $p );
            $out = $this->add_modifier( "\${$_content}",
                $attrs, $modifiers, $modifier_funcs, $func_map, $requires, false );
            list( $begin, $last ) = strpos( $out, '(' )
                ? ['ob_start();', "\${$_content}=ob_get_clean();echo({$out});"] : ['', ''];
            $setup_args = $adv ? "\$this->setup_args({$t_args},null,\$this)" : $t_args;
            $sta = "<?php \${$_content}=null;{$begin}{$pid}old_params['{$bid}']="
                 . "{$pid}local_params;{$pid}old_vars['{$bid}']={$pid}local_vars;"
                 . "\${$_args}={$setup_args};";
            if ( isset( $all_tags['block_once'][ $block ] ) )
           {
            $sta .= "ob_start();" . EP;
            $end ="<?php \${$_content}=ob_get_clean();\${$_content}=$method(\${$_args},"
                 . "\${$_content},\$this,\${$_repeat},1,'{$bid}');echo({$out});{$restore}" . EP;
           } else
           {
            $cond = "while(\${$_repeat}===true):";
            $sta .= "\${$bid}=-1;\${$_repeat}=true;${cond}\${$_repeat}=(\${$bid}!==-1)"
                 .  "?false:true;echo $method(\${$_args},\${$_content},"
                 .  "\$this,\${$_repeat},++\${$bid},'{$bid}');ob_start();" . EP;
            $sta .= "<?php \${$_continue} = true; if(isset(\$this->local_vars['__total__'])"
                 . "&&isset(\$this->local_vars['__counter__'])&&\$this->local_vars['__total__']<"
                 . "\$this->local_vars['__counter__']){\${$_continue}=false;}if(\${$_continue} ):" . EP;
            $end = "<?php endif;\${$_content}=ob_get_clean();endwhile;{$last}{$restore}" . EP;
           }
          }
            if ( $containsElse ) {
                $end = "</{$prefix}elseblock{$bid}>" . $end;
                $end .= "<?php if(\${$bid}===0):" . EP;
                $end .= "<{$prefix}elseblock{$bid}_content><?php endif" . EP;
            }
            $parent = $ele->parentNode;
            if ( $block === 'ignore' )
            {
              $parent->removeChild( $ele );
            } else {
              if ( $block === 'literal' ) $ele->nodeValue = '';
              $parent->insertBefore( $dom->createTextNode( $sta ), $ele );
              $parent->insertBefore( $dom->createTextNode( $end ), $ele->nextSibling );
            }
          }
        }
            $cores = $core_tags['conditional'];
            $conditional_tags = $tags['conditional'];
            $conditional_tags = array_unique( $conditional_tags );
            foreach ( $conditional_tags as $conditional )
        {
            if ( stripos( $content, $conditional ) === false ) continue;
            $elements = $dom->getElementsByTagName( $prefix . $conditional );
            if (!$elements->length ) continue;
            $i = $elements->length - 1;
            $tag_name = 'conditional_' . $conditional;
            $func_name = 'block_' . $conditional;
            $use_tags[] = $tag_name;
            $method = $p = isset( $functions[ $func_name ] )? $functions[ $func_name ][0] : '';
            if ( $method )
          {
            if (!function_exists( $method ) ) include( $functions[ $func_name ][1] );
            if ( $in_nocache ) $this->cache_includes[] = $functions[ $func_name ][1];
          }
            if ( isset( $func_map[ $tag_name ] ) )
          {
            list( $class, $name ) = $this->func_map[ $tag_name ];
            $method = '$this->component(\'' . get_class( $class ) . '\')->' . $name;
          } elseif ( in_array( $conditional , $cores ) ) {
            $method = $conditional === 'elseifgetvar' ? '$this->conditional_ifgetvar'
                    : '$this->conditional_' . $conditional;
          }
            if (!$method ) continue;
            while ( $i > -1 )
          {
            $ele = $elements->item( $i );
            $i -= 1;
            list( $attrs, $t_args ) = $this->get_attributes( $ele, $conditional, $p );
            $bid = $this->magic( $content );
            $_args = '_' . $bid;
            $out = $this->add_modifier( "\${$bid}",
                $attrs, $modifiers, $modifier_funcs, $func_map, $requires, false );
            list( $begin, $last ) = strpos( $out, '(' )
                ? ['ob_start();', "\${$bid}=ob_get_clean();echo {$out};"] : ['', ''];
            $setup_args = $adv ? "\$this->setup_args({$t_args},null,\$this)" : $t_args;
            if (!$adv && ( $conditional === 'ifgetvar' || $conditional === 'elseifgetvar' )
                && isset( $attrs['name'] ) ) {
                $nm = $attrs['name'];
                $cond = "(isset({$pid}local_vars['${nm}'])&&{$pid}local_vars['${nm}'])||"
                      . "(isset({$pid}vars['${nm}'])&&{$pid}vars['${nm}'])";
            } else {
                $cond = "{$method}({$setup_args},null,\$this,true,true)";
            }
            $parent = $ele->parentNode;
            if ( $conditional === 'elseif' || $conditional === 'elseifgetvar' )
            {
              $parent->replaceChild( 
                  $dom->createTextNode( '<?php elseif(' . $cond . '):' . EP ), $ele );
            } elseif ( $conditional === 'else' ) {
              $parent->replaceChild( $dom->createTextNode('<?php else:' . EP ), $ele );
            } else {
              $sta = "<?php $begin{$pid}old_params['{$bid}']={$pid}local_params;"
                   . "{$pid}old_vars['{$bid}']={$pid}local_vars;if({$cond}):" . EP;
              $end = "<?php endif;{$last}{$pid}local_params={$pid}old_params['{$bid}'];"
                   . "{$pid}local_vars={$pid}old_vars['{$bid}'];" . EP;
              $parent->insertBefore( $dom->createTextNode( $sta ), $ele );
              $parent->insertBefore( $dom->createTextNode( $end ), $ele->nextSibling );
            }
          }
        }
            $function_tags = $tags['function'];
            $function_tags = array_unique( $function_tags );
            $cores = $core_tags['function'];
            foreach ( $function_tags as $function )
        {
            if ( stripos( $content, $function ) === false ) continue;
            $elements = $dom->getElementsByTagName( $prefix . $function );
            if (!$elements->length ) continue;
            $i = $elements->length - 1;
            $tag_name = 'function_' . $function;
            $use_tags[] = $tag_name;
            $method = $p = isset( $functions[ $tag_name ] ) ? $functions[ $tag_name ][0] : '';
            if ( $method )
          {
            if (!function_exists(!$method ) ) include( $functions[ $tag_name ][1] );
            if ( $in_nocache ) $this->cache_includes[] = $functions[ $tag_name ][1];
          }
            if ( isset( $func_map[ $tag_name ] ) )
          {
            list( $class, $name ) = $this->func_map[ $tag_name ];
            $method = '$this->component(\'' . get_class( $class ) . '\')->' . $name;
          } elseif ( in_array( $function, $cores ) ) {
            $method = "\$this->{$tag_name}";
          }
            if (!$method ) continue;
            while ( $i > -1 )
          {
            $ele = $elements->item( $i );
            $i -= 1;
            list( $attrs, $t_args ) = $this->get_attributes( $ele, $function, $p );
            if (!$adv && $function === 'var' ) $function = 'getvar';
            if ( $function === 'getvar' ) {
                $nm = addslashes( $attrs['name'] );
                $out = "isset({$pid}local_vars['{$nm}'])?{$pid}local_vars['{$nm}']:"
                     . "(isset({$pid}vars['{$nm}'])?{$pid}vars['{$nm}']:'')";
            } else {
                $setup_args = $adv ? "\$this->setup_args({$t_args},null,\$this)" : $t_args;
                $out = "{$method}({$setup_args},\$this)";
            }
            $out = '<?php echo ' . $this->add_modifier( $out, $attrs, $modifiers,
                $modifier_funcs, $func_map, $requires, $esc ) . EP;
            $ele->parentNode->replaceChild( $dom->createTextNode( $out ), $ele );
          }
        }
        if (!empty( $callbacks['dom_filter'] ) )
            $dom = $this->call_filter( $dom, 'dom_filter', $orig_content );
        $out = mb_convert_encoding( $dom->saveHTML(), 'utf-8', 'HTML-ENTITIES' );
        if (!empty( $elseblocks ) ) {
            foreach ( $elseblocks as $elseblock ) {
                if ( preg_match( "!<{$elseblock}>(.*?)</{$elseblock}>!si", $out, $matches ) ) {
                    $out = str_replace( "<{$elseblock}_content>", $matches[1], $out );
                    $out = str_replace( $matches[0], '', $out );
                }
            }
        }
        unset( $dom, $content );
        if (! empty( $this->replaced_map ) ) {
            foreach ( $this->replaced_map as $magic => $raw ) {
                if ( strpos( $out, $magic ) !== false ) {
                    $out = preg_replace( "/{$magic}/", $raw, $out, 1 );
                }
            }
        }
        $out = str_replace( $this->html_ldelim, '<', $out );
        $out = str_replace( $this->html_rdelim, '>', $out );
        $out = str_replace( $insert, '', $out );
        if ( preg_match_all( "/{$h_sta}\\s*\\\${0,1}({$pfx}|\\\$):{0,1}\\s"
            . "*\\\${0,1}(.*?)\\\${0,1}{$h_end}/is", $out, $mts ) )
          { // Convert inline variables to 'var' tag.
            list( $xml, $tag_cnt, $matches ) = [ new DOMDocument(), -1, $mts[2] ];
            foreach ( $matches as $tag )
            {
              list( $v, $tag, $attrs ) = [ null, trim( $tag ), [] ];
              if ( preg_match( "/(.{1,})\[.*?]$/", $tag, $_mts ) ) {
                  list( $v, $tag ) = [ $tag, trim( $_mts[2] ) ];
            } elseif ( strpos( $tag, '.' ) !== false ) {
              $parse_tag = preg_split( "/\s{1,}/", $tag );
              if ( strpos( $parse_tag[0], '.' ) !== false )
                  list( $v, $tag ) = [ $parse_tag[0], 'dummy'];
              //if ( $this->app_name ) {
              array_shift( $parse_tag );
              foreach ( $parse_tag as $parse_tag_attr ) {
                  if (! $parse_tag_attr ) continue;
                  if ( strpos( $parse_tag_attr, '=' ) !== false ) {
                      list( $attr_name, $attr_value ) = explode( '=', $parse_tag_attr );
                      $attr_value = trim( $attr_value, '"' );
                      $attrs[ $attr_name ] = $attr_value;
                  } else {
                      $attrs[ $parse_tag_attr ] = 1;
                  }
              }
              //}
            } else {
                //if ( $this->app_name ) {
                $parse_tag = preg_split( "/\s{1,}/", $tag );
                list( $v, $tag ) = [ $parse_tag[0], 'dummy'];
                array_shift( $parse_tag );
                foreach ( $parse_tag as $parse_tag_attr ) {
                    if (! $parse_tag_attr ) continue;
                    if ( strpos( $parse_tag_attr, '=' ) !== false ) {
                        list( $attr_name, $attr_value ) = explode( '=', $parse_tag_attr );
                        $attr_value = trim( $attr_value, '"' );
                        $attrs[ $attr_name ] = $attr_value;
                    } else {
                        $attrs[ $parse_tag_attr ] = 1;
                    }
                }
                //}
            }
              $src = '<?xml version="1.0" encoding="UTF-8"?><root><' . $tag . ' /></root>';
              if (!$xml->loadXML( $src ) ) continue;
              $_tag = $xml->getElementsByTagName( 'root' )->item( 0 )->firstChild;
              list( $_attrs, $nm ) = [ $_tag->attributes, $_tag->tagName ];
              if (!preg_match( "/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $nm ) )
                  continue;
              if ( isset( $v ) ) $nm = $v;
              for ( $i = 0; $i < $_attrs->length; $i++ )
                  $attrs[ $_attrs->item( $i )->name ] = $_attrs->item( $i )->value;
              $res = $adv ? "\$this->function_var(\$this->setup_args(['name'=>'{$nm}'],null,"
                   . "\$this),\$this)" : "isset({$pid}local_vars['{$nm}'])?{$pid}local_vars"
                   . "['{$nm}']:(isset({$pid}vars['{$nm}'])?{$pid}vars['{$nm}']:'')";
              if ( isset( $attrs ) && !empty( $attrs ) )
                  $res = $this->add_modifier( $res, $attrs, $modifiers, $modifier_funcs,
                      $func_map, $requires, $esc );
              $out = str_replace( $mts[0][++$tag_cnt ], "<?php echo {$res}" . EP, $out );
            } unset( $xml );
          }
            if (!empty( $callbacks['post_compile_filter'] ) )
                $out = $this->call_filter( $out, 'post_compile_filter', $orig_content );
            $out = str_replace( ["<{$id}>", "</{$id}>"], '', $out );
            $out = preg_replace( '/' . $h_sta . '(.*?)' . $h_end . '/si', '<$1>', $out );
            $out = preg_replace( "/<\/{0,1}{$pfx}.*?>/si", '', $out );
            $_pfx = $this->id . '_';
            $vars = "<?php \${$_pfx}vars=&\$this->vars;\${$_pfx}old_params=&\$this->"
                  . "old_params;\${$_pfx}local_params=&\$this->local_params;\${$_pfx}"
                  . "old_vars=&\$this->old_vars;\${$_pfx}local_vars=&\$this->local_vars;?>";
            $out = $vars . $out;
            if (! empty( $this->literal_vars ) ) {
                $meta = "\$literal_old_{$_pfx}=\$this->literal_vars;";
                $meta .= '$this->literal_vars=' . var_export( $this->literal_vars, true );
                $out ="<?php {$meta};?>{$out}<?php \$this->literal_vars=\$literal_old_{$_pfx}?>";
            }
            if ( $compiled ) {
                return $out;
            }
            $require = '';
            if ( is_array( $use_tags ) && !empty( $use_tags ) ) {
                foreach ( $use_tags as $func )
                    if ( isset( $functions[ $func ] ) )
                        $require .= "include('" . $functions[ $func ][1] . "');";
            }
            if ( is_array( $requires ) && !empty( $requires ) ) {
                $requires = array_keys( $requires );
                foreach ( $requires as $path ) $require .= "include('{$path}');";
            }
            if (!$this->in_build && !$this->force_compile && $this->compile_key ) {
                if (!$this->re_compile ) $this->set_cache( $this->compile_key, $out,
                    $this->compile_path, $require, $nocache );
                $this->nocache = false;
            }
            $this->compiled[ $orig_key ] = $out;
            $this->compiled["{$orig_key}_require"] = $require;
        }
        $out = preg_replace( "/\n$/s", '', $out );
        return $this->finish( $out, $disp, $callbacks, $literals, $templates, $nocache );
    }

/**
 * Set(Get) cache.
 */
    function set_cache ( $key, $out, $path = null, $req = '', $nocache = false ) {
        if (! $path || strpos( $path, DS ) === false ) {
            $path = is_object( $this->cache_driver ) ? 'compiled' . DS . $key
                  : $this->compile_dir . $this->prefix . '__' . $key . '.php';
        }
        $meta = '$this->meta=' . var_export( ['template_paths' => $this->template_paths,
        'version' => PAMLVSN, 'advanced' => $this->advanced_mode, 'time' => $this->request_time ],
        true ) . ';';
        $out = preg_replace( "/\n$/s", '', $out );
        $meta .= ( $nocache ) ? '$this->nocache=true;' : '';
        $meta .= '$this->literal_vars=' . var_export( $this->literal_vars, true ) . ';';
        $out ="<?php {$req}{$meta}ob_start();?>{$out}<?php \$this->out=ob_get_clean();?>";
        if ( is_object( $this->cache_driver ) ) {
            $this->cache_driver->set( $path, $out );
        } else if ( $this->compile_dir ) {
            if ( @file_put_contents( "{$path}.tmp", $out, LOCK_EX ) ) {
                if (! @rename( "{$path}.tmp", $path ) ) {
                    @unlink( "{$path}.tmp" );
                    return;
                }
                return true;
            }
        }
    }

    function clear_cache ( $key, $path = null ) {
        if (! $path ) {
            $path = is_object( $this->cache_driver ) ? 'compiled' . DS . $key
                  : $this->compile_dir . $this->prefix . '__' . $key . '.php';
        }
        if ( is_object( $this->cache_driver ) ) {
            $this->cache_driver->delete( $path );
        } else if ( $this->compile_dir ) {
            @unlink( $path );
        }
    }

    function get_cache ( $key, $ttl = null, $comp = null, $path = null ) {
        if (!$this->compile_check ) {
            $ttl = null;
        } else if ( $ttl === null ) {
            $ttl = $this->cache_ttl;
        }
        $this->out = null;
        $this->meta = [];
        $cdir = $this->compile_dir;
        $path = is_object( $this->cache_driver ) ? 'compiled' . DS . $key
              : $cdir . $this->prefix . '__' . $key . '.php';
        if ( is_object( $this->cache_driver ) ) {
            $compiled = $this->cache_driver->get( $path );
            if ( $compiled && is_string( $compiled ) ) {
                $this->_eval( $compiled );
            }
        } else if ( file_exists( $path ) ) {
            if ( $ttl < 1 || ( $this->request_time - filemtime( $path ) < $ttl ) ) {
                include( $path );
                if ( $this->out === false ) {
                    @unlink( $path );
                    $this->meta = null;
                    // https://github.com/PowerCMS/Prototype/issues/927
                }
            }
        }
        if ( $meta = $this->meta ) {
            if (!$this->compile_check ) return $path;
            if ( $meta['version'] !== PAMLVSN ||
                $meta['advanced'] !== $this->advanced_mode ||
                ( isset( $meta['time'] ) && $ttl && $meta['time'] < ( $this->request_time - $ttl ) ) ) {
                $this->out = null;
                $this->meta = null;
                if ( is_object( $this->cache_driver ) ) {
                    $this->cache_driver->delete( $path );
                }
                return $path;
            }
            $tpl_paths = $meta['template_paths'];
            if (! $tpl_paths ) return $path;
            foreach( $tpl_paths as $tmpl => $mod ) {
                if (! $tmpl ) continue;
                if (!file_exists( $tmpl ) || filemtime( $tmpl ) > $mod ) {
                    $this->out = null;
                    $this->meta = [];
                    $no_cache = true;
                    return;
                }
            }
            return $path;
        }
        $this->out = null;
        $this->meta = null;
        return $path;
    }

/**
 * DOMElement attributes to PHP code or PAML template attributes.

 * @param  object $elem  : Object DOMElement.
 * @param  string $tag   : Template tag name.
 * @param  string $plugin: Plugin's method.
 * @return array( array, string, string ) $arguments: Set-uped $arguments.
 */
    function get_attributes ( $elem, $tag = null, $plugin = null ) {
        list( $_attrs, $attributes, $attrs, $t_args ) = [ $elem->attributes, '', [], [] ];
        if ( $tag && !$plugin ) $elem->setAttribute( 'this_tag', $tag );
        $length = $_attrs->length;
        for ( $i = 0; $i < $length; $i++ ) {
            $attr_n = strtolower( addslashes( $_attrs->item( $i )->name ) );
            if ( $attr_n === 'assign' ) $attr_n = 'setvar';
            $attr_v = addslashes( $_attrs->item( $i )->value );
            $t_args[] = "'{$attr_n}'=>'{$attr_v}'";
            $attrs[ $attr_n ] = $attr_v;
            $attributes .= " {$attr_n}=\"{$attr_v}\"";
        }
        return [ $attrs, '[' . join( ',', $t_args ) . ']', $attributes ];
    }

/**
 * Recursively interpose output with a modifiers.
 *
 * @param  string $out       : Output variable.
 * @param  array  $attributes: Tag attributes.
 * @param  array  $modifiers : All modifiers.
 * @param  array  $modifier_funcs: Mapping of modifier name and function name.
 * @param  array  $func_map  : Mapping of function and [ $plugin, $method ].
 * @param  array  $requires  : Plug-ins required to load.
 * @param  bool   $esc       : Need escape or not.
 * @return string $out       : PHP code for output.
 */
    function add_modifier
      ( $out, $attributes, $modifiers, $modifier_funcs, $func_map, &$requires, $esc ) {
        $this_tag = isset( $attributes['this_tag'] ) ? $attributes['this_tag'] : '';
        foreach ( $attributes as $attr_n => $attr_v ) {
            $attr_v = addslashes( $attr_v );
            if (!isset( $modifiers[ $attr_n ] ) ) continue;
            if ( $this_tag === 'for' && ( $attr_n === 'increment' || $attr_n === 'decrement' ) ) continue;
            if ( $attr_n === 'escape' && ( strtolower( $attr_v ) === 'html' ||
                strtolower( $attr_v ) === 'url' || $attr_v == 1 || !$attr_v ) ) {
                $out = strtolower( $attr_v ) === 'url' ?
               'rawurlencode(' . $out . ')':'htmlspecialchars(' . $out . ',ENT_QUOTES)';
            } elseif ( isset( $modifier_funcs[ $attr_n ] ) && function_exists( $modifier_funcs[ $attr_n ] ) ) {
                $func_ref = new ReflectionFunction( $modifier_funcs[ $attr_n ] );
                $attr_num = $func_ref->getNumberOfParameters();
                if ( $attr_num > 1 && $attr_v && $attr_v !== '1' ) {
                    if (! defined( $attr_v ) ) {
                        $attr_v = "'{$attr_v}'";
                    }
                    $out = $modifier_funcs[ $attr_n ] . '(' . $out . ",{$attr_v})";
                } else {
                    $out = $modifier_funcs[ $attr_n ] . '(' . $out . ')';
                }
            } else {
                if ( method_exists( $this, 'modifier_' . $attr_n ) ) {
                    $out = "\$this->modifier_{$attr_n}({$out},\$this->setup_args"
                         ."('{$attr_v}','{$attr_n}',\$this),\$this,'{$attr_n}')";
                } else {
                    $mname = 'modifier_' . addslashes( $attr_n );
                    if (isset( $func_map[ $mname ] ) ) {
                        list( $class, $name ) = $func_map[ $mname ];
                        $method = '$this->component(\''
                                . get_class( $class ) . '\')->' . $name;
                        $out = "{$method}({$out},\$this->setup_args('{$attr_v}',"
                             . "'{$attr_n}',\$this),\$this,'{$attr_n}')";
                    } else {
                        $f = $this->autoload_modifier( $mname );
                        if ( $f && $this->functions[ $mname ][1] &&
                            file_exists( $this->functions[ $mname ][1] ) ) {
                            $requires[ $this->functions[ $mname ][1] ] = true;
                        }
                        $out = "\$this->do_modifier('{$mname}',{$out},'{$attr_v}',\$this)";
                    }
                }
            }
        }
        if ( $esc && (!isset( $attributes['raw'] ) || !$attributes['raw'] ) )
            $out = "htmlspecialchars({$out},ENT_QUOTES)";
        return $out;
    }

/**
 * Finalize. Display content or return content.
 */
    function finish ( $out, $disp, $cb, $lits = [], $tmpls = [], $nocache = false ) {
        $out = $this->out ? $this->out : $this->_eval( $out );
        if (!empty( $cb['output_filter'] ) )
            $out = $this->call_filter( $out, 'output_filter' );
        if (! $this->force_compile ) {
            if (! $this->keep_vars ) {
                $this->local_vars = [];
                $this->vars = [];
            }
        } else {
            if ( $nocache ) $out = $this->_eval( $out );
        }
        if ( $this->unify_breaks || $this->trim_tmpl ) {
            $out = $this->finalize( $out );
        }
        $this->literal_vars = $lits;
        $this->template_paths = $tmpls;
        if (!$this->in_build ) unset( $this->out );
        if ( $disp ) echo $out;
        return $out;
    }

/**
 * Text formatting.
 */
    function finalize ( $out ) {
        if ( $this->unify_breaks ) {
            $out = preg_replace( "/\r\n|\r|\n/", PHP_EOL, $out );
        }
        if ( $this->trim_tmpl ) {
            $out = trim( $out );
        }
        return $out;
    }

/**
 * Math operation.
 */
    static function math_operation ( $op, $value1, $value2, $args = [] ) {
        if ( is_array( $value2 ) ) return;
        if ( is_array( $value1 ) ) {
            if ( isset( $args['key'] ) ) {
                $value1 = isset( $value1[ $args['key'] ] ) ? $value1[ $args['key'] ] : null;
                if ( $value1 === null ) {
                    return;
                }
            } else {
                return;
            }
        }
        if (!preg_match('/^\-?[\d\.]+$/', $value1 ) ) return;
        if ( $value2 && !preg_match('/^\-?[\d\.]+$/', $value2 ) ) return;
        $value = null;
        if ( $op === '+' || $op === 'add' ) {
            $value = $value1 + $value2;
        } else if ( $op === '++' || $op === 'inc' ) {
            $value = $value1 + 1;
        } else if ( $op === '-' || $op === 'sub' ) {
            $value = $value1 - $value2;
        } else if ( $op === '--' || $op === 'dec' ) {
            $value = $value1 - 1;
        } else if ( $op === '*' || $op === 'mul' ) {
            $value = $value1 * $value2;
        } else if ( $op === '/' || $op === 'div' ) {
            if ( $value2 == 0 ) return;
            $value = $value1 / $value2;
        } else if ( $op === '%' || $op === 'mod' ) {
            $value1 = floor( $value1 );
            $value2 = floor( $value2 );
            if ( $value2 == 0 ) return;
            $value = $value1 % $value2;
        }
        return $value;
    }

/**
 * Strip PHP tags from a input content.
 */
    static function strip_php ( $php ) {
        list( $tokens, $res, $in_php ) = [ token_get_all( $php ), '', false ];
        foreach ( $tokens as $token ) {
            list( $id, $str ) = is_string( $token ) ? ['', $token ] : $token;
            if (!$in_php ) {
                $in_php = $id === T_OPEN_TAG || $id === T_OPEN_TAG_WITH_ECHO;
                if ( $in_php === false ) $res .= $str;
            } else {
                if ( $id === T_CLOSE_TAG ) $in_php = false;
            }
        }
        return $php !== $res ? self::strip_php( $res ) : $res;
    }

    function _eval ( $out ) {
        $this->old_params = $this->old_params;
        $this->old_vars = $this->old_vars;
        ob_start();eval( '?>' . $out ); $out = ob_get_clean();
        if ( $err = error_get_last() )
        $this->errorHandler( $err['type'], $err['message'], $err['file'], $err['line'] );
        return $out;
    }

    function errorHandler ( $errno, $errmsg, $f, $line ) {
        if ( $tmpl = $this->template_file ) $errmsg = " $errmsg( in {$tmpl} )";
        $msg = "{$errmsg} ({$errno}) occured( line {$line} of {$f} ).";
        if ( $this->logging && !$this->log_path ) $this->log_path = PAMLDIR . 'log' . DS;
        if ( $this->logging ) error_log( date( 'Y-m-d H:i:s T', time() ) .
            "\t" . $msg . "\n", 3, $this->log_path . 'error.log' );
    }
}