<div id="xhelp_bOpenTickets">
    <ul>
        <{if $block.numTickets neq 0}>
            <{foreach from=$block.ticket item=ticket}>
                <{if $ticket.overdue}>
                    <li class="overdue"><a href="<{$ticket.url}>"
                                           title="<{$ticket.id}> - <{$ticket.subject}>"><{$ticket.truncSubject}></a>
                    </li>
                <{else}>
                    <li><a href="<{$ticket.url}>"
                           title="<{$ticket.id}> - <{$ticket.subject}>"><{$ticket.truncSubject}></a></li>
                <{/if}>
            <{/foreach}>
        <{else}>
            <li><{$block.noTickets}></li>
        <{/if}>
    </ul>
    <{if $block.isStaff}>
        <br>
        <a href="<{$block.viewAll}>"><{$block.viewAllText}></a>
    <{/if}>
</div>
