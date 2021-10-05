<mt:if name="sapi" ne="cli"><html>
<head>
  <title><mt:block name="title"></mt:block></title>
</head>
<body>
<mt:else><mt:block name="title"></mt:block>
</mt:if><mt:block name="body" regex_replace="'/^%s+$/um',''" remove_blank="1"></mt:block>
<mt:if name="sapi" ne="cli"></body>
</html></mt:if>