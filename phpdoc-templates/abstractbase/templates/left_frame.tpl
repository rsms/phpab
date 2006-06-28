{include file="header.tpl" top2=true}
<div class="package-title">{$packages[0]} {$package}</div>
<div class="package-details">

	<dl class="tree">

		{section name=p loop=$info}
					
			{if $info[p].subpackage == ""}
				
				{if $info[p].tutorials}
					<dt class="folder-title">Tutorials/Manuals</dt>
					<dd>
					{if $info[p].tutorials.pkg}
						<dl class="tree">
						<dt class="folder-title">Package-level</dt>
						<dd>
						{section name=ext loop=$info[p].tutorials.pkg}
							{$info[p].tutorials.pkg[ext]}
						{/section}
						</dd>
						</dl><br/>
					{/if}
					
					{if $info[p].tutorials.cls}
						<dl class="tree">
						<dt class="folder-title">Class-level</dt>
						<dd>
						{section name=ext loop=$info[p].tutorials.cls}
							{$info[p].tutorials.cls[ext]}
						{/section}
						</dd>
						</dl><br/>
					{/if}
					
					{if $info[p].tutorials.proc}
						<dl class="tree">
						<dt class="folder-title">Function-level</dt>
						<dd>
						{section name=ext loop=$info[p].tutorials.proc}
							{$info[p].tutorials.proc[ext]}
						{/section}
						</dd>
						</dl><br/>
					{/if}
					</dd>
				{/if}
				{if $info[p].classes || $info[p].functions || $info[p].files}
					{if $info[p].classes}
						<!--dt class="folder-title">Classes</dt-->
						<dt class="sub-package">{$package}</dt>
						{section name=class loop=$info[p].classes}
							<dd><a href='{$info[p].classes[class].link}' target='right'>{$info[p].classes[class].title}</a></dd>
						{/section}<br/>
					{/if}
					{if $info[p].functions}
						<dt class="folder-title">Functions</dt>
						{section name=f loop=$info[p].functions}
							<dd><a href='{$info[p].functions[f].link}' target='right'>{$info[p].functions[f].title}</a></dd>
						{/section}<br/>
					{/if}
					{*{if $info[p].files}
						<dt class="folder-title">Files</dt>
						{section name=nonclass loop=$info[p].files}
							<dd><a href='{$info[p].files[nonclass].link}' target='right'>{$info[p].files[nonclass].title}</a></dd>
						{/section}<br/>
					{/if}*}
				<!--hr/-->
				{/if}
								
			{else}
				{if $info[p].tutorials}			
					<dt class="folder-title">Tutorials/Manuals</dt>
					<dd>
					{if $info[p].tutorials.pkg}
						<dl class="tree">
						<dt class="folder-title">Package-level</dt>
						<dd>
						{section name=ext loop=$info[p].tutorials.pkg}
							{$info[p].tutorials.pkg[ext]}
						{/section}
						</dd>
						</dl><br/>
					{/if}
					
					{if $info[p].tutorials.cls}
						<dl class="tree">
						<dt class="folder-title">Class-level</dt>
						<dd>
						{section name=ext loop=$info[p].tutorials.cls}
							{$info[p].tutorials.cls[ext]}
						{/section}
						</dd>
						</dl><br/>
					{/if}
					
					{if $info[p].tutorials.proc}
						<dl class="tree">
						<dt class="folder-title">Function-level</dt>
						<dd>
						{section name=ext loop=$info[p].tutorials.proc}
							{$info[p].tutorials.proc[ext]}
						{/section}
						</dd>
						</dl><br/>
					{/if}
					</dd>
				{/if}
				
				<dt class="sub-package">{$package}.{$info[p].subpackage}</dt>
				<dd>
					<dl class="tree">
						{if $info[p].subpackagetutorial}
							<div><a href="{$info.0.subpackagetutorialnoa}" target="right">{$info.0.subpackagetutorialtitle}</a></div><br/>
						{/if}
						{if $info[p].classes}
							<!--dt class="folder-title">Classes</dt-->
							{section name=class loop=$info[p].classes}
								<a href='{$info[p].classes[class].link}' target='right'>{$info[p].classes[class].title}</a><br/>
							{/section}<br/>
						{/if}
						{if $info[p].functions}
							<dt class="folder-title">Functions</dt>
							{section name=f loop=$info[p].functions}
								<dd><a href='{$info[p].functions[f].link}' target='right'>{$info[p].functions[f].title}</a></dd>
							{/section}<br/>
						{/if}
						{* {if $info[p].files}
							<dt class="folder-title">Files</dt>
							{section name=nonclass loop=$info[p].files}
								<dd><a href='{$info[p].files[nonclass].link}' target='right'>{$info[p].files[nonclass].title}</a></dd>
							{/section}<br/>
						{/if} *}
					</dl>
				</dd>
								
			{/if}
			
		{/section}
		
		<hr/>
		<!--dt class="sub-package">Description</dt-->
		<!--dd-->
			{if $hastodos}
				<a href="{$todolink}" target="right">TODO List</a><br />
			{/if}
			<a href='{$classtreepage}.html' target='right'>Class trees</a><br />
			<a href='{$elementindex}.html' target='right'>Index of elements</a><br />
		<!--/dd--><br/>
		
	</dl>
</div>
</body>
</html>
