{if name="sapi" ne="cli"}<html>
<head>
  <title>{block name="title"}{/block}</title>
</head>
<body>
{else}{block name="title"}{/block}
{/if}{block name="body" regex_replace="'/^%s+$/um',''" remove_blank="1"}{/block}
{if name="sapi" ne="cli"}</body>
</html>{/if}