<{if $xhelp_errors neq null}>
    <div id="readOnly" class="errorMsg"
         style="border:1px solid #D24D00; background:#FEFECC no-repeat 7px 50%;color:#333;padding-left:45px;">
        <img src="<{$xhelp_imagePath}>important.png" alt="">
        <{$smarty.const._XHELP_MESSAGE_VALIDATE_ERROR}><br>
        <{foreach from=$xhelp_errors item=error key=key}>
            <li><a href="#<{$key}>" onclick="document.addResponse.<{$key}>.focus();"><{$key}><{$error}></a></li>
        <{/foreach}>
    </div>
    <br>
<{/if}>
<{include file='db:xhelp_staff_header.tpl'}>

<div class="formButton" style="border:1px solid black;">
    <table width="100%" cellpadding="0" cellspacing="2">
        <tr>
            <th colspan="2">
                <img src="<{$xhelp_imagePath}>response.png"
                     alt="<{$smarty.const._XHELP_TITLE_ADDRESPONSE}>"> <{$smarty.const._XHELP_TITLE_ADDRESPONSE}>
            </th>
        </tr>
        <{if $xhelp_isSubmitter eq false || $xhelp_hasResponseTpl eq true}>
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_PREDEFINED_RESPONSES}>
                </td>
                <td class="even">
                    <form name="formRefresh" method="get" action="<{$xhelp_baseURL}>/response.php">
                        <select name="replies"
                                onchange="window.location='<{$xhelp_baseURL}>/response.php?id=<{$xhelp_ticketID}>&amp;op=staffFrm&amp;refresh='+this.options[this.selectedIndex].value;">
                            <{html_options values=$xhelp_responseTpl_ids selected=$xhelp_responseTpl_selected output=$xhelp_responseTpl_values}>
                        </select>
                    </form>
                </td>
            </tr>
        <{/if}>
    </table>

    <form style="margin:0; padding:0;" method="post" enctype="multipart/form-data" action="<{$xhelp_baseURL}>/response.php?id=<{$xhelp_ticketID}>" name="addResponse">
        <{securityToken}><{*//mb*}>
        <table width="100%" cellpadding="0" cellspacing="2">
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_RESPONSE}>
                </td>
                <td class="even">
                    <textarea name="response" id="response" rows="10" cols="50"
                              class="<{$xhelp_element_response}>"><{$xhelp_response_message}></textarea>
                </td>
            </tr>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_TIMESPENT}>
                </td>
                <td class="even">
                    <input type="text" name="timespent" id="timespent" value="<{$xhelp_response_timespent}>"
                           class="<{$xhelp_element_timespent}>"><{$smarty.const._XHELP_TEXT_MINUTES}>
                </td>
            </tr>
            <{if $xhelp_allowUpload eq 1}>
                <tr>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_ADDFILE}>
                    </td>
                    <td class="even">
                        <input name="userfile" type="file" class="formButton">
                    </td>
                </tr>
            <{/if}>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_STATUS}>
                </td>
                <td class="even">
                    <select name="status">
                        <{foreach from=$xhelp_statuses item=status}>
                            <option value="<{$status.id}>"
                                    <{if $xhelp_ticket_status eq $status.id}>selected="selected"<{/if}>><{$status.desc}></option>
                        <{/foreach}>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_PRIVATE_RESPONSE}>
                </td>
                <td class="even">
                    <{if $xhelp_response_private eq false}>
                        <input type="checkbox" name="private" value="1" class="formButton">
                    <{else}>
                        <input type="checkbox" name="private" value="1" class="formButton" checked="checked">
                    <{/if}>
                </td>
            </tr>
            <{if $xhelp_isSubmitter eq false && $xhelp_has_takeOwnership eq true}>
                <{if $xhelp_has_owner neq $xhelp_currentUser}>
                    <tr>
                        <td class="head">
                            <{$smarty.const._XHELP_TEXT_CLAIMOWNER}>
                        </td>
                        <td class="even">
                            <{if $xhelp_response_ownership}>
                                <input name="claimOwner" value="<{$xhelp_currentUser}>" type="radio" class="formButton"
                                       <{if $xhelp_response_ownership eq 1}>checked="checked"<{/if}>><{$smarty.const._XHELP_TEXT_YES}>
                                <input name="claimOwner" value="0" type="radio" class="formButton"
                                       <{if $xhelp_response_ownership eq 0}>checked="checked"<{/if}>><{$smarty.const._XHELP_TEXT_NO}>
                            <{else}>
                                <input name="claimOwner" value="<{$xhelp_currentUser}>" type="radio" class="formButton"
                                       <{if $xhelp_has_owner eq 0}>checked="checked"<{/if}>><{$smarty.const._XHELP_TEXT_YES}>
                                <input name="claimOwner" value="0" type="radio" class="formButton"
                                       <{if $xhelp_has_owner neq 0}>checked="checked"<{/if}>><{$smarty.const._XHELP_TEXT_NO}>
                            <{/if}>
                        </td>
                    </tr>
                <{/if}>
            <{else}>
                <input type="hidden" name="claimOwner" value="0">
            <{/if}>
            <tr>
                <td class="head">
                </td>
                <td class="even">
                    <input type="submit" value="<{$smarty.const._XHELP_BUTTON_ADDRESPONSE}>" class="formButton">
                    <input type="reset" value="<{$smarty.const._XHELP_BUTTON_RESET}>" class="formButton">
                    <input type="hidden" name="op" value="staffAdd">
                </td>
            </tr>
        </table>
    </form>
</div>

<br>
<table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
    <tr>
        <th colspan="2">
            <img src="<{$xhelp_imagePath}>ticketInfo.png" alt="<{$xhelp_ticket_details}>"><{$xhelp_ticket_details}>
        </th>
    </tr>
    <tr>
        <td class="head">
            <{$smarty.const._XHELP_TEXT_SUBJECT}>
        </td>
        <td class="even">
            <{$xhelp_ticket_subject}>
        </td>
    </tr>
    <tr>
        <td class="head">
            <{$smarty.const._XHELP_TEXT_DESCRIPTION}>
        </td>
        <td class="even">
            <{$xhelp_ticket_description}>
        </td>
    </tr>
</table>



