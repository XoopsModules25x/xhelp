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

<{* javascript file for multiple file uploads *}>
<{if $xhelp_allowUpload eq 1}>
    <script src="<{$xhelp_includeURL}>/multifile.js"></script>
<{/if}>

<form method="post" enctype="multipart/form-data" action="<{$xhelp_current_file}>" name="addTicket">
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton" id="tblAddTicket">
        <tr>
            <th colspan="2">
                <img src="<{$xhelp_imagePath}>addTicket.png"
                     alt="<{$smart.const._XHELP_TITLE_ADDTICKET}>"> <{$smarty.const._XHELP_TITLE_ADDTICKET}>
            </th>
        </tr>
        <tr>
            <td class="head">
                Email:
            </td>
            <td class="even">
                <label for="email"></label><input type="textbox" name="email" id="email" value="<{$xhelp_email}>"
                                                  class="<{$xhelp_element_email}>">
            </td>
        </tr>
        <tr>
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_ASSIGNTO}>
            </td>
            <td class="even">
                <label for="departments"></label><select name="departments" id="departments">
                    <{foreach from=$xhelp_departments item=dept}>
                        <{if $xhelp_ticket_department eq $dept.id || $xhelp_default_dept eq $dept.id}>
                            <option value="<{$dept.id}>" selected="selected"><{$dept.department}></option>
                        <{else}>
                            <option value="<{$dept.id}>"><{$dept.department}></option>
                        <{/if}>
                    <{/foreach}>
                </select>
            </td>
        </tr>
        <tr id="priority">
            <td class="head">
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
            <td class="head">
                <{$smarty.const._XHELP_TEXT_SUBJECT}>
            </td>
            <td class="even">
                <label for="subject"></label><input type="text" name="subject" id="subject" maxlength="100" size="67"
                                                    value="<{$xhelp_ticket_subject}>" class="<{$xhelp_element_subject}>">
            </td>
        </tr>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_DESCRIPTION}>
            </td>
            <td class="even">
                <label for="description"></label><textarea name="description" id="description" rows="10" cols="50"
                                                           class="<{$xhelp_element_description}>"><{$xhelp_ticket_description}></textarea>
            </td>
        </tr>
        <{if $xhelp_allowUpload eq 1}>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_ADDFILE}>
                </td>
                <td class="even">
                    <input name="userfile_1" id="userfile" type="file" class="formButton">
                    <div id="files_list"></div>
                    <script type="text/javascript">
                        <!--
                        Create
                        an
                        instance
                        of
                        the
                        multiSelector

                        class

                        ,
                        pass
                        it
                        the
                        output
                        target
                        and
                        the
                        max
                        number
                        of
                        files -->
                        var multi_selector = new MultiSelector(document.getElementById('files_list'),<{$xhelp_numTicketUploads}>);
                        <!-- Pass in the file element -->
                        multi_selector.addElement(document.getElementById('userfile'));
                    </script>
                </td>
            </tr>
        <{/if}>
        <{* Start custom field code *}>
        <{if $xhelp_hasCustFields}>
            <{foreach from=$xhelp_custFields item=field}>
                <tr class="custfld">
                    <td class="head">
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
                            <{* else is for XHELP_CONTROL_FILE *}>
                            <input type="file" name="<{$field.fieldname}>" id="<{$field.fieldname}>" value=""
                                   size="<{$field.fieldlength}>">
                        <{/if}>
                    </td>
                </tr>
            <{/foreach}>
        <{/if}>
        <{* End custom field code *}>
        <tr id="addButtons">
            <td class="head">
            </td>
            <{if $xhelp_allowUpload neq 1}>
            <td class="even">
                <{else}>
            <td class="even">
                <{/if}>
                <input type="submit" name="addTicket" value="<{$smarty.const._XHELP_BUTTON_SUBMIT}>"
                       class="formButton">
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
