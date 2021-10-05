{{extends file="extends/mustache.tpl"}}

{{assign var="_title" value="Generating static files..."}}

{{#block name="title"}}{{_title remove_html}}{{/block}}

{{#block name="body"}}

  {{! Fetch JSON from API. }}

  {{fetch url="$end_point" from_json="results"}}

  {{results.items assign="entries"}}

  {{#foreach from="entries"}}

    {{! Set the local variable in loop. }}

    {{let _title="$title"}}

    {{! Build each HTML. }}

    {{include file="includes/mustache.tpl" assign="content"}}

    {{! Generate the path. }}
    
    {{#capture name="path"}}{{base_path}}{{#categories}}{{categories.0.Path}}/{{/categories}}{{basename}}.html{{/capture}}

    {{! Output the file. }}

    {{fileput path="$path" contents="$content" assign="result"}}

    {{! Display or stdout the results. }}

    {{#__first__}}
    {{#if name="sapi" ne="cli"}}<ul>{{/if}}
    {{/__first__}}
      {{#if name="sapi" ne="cli"}}<li>{{/if}}
      {{#result}}
Generated the entry '{{_title|escape}}' to file '{{path}}'.
      {{/result}}
      {{^result}}
Failed generate the entry '{{_title|escape}}' to file '{{path}}'.
      {{/result}}
      {{#if name="sapi" ne="cli"}}</li>{{/if}}
    {{#__last__}}
    {{if name="sapi" ne="cli"}}</ul>{{/if}}
    {{/__last__}}

  {{/foreach}}

  {{! Get the variable out of the loop. }}
  <p>{{_title}} done.</p>

{{/block}}