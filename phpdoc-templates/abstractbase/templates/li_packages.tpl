<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>{$title}</title>
		<link rel="stylesheet" href="{$subdir}media/stylesheet.css" />
		{literal}
		<!--[if lt IE 7]>
			<style type="text/css">a:link, a:visited { border-bottom:1px solid #d5d5d5; }</style>
		<![endif]-->
		{/literal}
		<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'/>
	</head>
	<body>
		<div class="bold-title">Packages</div>
		<div class="package-details">
			<dl class="tree">
				{foreach key=package_name item=package from=$packages}
					{foreach key=subpackage_key item=subpackage from=$package}
						{if $subpackage.subpackage != ""}
							{section name=fe_count loop=$subpackage.classes}{/section}
							{if $smarty.section.fe_count.total > 0}
								<a href="li_classes.html#{$subpackage.package}.{$subpackage.subpackage}" target="li_classes">{$subpackage.package}.{$subpackage.subpackage}</a><br />
							{/if}
						{else}
							{section name=fe_count loop=$subpackage.classes}{/section}
							{if $smarty.section.fe_count.total > 0}
								<a href="li_classes.html#{$subpackage.package}" target="li_classes">{$subpackage.package}</a><br />
							{/if}
						{/if}
					{/foreach}
				{/foreach}
			</dl>
		</div>
	</body>
</html>