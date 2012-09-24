{if $jsonError == 'true'}
	<iframe src="{$url}?wid={$code}&showlogo={$logo}" style="width:{$width}px;height:{$height}px;" frameborder="0"></iframe>
{else}
	<strong>Oops, Meeting is expired!</strong>
{/if}	