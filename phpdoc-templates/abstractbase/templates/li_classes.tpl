{include file="header.tpl" top2=true}
<script type="text/javascript">
{literal}
	var hook_last_location = null;
	function hook_check_request() {
		if(document.location.hash == hook_last_location)
			return;
		hook_last_location = document.location.hash;
		var req = document.location.hash ? document.location.hash.substr(1) : "";
		if(req != "") {
			var as = document.getElementsByTagName("A");
			for(k in as) {
				if(as[k].name) {
					if(as[k].name == req) {
						as[k].style.backgroundColor = "#ff7";
						as[k].style.color = "#000";
					}
					else if(as[k].style && as[k].name && (!as[k].href)) {
						as[k].style.backgroundColor = "";
						as[k].style.color = "#333";
					}
				}
			}
		}
	}
	window.onload = function() {
		window.setInterval(hook_check_request, 100);
		for(k in document.links)
			document.links[k].onfocus = document.links[k].blur;
	}
{/literal}
</script>
<div class="bold-title">Classes</div>
<div class="package-details">
	<dl class="tree">
		{foreach key=package_name item=package from=$packages}
			{foreach key=subpackage_key item=subpackage from=$package}
				{if $subpackage.subpackage != ""}
					{section name=fe_classes loop=$subpackage.classes}{/section}
					{if $smarty.section.fe_classes.total > 0}
						<a name="{$subpackage.package}.{$subpackage.subpackage}" class="no"><b>{$subpackage.package}.{$subpackage.subpackage}</b></a><br />
						{foreach key=class_key item=class from=$subpackage.classes}
							<a href="{$class.link}" 
								target="right" 
								class="class {$class.type}"
								name="{$subpackage.package}.{$subpackage.subpackage}.{$class.title}" 
								title="{$class.access} {$class.type} {$subpackage.package}.{$subpackage.subpackage}.{$class.title}">{$class.title}</a><br />
						{/foreach}
						<br />
					{/if}
				{else}
					{section name=fe_classes loop=$subpackage.classes}{/section}
					{if $smarty.section.fe_classes.total > 0}
						<a name="{$subpackage.package}" class="no"><b>{$subpackage.package}</b></a><br />
						{foreach key=class_key item=class from=$subpackage.classes}
							<a href="{$class.link}" 
								target="right" 
								class="class {$class.type}"
								name="{$subpackage.package}.{$class.title}" 
								title="{$class.access} {$class.type} {$subpackage.package}.{$class.title}">{$class.title}</a><br />
						{/foreach}
						<br />
					{/if}
				{/if}
			{/foreach}
		{/foreach}
	</dl>
</div>
</body>
</html>
