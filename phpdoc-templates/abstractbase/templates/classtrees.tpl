{include file="header.tpl" top1=true}

<h2 class="class-name classtree-name">Class tree for package {$package}</h2>

<!-- Start of Class Data -->
{section name=classtrees loop=$classtrees}
{$classtrees[classtrees].class_tree}
{/section}
{include file="footer.tpl"}