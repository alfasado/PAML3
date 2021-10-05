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