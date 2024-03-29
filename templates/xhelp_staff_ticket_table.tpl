<table id="allTickets" width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
    <tr>
        <th colspan="9">
            <img src="<{$xhelp_imagePath}>openTicket.png" alt="<{$xhelp_text_allTickets}>"><{$xhelp_text_allTickets}>
        </th>
    </tr>
    <{if $xhelp_has_tickets eq true}>
        <tr>
            <td class="head">
                <a href="<{$xhelp_cols.id.url}>"
                   title="<{$xhelp_cols.id.urltitle}>"><{$smarty.const._XHELP_TEXT_ID}><{if $xhelp_cols.id.sortby eq true}>
                        <img
                        src="assets/images/<{$xhelp_cols.id.sortdir}>.png" alt="<{$xhelp_cols.id.sortdir}>"><{/if}>
                </a>
            </td>
            <td class="head">
                <a href="<{$xhelp_cols.priority.url}>"
                   title="<{$xhelp_cols.priority.urltitle}>"><{$smarty.const._XHELP_TEXT_PRIORITY}><{if $xhelp_cols.priority.sortby eq true}>
                        <img
                        src="assets/images/<{$xhelp_cols.priority.sortdir}>.png"
                        alt="<{$xhelp_cols.priority.sortdir}>"><{/if}></a>
            </td>
            <td class="head">
                <a href="<{$xhelp_cols.elapsed.url}>"
                   title="<{$xhelp_cols.elapsed.urltitle}>"><{$smarty.const._XHELP_TEXT_ELAPSED}><{if $xhelp_cols.elapsed.sortby eq true}>
                        <img
                        src="assets/images/<{$xhelp_cols.elapsed.sortdir}>.png"
                        alt="<{$xhelp_cols.elapsed.sortdir}>"><{/if}></a>
            </td>
            <td class="head">
                <a href="<{$xhelp_cols.lastupdate.url}>"
                   title="<{$xhelp_cols.lastupdate.urltitle}>"><{$smarty.const._XHELP_TEXT_LASTUPDATE}><{if $xhelp_cols.lastupdate.sortby eq true}>
                        <img
                        src="assets/images/<{$xhelp_cols.lastupdate.sortdir}>.png"
                        alt="<{$xhelp_cols.lastupdate.sortdir}>"><{/if}></a>
            </td>
            <td class="head">
                <a href="<{$xhelp_cols.status.url}>"
                   title="<{$xhelp_cols.status.urltitle}>"><{$smarty.const._XHELP_TEXT_STATUS}><{if $xhelp_cols.status.sortby eq true}>
                        <img
                        src="assets/images/<{$xhelp_cols.status.sortdir}>.png"
                        alt="<{$xhelp_cols.status.sortdir}>"><{/if}></a>
            </td>
            <td class="head">
                <a href="<{$xhelp_cols.subject.url}>"
                   title="<{$xhelp_cols.subject.urltitle}>"><{$smarty.const._XHELP_TEXT_SUBJECT}><{if $xhelp_cols.subject.sortby eq true}>
                        <img
                        src="assets/images/<{$xhelp_cols.subject.sortdir}>.png"
                        alt="<{$xhelp_cols.subject.sortdir}>"><{/if}></a>
            </td>
            <td class="head">
                <a href="<{$xhelp_cols.department.url}>"
                   title="<{$xhelp_cols.department.urltitle}>"><{$smarty.const._XHELP_TEXT_DEPARTMENT}><{if $xhelp_cols.department.sortby eq true}>
                        <img
                        src="assets/images/<{$xhelp_cols.department.sortdir}>.png"
                        alt="<{$xhelp_cols.department.sortdir}>"><{/if}></a>
            </td>
            <td class="head">
                <a href="<{$xhelp_cols.ownership.url}>"
                   title="<{$xhelp_cols.ownership.urltitle}>"><{$smarty.const._XHELP_TEXT_OWNER}><{if $xhelp_cols.ownership.sortby eq true}>
                        <img
                        src="assets/images/<{$xhelp_cols.ownership.sortdir}>.png"
                        alt="<{$xhelp_cols.ownership.sortdir}>"><{/if}></a>
            </td>
            <td class="head">
                <a href="<{$xhelp_cols.uid.url}>"
                   title="<{$xhelp_cols.uid.urltitle}>"><{$smarty.const._XHELP_TEXT_LOGGED_BY}><{if $xhelp_cols.uid.sortby eq true}>
                        <img
                        src="assets/images/<{$xhelp_cols.uid.sortdir}>.png" alt="<{$xhelp_cols.uid.sortdir}>"><{/if}>
                </a>
            </td>
        </tr>
        <{foreach from=$xhelp_allTickets item=ticket}>
            <{if $ticket.overdue}>
                <tr class="<{cycle values="odd, even"}> pri<{$ticket.priority}> overdue">
                    <{else}>
                <tr class="<{cycle values="odd, even"}> pri<{$ticket.priority}>">
            <{/if}>
            <td nowrap="nowrap">
                <label>
                    <input type="checkbox" name="tickets[]" value="<{$ticket.id}>">
                </label> <a
                        href="ticket.php?id=<{$ticket.id}>"><{$ticket.id}></a>
            </td>
            <td class="priority">
                <img src="assets/images/priority<{$ticket.priority}>.png"
                     alt="<{$smarty.const._XHELP_TEXT_PRIORITY}> <{$ticket.priority}>">
            </td>
            <td class="elapsed">
                <{$ticket.elapsed}>
            </td>
            <td class="lastUpdate">
                <{$ticket.lastUpdate}>
            </td>
            <td class="status">
                <{$ticket.status}>
            </td>
            <td class="subject">
                <a href="ticket.php?id=<{$ticket.id}>"><{$ticket.subject}></a>
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
            <td class="user">
                <a href="<{$ticket.userinfo}>"><{$ticket.uname}></a>
            </td>
            </tr>
        <{/foreach}>
    <{else}>
        <tr class="odd">
            <td colspan="9">
                <{$smarty.const._XHELP_NO_TICKETS_ERROR}>
            </td>
        </tr>
    <{/if}>
</table>
<div id="xhelp_nav"><{$xhelp_pagenav}></div>
