<mt:for regex_replace="'/^%s+$/um',''" remove_blank="1">
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title><mt:var name="entry.title" remove_html escape></title>
</head>
<body>
<h1><mt:var name="entry.title"></h1>

<div><mt:var name="entry.text" convert_breaks="$entry.text_format"></div>

<mt:for category in entry.categories>
  <mt:if name=__first__>
  <h2>Categories</h2>
  <ul></mt:if>
    <li><a href="<mt:var name="category.Permalink">"><mt:var name="category.label"></a></li>
  <mt:if name=__last__></ul></mt:if>
</mt:for>

</body>
</html>
</mt:for>