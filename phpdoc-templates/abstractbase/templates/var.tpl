<!-- var.tpl -->
{section name=vars loop=$vars}
<a name="var{$vars[vars].var_name}" id="{$vars[vars].var_name}"><!-- --></A>
<div class="{cycle values="evenrow,oddrow"}">

	{assign var="access_type" value="public"}
	{assign var="static_type" value=""}
	{if $vars[vars].tags}
		{section name=tags loop=$vars[vars].tags}
			{if $vars[vars].tags[tags].keyword == "access"}
				{assign var="access_type" value=$vars[vars].tags[tags].data}
			{elseif $vars[vars].tags[tags].keyword == "static"}
				{assign var="static_type" value="static "}
			{/if}
		{/section}
	{/if}
	
	<pre class="var-signature">{$access_type} {$static_type}<span class="method-result">{$vars[vars].var_type}</span>&nbsp;<span class="var-name">{$vars[vars].var_name}</span>{if $vars[vars].var_default} =<span class="var-default">{$vars[vars].var_default|replace:"\n":"<br />"}</span>{/if}</pre>
	
	<dl>
	
		{if $vars[vars].sdesc or $vars[vars].desc}
			<dd class="description">{if $vars[vars].sdesc}{$vars[vars].sdesc}{/if}
			{if $vars[vars].desc}{$vars[vars].desc}{/if}</dd>
		{/if}
		
	
		<dd>
		{if $vars[vars].tags}
		<dl>
			{section name=tags loop=$vars[vars].tags}
				{if $vars[vars].tags[tags].keyword != "access" and $vars[vars].tags[tags].keyword != "static"}
					<dd>
						<dt>{$vars[vars].tags[tags].keyword|capitalize:true}:</dt>
						<dd>{$vars[vars].tags[tags].data}</dd>
					</dd>
				{/if}
			{/section}
		</dl>
		{/if}
		</dd>
		
	
	
		{if $vars[vars].var_overrides}
		<dl>
			<dt>Redefinition of:</dt>
			<dd>
				<dd><code>{$vars[vars].var_overrides.link}</code></dd>
				{if $vars[vars].var_overrides.sdesc}
				<dd>{$vars[vars].var_overrides.sdesc}</dd>
				{/if}
			</dd>
		</dl>
		{/if}
		
		
		
		{if $vars[vars].descvar}
		<dl>
			<dt>Redefined in descendants as:</dt>
			<dd>
			{section name=vm loop=$vars[vars].descvar}
				<dd><code>{$vars[vars].descvar[vm].link}</code>
					{if $vars[vars].descvar[vm].sdesc}
					&nbsp; {$vars[vars].descvar[vm].sdesc}
					{/if}
				</dd>
			{/section}
			</dd>
		</dl>
		{/if}
		
		</dd>
	</dl>

</div>
{/section}
<!-- /var.tpl -->