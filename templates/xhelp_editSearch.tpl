<{include file='db:xhelp_staff_header.tpl'}>
<div id="search">
    <form method="post"
          action="<{$xhelp_baseURL}>/search.php<{if $xhelp_returnPage neq false}>?return=<{$xhelp_returnPage}><{/if}>">
        <{securityToken}><{*//mb*}>
        <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
            <tr>
                <th colspan="2">
                    <{$smarty.const._XHELP_TITLE_SEARCH}>
                </th>
            </tr>
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_SEARCH_NAME}>
                </td>
                <td class="even">
                    <{$xhelp_searchName}>
                    <input type="hidden" name="searchid" id="searchid" value="<{$xhelp_searchid}>">
                    <input type="hidden" name="searchName" id="searchName" value="<{$xhelp_searchName}>">
                </td>
            </tr>
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_ID}>
                </td>
                <td class="even">
                    <label for="ticketid"></label><input type="text" name="ticketid" id="ticketid" value="<{$xhelp_searchticketid}>" size="6">
                </td>
            </tr>
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_DEPARTMENT}>
                </td>
                <td class="even">
                    <label>
                        <select name="department[]" multiple="multiple" size="6">
                            <{html_options options=$xhelp_depts selected=$xhelp_searchdepartment}>
                        </select>
                    </label>
                </td>
            </tr>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_PRIORITY}>
                </td>
                <td class="even">
                    <{foreach from=$xhelp_priorities item=priority}>
                        <label>
                            <input type="radio" value="<{$priority}>" name="priority"
                                   <{if $xhelp_searchpriority == $priority}>checked <{/if}>>
                        </label>
                        <img src="<{$xhelp_imagePath}>priority<{$priority}>.png"
                             alt="<{$xhelp_priorities_desc.$priority}>">
                    <{/foreach}>
                    <label>
                        <input type="radio" value="-1" name="priority"
                               <{if $xhelp_searchpriority == -1}>checked <{/if}>>
                    </label><{$smarty.const._XHELP_TEXT_SELECT_ALL}>
                </td>
            </tr>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_STATUS}>
                </td>
                <td class="even">
                    <b><{$smarty.const._XHELP_TEXT_BY_STATUS}></b>
                    <br>
                    &nbsp;&nbsp;&nbsp;<label>
                        <select name="status[]" multiple="multiple">
                            <{html_options options=$xhelp_statuses selected=$xhelp_searchstatus}>
                        </select>
                    </label>
                    <br><br><b><{$smarty.const._XHELP_TEXT_SEARCH_OR}></b><br><br>
                    <b><{$smarty.const._XHELP_TEXT_BY_STATE}></b>
                    <br>
                    &nbsp;&nbsp;&nbsp;<label>
                        <input type="radio" value="1" name="state"
                                                 <{if $xhelp_searchstate == 1}>checked <{/if}>>
                    </label><{$smarty.const._XHELP_STATE1}>
                    <label>
                        <input type="radio" value="2" name="state"
                               <{if $xhelp_searchstate == 2}>checked <{/if}>>
                    </label><{$smarty.const._XHELP_STATE2}>
                    <label>
                        <input type="radio" value="-1" name="state"
                               <{if $xhelp_searchstate == -1 || $xhelp_searchstate == ''}>checked <{/if}>>
                    </label><{$smarty.const._XHELP_TEXT_SELECT_ALL}>
                </td>
            </tr>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_SUBJECT}>
                </td>
                <td class="even">
                    <label>
                        <input type="text" name="subject" value="<{$xhelp_searchsubject}>">
                    </label>
                </td>
            </tr>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_DESCRIPTION}>
                </td>
                <td class="even">
                    <label>
                        <input type="text" name="description" value="<{$xhelp_searchdescription}>">
                    </label>
                </td>
            </tr>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_SUBMITTEDBY}>
                </td>
                <td class="even">
                    <label>
                        <input type="text" name="submittedBy" value="<{$xhelp_searchsubmittedBy}>">
                    </label>
                </td>
            </tr>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_OWNER}>
                </td>
                <td class="even">
                    <label>
                        <select name="ownership">
                            <{html_options options=$xhelp_staff selected=$xhelp_searchownership}>
                        </select>
                    </label>
                </td>
            </tr>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_CLOSEDBY}>
                </td>
                <td class="even">
                    <label>
                        <select name="closedBy">
                            <{html_options options=$xhelp_staff selected=$xhelp_searchclosedBy}>
                        </select>
                    </label>
                </td>
            </tr>
            <{if $xhelp_hasCustFields}>
                <{foreach from=$xhelp_custFields item=field}>
                    <{if $field.controltype != $smarty.const.XHELP_CONTROL_FILE}>
                        <tr class="custfld">
                            <td class="head">
                                <{$field.name}>:
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
                                        <option value="-1"
                                                <{if $field.defaultvalue == -1}>selected="selected"<{/if}>><{$smarty.const._XHELP_TEXT_SELECT_ALL}></option>
                                        <{foreach from=$field.fieldvalues item=value key=key}>
                                            <option value="<{$key}>"
                                                    <{if $field.defaultvalue == $key}>selected="selected"<{/if}>><{$value}></option>
                                        <{/foreach}>
                                    </select>
                                <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_MULTISELECT}>
                                    <label for="<{$field.fieldname}>"></label>
                                    <select name="<{$field.fieldname}>" id="<{$field.fieldname}>" size="3"
                                            multiple="multiple">
                                        <{foreach from=$field.fieldvalues item=value key=key}>
                                            <option value="<{$key}>"
                                                    <{if $field.defaultvalue == $key}>selected="selected"<{/if}>><{$value}></option>
                                        <{/foreach}>
                                    </select>
                                <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_YESNO}>
                                    <label for="<{$field.fieldname}>"></label>
                                    <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}>" value="1"
                                           <{if $field.defaultvalue == 1}>checked<{/if}>><{$smarty.const._XHELP_TEXT_YES}>
                                    <br>
                                    <label for="<{$field.fieldname}>"></label>
                                    <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}>" value="0"
                                           <{if $field.defaultvalue == 0}>checked<{/if}>><{$smarty.const._XHELP_TEXT_NO}>
                                    <br>
                                    <label for="<{$field.fieldname}>"></label>
                                    <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}>" value="-1"
                                           <{if $field.defaultvalue == -1}>checked<{/if}> ><{$smarty.const._XHELP_TEXT_SELECT_ALL}>
                                <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_CHECKBOX}>
                                    <{foreach from=$field.fieldvalues item=value key=key}>
                                        <label for="<{$field.fieldname}><{$key}>"></label>
                                        <input type="checkbox" name="<{$field.fieldname}>"
                                               id="<{$field.fieldname}><{$key}>" value="<{$key}>"
                                               <{if $field.defaultvalue == $key}>checked<{/if}>><{$value}>
                                        <br>
                                    <{/foreach}>
                                    <label for="<{$field.fieldname}>"></label>
                                    <input type="checkbox" name="<{$field.fieldname}>" id="<{$field.fieldname}>"
                                           value="-1"
                                           <{if $field.defaultvalue == -1}>checked<{/if}>><{$smarty.const._XHELP_TEXT_SELECT_ALL}>
                                <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_RADIOBOX}>
                                    <{foreach from=$field.fieldvalues item=value key=key}>
                                        <label for="<{$field.fieldname}><{$key}>"></label>
                                        <input type="radio" name="<{$field.fieldname}>"
                                               id="<{$field.fieldname}><{$key}>" value="<{$key}>"
                                               <{if $field.defaultvalue == $key}>checked<{/if}>><{$value}>
                                        <br>
                                    <{/foreach}>
                                    <label for="<{$field.fieldname}>"></label>
                                    <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}>" value="-1"
                                           <{if $field.defaultvalue == -1}>checked<{/if}>><{$smarty.const._XHELP_TEXT_SELECT_ALL}>
                                <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_DATETIME}>
                                    <label for="<{$field.fieldname}>"></label>
                                    <input type="text" name="<{$field.fieldname}>" id="<{$field.fieldname}>"
                                           value="<{$field.defaultvalue}>" maxlength="<{$field.maxlength}>"
                                           size="<{$field.fieldlength}>">
                                <{else}>
                                    <{* else is for XHELP_CONTROL_FILE *}>
                                    <{* do nothing for a file *}>
                                <{/if}>
                            </td>
                        </tr>
                    <{/if}>
                <{/foreach}>
            <{/if}>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_NUMRESULTS}>
                </td>
                <td class="even">
                    <label>
                        <select name="limit">
                            <option value="<{$smarty.const._XHELP_TEXT_RESULT1}>"
                                    <{if $xhelp_searchLimit == $smarty.const._XHELP_TEXT_RESULT1}>selected="selected"<{/if}>><{$smarty.const._XHELP_TEXT_RESULT1}></option>
                            <option value="<{$smarty.const._XHELP_TEXT_RESULT2}>"
                                    <{if $xhelp_searchLimit == $smarty.const._XHELP_TEXT_RESULT2}>selected="selected"<{/if}>><{$smarty.const._XHELP_TEXT_RESULT2}></option>
                            <option value="<{$smarty.const._XHELP_TEXT_RESULT3}>"
                                    <{if $xhelp_searchLimit == $smarty.const._XHELP_TEXT_RESULT3}>selected="selected"<{/if}>><{$smarty.const._XHELP_TEXT_RESULT3}></option>
                            <option value="<{$smarty.const._XHELP_TEXT_RESULT4}>"
                                    <{if $xhelp_searchLimit == $smarty.const._XHELP_TEXT_RESULT4}>selected="selected"<{/if}>><{$smarty.const._XHELP_TEXT_RESULT4}></option>
                        </select>
                    </label>
                </td>
            </tr>
            <tr class="foot">
                <td colspan="2">
                    <input type="hidden" name="save" value="1">
                    <input type="submit" name="search" value="<{$smarty.const._XHELP_BUTTON_SEARCH}>">
                    <input type="reset" name="reset" value="<{$smarty.const._XHELP_BUTTON_RESET}>">
                </td>
            </tr>
        </table>
    </form>
</div>
