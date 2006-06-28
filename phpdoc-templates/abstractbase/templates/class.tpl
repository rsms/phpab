<!-- class.tpl -->
{include file="header.tpl" top3=true}
<div style="float:right">
	<a href="../{if $subpackage}../{/if}?{$package}{if $subpackage}/{$subpackage}{/if}/{$page}" target="_top">Frames</a>
</div>
<div class="class-package-small">{$package}{if $subpackage}.{$subpackage}{/if}</div>
<h2 class="class-name">{if $is_interface}Interface{else}Class{/if} {$class_name}</h2>

<a name="sec-description"></a>
<div class="info-box">
	<hr />
	<div class="nav-bar">
		{* if $children || $vars || $ivars || $methods || $imethods || $consts || $iconsts }
			<span class="disabled">Description</span> |
		{/if *}
		{if $children}
			<a href="#sec-descendents">Descendents</a>
			{if $vars || $ivars || $methods || $imethods || $consts || $iconsts}|{/if}
		{/if}
		{if $vars || $ivars}
			{if $vars}
				<a href="#sec-var-summary">Vars</a> (<a href="#sec-vars">details</a>)
			{else}
				<a href="#sec-vars">Vars</a>
			{/if}
			{if $methods || $imethods}|{/if}
		{/if}
		{if $methods || $imethods}
			{if $methods}
				<a href="#sec-method-summary">Methods</a> (<a href="#sec-methods">details</a>)
			{else}
				<a href="#sec-methods">Methods</a>
			{/if}			
		{/if}
		{if $consts || $iconsts}
			{if $consts}
				<a href="#sec-const-summary">Constants</a> (<a href="#sec-consts">details</a>)
			{else}
				<a href="#sec-consts">Constants</a>
			{/if}			
		{/if}
	</div>
	<div class="info-box-body">
        {include file="docblock.tpl" type="class" sdesc=$sdesc desc=$desc}
		
		<p class="notes">
			Located in <a class="field" href="{$page_link}">{$source_location}</a> (line <span class="field">{if $class_slink}{$class_slink}{else}{$line_number}{/if}</span>)
		</p>
		
		{if $tutorial}
			<hr class="separator" />
			<div class="notes">Tutorial: <span class="tutorial">{$tutorial}</span></div>
		{/if}
		
		<pre>{section name=tree loop=$class_tree.classes}{$class_tree.classes[tree]}{$class_tree.distance[tree]}{/section}</pre>
	
		{if $conflicts.conflict_type}
			<hr class="separator" />
			<div><span class="warning">Conflicts with classes:</span><br /> 
			{section name=me loop=$conflicts.conflicts}
				{$conflicts.conflicts[me]}<br />
			{/section}
			</div>
		{/if}
	</div>
</div>

{if $implements || $children}
	<div class="info-box">
		<hr />
{/if}
{if $implements}
	<div class="info-box-title light">Implemented interfaces</div>
	<br clear="all" /><br />
	<div class="info-box-body">
		{foreach name=ifaces item=iface from=$implements}
			{$iface}{if !$smarty.foreach.ifaces.last}, {/if}
		{/foreach}
	</div>
{/if}
{if $children}
	<a name="sec-descendents"></a>
		<div class="info-box-title light">Direct known subclasses</div>
		<div class="nav-bar light">
			<a href="#sec-description">Description</a> |
			<span class="disabled">Descendents</span>
			{if $vars || $ivars || $methods || $imethods}|{/if}
			{if $vars || $ivars}
				{if $vars}
					<a href="#sec-var-summary">Vars</a> (<a href="#sec-vars">details</a>)
				{else}
					<a href="#sec-vars">Vars</a>
				{/if}
				{if $methods || $imethods}|{/if}
			{/if}
			{if $methods || $imethods}
				{if $methods}
					<a href="#sec-method-summary">Methods</a> (<a href="#sec-methods">details</a>)
				{else}
					<a href="#sec-methods">Methods</a>
				{/if}			
			{/if}
			{if $consts || $iconsts}
				{if $consts}
					<a href="#sec-const-summary">Constants</a> (<a href="#sec-consts">details</a>)
				{else}
					<a href="#sec-consts">Constants</a>
				{/if}			
			{/if}
		</div>
		<br clear="all" />
		<div class="info-box-body">
			{section name=kids loop=$children}
				{$children[kids].link}&nbsp;
				<span class="note">{if $children[kids].sdesc}
					{$children[kids].sdesc}
				{else}
					{$children[kids].desc}
				{/if}</span>
				<br />
			{/section}
		</div>
{/if}
{if $implements || $children}
	</div>
{/if}

{if $consts}
	<a name="sec-const-summary"></a>
	<div class="info-box">
		<div class="info-box-title">Class Constant Summary</span></div>
		<div class="nav-bar">
			<a href="#sec-description">Description</a> |
			{if $children}
				<a href="#sec-descendents">Descendants</a> |
			{/if}
			<span class="disabled">Constants</span> (<a href="#sec-consts">details</a>)
			{if $vars || $ivars}
				{if $vars}
					<a href="#sec-var-summary">Vars</a> (<a href="#sec-vars">details</a>)
				{else}
					<a href="#sec-vars">Vars</a>
				{/if} 
				|
			{/if}
			{if $methods || $imethods}
				| 
				{if $methods}
					<a href="#sec-method-summary">Methods</a> (<a href="#sec-methods">details</a>)
				{else}
					<a href="#sec-methods">Methods</a>
				{/if}			
			{/if}
		</div>
		<div class="info-box-body">
			<div class="const-summary">
				{section name=consts loop=$consts}
				<div class="const-title">
					<img src="{$subdir}media/images/Constant.png" alt=" " />
					<a href="#{$consts[consts].const_name}" title="details" class="const-name">{$consts[consts].const_name}</a> = <span class="var-default">{$consts[consts].const_value}</span>
				</div>
				{/section}
			</div>
		</div>
	</div>
{/if}

{if $vars}
	<a name="sec-var-summary"></a>
	<div class="info-box">
		<div class="info-box-title">Property Summary</span></div>
		<div class="nav-bar">
			<a href="#sec-description">Description</a> |
			{if $children}
				<a href="#sec-descendents">Descendents</a> |
			{/if}
			<span class="disabled">Properties</span> (<a href="#sec-vars">details</a>)
			{if $methods || $imethods}
				| 
				{if $methods}
					<a href="#sec-method-summary">Methods</a> (<a href="#sec-methods">details</a>)
				{else}
					<a href="#sec-methods">Methods</a>
				{/if}			
			{/if}
			{if $consts || $iconsts}
				{if $consts}
					<a href="#sec-const-summary">Constants</a> (<a href="#sec-consts">details</a>)
				{else}
					<a href="#sec-consts">Constants</a>
				{/if}			
			{/if}
		</div>
		<table border="0" cellspacing="0" cellpadding="2" width="100%">
			<tbody>
				{section name=vars loop=$vars}
					{assign var="static_type" value=""}
					{assign var="access_type" value=""}
					{if $vars[vars].tags}
						{section name=tags loop=$vars[vars].tags}
							{if $vars[vars].tags[tags].keyword == "static"}
								{assign var="static_type" value="static "}
							{elseif $vars[vars].tags[tags].keyword == "access" && $vars[vars].tags[tags].data != "public"}
								{assign var="access_type" value=$vars[vars].tags[tags].data}
							{/if}
						{/section}
					{/if}
					<tr class="method-sumaries {cycle values="evenrow,oddrow"}" valign="top">
						<td align="right" class="left-column"><code>{$access_type} {$static_type}<span class="method-result">{$vars[vars].var_type}</span></code></td>
						<td><code><a href="#{$vars[vars].var_name}" title="details" class="var-name {$static_type}{$access_type}">{$vars[vars].var_name}</a></code>{if $vars[vars].sdesc}<br/><span class="method-sumary-sdesc">{$vars[vars].sdesc}{/if}</span></td>
					</tr>
				{/section}
			</tbody>
		</table>
	</div>
{/if}

{if $methods}
	<a name="sec-method-summary"></a>
	<div class="info-box">
		<div class="info-box-title">Method Summary</span></div>
		<div class="nav-bar">
			<a href="#sec-description">Description</a> |
			{if $children}
				<a href="#sec-descendents">Descendents</a> |
			{/if}
			{if $consts || $iconsts}
				{if $consts}
					<a href="#sec-const-summary">Constants</a> (<a href="#sec-consts">details</a>)
				{else}
					<a href="#sec-consts">Constants</a>
				{/if}			
			{/if}
			{if $vars || $ivars}
				{if $vars}
					<a href="#sec-var-summary">Vars</a> (<a href="#sec-vars">details</a>)
				{else}
					<a href="#sec-vars">Vars</a>
				{/if} 
				|
			{/if}
			<span class="disabled">Methods</span> (<a href="#sec-methods">details</a>)
		</div>
		<table border="0" cellspacing="0" cellpadding="2" width="100%">
			<tbody>
				{section name=methods loop=$methods}
					{assign var="static_type" value=""}
					{assign var="access_type" value=""}
					{if $methods[methods].tags}
						{section name=tags loop=$methods[methods].tags}
							{if $methods[methods].tags[tags].keyword == "static"}
								{assign var="static_type" value="static "}
							{elseif $methods[methods].tags[tags].keyword == "access" && $methods[methods].tags[tags].data != "public"}
								{assign var="access_type" value=$methods[methods].tags[tags].data}
							{/if}
						{/section}
					{/if}
					<tr class="method-sumaries {cycle values="evenrow,oddrow"}" valign="top">
						<td align="right" class="left-column"><code>{$access_type} {$static_type}<span class="method-result">{if $methods[methods].function_return}{$methods[methods].function_return|replace:" ":"&nbsp;"}{/if}</span></code></td>
						<td><code>{if $methods[methods].ifunction_call.returnsref}&amp;{/if}<a href="#{$methods[methods].function_name}" title="details" class="method-name {$static_type}{$access_type}">{$methods[methods].function_name}</a>{if count($methods[methods].ifunction_call.params)}({section name=params loop=$methods[methods].ifunction_call.params}{if $smarty.section.params.iteration != 1}, {/if}<span class="var-type">{$methods[methods].ifunction_call.params[params].type}</span>&nbsp;{$methods[methods].ifunction_call.params[params].name}{if $methods[methods].ifunction_call.params[params].default} = {$methods[methods].ifunction_call.params[params].default}{/if}{/section}){else}(){/if}</code>{if $methods[methods].sdesc}<br/><span class="method-sumary-sdesc">{$methods[methods].sdesc}{/if}</span></td>
					</tr>
				{/section}
			</tbody>
		</table>
	</div>		
{/if}

{if $vars || $ivars}
	<a name="sec-vars"></a>
	<div class="info-box">
		<div class="info-box-title">Properties</div>
		<div class="nav-bar">
			<a href="#sec-description">Description</a> |
			{if $children}
				<a href="#sec-descendents">Descendents</a> |
			{/if}
			{if $methods}
				<a href="#sec-var-summary">Vars</a> (<span class="disabled">details</span>)
			{else}
				<span class="disabled">Vars</span>
			{/if}			
			
			{if $consts || $iconsts}
				{if $consts}
					<a href="#sec-const-summary">Constants</a> (<a href="#sec-consts">details</a>)
				{else}
					<a href="#sec-consts">Constants</a>
				{/if}			
			{/if}
			{if $methods || $imethods}
				| 
				{if $methods}
					<a href="#sec-method-summary">Methods</a> (<a href="#sec-methods">details</a>)
				{else}
					<a href="#sec-methods">Methods</a>
				{/if}			
			{/if}
		</div>
		<div class="info-box-body">
			{include file="var.tpl"}
			{if $ivars}
				<h4>Inherited Variables</h4>
				<a name='inherited_vars'><!-- --></A>
				{section name=ivars loop=$ivars}
					<p>Inherited from <span class="classname">{$ivars[ivars].parent_class}</span></p>
					<blockquote>
						{section name=ivars2 loop=$ivars[ivars].ivars}
							<span class="var-title">
								<span class="var-name">{$ivars[ivars].ivars[ivars2].link}</span>{if $ivars[ivars].ivars[ivars2].ivar_sdesc}: {$ivars[ivars].ivars[ivars2].ivar_sdesc}{/if}<br>
							</span>
						{/section}
					</blockquote> 
				{/section}
			{/if}			
		</div>
	</div>
{/if}
	
{if $methods || $imethods}
	<a name="sec-methods"></a>
	<div class="info-box">
		<div class="info-box-title">Methods</div>
		<div class="nav-bar">
			<a href="#sec-description">Description</a> |
			{if $children}
				<a href="#sec-descendents">Descendents</a> |
			{/if}
			{if $vars || $ivars}
				{if $vars}
					<a href="#sec-var-summary">Vars</a> (<a href="#sec-vars">details</a>)
				{else}
					<a href="#sec-vars">Vars</a>
				{/if}
			{/if}
			{if $consts || $iconsts}
				{if $consts}
					<a href="#sec-const-summary">Constants</a> (<a href="#sec-consts">details</a>)
				{else}
					<a href="#sec-consts">Constants</a>
				{/if}			
			{/if}
			{if $methods}
				<a href="#sec-method-summary">Methods</a> (<span class="disabled">details</span>)
			{else}
				<span class="disabled">Methods</span>
			{/if}			
		</div>
		<div class="info-box-body">
			{include file="method.tpl"}
			{if $imethods}
				<h4>Inherited Methods</h4>
				<a name='inherited_methods'><!-- --></a>	
				{section name=imethods loop=$imethods}
					<!-- =========== Summary =========== -->
					<p>Inherited From <span class="classname">{$imethods[imethods].parent_class}</span></p>
					<blockquote>
						{section name=im2 loop=$imethods[imethods].imethods}
							<span class="method-name">{$imethods[imethods].imethods[im2].link}</span>{if $imethods[imethods].imethods[im2].ifunction_sdesc}: {$imethods[imethods].imethods[im2].ifunction_sdesc}{/if}<br>
						{/section}
					</blockquote>
				{/section}
			{/if}			
		</div>
	</div>
{/if}

{if $consts || $iconsts}
	<a name="sec-consts"></a>
	<div class="info-box">
		<div class="info-box-title">Class Constants</div>
		<div class="nav-bar">
			<a href="#sec-description">Description</a> |
			{if $children}
				<a href="#sec-descendents">Descendants</a> |
			{/if}
			{if $methods}
				<a href="#sec-var-summary">Constants</a> (<span class="disabled">details</span>)
			{else}
				<span class="disabled">Constants</span>
			{/if}			
			
			{if $vars || $ivars}
				{if $vars}
					<a href="#sec-var-summary">Vars</a> (<a href="#sec-vars">details</a>)
				{else}
					<a href="#sec-vars">Vars</a>
				{/if}
			{/if}
			{if $methods || $imethods}
				| 
				{if $methods}
					<a href="#sec-method-summary">Methods</a> (<a href="#sec-methods">details</a>)
				{else}
					<a href="#sec-methods">Methods</a>
				{/if}			
			{/if}
		</div>
		<div class="info-box-body">
			{include file="const.tpl"}
			{if $iconsts}
				<h4>Inherited Constants</h4>
				<a name='inherited_vars'><!-- --></A>
				{section name=iconsts loop=$iconsts}
					<p>Inherited from <span class="classname">{$iconsts[iconsts].parent_class}</span></p>
					<blockquote>
						{section name=iconsts2 loop=$iconsts2[iconsts2].iconsts}
							<img src="{$subdir}media/images/{if $iconsts[iconsts].iconsts[iconsts2].access == 'private'}PrivateVariable{else}Variable{/if}.png" />
							<span class="const-title">
								<span class="const-name">{$iconsts[iconsts].iconsts[iconsts2].link}</span>{if $iconsts[iconsts].iconsts[iconsts2].iconst_sdesc}: {$iconsts[iconsts].iconsts[iconsts2].iconst_sdesc}{/if}<br>
							</span>
						{/section}
					</blockquote> 
				{/section}
			{/if}			
		</div>
	</div>
{/if}

{include file="footer.tpl" top3=true}
<!-- /class.tpl -->
