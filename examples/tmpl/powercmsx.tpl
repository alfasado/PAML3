<mt:extends file="extends/powercmsx.tpl">

<mt:let _title="Generating static files...">

<mt:block name="title"><mt:var name="_title" remove_html></mt:block>

<mt:block name="body">

  <mt:ignore>Fetch JSON from API.</mt:ignore>

  <mt:fetch url="$end_point" from_json="results">

  <mt:results.items setvar="entries">

  <mt:foreach entries as entry>

    <mt:ignore>Set the local variable in loop.</mt:ignore>

    <mt:let _title="$entry.title">

    <mt:ignore>Build each HTML.</mt:ignore>

    <mt:include file="includes/powercmsx.tpl" set="content">

    <mt:ignore>Generate the path.</mt:ignore>

    <mt:setvarblock name="path"><mt:var name="base_path"><mt:if name="entry.categories"><mt:var name="entry.categories.0.Path">/</mt:if><mt:var name="entry.basename">.html</mt:setvarblock>

    <mt:ignore>Output the file.</mt:ignore>

    <mt:fileput path="$path" contents="$content" setvar="result">

    <mt:ignore>Display or stdout the results.</mt:ignore>

    <mt:if name="__first__">
    <mt:if name="sapi" ne="cli"><ul></mt:if>
    </mt:if>
      <mt:if name="sapi" ne="cli"><li></mt:if>
      <mt:if name="result">
Generated the entry '<mt:var name="_title" escape>' to file <mt:var name="path">'.
      <mt:else>
Failed generate the entry '<mt:var name="_title" escape>' to file <mt:var name="path">'.
      </mt:if>
      <mt:if name="sapi" ne="cli"></li></mt:if>
    <mt:if name="__last__">
    <mt:if name="sapi" ne="cli"></ul></mt:if>
    </mt:if>

  </mt:foreach>

  <mt:ignore>Get the variable out of the loop.</mt:ignore>
  <p><mt:var name="_title"> done.</p>

</mt:block>