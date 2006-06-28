{include file="header.tpl" top2=true}
<div class="bold-title">Functions</div>
<div class="package-details">
	<dl class="tree">
		{foreach item=package from=$packages}
			{foreach item=subpackage from=$package}
				{foreach item=func from=$subpackage.functions}
					<a href="{$func.link}" target="right" 
						name="{$subpackage.package}.{$subpackage.subpackage}::{$func.title}" 
						title="function {$func.title}() in package {$subpackage.package}.{$subpackage.subpackage}">{$func.title}</a><br />
				{/foreach}
			{/foreach}
		{/foreach}
	</dl>
</div>
</body>
</html>
