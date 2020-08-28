<table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
    <{if $block.xhelp_has_changeOwner}>
        <tr>
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_OWNERSHIP}>
            </td>
            <td class="even" colspan="2">
                <form method="post" action="ticket.php?id=<{$block.ticketid}>&amp;op=ownership">
                    <select name="uid" class="formButton">
                        <{foreach from=$block.ownership item=staff}>
                            <option value="<{$staff.uid}>"
                                    <{if $block.ticket_ownership eq $staff.uid}>selected="selected"<{/if}>><{$staff.uname}></option>
                        <{/foreach}>
                    </select>
                    <br>
                    <input type="image" src="<{$block.imagePath}>assignOwner.png"
                           title="<{$smarty.const._XHELP_TEXT_ASSIGN_OWNER}>" name="assignOwner"
                           style="border:0;background:transparent;">
                    <br><{$smarty.const._XHELP_TEXT_ASSIGN_OWNER}>
                </form>
            </td>
        </tr>
        <tr>
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_ASSIGNTO}>
            </td>
            <td class="even" colspan="2">
                <form method="post" action="index.php?op=setdept">
                    <{securityToken}><{*//mb*}>
                    <select name="department">
                        <{html_options options=$block.departments selected=$block.departmentid}>
                    </select>
                    <input type="hidden" name="tickets" value="<{$block.ticketid}>">
                    <input type="hidden" name="setdept" value="1">
                    <input type="image" src="<{$block.imagePath}>assignOwner.png"
                           title="<{$smarty.const._XHELP_TEXT_ASSIGNTO}>" name="assignDept"
                           style="border:0;background:transparent;">
                </form>
            </td>
        </tr>
    <{/if}>
    <tr>
        <td class="head" rowspan="<{$block.xhelp_actions_rowspan}>">
            <{$smarty.const._XHELP_TEXT_TICKET}>
        </td>

        <td class="even center">
            <{if $block.xhelp_has_addResponse}>
                <a href="response.php?id=<{$block.ticketid}>&amp;op=staffFrm"><img
                            src="<{$block.imagePath}>response.png" alt="<{$smarty.const._XHELP_TEXT_ADDRESPONSE}>"></a>
                <br>
                <{$smarty.const._XHELP_TEXT_ADD_RESPONSE}>
            <{/if}>
        </td>
    </tr>
    <{if $block.xhelp_has_editTicket}>
        <tr>
            <td class="even center">
                <a href="ticket.php?id=<{$block.ticketid}>&amp;op=edit"><img src="<{$block.imagePath}>edit.png"
                                                                             alt="<{$smarty.const._XHELP_TEXT_EDITTICKET}>"></a>
                <br><{$smarty.const._XHELP_TEXT_EDIT_TICKET}>
            </td>
        </tr>
    <{/if}>
    <{if $block.xhelp_has_deleteTicket}>
        <tr>
            <td class="even center">
                <form method="post" action="ticket.php?id=<{$block.ticketid}>&amp;op=delete">
                    <{securityToken}><{*//mb*}>
                    <input type="hidden" value="<{$block.ticketid}>" name="ticketid">
                    <input type="hidden" value="1" name="delete_ticket">
                    <script language="javascript" type="text/javascript">
                        function confirmDelete() {
                            confirm('<{$smarty.const._XHELP_JSC_TEXT_DELETE}>');
                        }
                    </script>
                    <input type="image" src="<{$block.imagePath}>delete.png"
                           title="<{$smarty.const._XHELP_TEXT_DELETE_TICKET}>" name="deleteTicket"
                           onclick='return confirm("Are you sure you want to delete this ticket?");'
                           style="border:0;background:transparent;">
                    <br><{$smarty.const._XHELP_TEXT_DELETE_TICKET}>
                </form>
            </td>
        </tr>
    <{/if}>
    <{if $block.xhelp_has_mergeTicket}>
        <td class="even center">
            <form method="post" action="ticket.php?id=<{$block.ticketid}>&amp;op=merge">
                <input type="text" name="ticket2" size="8" title="<{$smarty.const._XHELP_TEXT_MERGE_TITLE}>"
                       class="formButton">
                <input type="image" src="<{$block.imagePath}>merge.png"
                       title="<{$smarty.const._XHELP_TEXT_MERGE_TICKET}>" name="mergeTicket"
                       style="border:0;background:transparent;">
                <br><{$smarty.const._XHELP_TEXT_MERGE_TICKET}>
            </form>
        </td>
    <{/if}>
    <tr>
        <td class="even center">
            <a href="ticket.php?id=<{$block.ticketid}>&amp;op=print" target="_blank"><img
                        src="<{$block.imagePath}>print.png" alt="<{$smarty.const._XHELP_TEXT_PRINT_TICKET}>"></a>
            <br><{$smarty.const._XHELP_TEXT_PRINT_TICKET}>
        </td>
    </tr>
    <{if $block.xhelp_has_changePriority}>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_UPDATE_PRIORITY}>
            </td>
            <td class="even">
                <form method="post" action="ticket.php?id=<{$block.ticketid}>&amp;op=updatePriority">
                    <{foreach from=$block.xhelp_priorities item=priority}>
                        <input type="radio" value="<{$priority}>" id="priority<{$priority}>" name="priority"
                               <{if $block.ticket_priority eq $priority}>checked<{/if}>>
                        <label for="priority<{$priority}>"><img src="<{$block.imagePath}>priority<{$priority}>.png"
                                                                alt="<{$block.xhelp_priorities_desc.$priority}>"></label>
                        <br>
                    <{/foreach}>
                    <input type="submit" name="updatePriority" value="<{$smarty.const._XHELP_BUTTON_UPDATE_PRIORITY}>"
                           class="formButton">
                </form>
            </td>
        </tr>
    <{/if}>
    <{if $block.xhelp_has_changeStatus}>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_UPDATE_STATUS}>
            </td>
            <td class="even" colspan="4">
                <form method="post" action="ticket.php?id=<{$block.ticketid}>&amp;op=updateStatus">
                    <select name="status">
                        <{foreach from=$block.statuses item=status}>
                            <option value="<{$status.id}>"
                                    <{if $block.ticket_status eq $status.id}>selected="selected"<{/if}>><{$status.desc}></option>
                        <{/foreach}>
                    </select><br>
                    <input type="text" name="response" id="response" value="" class="formButton"><br>
                    <input type="submit" name="updateStatus" value="<{$smarty.const._XHELP_BUTTON_UPDATE_STATUS}>"
                           class="formButton">
                </form>
            </td>
        </tr>
    <{/if}>

</table>
