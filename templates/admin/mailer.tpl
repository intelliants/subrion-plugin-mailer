{if isset($queue)}
	<div class="widget widget-default">
		<div class="widget-content">
			<table class="table table-hover" id="queue">
			<thead>
				<tr>
					<th width="70%">{lang key='subject'}</th>
					<th width="10%">{lang key='status'}</th>
					<th>{lang key='amount'}</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				{foreach name=que from=$queue item=q}
					<tr id="group_{$q.id}">
						<td>{if !empty($q.subj)}{$q.subj}{else}---{/if}</td>
						<td>{if empty($q.active)}{lang key='pending'}{else}{lang key='active'}{/if}</td>
						<td>{$q.total}</td>
						<td class="text-right">
							<a class="btn{if $q.active} btn-warning{else} btn-success{/if} btn-sm" href="{$url}mailer/?action=toggleQueue&id={$q.id}" rel="pause" title="{if $q.active}{lang key='pause'}{else}{lang key='start'}{/if}">
								{if $q.active}
									<i class="i-minus-alt"></i>
								{else}
									<i class="i-ok-sign"></i>
								{/if}
							</a>
							<a class="btn btn-danger btn-sm" href="{$url}mailer/?action=remove&id={$q.id}" rel="remove" title="{lang key='delete'}">
								<i class="i-remove"></i>
							</a>
						</td>
					</tr>
				{/foreach}
			</tbody>
			</table>
		</div>
	</div>
{else}
	<div class="widget widget-default">
		<div class="widget-content">
			{lang key='queue_empty'}
		</div>
	</div>
{/if}