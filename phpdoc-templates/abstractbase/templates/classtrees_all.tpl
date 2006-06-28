{include file="header.tpl" top1=true}
<h2 class="class-name classtree-name">Class tree</h2>
{section name=classtrees loop=$classtrees}
	<b>{$packages[classtrees]}</b><br />
	{section name=classtree loop=$classtrees[classtrees]}
		{$classtrees[classtrees][classtree].class_tree}
	{/section}
{/section}
<hr />
<h2><a href="todolist.html">TODO list</a></h2>
<iframe src="todolist.html" frameboarder="0" border="0" style="width:100%;height:400px;border:none;"></iframe>
{include file="footer.tpl"}