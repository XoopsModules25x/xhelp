<{if $xhelp_hasGoodTickets}>
    <br>
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
        <tr>
            <th colspan="6">
                <{$smarty.const._XHELP_TEXT_ACC_TICKETS}>
            </th>
        </tr>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_ID}>
            </td>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_PRIORITY}>
            </td>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_SUBJECT}>
            </td>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_STATUS}>
            </td>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_DEPARTMENT}>
            </td>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_OWNER}>
            </td>
        </tr>
        <{foreach from=$xhelp_goodTickets item=ticket}>
            <tr class="<{cycle values="odd,even"}> pri<{$ticket.priority}><{if $ticket.overdue}> overdue<{/if}>">
                <td nowrap="nowrap">
                    <input type="checkbox" name="tickets[]" value="<{$ticket.id}>" checked> <a
                            href="ticket.php?id=<{$ticket.id}>"><{$ticket.id}></a>
                </td>
                <td class="priority">
                    <img src="assets/images/priority<{$ticket.priority}>.png"
                         alt="<{$smarty.const._XHELP_TEXT_PRIORITY}> <{$ticket.priority}>">
                </td>
                <td class="subject">
                    <a href="ticket.php?id=<{$ticket.id}>"><{$ticket.subject}></a>
                </td>
                <td class="status">
                    <{$ticket.status}>
                </td>
                <td class="department">
                    <a href="<{$ticket.departmenturl}>"><{$ticket.department}></a>
                </td>
                <td class="owner" nowrap="nowrap">
                    <{if $ticket.ownerinfo neq ''}>
                        <a href="<{$ticket.ownerinfo}>"><{$ticket.ownership}></a>
                    <{else}>
                        <{$ticket.ownership}>
                    <{/if}>
                </td>
            </tr>
        <{/foreach}>
    </table>
<{/if}>

<{if $xhelp_hasBadTickets}>
    <br>
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
        <tr>
            <th colspan="6">
                <{$smarty.const._XHELP_TEXT_UNACC_TICKETS}>: <{$xhelp_batchErrorMsg}>
            </th>
        </tr>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_ID}>
            </td>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_PRIORITY}>
            </td>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_SUBJECT}>
            </td>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_STATUS}>
            </td>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_DEPARTMENT}>
            </td>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_OWNER}>
            </td>
        </tr>
        <{foreach from=$xhelp_badTickets item=ticket}>
            <tr class="<{cycle values="odd,even"}> pri<{$ticket.priority}><{if $ticket.overdue}> overdue<{/if}>">
                <td nowrap="nowrap">
                    <input type="checkbox" name="tickets[]" value="<{$ticket.id}>"> <a
                            href="ticket.php?id=<{$ticket.id}>"><{$ticket.id}></a>
                </td>
                <td class="priority">
                    <img src="assets/images/priority<{$ticket.priority}>.png"
                         alt="<{$smarty.const._XHELP_TEXT_PRIORITY}> <{$ticket.priority}>">
                </td>
                <td class="subject">
                    <a href="ticket.php?id=<{$ticket.id}>"><{$ticket.subject}></a>
                </td>
                <td class="status">
                    <{$ticket.status}>
                </td>
                <td class="department">
                    <a href="<{$ticket.departmenturl}>"><{$ticket.department}></a>
                </td>
                <td class="owner" nowrap="nowrap">
                    <{if $ticket.ownerinfo neq ''}>
                        <a href="<{$ticket.ownerinfo}>"><{$ticket.ownership}></a>
                    <{else}>
                        <{$ticket.ownership}>
                    <{/if}>
                </td>
            </tr>
        <{/foreach}>
    </table>
<{/if}>
