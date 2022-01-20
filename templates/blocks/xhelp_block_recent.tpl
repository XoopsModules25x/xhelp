<div id="xhelp_dept_recent">
    <ul>
        <{foreach from=$block.tickets|default:null item=ticket}>
            <{if $ticket.overdue}>
                <li class="overdue"><a href="<{$ticket.url}>"
                                       title="<{$ticket.id}> - <{$ticket.subject}>"><{$ticket.trim_subject}></a></li>
            <{else}>
                <li><a href="<{$ticket.url}>" title="<{$ticket.id}> - <{$ticket.subject}>"><{$ticket.trim_subject}></a>
                </li>
            <{/if}>
        <{/foreach}>
    </ul>
</div>
