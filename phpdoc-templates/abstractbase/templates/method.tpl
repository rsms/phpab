<!-- method.tpl -->
<a name='method_detail'></a>
{section name=methods loop=$methods}
<a name="method{$methods[methods].function_name}" id="{$methods[methods].function_name}"><!-- --></a>
<div class="{cycle values="evenrow,oddrow"}">
    
	{assign var="static_type" value=""}
	{assign var="access_type" value="public"}
	{if $methods[methods].tags}
		{section name=tags loop=$methods[methods].tags}
			{if $methods[methods].tags[tags].keyword == "access"}
				{assign var="access_type" value=$methods[methods].tags[tags].data}
			{elseif $methods[methods].tags[tags].keyword == "static"}
				{assign var="static_type" value="static "}
			{/if}
		{/section}
	{/if}
	
	<div class="method-header">
		{if $methods[methods].ifunction_call.constructor}<span class="method-constructor">Constructor</span>
		{elseif $methods[methods].ifunction_call.destructor}<span class="method-destructor">Destructor</span>
		{/if}<span class="method-title {$static_type}{$access_type}">{$methods[methods].function_name}</span>
		<!-- (line <span class="line-number">{if $methods[methods].slink}{$methods[methods].slink}{else}{$methods[methods].line_number}{/if}</span>) -->
	</div>
	

	{if $methods[methods].ifunction_call.constructor or $methods[methods].ifunction_call.destructor}
		{assign var="function_return" value=""}
		{assign var="function_return_s" value=""}
		{assign var="function_return_t" value=""}
	{else}
		{assign var="function_return" value=$methods[methods].function_return|regex_replace:"/<[^>]*>/":""}
		{assign var="function_return_s" value=$methods[methods].function_return}
		{assign var="function_return_t" value=" "}
	{/if}
	{assign var="function_name" value=$methods[methods].function_name}
	{assign var="returnsref" value=""}
	{assign var="empty_string" value=""}
	{if $methods[methods].ifunction_call.returnsref}{assign var="returnsref" value="  "}{/if}
	{assign var="method_desc_head" value="$access_type $static_type$function_return$function_return_t$returnsref$function_name "}
	{if $methods[methods].ifunction_call.returnsref}{assign var="returnsref" value="&amp; "}{/if}
	{assign var="method_desc_head_c" value=$method_desc_head|count_characters:true}
	
	
	<pre class="method-signature">{$access_type} {$static_type}<span class="method-result">{$function_return_s}</span>{$function_return_t}<span class="method-name">{$returnsref}{$function_name}</span>{if count($methods[methods].ifunction_call.params)}({section name=params loop=$methods[methods].ifunction_call.params}{if $smarty.section.params.iteration != 1},
{$empty_string|indent:$method_desc_head_c}{/if}<span class="var-type">{$methods[methods].ifunction_call.params[params].type}</span>&nbsp;<span class="var-name">{$methods[methods].ifunction_call.params[params].name}</span>{if $methods[methods].ifunction_call.params[params].default} = <span class="var-default">{$methods[methods].ifunction_call.params[params].default}</span>{/if}{/section}){else}(){/if}</pre>
	
	<dl>
	{if $methods[methods].sdesc or $methods[methods].desc}
		<dd class="description">{if $methods[methods].sdesc}{$methods[methods].sdesc}{/if}
		{if $methods[methods].desc}{$methods[methods].desc}{/if}<br /></dd>
	{/if}
		<dd>
		
		{if $methods[methods].params}
			{assign var="hasvalidparams" value=0}
			{section name=params loop=$methods[methods].params}
				{if $methods[methods].params[params].data and $hasvalidparams == 0}
					{assign var="hasvalidparams" value=1}
				{/if}
			{/section}
			{if $hasvalidparams == 1}
			<dl>
				<dd>
					<dt>Parameters:</dt>
					{section name=params loop=$methods[methods].params}
						{if $methods[methods].params[params].data}
							{* $methods[methods].params[params].datatype *}{* if $methods[methods].params[params].data *}
							<dd><code>{$methods[methods].params[params].var}</code> - {$methods[methods].params[params].data}</dd>
						{/if}
					{/section}
				</dd>
			</dl>
			{/if}
		{/if}
		
		
		
		{if $methods[methods].tags}
		<dl>
			{assign var="tag_see_drawn" value="0"}
			{assign var="tag_throws_drawn" value="0"}
			{section name=tags loop=$methods[methods].tags}
				{if $methods[methods].tags[tags].keyword != "access" and $methods[methods].tags[tags].keyword != "static"}
					<dd>
						{if $methods[methods].tags[tags].keyword == "see"}
							{if $tag_see_drawn == "0"}
								{assign var="tag_see_drawn" value="1"}
								<dt>See:</dt>
							{/if}
						{elseif $methods[methods].tags[tags].keyword == "throws"}
							{if $tag_throws_drawn == "0"}
								{assign var="tag_throws_drawn" value="1"}
								<dt>Throws:</dt>
							{/if}
						{else}
							<dt>{$methods[methods].tags[tags].keyword|capitalize:true}:</dt>
						{/if}
						<dd>{$methods[methods].tags[tags].data}</dd>
					</dd>
				{/if}
			{/section}
		</dl>
		{/if}
			
	
		
		{if $methods[methods].method_overrides}
		<dl>
			<dt>Redefinition of:</dt>
			<dd>
				<dd><code>{$methods[methods].method_overrides.link}</code></dd>
				{if $methods[methods].method_overrides.sdesc}
				<dd>{$methods[methods].method_overrides.sdesc}</dd>
				{/if}
			</dd>
		</dl>
		{/if}
		
		
		
		{if $methods[methods].descmethod}
		<dl>
			<dt>Redefined in descendants as:</dt>
			<dd>
			{section name=dm loop=$methods[methods].descmethod}
				<dd><code>{$methods[methods].descmethod[dm].link}</code>
					{if $methods[methods].descmethod[dm].sdesc}
					&nbsp; {$methods[methods].descmethod[dm].sdesc}
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
<!-- /method.tpl -->