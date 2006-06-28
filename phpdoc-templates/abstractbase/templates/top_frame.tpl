<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>{$title}</title>
		<link rel="stylesheet" href="{$subdir}media/stylesheet.css" />
		<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'/>
	</head>
	<body class="package-index">
		<h2>{$title}</h2>
		
		{if count($ric) >= 1}
			<dt>RICs</dt>
			<dd>
			{assign var="last_ric_name" value=""}
			{section name=ric loop=$ric}
				<dd><a href="{$ric[ric].file}" target="right">{$ric[ric].name}</a><dd/>
			{/section}
			</dd>
		{/if}

		{if count($packages) > 1}
			{if count($ric) >= 1}<br/><br/>{/if}
			<div class="package-title">All classes</div>
			<div class="package-details">
				<dl class="tree">
				{assign var="last_package_name" value=""}
				{section name=p loop=$packages}
					<dd><a href="li_all.html#{$packages[p].title}" target="left_bottom">{$packages[p].title}</a></dd>
				{/section}
				</dl>
			</div>
		{/if}

	</body>
</html>