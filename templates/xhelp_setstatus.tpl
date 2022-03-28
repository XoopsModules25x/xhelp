<div id="xhelp_setstatus">
    <form method="post" name="frmSetstatus">
        <{securityToken}><{*//mb*}>
        <table style="width:100%;" border="1" cellpadding="0" cellspacing="2" class="outer">
            <tr>
                <th colspan="2"><{$smarty.const._XHELP_TEXT_SETSTATUS}></th>
            </tr>
            <tr>
                <td class="head" width="20%"><label for="status"><{$smarty.const._XHELP_TEXT_STATUS}></label></td>
                <td class="odd">
                    <select id="status" name="status">
                        <{html_options options=$xhelp_status_options}>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="even" align="right">
                    <input type="submit" name="setstatus" value="<{$smarty.const._XHELP_BUTTON_SET}>">
                    <input type="hidden" name="tickets" value="<{$xhelp_tickets}>">
                    <input type="hidden" name="op" value="setstatus">
                </td>
            </tr>
        </table>

        <{include file='db:xhelp_batchTickets.tpl'}>

    </form>
</div>
