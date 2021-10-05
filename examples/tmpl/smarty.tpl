{extends file="extends/smarty.tpl"}

{assign var="_title" value="Generating static files..."}

{block name="title"}{$_title remove_html}{/block}

{block name="body"}

  {* Fetch JSON from API. *}

  {fetch url="$end_point" from_json="results"}

  {results.items assign="entries"}

  {foreach from="entries"}

    {* Set the local variable in loop. *}

    {let _title="$title"}

    {* Build each HTML. *}

    {include file="includes/smarty.tpl" assign="content"}

    {* Generate the path. *}
    
    {capture name="path"}{$base_path}{if name="categories"}{$categories.0.Path}/{/if}{$basename}.html{/capture}

    {* Output the file. *}

    {fileput path="$path" contents="$content" assign="result"}

    {* Display or stdout the results. *}

    {if name="__first__"}
    {if name="sapi" ne="cli"}<ul>{/if}
    {/if}
      {if name="sapi" ne="cli"}<li>{/if}
      {if name="result"}
Generated the entry '{$_title|escape}' to file '{$path}'.
      {else}
Failed generate the entry '{$_title|escape}' to file '{$path}'.
      {/if}
      {if name="sapi" ne="cli"}</li>{/if}
    {if name="__last__"}
    {if name="sapi" ne="cli"}</ul>{/if}
    {/if}

  {/foreach}

  {* Get the variable out of the loop. *}
  <p>{$_title} done.</p>

{/block}