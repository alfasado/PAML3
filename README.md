# PAML3 : PHP Alternative Markup Language version 3

* バージョン    3\.0
* 作成者     Alfasado Inc\. &lt;webmaster@alfasado\.jp&gt;
* © 2021 Alfasado Inc\. All Rights Reserved\.

## 動作環境

* 動作環境: PHP7\.2以上
* 文字コードは UTF\-8のみをサポート

## 概要

- 軽量、高速。静的サイトジェネレータとして利用可能。
- PowerCMS X形式、Mustache互換、Twig互換、Smarty互換\(※\)のタグ・デリミタを選択可能。
- プロパティ「autoescape」を指定することでファンクションタグの出力を自動エスケープに対応\(rawモディファイアの指定のないものすべてを自動エスケープ\)。
- テンプレートの継承、インクルード、インクルードブロックのサポート。
- Smarty2\(BC\)プラグインと互換性があり、プラグインの作成や追加で機能拡張が可能。

※ 選択できるのはタグの記法、タグのデリミタであって、PowerCMS X以外の形式では各々のテンプレートエンジンと完全な互換性はありません。  
※ PAMLはタグのパースに DomDocumentを利用するため、以下の制限事項があります。

- 属性値に「&lt;」「&gt;」「=」は指定できません。これらの文字を含めたい場合は一度変数に格納して「$」を先頭につけて渡す必要があります。
- 一つのタグの中に同一のモディファイアを複数回指定できません。
- モディファイアを複数指定するときは、パイプ「|」ではなく、半角空白で区切ります。「=」を含まないタグに限り、Twig互換、Smarty互換のときはパイプ「|」で繋ぐことができます。
- ifタグの testモディファイアに指定する以外は式は利用できません。

## クイックスタート

1. PHP Markdownのインストール

    cd PAML/plugins/lib
    composer require michelf/php-markdown

2. PAML/examples/以下のいずれかのファイルを編集します。変数 end\_point の部分を取得したい listエンドポイントに設定します。

        $end_point = 'https://powercmsx.jp/api/v1/1/entry/list.json?cols=title,text,text_format,basename,categories,Path,Permalink&limit=10';

3. CLIまたは Webから当該のプログラムを実行します。

        php powercmsx.php

4. contentsディレクトリに静的なHTMLファイルが生成されていることを確認してください。

## サンプル

※ プロパティ「compatible」には「Twig」「Mustache」「Smarty」が指定可能です。プロパティ「prefix」に「mt」を指定するとPowerCMS \(X\)互換の記法となります。

### twig\.php

    <?php
        require_once( 'class.paml3.php' );
        $base_path = './contents/';
        $end_point = 'https://powercmsx.jp/api/v1/1/entry/list.json?cols=title,text,text_format,basename,categories,Path,Permalink&limit=10';
        $ctx = new PAML ();
        // Set to Twig compatible delimiter.
        $ctx->compatible = 'Twig';
        $ctx->force_compile = false;
        $ctx->compile_dir = './compiled/';
        $ctx->allow_fileput = true;
        $ctx->use_plugin = true;
        $ctx->init();
        $params = ['base_path' => $base_path, 'end_point' => $end_point, 'sapi' => php_sapi_name() ];
        echo $ctx->build_page( 'tmpl/twig.tpl', $params );

### tmpl/twig.tpl

    {% extends file=extends/twig.tpl %}

    {% let _title='Generating static files...' %}

    {% block name="title" %}{{ _title|remove_html }}{% endblock %}

    {% block name="body" %}

      {# Fetch JSON from API. #}
  
      {% fetch url=$end_point from_json=results %}

      {{ results.items set=entries }}

      {% for entry in entries %}

        {# Set the local variable in loop. #}

        {% let _title=$entry.title %}

        {# Build each HTML. #}

        {% include file=includes/twig.tpl set=content %}

        {# Generate the path. #}

        {% set path %}{{ base_path }}{% if entry.categories is defined %}{{ entry.categories.0.Path }}/{% endif %}{{ entry.basename }}.html{% endset %}

        {# Output the file. #}

        {% fileput path=$path contents=$content set=result %}

        {# Display or stdout the results. #}

        {% if name=__first__ %}
        {% if name=sapi ne=cli %}<ul>{% endif %}
        {% endif %}
          {% if name=sapi ne=cli %}<li>{% endif %}
          {% if name=result %}
    Generated the entry '{{ _title|escape }}' to file '{{ path }}'.
          {% else %}
    Failed generate the entry '{{ _title|escape }}' to file '{{ path }}'.
          {% endif %}
          {% if name=sapi ne=cli %}</li>{% endif %}
        {% if name=__last__ %}
        {% if name=sapi ne=cli %}</ul>{% endif %}
        {% endif %}

      {% endfor %}

      {# Get the variable out of the loop. #}
      <p>{{ _title }} done.</p>

    {% endblock %}

### extends/twig.tpl

    {% if name="sapi" ne="cli" %}<html>
    <head>
      <title>{% block name=title %}{% endblock %}</title>
    </head>
    <body>
    {% else %}{% block name=title %}{% endblock %}
    {% endif %}{% block name=body regex_replace="'/^%s+$/um',''" remove_blank="1" %}{% endblock %}
    {% if name="sapi" ne="cli" %}</body>
    </html>{% endif %}

### includes/twig.tpl

    {% for regex_replace="'/^%s+$/um',''" remove_blank="1" %}
    <!DOCTYPE html>
    <html lang="ja">
    <head>
    <meta charset="utf-8">
    <title>{{ entry.title|remove_html|escape }}</title>
    </head>
    <body>
    <h1>{{ entry.title }}</h1>

    <div>{{ entry.text convert_breaks="$entry.text_format" }}</div>

    {% for category in entry.categories %}
      {% if name=__first__ %}
      <h2>Categories</h2>
      <ul>{% endif %}
        <li><a href="{{ category.Permalink }}">{{ category.label }}</a></li>
      {% if name=__last__ %}</ul>{% endif %}
    {% endfor %}

    </body>
    </html>
    {% endfor %}

## テンプレート記法

テンプレート・タグは大文字小文字を考慮せずに記述できます。タグの先頭と最後に'$' を書いてもよく、
接頭子の後に':'を付与することができます。ファンクションタグ末尾の"/"は省略可能です。  
引数の不要なモディファイアでは、属性値を省略できます。

### ファンクションタグ\(タグ属性とモディファイア\)

    {{ var name="name" escape }} , {{ var name="name" escape="1" }},
    {{ Var name="name" escape / }}, {{ Var name="name" escape="1" / }},
    {{ var name="name" escape="1" / }}, {{ $var name="name" escape$ }}...

### インライン変数

    {% let name="value" %}
    {{ name }} は {{ var name="name" }} と等価です。
        配列の出力は以下のように行います。
    {{ array.key1.key2 }}

以下のテンプレートは意図通りに動作しません。インライン変数よりもテンプレート・タグが優先されるからです。

    {% let var="value" %}
    {{ $var }} <= これは、OK
    {{ var }}  <= これは、NG('var'ファンクションタグと解釈されます)

    {% let variable="value" %}
    {{ $variable }} <= これは、OK
    {{ variable }}  <= これは、NG('var'ファンクションタグと解釈されます)
    
このような問題を解消するためには、変数の頭に何らかの接頭子\('\_'など\)を付与するか、明示的に変数名の先頭に$を付与するとよいでしょう。

## テンプレート・タグ

### ブロックタグ

    {% tagname attr1="1" attr2="2" %}
        ...
    {% endtagname %}
    
    または(Mustache互換)、
    
    {{#tagname attr1="1" attr2="2"}}
        ...
    {{/tagname}}
    
    または(Smarty互換)、

    {tagname attr1="1" attr2="2"}
        ...
    {/tagname}
    
    または(PowerCMS互換)、
    
    <mt:tagname>
    
    </mt:tagname>

#### 予約変数

ignore, setvars, sethashvars, capture, setvarblock, assignvars, setvartemplate以外のブロックタグではループの回数に応じて自動的に以下の変数がセットされます。

* \_\_first\_\_   : ループの初回
* \_\_last\_\_    : ループの最終回
* \_\_odd\_\_     : ループの奇数回
* \_\_even\_\_    : ループの偶数回
* \_\_counter\_\_ : ループのカウンター
* \_\_index\_\_   : ループのカウンター\(0から始まる\)
* \_\_value\_\_   : 配列またはハッシュの値\(タグ属性'var'の指定のない場合\)
* \_\_total\_\_   : 配列またはオブジェクトの数

また、これらのタグではループ対象の配列が空の時、ブロック中の else ブロックの内容を出力します。

    {% foreach entries as entry %}
        {{ entry.title }}
    {% else %}
        記事が見つかりません。
    {% endforeach %}

### 条件タグ

    {% if attr1="1" attr2="2" %}
        ...
    {% elseif attr1="1" attr2="2" %}
        ...
    {% else %}
        ...
    {% endif %}
    
    または、

    {% if attr1="1" attr2="2" %}
        ...
    {% elseif attr1="1" attr2="2" %}
        ...
    {% else %}
        ...
    {% /if %}

### タグ属性とモディファイアの指定

文字列、変数\($から始まるか\.を含む\)、または配列\(CSV\)で値を渡します。

    value    : {{ var name="name" }}
    array    : {{ var name="array[key]" }}
          or : {{ var name="array.key1.key2" }}
    variable : {{ var name="$variable" }}
          or : {{ var name="$variable[key]" }}
    request variable :
               {{ var name="request.name" escape }}
                            (HTTPリクエスト変数'$_REQUEST'から値を取得します。)
    csv      : {{ var name="name" replace="'value1','value\'2'" }}
          or : {{ var name="name" replace="value1,value2" }}
                            (<=タグの実装により)

    CSVのフィールドの囲み文字、フィールドの区切り文字はクラスのプロパティです(変更可能です)。
        規定値 : $csv_enclosure = "'" (囲み文字)
                $csv_delimiter = ',' (区切り文字)

### ファンクションタグ

### var

変数から値を呼び出します。
タグ属性 value を指定した場合は setvar ファンクションタグと同じ動作になります。

#### タグ属性

* name\(必須\): 変数名  
* value : 値をセットする場合、その値
* append : 既存の変数の後ろに、指定した値を連結します
* prepend : 既存の変数の前に、指定した値を連結します

もしくは、上記以外の属性・値を指定することで、その属性名の変数に値をセットできます。

    {% var _title="文書のタイトル" %}
    {{ _title }}

### let

属性名の変数に値をセットします。変数は親ブロックの中のみで保持されるローカル変数です。

    {% let _title="文書のタイトル" %}
    {{ _title }}

### set

属性名のグローバル変数に値をセットします。

    {% set _title="文書のタイトル" %}
    {{ _title }}

### setvar

テンプレート変数に値を設定します。

#### タグ属性

* name\(必須\): 変数名
* value : セットする値
* append : 既存の変数の後ろに、指定した値を連結します
* prepend : 既存の変数の前に、指定した値を連結します
* function : 配列の場合に実行する関数名\('push'もしくは'unshift'\)
* key : ハッシュのキー名
* op : 変数に対して四則計算を行う
* scope : 'local' または 'global' \(省略時global\)

### assign

'setvar'の別名です。

#### タグ属性

* var\(必須\) : 変数名
* value : セットする値
* append : 既存の変数の後ろに、指定した値を連結します
* prepend : 既存の変数の前に、指定した値を連結します
* function : 配列の場合に実行する関数名\('push'もしくは'unshift'\)
* key : ハッシュのキー名
* op : 変数に対して四則計算を行う

### unsetvar

変数を unsetします。

#### タグ属性

* name\(必須\): 変数名

### unset

変数を unsetします。nameには「\.」で連結して配列の特定のキーを指定できます。

* name\(必須\): 変数名

### gethashvar

変数名と値を指定してキーを出力します。ハッシュがネストされている場合は複数のキーを繋げて配列で指定可能です。

#### タグ属性

* name : 変数の名前
* key : 変数のキー\(文字列または配列\)
* index : 'key'のエイリアス

### break

現在のループを終了します。

#### タグ属性

* close : HTMLの閉じタグを指定します

### math

テンプレート内で演算を行います。

#### タグ属性

* eq : 計算式\(参考: [https://www.smarty.net/docsv2/ja/language.function.math.tpl](https://www.smarty.net/docsv2/ja/language.function.math.tpl)\)

### date

現在の日時を表示します。

#### タグ属性

* format : 日付のフォーマット
* format_name : 指定した書式でフォーマットした日付を返す('rfc822'など)
* ts : Ymdhis形式のタイムスタンプを指定します。
* unixtime : Ymdhis形式のタイムスタンプの代わりに Unix Timestampを指定します。もしくは Unix Timestampを取得します。
* utc : 出力する日付を協定世界時にします。
* relative :
  * 1=日付の現在の日付を含む前後一週間の場合、直前、N分前、N時間前 何日前というように表示します。範囲外の日付は現在の日付 \(例: 1月15日\) を、ts モディファイアで日付を設定していればその日付を出力します。
  * 2=2種類の表記で経過時間を表示します。直前、6日,1時間前、時間,4分前 といった内容で出力します。範囲外の日付は現在の日付 \(例: 1月15日\) を、ts モディファイアで日付を設定していればその日付を出力します。
  * 3=2種類の表記で経過時間を表示しますが『〜前』といった表現を追加せず単純に 6日,1時間 といった内容で出力します。
* language : 'ja'を指定すると「D」「l」を日本語表記の曜日にします。

### count

変数が配列の場合にカウントを表示します。

* name : 変数の名前

### query

エリストリングを出力します。

#### タグ属性

* excludes : 除外するパラメーター名の文字列または配列
* values : 配列のキーを空にして連結した結果を返します。

### constant

指定した定数の値を返します。

#### タグ属性

* name : 定数の名前

### arrayshuffle

配列をランダムにシャッフルします。

* name : 変数の名前

### arrayslice

配列の一部を展開し\(切り取り\)ます。

* name : 変数の名前
* offset : N目からリストを開始する\(Nは正の整数\)
* length : 取得する件数\(数値\)
* limit : 'length'のエイリアス

### arrayrand

配列から1つ以上の値をランダムに抽出します。

* name : 変数の名前
* num : 抽出する件数
* limit : 'num'のエイリアス
* length : 'num'のエイリアス

### fetch

指定のURL\(パス\)のコンテンツを取得します。

* url : 取得するURL
* path : ローカルディスク上のファイル・パス\(環境変数'allow\_fileget'の指定が必要\)
* ua : User-Agentヘッダ文字列
* to\_encoding : 変換後の文字エンコーディング\(デフォルトは'UTF\-8'\)
* method : HTTPリクエストメソッド\(デフォルトは'GET'\)
* content\_type : Content\-Typeヘッダ文字列
* access\_token : access\_tokenヘッダ文字列
* headers : その他のHTTPヘッダ\(配列または文字列\)
* content : HTTPリクエストボディ\(文字列または配列\)

### fileput

データをファイルへ書き込む\(環境変数'allow\_fileput'の指定が必要\)

* path : ファイルへのパス
* contents : 書き込むデータ

### unlink

ファイルを削除する\(環境変数'allow\_unlink'の指定が必要\)

* path : ファイルへのパス

### trans

値を翻訳します。 参考 =&gt; ./locale/ja.json

#### タグ属性

* phrase : 翻訳する文字列
* params : sprintf 関数を使ってフォーマット整形する場合、渡す値  
　　　　　複数の値を指定するときは CSVを指定します
* component : プラグイン・クラスの名前

### ldelim

タグの開始文字列を出力します\(規定値 '\{\{'\)\.
### rdelim

タグの終了文字列を出力します\(規定値 '}}'\)\.

### vardump

変数の内容をダンプします。

* preformat : &lt;pre&gt; 〜 &lt;/pre&gt;で囲んで出力する
* name : 変数名を指定する
* key : 'name'属性の指定があり変数があり配列の時、変数のキーを指定する

## ブロックタグ

### block

囲まれたブロックを1回だけ処理して内容を出力します。  
name 属性が指定されていて、変数に値が格納されている場合はそちらが出力されます。

#### タグ属性

* name    : 結果を出力せずに変数に結果を格納します
* append  : コンテンツを親テンプレートの block に追記します
* prepend : コンテンツを親テンプレートの block の前に置きます。
* その他の値を指定すると、ブロック内部でのみ利用できるローカル変数になります。

### loop

タグ属性 name または from で指定された配列またはオブジェクトをループ出力します。

#### タグ属性

* name : 配列・ハッシュ名
* from : 'name'のエイリアス
* key : 配列またはハッシュのキーを格納する変数名\(デフォルトは'\_\_key\_\_'\)
* item : 配列またはハッシュの値を格納する変数名\(デフォルトは'\_\_value\_\_'\)
* unique : 配列から重複した値を削除
* shuffle : 配列をランダムにシャッフルします。
* offset : N目からリストを開始する\(Nは正の整数\)
* limit : 表示する件数\(数値\)
* sort\_by : 'key'もしくは'value'を指定、オプションとして'numeric'と'reverse'が指定可能\(カンマ区切りで指定\)
* sort\_order : 'ascend\(昇順\)' または 'descend\(降順\)'
* glue : 繰り返し処理の際に指定された文字列で各ブロックを連結する

### foreach

'loop'の別名です。loopと同じ属性が指定可能ですが、以下のような書き方で配列を変数に格納しながらループ出力することが可能です。

    {% foreach entries as entry %}
        {{ entry.title }}
    {% endforeach %}

### for

指定された値の間、ブロックを繰り返し出力します。  

#### タグ属性

* from : ループの開始位置\(デフォルトは0\)
* to : ループの終了位置\(デフォルトは1\)
* start : 'from'のエイリアス
* end : 'to'のエイリアス
* loop : 'to'のエイリアス
* increment : ループカウンターの増分\(デフォルトは1\)
* step : 'increment'のエイリアス
* var : 値を格納する変数名\(デフォルトは'\_\_value\_\_'\)
* glue : 繰り返し処理の際に指定された文字列で各ブロックを連結する

また、以下のような書き方で配列を変数に格納しながらループ出力することが可能です。

    {% for entry in entries %}
        {{ entry.title }}
    {% endfor %}

### section

'for'の別名です。

### ignore

このブロックの中は出力されません\(テンプレート・コメント\)。
Twig互換モードのときは {# 〜 #}、Smarty互換モードのときは {\* 〜 \*} がコメントとなります。

### literal

ブロックの内容はビルドされず、 そのまま表示されます。Mustache, Twig互換モードのときは raw が利用できます。

### setvarblock

ブロックの内容を出力する代わりに変数に格納します。
これは、何らかのブロックタグに'setvar'モディファイアを指定した時の動作と同じです。

#### タグ属性

* name : 変数をセットする時の名称
* append : 既存の変数の後ろに指定した値を連結
* prepend : 既存の変数の前に指定した値を連結
* function : 配列の場合に実行する関数名\('push'もしくは'unshift'\)
* key : ハッシュのキー名
* scope : 'local' または 'global' \(省略時global\)

### capture

'setvarblock'の別名です。

### let

Mustache, Twig互換モードのときのみ利用可能です。

'setvarblock scope="local"'の別名です。

### set

Mustache, Twig互換モードのときのみ利用可能です。

'setvarblock'の別名です。

### setvartemplate

このブロックタグで囲んだテンプレートを変数に設定します。このタグでセットされたテンプレートは、'mt:var'タグで呼び出された時に評価されます。

#### タグ属性

* name : 変数をセットする時の名称

### setvars

各行ごとに記述された変数をまとめて設定します。キーと値の区切り文字は '='です。  
'name'属性を指定すると、その変数名に配列をセットします。

    {% setvars %}
    _url       =http://www.example.com/
    _site_name =PAML
    _site_desc ={{ var name="description" }}
    {% endsetvars %}

### assignvars

'setvars'の別名です。

### queries

クエリパラメタ\($_GET\)をループ出力します。

#### タグ属性

* var : 値を格納する変数名\(デフォルトは'\_\_value\_\_'\)
* excludes : 除外するキーの配列
* glue : 繰り返し処理の際に指定された文字列で各ブロックを連結する

## 条件タグ

### if

条件を満たした場合に内容を出力します。条件を満たさない場合に実行する場合は、unless 条件タグを使用するか、if 条件タグの中で else, elseif 条件タグを利用します。  
式を直接指定することはできません。式を評価するには testモディファイアを利用してください。

#### タグ属性

* name : 評価対象の変数名
* eq : 変数の値が属性値と同等である
* ne : 変数の値が属性値と同等でない
* not : 'ne'のエイリアス
* gt : 変数の値が属性値より大きい
* lt : 変数の値が属性値より小さい
* ge : 変数の値が属性値以上
* le : 変数の値が属性値以下
* like : 変数の値が属性値を含む
* match : 変数の値が正規表現にマッチする
* tag : タグを評価した値と比較するとき、そのタグ名
* \(tag attributes\) : 'tag'属性が指定されている時、そのタグを評価するための追加のタグ属性
* test : 式を評価する
* op : 変数に対して四則計算を行う

もしくは、値のある、なしだけの判断であれば、以下のように書くこともできます。

        {% if entry.categories is defined %}
            値あり
        {% endif %}
        
        {% if entry.categories %}
            値あり
        {% endif %}
        
        {% if entry.categories is not defined %}
            値なし
        {% endif %}

### else

if または unless ブロックの中で、条件に一致しなかったときにこのタグ以降の内容が出力されます。

### elseif

if または unless ブロックの中で、別の条件を指定する時にこの条件タグを利用します。if 条件タグと同じタグ属性が利用できます。

### unless

条件を満たさなかった場合に内容を出力します。if 条件タグと同じタグ属性が利用できます。

### ifinarray

'name'タグ属性で指定されたハッシュが存在する時にブロックが評価されます。

#### タグ属性

* array : 配列の名前
* name : 'array'のエイリアス
* value : 配列に含まれているか評価する値

### isarray

'name'タグ属性で指定された配列またはハッシュである場合にブロックが評価されます。	

#### タグ属性

* array : 配列の名前
* name : 'array'のエイリアス

### isset

'name'タグ属性で指定されたテンプレート変数が既に定義されている場合にブロックが評価されます。

#### タグ属性

* name : 変数の名前

## インクルードタグ

### include

現在のテンプレートに外部ファイルの内容を含めます。$include_paths プロパティに含まれていないディレクトリ配下のファイルを含めることはできません。

#### タグ属性

* file\(必須\) : インクルードするファイルのパス
* その他のタグ属性で指定した値はインクルードしたファイル内部でのみ利用できるローカル変数になります。

### includeblock

このタグはブロックタグのように記述します。  
include タグと同じくテンプレートモジュールを読み込みます。$include\_paths プロパティに含まれていないディレクトリ配下のファイルを含めることはできません。このタグで囲まれたコンテンツは、このファイルの中だけで利用できる変数'contents'にセットされます。

### extends

テンプレートの継承で親テンプレートを継承するときに使います。  
このタグは、テンプレートの最初の行に書かなければなりません。

* file\(必須\) : 継承するテンプレート・ファイルのパス

## モディファイア

### escape

値をHTMLエンティティに変換または指定の形式にエンコードします。

#### 属性値

* html, xml, js, json, json\_unescaped\_unicode, php, url, single\(既存のHTMLエンティティをエンコードしない\)のいずれかで、省略した場合は特殊文字がHTMLエンティティに変換されます。

### remove\_html

HTMLタグとPHPタグを削除します。

## sanitize

文字列からHTMLタグを取り除きます。

#### 属性値

* 許可するHTMLタグをカンマ区切りテキストで指定します。「1」を指定するとすべてのHTMLを削除します。

### encode\_url

RFC 3986に従ってURLをエンコードします。escape="url"と同等です。

### encode\_js

JavaScriptの文字列として扱えるように値をエスケープします。escape="js"と同等です。

### encode\_json

JSONの文字列として扱えるように値をエスケープします。escape="json"と同等です。

### encode\_php

PHPの文字列として扱えるように値をエスケープします。escape="php"と同等です。

### encode\_xml

値をXMLフォーマットに適した形式にエンコードします。escape="xml"と同等です。

### encode\_html

特殊文字がHTMLエンティティに変換されます。escape="html"と同等です。

### setvar

出力されるべき値を出力せずに変数に格納します。

#### 属性値

* 変数名

### assign

'setvar'の別名です。

### set

'setvar'の別名です。

### let

setvarと同じく変数に値をセットしますが、セットされる変数のスコープが親ブロック内のローカル変数となります。

### format\_ts

日付文字列をフォーマットします。

#### 属性値

PHPのdateファンクションに渡す引数の形式\('Y\-m\-d H:i:s'など\)

### upper\_case

文字列を大文字にします。

### lower\_case

文字列を小文字にします。

### trim

文字列の先頭および末尾にあるホワイトスペースを取り除きます。

### ltrim

文字列の最初から空白(もしくはその他の文字)を取り除きます。

### rtrim

文字列の最後から空白(もしくはその他の文字)を取り除きます。

### truncate

指定したキャラクタ数で値を切り捨てます。

参考 =&gt; [http://www.smarty.net/docsv2/ja/language.modifier.truncate.tpl](http://www.smarty.net/docsv2/ja/language.modifier.truncate.tpl) 
or [https://www.movabletype.jp/documentation/appendices/modifiers/trim_to.html](https://www.movabletype.jp/documentation/appendices/modifiers/trim_to.html)

### trim\_to

'truncate'の別名です。

### zero\_pad

指定した文字数になるよう、先頭の余白を0で埋めます。

### strip\_linefeeds

改行を削除します。

### merge\_linefeeds

連続した空行を1つにまとめます。

### sprintf

フォーマットされた文字列を返します。

### nl2br

改行文字の前に HTML の改行タグを挿入します。

### replace

検索文字列に一致したすべての文字列を置換します。

### regex\_replace

変数に対して正規表現による検索・置換を行います。  
正規表現は、PHPマニュアルの [preg\_replace\(\)](http://php.net/manual/ja/function.preg-replace.php) の構文を使用してください。

### wrap 

指定した文字数でテキストを改行文字で折り返します。

### trim\_space

1を指定すると、ホワイトスペースをトリミングします。
2を指定すると、改行文字をトリミングします。
3を指定した場合、その両方となります。

### to\_json

テンプレート変数を指定してJSON文字列を出力します。
            
### from\_json

JSON文字列デコードして指定した変数にセットします。

### add\_slash

文字列の最後が「/」でない場合のみ、末尾に「/」を追加します。

### count\_chars

文字列の長さを取得します。

#### 属性値

省略した場合はバイト数を、'1'が指定された場合は文字数をカウントします。

### split

文字列を属性値で区切って配列化します。

#### 属性値

* デリミタ\(境界文字列\)

### join

指定の文字で配列の要素を連結します。

#### 属性値

* 連結する文字

### format\_size

数字をGB\(ギガバイト\)、MB\(メガバイト\)、KB\(キロバイト\)、Byteいずれかの形式で出力します。

#### 属性値

* 小数点以下の桁数

### instr

文字列内で指定の文字列が最初に現れる位置を見つけます。\(strpos関数を使用します\)

#### 属性値

* 検索文字列

### mb\_instr

文字列内で指定の文字列が最初に現れる位置を見つけます。\(mb\_strpos関数を使用します\)

#### 属性値

* 検索文字列

### relative

URLをルート相対パスに変換します。

### numify

数値を3桁ごとに区切ります。

#### 属性値

* 小数点以下の桁数

### array\_pop

配列の末尾から要素を取り除き、その値を返します。

### default

値が空の場合に出力するテキストを指定します。

#### 属性値

* 代替テキスト

### normalize

値をノーマライズして返します。

#### 属性値

* normalizer\_normalize関数に渡す定数名

### preg\_quote

正規表現構文の特殊文字の前にバックスラッシュを挿入します。

#### 属性値

* デリミタ\(デフォルトは'/'\)

### remove\_blank

空行を削除します。

### increment

値を加算します。

#### 属性値

* 増分値\(デフォルトは'1'\)

### decrement

値を減算します。

#### 属性値

* 減分値\(デフォルトは'1'\)

### cast_to

型を変換します。

#### 属性値

* int, bool, float, double, real, string, array, object, unset, nullのいずれか

### eval

テンプレートをビルドします。

### translate

値を翻訳します。 参考 =&gt; ./locale/ja.json

## テンプレートの継承

継承機能は、オブジェクト指向プログラミングの考え方をテンプレートに導入したものです。  
子テンプレートの先頭で extendsタグによって指定された親テンプレートの name属性付きblockタグを子テンプレートで指定した blockタグの結果に置き換えます。  
参考 =&gt; [http://www.smarty.net/docs/ja/advanced.features.template.inheritance.tpl](http://www.smarty.net/docs/ja/advanced.features.template.inheritance.tpl)

## テンプレート・タグの実装

### ファンクションタグ

ファンクションタグは、次のように記述されるタイプのタグです。

    {{ tagname attr="name" }}
        または
    {% tagname attr="name" %}

#### パラメタ

* array   $args   : タグ属性の配列
* object  $ctx    : クラス PAML

メソッドの戻り値はテンプレート関数のタグの部分と置き換えられます\(例: &lt;paml:var&gt;\)。  
あるいは何も出力せずに単に他のタスクを実行する事ができます\(例: &lt;paml:setvar&gt;\)。

### ブロックタグ

ブロックタグは、次のように記述されるタイプのタグです。ブロックタグでは、出力される $content を戻り値に指定します。

    {% tagname attr1="1" attr2="2" %}
        ...
    {% endtagname %}

#### パラメタ

* array   $args     : タグ属性の配列
* string  &$content : \*1 
* object  $ctx      : クラス PAML
* boolean &$repeat  : \*2
* numeric $counter  : ループのカウンタ

\*1 $content にはテンプレートの出力結果がセットされます。  
$content は最初のループでは null、2回目以降のループではテンプレートブロックのコンテンツがセットされています。  

\*2 $repeat は最初のループでは true、2回目以降のループでは falseがセットされています。  
タグの中で $repeat を true にセットすると、 &lt;paml:block&gt; \.\.\. &lt;/paml:block&gt;
ブロック内は繰り返しビルドされ、$content パラメータに新しいブロックコンテンツが格納された状態で再び呼び出されます。

最もシンプルなブロックタグの実装例\(1度だけブロックがビルドされます\)。

    function some_block_tag ( $args, $content, $ctx, &$repeat, $counter ) {
        return ( $counter ) ? $content : null;
    }

### 条件タグ

条件タグは、次のように記述されるタイプのタグです。

    {% if attr1="1" attr2="2" %}
        ...
    {% elseif attr1="1" attr2="2" %}
        ...
    {% else %}
        ...
    {% endif %}

&lt;paml:else&gt; と &lt;paml:elseif&gt; が利用可能です。
条件タグは true か false のいずれかを返します。

#### パラメタ

* array   $args    : タグ属性の配列
* string  $content : 常に null
* object  $ctx     : クラス PAML
* boolean $repeat  : 常に true
* boolean $context : false を指定すると unless として動作します。

新たに条件タグを作成する際は、true、false を返すかわりに $ctx\-&gt;conditional\_if を返すことによって eq, ne, likeなどのタグ属性を利用できるようになります。

### モディファイア

モディファイアは、テンプレートの変数が表示される前または他のコンテンツに使用される前に適用される関数です。  
モディファイアは複数指定できますが、同じモディファイアを一つのテンプレート・タグに指定することはできません。

#### パラメタ

* string $str  : テンプレートの出力
* mixed  $arg  : 属性値
* object $ctx  : クラス PAML
* string $name : 呼び出されたモディファイアの名前

もしくは Smarty2\(BC\) スタイルのプラグインでは

* string $str  : テンプレートの出力
* mixed  $arg1 : 属性値1
* mixed  $arg2 : 属性値2\.\.\.

プラグイン内でモディファイア encode\_javascript を定義した場合、escape=&quot;javascript&quot; 指定で encode\_javascript=&quot;1&quot; と同等の結果が返ります。

## コールバックの実装

コールバックでは、\(出力を制御したいなどの特別な理由のない限り\)第一引数の値もしくは第一引数の値を加工したものを返す必要があります。

### input\_filter \( $content, $ctx \) または output\_filter \( $content, $ctx \)

#### パラメタ

* string $content : 入力ソース \(input\_filter\)もしくは 出力結果 \(output\_filter\)
* object $ctx     : クラス PAML

### pre\_parse\_filter\( $content, $ctx, $insert \)

DOMDocument::loadHTML がコールされる直前に呼び出されます。

#### パラメタ

* string $content : loadHTMLに渡されるコンテンツ
* object $ctx     : クラス PAML
* string $insert  : ダミー文字列\(DOMDocument::saveHTML で要素間の空白文字が消える問題への対策のため\)

### dom\_filter\( $dom, $ctx \)

DOMDocument::saveHTML がコールされる直前に呼び出されます。

#### パラメタ

* object $dom : クラス DOMDocument
* object $ctx : クラス PAML

### post\_compile\_filter\( $content, $ctx \)

DOMDocument::saveHTML がコールされた直後に呼び出されます。

#### パラメタ

* string $content : コンパイル済みのテンプレート
* object $ctx     : クラス PAML

## プラグイン、コールバック、テンプレートタグの登録

plugins/PluginID/class\.ClassName\.php にクラス「ClassName」の定義がある時、登録は自動的に行われます。

### $ctx->register\_component( $plugin, $path, $registry );

#### パラメタ

* object $plugin  : プラグインクラス
* string $path    : プラグインディレクトリのパス\( \_\_DIR\_\_\)
* array $registry : タグとコールバックの配列\(もしくは $registry をJSON形式のデータにして'config\.json'ファイルに保存できます\)

### $ctx->register\_tag( $tag\_name, $tag\_kind, $func\_name, $plugin );

#### パラメタ

* string $tag\_name  : タグ名
* string $tag\_kind  : タグの種類 \(function, block, block\_once, conditional include または modifier\)
* string $func\_name : クラスのメソッド名
* object $plugin     : プラグインクラス

### $ctx\->register\_callback\( $id, $type, $func\_name, $plugin \);

#### パラメタ

* string  $id         : コールバックID \(コールバックタイプごとにユニークであること\)
* string  $type       : コールバックタイプ \(input\_filter, output\_filter, loop\_filter, conditional\_filter or dom\_filter\)
* string  $func\_name : クラスのメソッド名
* object  $plugin     : プラグインクラス

## メソッド

### $ctx\->assign\( $name, $value \);

変数に値をセットします。

#### パラメタ

* mixed   $name      : セットする変数名もしくは変数の配列
* array   $value     : セットする値の配列

### $ctx\->stash\( $name, $value \);

変数の格納場所です。

#### パラメタ

* string  $name      : セットまたは取得する変数名
* mixed   $value     : セットする値

#### 戻り値

* mixed  $var        : 格納された値

### $ctx\->build\_page\( $path, $params, $cache\_id, $disp, $src \);

テンプレートをビルドし、出力するか結果を返します。

#### パラメタ

* string  $path      : テンプレートファイルのパス
* array   $params    : テンプレート変数にセットする値の配列
* string  $cache\_id : キャッシュID
* bool    $disp      : 結果を出力するかどうか
* string  $src       : ファイルの代わりに利用するテンプレートのソース文字列

#### 戻り値

* string  $content   : ビルドされた結果のテキスト

### $ctx\->fetch\( $path, $params, $cache\_id \);

テンプレートをビルドし、出力せずに値を返します。

#### パラメタ

* string  $path      : テンプレートファイルのパス
* array   $params    : テンプレート変数にセットする値の配列
* string  $cache\_id : キャッシュID

#### 戻り値

* string  $content   : ビルドされた結果のテキスト

### $ctx\->display\( $path, $params, $cache\_id \);

テンプレートをビルドし、出力して値を返します。

#### パラメタ

* string  $path      : テンプレートファイルのパス
* array   $params    : テンプレート変数にセットする値の配列
* string  $cache\_id : キャッシュID

#### 戻り値

* string  $content   : ビルドされた結果のテキスト

### $ctx\->render\( $src, $params, $cache\_id \);

ファイルからではなく、テンプレートのソースからビルドします。

#### パラメタ

* string  $src       : テンプレートのソース
* array   $params    : テンプレート変数にセットする値の配列
* string  $cache\_id : キャッシュID

#### 戻り値

* string  $content   : ビルドされた結果のテキスト

### $ctx\->build\( $src, $compiled = false \);

テンプレートのソースからビルドした値を返します。コンパイル結果をキャッシュしません。

#### パラメタ

* string  $src       : テンプレートのソース
* bool    $compiled  : 指定した場合、ビルド結果ではなくコンパイル後のPHPコードを返す

#### 戻り値

* string  $build     : ビルドされた結果のテキスト

### $ctx\->set\_loop\_vars\( $counter, $params \);

カウンタ値とループ対象の配列変数またはオブジェクトから予約変数に値をまとめてセットします \( '\_\_index\_\_', '\_\_counter\_\_', '\_\_odd\_\_','\_\_even\_\_', '\_\_first\_\_', '\_\_last\_\_', '\_\_total\_\_' \)。
#### パラメタ

* int     $counter   : ループのカウンタ値
* array   $params    : ループ対象の配列変数またはオブジェクト

### $ctx\->localize\( \[ 'name1', 'name2', \[ 'name3', 'name4' \] \] \);
### $ctx\->restore \( \[ 'name1', 'name2', \[ 'name3', 'name4' \] \] \);

変数のスコープをローカライズします。  
ブロックの初回 \( $counter == 0 \)で localize をコールし、ブロックの最後で restore をコールしてください。
引数には、対象の変数名を配列で指定します。
配列の中に配列で指定されたものは $ctx\-&gt;\_\_stash\[ 'vars' \]\[ $value \] が対象となり、文字列を指定した場合は $ctx\-&gt;stash\( $value \) が対象となります。

### $ctx\->get\_any\( $key \);

$local\_vars\[ $key \] と $vars\[ $key \] のいずれかに値が存在する時に、その値を受け取ります。

### $ctx\->setup\_args\( $args \);

タグ属性値を\(文字列、変数\($から始まるか\.を含む\)、または配列\(CSV\)に\)セットします。  
$advanced\_mode が trueの時は、各タグの実行時に自動的に呼ばれます。

### $ctx\->configure\_from\_json\( $json \);

プロパティをJSONファイルに記述してまとめてセットします。

#### パラメタ

* string  $json      : JSON ファイルのパス

## プロパティ\(初期値\)

### $vars\(\[\]\)

グローバル・テンプレート変数。

### $\_\_stash\(\[\]\)

$\_\_stash\['vars'\] は $varsの別名。

### $local\_vars\(\[\]\)

ブロックスコープ内のローカル変数。.

### $local\_params\(\[\]\)

ブロックスコープで主にループ処理に使われる変数やオブジェクト。

プロパティ $local\_vars と $local\_params は常にローカル変数となります。  
ブロックを抜ける時には、これらは自動的にブロックの直前の状態に戻されます。

### $prefix\('mt'\)

タグ接頭子。

### $tag\_block\(\['{%', '%}'\]\)

タグ開始文字列と終了文字列。

### $ldelim, $rdelim

$tag_block のエイリアス。

### $cache\_ttl\(86400\)

ページキャッシュの有効期限\(秒\)。

### $force\_compile\(true\)

リクエスト毎にテンプレートをコンパイルするかどうか。

### $caching\(false\)

ページキャッシュを生成・利用するかどうか。

### $compile\_check\(true\)

現在のテンプレートが最後に訪れた時から変更されている(タイムスタンプが異なる)かどうかをチェックします。

### $cache\_driver\(null\)

キャッシュドライバ\('Memcached'もしくはnull\)。  
null指定の場合はシンプルなファイルキャッシュが使われます。'Memcached'の利用については lib/cachedriver\.base\.php と lib/cachedriver\.memcached\.php が必要です。

    $ctx->cache_driver = 'Memcached';
    $ctx->memcached_servers = ['localhost:11211'];

### $csv\_delimiter\(':'\)

CSVフィールド区切り文字。

### $csv\_enclosure\("'"\)

CSVフィールド囲み文字。

### $autoescape\(false\)

trueを指定すると、ファンクションタグの出力を自動エスケープします\(rawモディファイアの指定のないものすべて\)。

### $debug\(false\)

1を指定すると、error\_reporting( E\_ALL )に設定し、2を設定するとエラーを画面出力します。3を指定するとコンパイル済みテンプレートを出力します。

### $includes\(\['txt', 'tpl', 'tmpl', 'inc', 'html'\]\)

インクルード・タグによってインクルードを許可するファイル拡張子の配列です。

## Smarty2(BC) タイプのプラグインのサポート

$ctx\->plugin\_compat = 'smarty\_'; 指定の時、

### ファンクションタグ

plugins/function\.&lt;prefix&gt;functionname\.php の中の関数  
smarty\_function\_&lt;prefix&gt;functionname が実行されます。

### ブロックタグ

plugins/block\.&lt;prefix&gt;blockname\.php の中の関数  
smarty\_block\_&lt;prefix&gt;blockname が実行されます。

### 条件タグ

plugins/block\.&lt;prefix&gt;ifconditionalname\.php の中の関数  
smarty\_block\_&lt;prefix&gt;ifconditionalname が実行されます。

### Modifier

plugins/modifier\.modifiername\.php の中の関数  
smarty\_modifier\_modifiername が実行されます。

## 多言語サポート

クラス PAML の language プロパティが適用されます。  
指定のない場合 $\_SERVER\[ 'HTTP\_ACCEPT\_LANGUAGE' \] から自動的に設定されます。

plugins/PluginID/locale/&lt;language&gt;\.json

### サンプル\(ja\.json\)
    {
    "Welcome to %s!":"%sへようこそ!"
    }

### テンプレート

    {{ trans phrase="Welcome to %s!" params="PAML" component="PAML" }}
