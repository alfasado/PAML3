{% if name="sapi" ne="cli" %}<html>
<head>
  <title>{% block name=title %}{% endblock %}</title>
</head>
<body>
{% else %}{% block name=title %}{% endblock %}
{% endif %}{% block name=body regex_replace="'/^%s+$/um',''" remove_blank="1" %}{% endblock %}
{% if name="sapi" ne="cli" %}</body>
</html>{% endif %}