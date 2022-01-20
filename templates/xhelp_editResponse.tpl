<{if $xhelp_errors neq null}>   <{* Errors on ticket submission? *}>
    <div id="readOnly" class="errorMsg"
         style="border:1px solid #D24D00; background:#FEFECC no-repeat 7px 50%;color:#333;padding-left:45px;">
        <img src="<{$xhelp_imagePath}>important.png">
        <{$smarty.const._XHELP_MESSAGE_VALIDATE_ERROR}><br>
        <{foreach from=$xhelp_errors item=error key=key}>
            <li><a href="#<{$key}>" onclick="document.editResponse.<{$key}>.focus();"><{$key}><{$error}></a></li>
        <{/foreach}>
    </div>
    <br>
<{/if}>
<{include file='db:xhelp_staff_header.tpl'}>   <{* Include staff header *}>

<div class="formButton" style="border:1px solid #000000;">
    <table width="100%" cellpadding="0" cellspacing="2">
        <tr>
            <th colspan="2">
                <img src="<{$xhelp_imagePath}>response.png"
                     alt="<{$smarty.const._XHELP_TITLE_EDITRESPONSE}>"> <{$smarty.const._XHELP_TITLE_EDITRESPONSE}>
            </th>
        </tr>
        <{if $xhelp_hasResponseTpl}>
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_PREDEFINED_RESPONSES}>
                </td>
                <td class="even">
                    <form name="formRefresh" method="get" action="<{$xhelp_baseURL}>/response.php">
                        <label>
                            <select name="replies"
                                    onchange="window.location='<{$xhelp_baseURL}>/response.php?op=staffEdit&amp;id=<{$xhelp_ticketID}>&amp;responseid=<{$xhelp_responseid}>&amp;refresh='+this.options[this.selectedIndex].value;">
                                <option value="0">------------------</option>
                                <{foreach from=$xhelp_responseTpl item=response}>
                                    <option value="<{$response.id}>"
                                            <{if $xhelp_refresh eq $response.id}>selected="selected"<{/if}>><{$response.name}></option>
                                <{/foreach}>
                            </select>
                        </label>
                    </form>
                </td>
            </tr>
        <{/if}>
    </table>

    <form style="margin:0; padding:0;" method="post" enctype="multipart/form-data"
          action="<{$xhelp_baseURL}>/response.php?op=staffEditSave&amp;id=<{$xhelp_ticketID}>&amp;responseid=<{$xhelp_responseid}>"
          name="editResponse">
        <table width="100%" cellpadding="0" cellspacing="2">
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_RESPONSE}>
                </td>
                <td class="even">
                    <label for="response"></label><textarea name="response" id="response" rows="10" cols="50"
                                                            class="<{$xhelp_element_response}>"><{if $xhelp_refresh neq 0}><{$xhelp_response_text}><{else}><{$xhelp_responseMessage}><{/if}>
        </textarea>
                </td>
            </tr>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_TIMESPENT}>
                </td>
                <td class="even">
                    <label>
                        <input type="text" name="timespent" value="<{$xhelp_timeSpent}>"
                               class="<{$xhelp_element_timespent}>">
                    </label><{$smarty.const._XHELP_TEXT_MINUTES}>
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
                    <label>
                        <select name="status">
                            <{foreach from=$xhelp_statuses item=status}>
                                <option value="<{$status.id}>"
                                        <{if $xhelp_status eq $status.id}>selected="selected"<{/if}>><{$status.desc}></option>
                            <{/foreach}>
                        </select>
                    </label>
                </td>
            </tr>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_PRIVATE_RESPONSE}>
                </td>
                <td class="even">
                    <{$xhelp_responsePrivate}>
                </td>
            </tr>
            <{if $xhelp_has_owner neq $xhelp_currentUser && $xhelp_has_takeOwnership}> <{* If current user is not ticket owner and has permission, display claim ownership *}>
                <tr>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_CLAIMOWNER}>
                    </td>
                    <td class="even">
                        <label>
                            <input name="claimOwner" value="<{$xhelp_currentUser}>" type="radio" class="formButton"
                                   <{if $xhelp_has_owner eq 0}>checked<{/if}>>
                        </label><{$smarty.const._XHELP_TEXT_YES}>
                        <label>
                            <input name="claimOwner" value="0" type="radio" class="formButton"
                                   <{if $xhelp_has_owner neq 0}>checked<{/if}>>
                        </label><{$smarty.const._XHELP_TEXT_NO}>
                    </td>
                </tr>
            <{/if}>
            <tr>
                <td class="head">
                </td>
                <td class="even">
                    <input type="submit" name="editResponse" value="<{$smarty.const._XHELP_BUTTON_EDITRESPONSE}>"
                           class="formButton">
                    <input type="reset" value="<{$smarty.const._XHELP_BUTTON_RESET}>" class="formButton">
                </td>
            </tr>
        </table>
    </form>
</div>





