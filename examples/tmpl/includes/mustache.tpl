{{#for regex_replace="'/^%s+$/um',''" remove_blank="1"}}
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>{{title remove_html|escape}}</title>
</head>
<body>
<h1>{{title}}</h1>
<div>{{text convert_breaks="$text_format"}}</div>

<div>
{{#foreach categories as category}}
  {{#__first__}}
  <h2>Categories</h2>
  <ul>{{/__first__}}
    <li><a href="{{category.Permalink}}">{{category.label}}</a></li>
  {{#__last__}}
  </ul>
  {{/__last__}}
{{/foreach}}
</div>

</body>
</html>
{{/for}}