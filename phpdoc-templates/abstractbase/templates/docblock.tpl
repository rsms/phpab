<!-- dockblock.tpl -->
{if $sdesc or $desc}
	<div class="description">{if $sdesc}{$sdesc}{/if}
	{if $desc}{$desc}{/if}</div><br/>
{/if}


{if $tags}
	<table border="0" cellpadding="2" cellspacing="0">
	<tbody>
		{section name=tags loop=$tags}
		<tr class="tag">
			<td><b>{$tags[tags].keyword|capitalize:true}:</b>&nbsp;&nbsp;</td>
			<td>{$tags[tags].data}</td>
		</tr>
		{/section}
	</tbody>
	</table>
{/if}
<!-- /dockblock.tpl -->
