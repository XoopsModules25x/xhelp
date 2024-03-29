<div id="xhelp_staff_viewall">
    <{include file='db:xhelp_staff_header.tpl'}>
    <form name="ticketFilter" method="post" action="<{$xhelp_current_file}>">
        <{securityToken}><{*//mb*}>
        <table id="ticketFilter" width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
            <tr>
                <th colspan="6"><{$smarty.const._XHELP_TEXT_FILTERTICKETS}></th>
            </tr>
            <tr>
                <td class="head"><{$smarty.const._XHELP_TEXT_DEPARTMENT}></td>
                <td class="head"><{$smarty.const._XHELP_TEXT_STATE}></td>
                <td class="head"><{$smarty.const._XHELP_TEXT_STATUS}></td>
                <td class="head"><{$smarty.const._XHELP_TEXT_OWNERSHIP}></td>
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
                <td><label for="ownership"></label><select name="ownership"
                                                           id="ownership"><{html_options values=$xhelp_ownership_values output=$xhelp_ownership_options selected=$xhelp_filter.ownership}></select>
                </td>
                <td><label for="limit"></label><select name="limit"
                                                       id="limit"><{html_options options=$xhelp_limit_options selected=$xhelp_filter.limit}></select>
                </td>
                <td><input type="submit" value="<{$smarty.const._XHELP_BUTTON_SEARCH}>"></td>
            </tr>
        </table>
        <div>
            <input type="hidden" name="op" value="staffViewAll">
            <input type="hidden" name="start" value="<{$xhelp_filter.start}>">
            <input type="hidden" name="sort" value="<{$xhelp_filter.sort}>">
            <input type="hidden" name="order" value="<{$xhelp_filter.order}>">
        </div>
    </form>
    <form name="tickets" method="post" action="<{$xhelp_current_file}>">
        <{securityToken}><{*//mb*}>
        <{include file='db:xhelp_staff_ticket_table.tpl'}>
        <br>
        <div id="xhelpBatchActions">
            <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
                <tr>
                    <th colspan="2"><{$smarty.const._XHELP_TEXT_BATCH_ACTIONS}></th>
                </tr>
                <tr>
                    <td class="head" width="20%">
                        <{$smarty.const._XHELP_TEXT_SELECTED}>
                    </td>
                    <td class="even">
                        <label>
                            <select name="op">
                                <option value="setdept"><{$smarty.const._XHELP_TEXT_BATCH_DEPARTMENT}></option>
                                <option value="setpriority"><{$smarty.const._XHELP_TEXT_BATCH_PRIORITY}></option>
                                <option value="setstatus"><{$smarty.const._XHELP_TEXT_BATCH_STATUS}></option>
                                <option value="delete"><{$smarty.const._XHELP_TEXT_BATCH_DELETE}></option>
                                <option value="addresponse"><{$smarty.const._XHELP_TEXT_BATCH_RESPONSE}></option>
                                <option value="setowner"><{$smarty.const._XHELP_TEXT_BATCH_OWNERSHIP}></option>
                            </select>
                        </label>
                        <input type="submit" value="<{$smarty.const._GO}>">
                    </td>
                </tr>
            </table>
        </div>

    </form>
</div>

