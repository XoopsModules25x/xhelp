<{include file='db:xhelp_user_header.tpl'}>

<{if $xhelp_noStaff}>
    <div id="readOnly" class="errorMsg"
         style="border:1px solid #D24D00; background:#FEFECC no-repeat 7px 50%;color:#333;padding-left:45px;">
        <{$smarty.const._XHELP_MESSAGE_NO_STAFF}>
    </div>
<{/if}>

<br>
<div id="userTickets">
    <form name="ticketFilter" method="post" action="<{$xhelp_baseURL}>/index.php">
        <{securityToken}><{*//mb*}>
        <table id="ticketFilter" width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
            <tr>
                <th colspan="5"><{$smarty.const._XHELP_TEXT_FILTERTICKETS}></th>
            </tr>
            <tr>
                <td class="head"><{$smarty.const._XHELP_TEXT_DEPARTMENT}></td>
                <td class="head"><{$smarty.const._XHELP_TEXT_STATE}></td>
                <td class="head"><{$smarty.const._XHELP_TEXT_STATUS}></td>
                <td class="head" colspan="2"><{$smarty.const._XHELP_TEXT_LIMIT}></td>
            </tr>
            <tr>
                <td><label for="dept"></label><select name="dept"
                                                      id="dept"><{html_options values=$xhelp_department_values output=$xhelp_department_options selected=$xhelp_filter.department}></select>
                </td>
                <td><label for="state"></label><select name="state"
                                                       id="state"><{html_options values=$xhelp_state_values output=$xhelp_state_options selected=$xhelp_filter.state}></select>
                </td>
                <td><label for="status"></label><select name="status"
                                                        id="status"><{html_options values=$xhelp_status_values output=$xhelp_status_options selected=$xhelp_filter.status}></select>
                </td>
                <td><label for="limit"></label><select name="limit"
                                                       id="limit"><{html_options options=$xhelp_limit_options selected=$xhelp_filter.limit}></select>
                </td>
                <td><input type="submit" value="<{$smarty.const._XHELP_BUTTON_SEARCH}>"></td>
            </tr>
        </table>
        <div>
            <input type="hidden" name="op" value="userViewAll">
            <input type="hidden" name="start" value="<{$xhelp_filter.start}>">
            <input type="hidden" name="sort" value="<{$xhelp_filter.sort}>">
            <input type="hidden" name="order" value="<{$xhelp_filter.order}>">
        </div>
    </form>
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
        <tr>
            <th colspan="5">
                <img src="<{$xhelp_imagePath}>ticket.png"
                     alt="<{$smart.const._XHELP_TEXT_ALL_TICKETS}>"><{$smarty.const._XHELP_TEXT_ALL_TICKETS}>
            </th>
        </tr>
        <{if $xhelp_has_userTickets neq 0}>
            <tr class="head">
                <td>
                    <a href="<{$xhelp_cols.subject.url}>"
                       title="<{$xhelp_cols.subject.urltitle}>"><{$smarty.const._XHELP_TEXT_SUBJECT}><{if $xhelp_cols.subject.sortby eq true}>
                            <img
                            src="assets/images/<{$xhelp_cols.subject.sortdir}>.png"
                            alt="<{$xhelp_cols.subject.sortdir}>"><{/if}></a>
                </td>
                <td>
                    <a href="<{$xhelp_cols.department.url}>"
                       title="<{$xhelp_cols.department.urltitle}>"><{$smarty.const._XHELP_TEXT_DEPARTMENT}><{if $xhelp_cols.department.sortby eq true}>
                            <img
                            src="assets/images/<{$xhelp_cols.department.sortdir}>.png"
                            alt="<{$xhelp_cols.department.sortdir}>"><{/if}></a>
                </td>
                <td>
                    <a href="<{$xhelp_cols.status.url}>"
                       title="<{$xhelp_cols.status.urltitle}>"><{$smarty.const._XHELP_TEXT_STATUS}><{if $xhelp_cols.status.sortby eq true}>
                            <img
                            src="assets/images/<{$xhelp_cols.status.sortdir}>.png"
                            alt="<{$xhelp_cols.status.sortdir}>"><{/if}></a>
                </td>
                <td>
                    <a href="<{$xhelp_cols.priority.url}>"
                       title="<{$xhelp_cols.priority.urltitle}>"><{$smarty.const._XHELP_TEXT_PRIORITY}><{if $xhelp_cols.priority.sortby eq true}>
                            <img
                            src="assets/images/<{$xhelp_cols.priority.sortdir}>.png"
                            alt="<{$xhelp_cols.priority.sortdir}>"><{/if}></a>
                </td>
                <td>
                    <a href="<{$xhelp_cols.elapsed.url}>"
                       title="<{$xhelp_cols.elapsed.urltitle}>"><{$smarty.const._XHELP_TEXT_ELAPSED}><{if $xhelp_cols.elapsed.sortby eq true}>
                            <img
                            src="assets/images/<{$xhelp_cols.elapsed.sortdir}>.png"
                            alt="<{$xhelp_cols.elapsed.sortdir}>"><{/if}></a>
                </td>
            </tr>
            <{foreach from=$xhelp_userTickets item=ticket}>
                <tr class="<{cycle values="odd,even"}>">
                    <td>
                        <a href="<{$xhelp_baseURL}>/ticket.php?id=<{$ticket.id}>"><{$ticket.subject}></a>
                    </td>
                    <td>
                        <a href="<{$ticket.departmenturl}>"><{$ticket.department}></a>
                    </td>
                    <td>
                        <{$ticket.status}>
                    </td>
                    <td>
                        <img src="<{$xhelp_imagePath}>priority<{$ticket.priority}>.png"
                             alt="<{$smarty.const._XHELP_TEXT_PRIORITY}> <{$ticket.priority}>">
                    </td>
                    <td>
                        <{$ticket.elapsed}>
                    </td>
                </tr>
            <{/foreach}>
        <{else}>
            <tr class="odd">
                <td colspan="5">
                    <{$smarty.const._XHELP_NO_TICKETS_ERROR}>
                </td>
            </tr>
        <{/if}>
    </table>
</div>
