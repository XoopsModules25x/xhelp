<{include file='db:xhelp_staff_header.tpl'}>

<div class="formButton" style="" id="xhelp_batchaddresponse">
    <div class="formButton" style="border:1px solid #000000;" id="xhelp_batchaddresponse_inner">
        <table width="100%" cellpadding="0" cellspacing="2">
            <tr>
                <th colspan="2">
                    <img src="<{$xhelp_imagePath}>response.png"
                         alt="<{$smarty.const._XHELP_TITLE_ADDRESPONSE}>"> <{$smarty.const._XHELP_TITLE_ADDRESPONSE}>
                </th>
            </tr>
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_PREDEFINED_RESPONSES}>
                </td>
                <td class="odd">
                    <form name="formRefresh" method="get" action="<{$xhelp_formaction}>">
                        <label>
                            <select name="tpl"
                                    onchange="window.location='<{$xhelp_formaction}>?op=addresponse&amp;tpl='+this.options[this.selectedIndex].value;">
                                <{html_options options=$xhelp_responseTpl_options selected=$xhelp_responseTpl}>
                            </select>
                        </label>
                    </form>
                </td>
            </tr>
        </table>

        <form method="post" action="<{$xhelp_formaction}>">
            <table width="100%" cellpadding="0" cellspacing="2" class="outer">
                <tr>
                    <td class="head" width="20%">
                        <{$smarty.const._XHELP_TEXT_RESPONSE}>
                    </td>
                    <td class="even">
                        <label>
<textarea name="response" rows="10" cols="50"
          class="formButton"><{$xhelp_response_message}></textarea>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_TIMESPENT}>
                    </td>
                    <td class="odd">
                        <label>
                            <input type="text" name="timespent" value="<{$xhelp_timespent}>"
                                   class="formButton">
                        </label><{$smarty.const._XHELP_TEXT_MINUTES}>
                    </td>
                </tr>
                <tr>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_PRIVATE_RESPONSE}>
                    </td>
                    <td class="even">
                        <label>
                            <input type="checkbox" name="private" value="1" class="formButton"
                                   <{if $xhelp_private eq true}>checked<{/if}>>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="foot">

                        <input type="submit" name="addresponse" value="<{$smarty.const._XHELP_BUTTON_ADDRESPONSE}>"
                               class="formButton">
                        <input type="reset" value="<{$smarty.const._XHELP_BUTTON_RESET}>" class="formButton">
                        <input type="hidden" name="op" value="addresponse">
                        <input type="hidden" name="tickets" value="<{$xhelp_tickets}>">
                    </td>
                </tr>
            </table>
    </div>

    <{include file='db:xhelp_batchTickets.tpl'}>

    </form>
</div>
