<{if $xhelp_errors neq null}>
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

<{if $xhelp_isStaff}>
    <{include file='db:xhelp_staff_header.tpl'}>
<{else}>
    <{include file='db:xhelp_user_header.tpl'}>
<{/if}>

<form method="post" enctype="multipart/form-data" action="<{$xhelp_baseURL}>/ticket.php?id=<{$xhelp_ticketID}>&amp;op=edit">
    <{securityToken}><{*//mb*}>
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton" id="tblEditTicket">
        <tr>
            <th colspan="2">
                <img src="<{$xhelp_imagePath}>addTicket.png"
                     alt="<{$smarty.const._XHELP_TITLE_EDITTICKET}>"> <{$smarty.const._XHELP_TITLE_EDITTICKET}>
            </th>
        </tr>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_ASSIGNTO}>
            </td>
            <td class="even">
                <select name="departments" id="departments">
                    <{foreach from=$xhelp_departments item=dept}>
                        <option value="<{$dept.id}>"
                                <{if $xhelp_ticket_department eq $dept.id}>selected="selected"<{/if}>><{$dept.department}></option>
                    <{/foreach}>
                </select>
            </td>
        </tr>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_PRIORITY}>
            </td>
            <td class="even">
                <{foreach from=$xhelp_priorities item=priority}>
                    <input type="radio" value="<{$priority}>" name="priority" id="priority"
                           <{if $xhelp_ticket_priority eq $priority}>checked<{/if}>>
                    <img src="<{$xhelp_imagePath}>priority<{$priority}>.png"
                         alt="<{$xhelp_priorities_desc.$priority}>">
                <{/foreach}>
            </td>
        </tr>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_SUBJECT}>
            </td>
            <td class="even">
                <input type="text" name="subject" id="subject" maxlength="100" size="50"
                       value="<{$xhelp_ticket_subject}>" class="<{$xhelp_element_subject}>">
            </td>
        </tr>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_DESCRIPTION}>
            </td>
            <td class="even">
                <textarea name="description" id="description" rows="5" cols="50"
                          class="<{$xhelp_element_description}>"><{$xhelp_ticket_description}></textarea>
            </td>
        </tr>
        <{if $xhelp_allowUpload eq 1}>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_ADDFILE}>
                </td>
                <td class="even">
                    <input name="userfile" id="userfile" type="file" class="formButton">
                </td>
            </tr>
        <{/if}>
        <{foreach from=$xhelp_custFields item=field}>
            <tr class="custfld">
                <td class="head">
                    <{$field.name}>:
                </td>
                <td class="even">
                    <{if $field.controltype == $smarty.const.XHELP_CONTROL_TXTBOX}>
                        <input type="text" name="<{$field.fieldname}>" id="<{$field.fieldname}>"
                               value="<{$field.value}>" maxlength="<{$field.maxlength}>" size="<{$field.fieldlength}>">
                    <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_TXTAREA}>
                        <textarea name="<{$field.fieldname}>" id="<{$field.fieldname}>" cols="<{$field.fieldlength}>"
                                  rows="5"><{$field.value}></textarea>
                    <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_SELECT}>
                        <select name="<{$field.fieldname}>" id="<{$field.fieldname}>" size="1">
                            <{foreach from=$field.fieldvalues item=value key=key}>
                                <option value="<{$key}>"
                                        <{if $field.value == $value}>selected="selected"<{/if}>><{$value}></option>
                            <{/foreach}>
                        </select>
                    <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_MULTISELECT}>
                        <select name="<{$field.fieldname}>" id="<{$field.fieldname}>" size="3" multiple="multiple">
                            <{foreach from=$field.fieldvalues item=value key=key}>
                                <option value="<{$key}>"
                                        <{if $field.value == $key}>selected="selected"<{/if}>><{$value}></option>
                            <{/foreach}>
                        </select>
                    <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_YESNO}>
                        <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}>1" value="1"
                               <{if $field.value == $smarty.const._YES}>checked<{/if}>><{$smarty.const._XHELP_TEXT_YES}>
                        <br>
                        <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}>0" value="0"
                               <{if $field.value == $smarty.const._NO}>checked<{/if}>><{$smarty.const._XHELP_TEXT_NO}>
                    <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_CHECKBOX}>
                        <{foreach from=$field.fieldvalues item=value key=key}>
                            <input type="checkbox" name="<{$field.fieldname}>" id="<{$field.fieldname}><{$key}>"
                                   value="<{$key}>" <{if $value == $field.value}>checked<{/if}>><{$value}>
                            <br>
                        <{/foreach}>
                    <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_RADIOBOX}>
                        <{foreach from=$field.fieldvalues item=value key=key}>
                            <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}><{$key}>"
                                   value="<{$key}>" <{if $value == $field.value}>checked<{/if}>><{$value}>
                            <br>
                        <{/foreach}>
                    <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_DATETIME}>
                        <input type="text" name="<{$field.fieldname}>" id="<{$field.fieldname}>"
                               value="<{$field.value}>" maxlength="<{$field.maxlength}>" size="<{$field.fieldlength}>">
                    <{else}>
                        <!-- else is for XHELP_CONTROL_FILE-->
                        <!--<input type="file" name="<{$field.fieldname}>" id="<{$field.fieldname}>" value="" size="<{$field.fieldlength}>">-->
                        <{if $field.filename|default:'' != ''}>
                            <a href="<{$smarty.const.XHELP_BASE_URL}>/viewFile.php?id=<{$field.fileid}>"><{$field.filename}></a>
                            <a href="ticket.php?op=deleteFile&amp;id=<{$xhelp_ticketID}>&amp;fileid=<{$field.fileid}>&amp;field=<{$field.fieldname}>"><img
                                        src="<{$xhelp_imagePath}>button_delete.png"
                                        alt="<{$smarty.const._XHELP_BUTTON_DELETE}>"></a>
                        <{else}>
                            <input type="hidden" name="<{$field.fieldname}>" id="<{$field.fieldname}>" value="">
                        <{/if}>
                    <{/if}>
                </td>
            </tr>
        <{/foreach}>
        <tr id="editButtons">
            <td class="head">
            </td>
            <td class="even">
                <input type="submit" name="editTicket" id="editTicket"
                       value="<{$smarty.const._XHELP_BUTTON_EDITTICKET}>" class="formButton">
                <input type="reset" value="<{$smarty.const._XHELP_BUTTON_RESET}>" class="formButton">
            </td>
        </tr>
    </table>
</form>
