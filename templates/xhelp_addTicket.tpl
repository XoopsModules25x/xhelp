<{if $xhelp_errors neq null}>   <{* Errors on ticket submission? *}>
    <div id="readOnly" class="errorMsg"
         style="border:1px solid #D24D00; background:#FEFECC no-repeat 7px 50%;color:#333;padding-left:45px;">
        <img src="<{$xhelp_imagePath}>important.png">
        <{$smarty.const._XHELP_MESSAGE_VALIDATE_ERROR}><br>
        <{foreach from=$xhelp_errors item=error key=key}>
            <li><a href="#<{$key}>" onclick="document.addTicket.<{$key}>.focus();"><{$key}><{$error}></a></li>
        <{/foreach}>
    </div>
    <br>
<{/if}>
<{if $xhelp_isStaff}>   <{* staff or user?: include appropriate header *}>
    <{include file='db:xhelp_staff_header.tpl'}>
<{elseif $xhelp_isUser}>
    <{include file='db:xhelp_user_header.tpl'}>
<{/if}>

<{* javascript file for multiple file uploads *}>
<{if $xhelp_allowUpload eq 1}>
    <script src="<{$xhelp_includeURL}>/multifile.js"></script>
    <script type="text/javascript">
        function createMultiSelector() {
            <!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->
            var multi_selector = new MultiSelector(document.getElementById('files_list'), <{$xhelp_numTicketUploads}> );
            <!-- Pass in the file element -->
            multi_selector.addElement(document.getElementById('userfile'));
            return;
        }
    </script>
<{/if}>

<form method="post" enctype="multipart/form-data" action="<{$xhelp_baseURL}>/<{$xhelp_current_file}>" name="addTicket" id="addTicket">
    <{securityToken}><{*//mb*}>
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton" id="tblAddTicket">
        <tr>
            <th colspan="2">
                <{if $xhelp_isStaff && $xhelp_logMode eq 1}>
                    <a href="addTicket.php?view_id=2"
                       style="float:right;"><{$smarty.const._XHELP_TEXT_SWITCH_TO}><{$smarty.const._XHELP_TEXT_VIEW2}></a>
                <{elseif $xhelp_isStaff && $xhelp_logMode neq 1}>
                    <a href="addTicket.php?view_id=1"
                       style="float:right;"><{$smarty.const._XHELP_TEXT_SWITCH_TO}><{$smarty.const._XHELP_TEXT_VIEW1}></a>
                <{/if}>
                <img src="<{$xhelp_imagePath}>addTicket.png"
                     alt="<{$smarty.const._XHELP_TITLE_ADDTICKET}>"> <{$smarty.const._XHELP_TITLE_ADDTICKET}>
            </th>
        </tr>
        <tr>
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_NAME}>
            </td>
            <td class="even">
                <label for="fullname"></label><input type="text" id="fullname" name="fullname" class="formButton" value="<{$xhelp_ticket_username}>"
                                                     disabled="disabled">
                <{if $xhelp_isStaff && $xhelp_has_logUser}>
                    <input type="hidden" id="user_id" name="user_id" class="formButton" value="<{$xhelp_ticket_uid}>">
                    <a href="javascript:openWithSelfMain('lookup.php','lookup',400,300);"><img
                                src="<{$xhelp_imagePath}>lookup.png"
                                title="<{$smarty.const._XHELP_TEXT_LOOKUP_USER}>"> <{$smarty.const._XHELP_TEXT_LOOKUP_USER}>
                    </a>
                <{else}>
                    <input type="hidden" name="user_id" class="formButton" value="<{$xhelp_ticket_uid}>">
                <{/if}>
            </td>
        </tr>
        <tr>
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_ASSIGNTO}>
            </td>
            <td class="even">
                <label for="departments"></label><select name="departments" id="departments">
                    <{foreach from=$xhelp_departments item=dept}>
                        <option value="<{$dept.id}>"
                                <{if $xhelp_ticket_department eq $dept.id}>selected="selected"<{/if}>><{$dept.department}></option>
                    <{/foreach}>
                </select>
            </td>
        </tr>
        <tr id="priority">
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_PRIORITY}>
            </td>
            <td class="even">
                <{foreach from=$xhelp_priorities item=priority}>
                    <input type="radio" value="<{$priority}>" id="priority<{$priority}>" name="priority"
                           <{if $xhelp_ticket_priority eq $priority}>checked<{/if}>>
                    <label for="priority<{$priority}>"><img src="<{$xhelp_imagePath}>priority<{$priority}>.png"
                                                            alt="<{$xhelp_priorities_desc.$priority}>"></label>
                <{/foreach}>
            </td>
        </tr>
        <tr>
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_SUBJECT}>
            </td>
            <td class="even">
                <label for="subject"></label><input type="text" name="subject" id="subject" maxlength="100" size="67"
                                                    value="<{$xhelp_ticket_subject}>" class="<{$xhelp_element_subject}>">
            </td>
        </tr>
        <tr>
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_DESCRIPTION}>
            </td>
            <td class="even">
                <label for="description"></label><textarea name="description" id="description" rows="5" cols="50"
                                                           class="<{$xhelp_element_description}>"><{$xhelp_ticket_description}></textarea>
            </td>
        </tr>
        <{if $xhelp_isStaff && $xhelp_logMode eq 1}>
            <{if $xhelp_allowUpload eq 1}>
                <tr>
                    <td class="head" width="20%">
                        <{$smarty.const._XHELP_TEXT_ADDFILE}>
                    </td>
                    <td class="even">
                        <input name="userfile_1" id="userfile" type="file" class="formButton">
                        <div id="files_list"></div>
                        <script type="text/javascript">
                            createMultiSelector();
                        </script>
                    </td>
                </tr>
            <{/if}>
        <{else}>
            <{if $xhelp_allowUpload eq 1}>
                <tr>
                    <td class="head" width="20%">
                        <{$smarty.const._XHELP_TEXT_ADDFILE}>
                    </td>
                    <td class="even">
                        <input name="userfile_1" id="userfile" type="file" class="formButton">
                        <div id="files_list"></div>
                        <script>
                            createMultiSelector();
                        </script>
                    </td>
                </tr>
            <{/if}>
        <{/if}>
        <{* Start of new response code *}>
        <{if $xhelp_isStaff && $xhelp_logMode eq 2}>
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_RESPONSE}>
                </td>
                <td class="even">
                    <label for="response"></label><textarea name="response" id="response" rows="5" cols="50"
                                                            class="<{$xhelp_element_response|default:''}>"><{$xhelp_response_message}></textarea>
                </td>
            </tr>
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_TIMESPENT}>
                </td>
                <td class="even">
                    <label for="timespent"></label><input type="text" name="timespent" id="timespent" value="<{$xhelp_response_timespent|default:''}>"
                                                          class="<{$xhelp_element_timespent|default:''}>"><{$smarty.const._XHELP_TEXT_MINUTES}>
                </td>
            </tr>
            <{if $xhelp_isStaff && $xhelp_logMode neq 1}>
                <{if $xhelp_allowUpload eq 1}>
                    <tr>
                        <td class="head" width="20%">
                            <{$smarty.const._XHELP_TEXT_ADDFILE}>
                        </td>
                        <td class="even">
                            <input name="userfile_1" id="userfile" type="file" class="formButton">
                            <div id="files_list"></div>
                            <script>
                                createMultiSelector();
                            </script>
                        </td>
                    </tr>
                <{/if}>
            <{/if}>
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_STATUS}>
                </td>
                <td class="even">
                    <label for="status"></label><select name="status" id="status">
                        <{foreach from=$xhelp_statuses item=status}>
                            <option value="<{$status.id}>"
                                    <{if $status.id eq $xhelp_ticket_status}>selected="selected"<{/if}>><{$status.desc}></option>
                        <{/foreach}>
                    </select>
                </td>
            </tr>
            <{if $xhelp_aOwnership neq false}>
                <tr>
                    <td class="head" width="20%">
                        <{$smarty.const._XHELP_TEXT_ASSIGN_OWNER}>
                    </td>
                    <td class="even">
                        <label for="owner"></label><select name="owner" id="owner" class="formButton">
                            <{foreach from=$xhelp_aOwnership item=uname key=uid}>
                                <option value="<{$uid}>"
                                        <{if $xhelp_ticket_ownership eq $uid}>selected="selected"<{/if}>><{$uname}></option>
                            <{/foreach}>
                        </select>
                    </td>
                </tr>
            <{/if}>
            <tr id="privResponse">
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_PRIVATE_RESPONSE}>
                </td>
                <td class="even">
                    <{if $xhelp_response_private eq false}>
                        <label>
                            <input type="checkbox" name="private" value="1" class="formButton">
                        </label>
                    <{else}>
                        <label>
                            <input type="checkbox" name="private" value="1" class="formButton" checked>
                        </label>
                    <{/if}>
                </td>
            </tr>
        <{/if}>
        <{* End of new response code *}>
        <{* Start of custom field code *}>
        <{if $xhelp_hasCustFields}>
            <{foreach from=$xhelp_custFields item=field}>
                <tr class="custfld">
                    <td class="head" width="20%">
                        <{$field.name}>:
                        <{if $field.desc|default:'' != ''}>
                            <br>
                            <br>
                            <{$field.desc}>
                        <{/if}>
                    </td>
                    <td class="even">
                        <{if $field.controltype == $smarty.const.XHELP_CONTROL_TXTBOX}>
                            <label for="<{$field.fieldname}>"></label>
                            <input type="text" name="<{$field.fieldname}>" id="<{$field.fieldname}>"
                                   value="<{$field.defaultvalue}>" maxlength="<{$field.maxlength}>"
                                   size="<{$field.fieldlength}>">
                        <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_TXTAREA}>
                            <label for="<{$field.fieldname}>"></label>
                            <textarea name="<{$field.fieldname}>" id="<{$field.fieldname}>"
                                      cols="<{$field.fieldlength}>" rows="5"><{$field.defaultvalue}></textarea>
                        <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_SELECT}>
                            <label for="<{$field.fieldname}>"></label>
                            <select name="<{$field.fieldname}>" id="<{$field.fieldname}>" size="1">
                                <{foreach from=$field.fieldvalues item=value key=key}>
                                    <option value="<{$key}>"
                                            <{if $field.defaultvalue == $key}>selected="selected"<{/if}>><{$value}></option>
                                <{/foreach}>
                            </select>
                        <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_MULTISELECT}>
                            <label for="<{$field.fieldname}>"></label>
                            <select name="<{$field.fieldname}>" id="<{$field.fieldname}>" size="3" multiple="multiple">
                                <{foreach from=$field.fieldvalues item=value key=key}>
                                    <option value="<{$key}>"
                                            <{if $field.defaultvalue == $key}>selected="selected"<{/if}>><{$value}></option>
                                <{/foreach}>
                            </select>
                        <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_YESNO}>
                            <label for="<{$field.fieldname}>1"></label>
                            <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}>1" value="1"
                                   <{if $field.defaultvalue == 1}>checked<{/if}>><{$smarty.const._XHELP_TEXT_YES}>
                            <br>
                            <label for="<{$field.fieldname}>0"></label>
                            <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}>0" value="0"
                                   <{if $field.defaultvalue == 0}>checked<{/if}>><{$smarty.const._XHELP_TEXT_NO}>
                        <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_CHECKBOX}>
                            <{foreach from=$field.fieldvalues item=value key=key}>
                                <label for="<{$field.fieldname}><{$key}>"></label>
                                <input type="checkbox" name="<{$field.fieldname}>" id="<{$field.fieldname}><{$key}>"
                                       value="<{$key}>"
                                       <{if $key == $field.defaultvalue}>checked<{/if}>><{$value}>
                                <br>
                            <{/foreach}>
                        <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_RADIOBOX}>
                            <{foreach from=$field.fieldvalues item=value key=key}>
                                <label for="<{$field.fieldname}><{$key}>"></label>
                                <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}><{$key}>"
                                       value="<{$key}>"
                                       <{if $key == $field.defaultvalue}>checked<{/if}>><{$value}>
                                <br>
                            <{/foreach}>
                        <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_DATETIME}>
                            <label for="<{$field.fieldname}>"></label>
                            <input type="text" name="<{$field.fieldname}>" id="<{$field.fieldname}>"
                                   value="<{$field.defaultvalue}>" maxlength="<{$field.maxlength}>"
                                   size="<{$field.fieldlength}>">
                        <{else}>
                            <!-- else is for XHELP_CONTROL_FILE-->
                            <input type="file" name="<{$field.fieldname}>" id="<{$field.fieldname}>" value=""
                                   size="<{$field.fieldlength}>">
                        <{/if}>
                    </td>
                </tr>
            <{/foreach}>
        <{/if}>
        <{* End of custom field code *}>
        <tr id="addButtons">
            <td class="head" width="20%">
            </td>
            <td class="even">
                <input type="submit" name="addTicket" value="<{$smarty.const._XHELP_BUTTON_ADDTICKET}>"
                       class="formButton">
                <{if $xhelp_aOwnership eq false}>
                    <input type="hidden" name="owner" value="0">
                <{/if}>
            </td>
        </tr>
    </table>
</form>

<{if $xhelp_allowUpload eq 1}>
    <br>
    <fieldset>
        <legend><{$smarty.const._XHELP_TEXT_AVAIL_FILETYPES}></legend>
        <div id="mimetypes">
            <{$xhelp_mimetypes}>
        </div>
    </fieldset>
<{/if}>
