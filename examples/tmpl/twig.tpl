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