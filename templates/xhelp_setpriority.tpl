<div id="xhelp_setpriority">
    <form method="post" name="frmSetPriority">
        <{securityToken}><{*//mb*}>
        <table style="width:100%;" border="1" cellpadding="0" cellspacing="2" class="outer">
            <tr>
                <th colspan="2"><{$smarty.const._XHELP_TEXT_SETPRIORITY}></th>
            </tr>
            <tr>
                <td class="head" width="20%"><{$smarty.const._XHELP_TEXT_PRIORITY}></td>
                <td class="odd">
                    <{foreach from=$xhelp_priorities item=priority}>
                        <input type="radio" value="<{$priority}>" id="priority<{$priority}>" name="priority"
                               <{if $xhelp_priority eq $priority}>checked<{/if}>>
                        <label for="priority<{$priority}>"><img src="<{$xhelp_imagePath}>priority<{$priority}>.png"
                                                                alt="<{$xhelp_priorities_desc.$priority}>"></label>
                    <{/foreach}>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="even" align="right">
                    <input type="submit" name="setpriority" value="<{$smarty.const._XHELP_BUTTON_SET}>">
                    <input type="hidden" name="tickets" value="<{$xhelp_tickets}>">
                    <input type="hidden" name="op" value="setpriority">
                </td>
            </tr>
        </table>

        <{include file='db:xhelp_batchTickets.tpl'}>

    </form>
</div>
