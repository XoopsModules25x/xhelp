<{include file='db:xhelp_staff_header.tpl'}>

<{if $xhelp_viewResults neq true}>  <{* view results ? *}>
    <{if $xhelp_savedSearches neq false}>   <{* any saved searches? *}>
        <div id="Xhelp\SavedSearches">
            <form name="savedSearches" method="post" action="<{$xhelp_baseURL}>/search.php">
                <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
                    <tr>
                        <th colspan="2">
                            <{$smarty.const._XHELP_TEXT_SAVED_SEARCHES}>
                        </th>
                    </tr>
                    <tr>
                        <td class="head" width="20%">
                            <{$smarty.const._XHELP_TEXT_SEARCH_NAME}>
                        </td>
                        <td class="even">
                            <label>
                                <select name="savedSearch">
                                    <{foreach from=$xhelp_savedSearches item=search}>
                                        <option value="<{$search.id}>"><{$search.name}></option>
                                    <{/foreach}>
                                </select>
                            </label>
                            <input type="submit" name="delete_savedSearch"
                                   value="<{$smarty.const._XHELP_BUTTON_DELETE}>">
                        </td>
                    </tr>
                    <tr class="foot">
                        <td colspan="2">
                            <input type="submit" name="runSavedSearch" id="runSavedSearch"
                                   value="<{$smarty.const._XHELP_BUTTON_RUN}>">
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    <{/if}>
    <br>
    <div id="search">
        <form method="post"
              action="<{$xhelp_baseURL}>/search.php<{if $xhelp_returnPage neq false}>?return=<{$xhelp_returnPage}><{/if}>">
            <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
                <tr>
                    <th colspan="2">
                        <{$smarty.const._XHELP_TITLE_SEARCH}>
                    </th>
                </tr>
                <tr>
                    <td class="head" width="20%">
                        <{$smarty.const._XHELP_TEXT_ID}>
                    </td>
                    <td class="even">
                        <label for="ticketid"></label><input type="text" name="ticketid" id="ticketid" size="6">
                    </td>
                </tr>
                <tr>
                    <td class="head" width="20%">
                        <{$smarty.const._XHELP_TEXT_DEPARTMENT}>
                    </td>
                    <td class="even">
                        <label>
                            <select name="department[]" multiple="multiple" size="6">
                                <{html_options options=$xhelp_depts}>
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
                                <input type="radio" value="<{$priority}>" name="priority">
                            </label>
                            <img src="<{$xhelp_imagePath}>priority<{$priority}>.png"
                                 alt="<{$xhelp_priorities_desc.$priority}>">
                        <{/foreach}>
                        <label>
                            <input type="radio" value="-1" name="priority"
                                   checked>
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
                                <{foreach from=$xhelp_statuses item=status}>
                                    <option value="<{$status.id}>"
                                            <{if $xhelp_ticket_status eq $status.id}>selected="selected"<{/if}>><{$status.desc}></option>
                                <{/foreach}>
                            </select>
                        </label>
                        <br><br><b><{$smarty.const._XHELP_TEXT_SEARCH_OR}></b><br><br>
                        <b><{$smarty.const._XHELP_TEXT_BY_STATE}></b>
                        <br>
                        &nbsp;&nbsp;&nbsp;<label>
                            <input type="radio" value="1" name="state">
                        </label><{$smarty.const._XHELP_STATE1}>
                        <label>
                            <input type="radio" value="2" name="state">
                        </label><{$smarty.const._XHELP_STATE2}>
                        <label>
                            <input type="radio" value="-1" name="state">
                        </label><{$smarty.const._XHELP_TEXT_SELECT_ALL}>
                    </td>
                </tr>
                <tr>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_DATE}>
                    </td>
                    <td class="even">
                        <{$dateform}>
                    </td>
                </tr>
                <tr>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_SUBJECT}>
                    </td>
                    <td class="even">
                        <label>
                            <input type="text" name="subject">
                        </label>
                    </td>
                </tr>
                <tr>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_DESCRIPTION}>
                    </td>
                    <td class="even">
                        <label>
                            <input type="text" name="description">
                        </label>
                    </td>
                </tr>
                <tr>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_SUBMITTEDBY}>
                    </td>
                    <td class="even">
                        <label>
                            <input type="text" name="submittedBy">
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
                                <{html_options options=$xhelp_staff}>
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
                                <{html_options options=$xhelp_staff}>
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
                                                  cols="<{$field.fieldlength}>"
                                                  rows="5"><{$field.defaultvalue}></textarea>
                                    <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_SELECT}>
                                        <label for="<{$field.fieldname}>"></label>
                                        <select name="<{$field.fieldname}>" id="<{$field.fieldname}>" size="1">
                                            <option value="-1"
                                                    selected="selected"><{$smarty.const._XHELP_TEXT_SELECT_ALL}></option>
                                            <{foreach from=$field.fieldvalues item=value key=key}>
                                                <option value="<{$key}>"><{$value}></option>
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
                                        <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}>"
                                               value="1"><{$smarty.const._XHELP_TEXT_YES}>
                                        <br>
                                        <label for="<{$field.fieldname}>"></label>
                                        <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}>"
                                               value="0"><{$smarty.const._XHELP_TEXT_NO}>
                                        <br>
                                        <label for="<{$field.fieldname}>"></label>
                                        <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}>"
                                               value="-1" checked><{$smarty.const._XHELP_TEXT_SELECT_ALL}>
                                    <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_CHECKBOX}>
                                        <{foreach from=$field.fieldvalues item=value key=key}>
                                            <label for="<{$field.fieldname}><{$key}>"></label>
                                            <input type="checkbox" name="<{$field.fieldname}>"
                                                   id="<{$field.fieldname}><{$key}>" value="<{$key}>"><{$value}>
                                            <br>
                                        <{/foreach}>
                                        <label for="<{$field.fieldname}>"></label>
                                        <input type="checkbox" name="<{$field.fieldname}>" id="<{$field.fieldname}>"
                                               value="-1" checked><{$smarty.const._XHELP_TEXT_SELECT_ALL}>
                                    <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_RADIOBOX}>
                                        <{foreach from=$field.fieldvalues item=value key=key}>
                                            <label for="<{$field.fieldname}><{$key}>"></label>
                                            <input type="radio" name="<{$field.fieldname}>"
                                                   id="<{$field.fieldname}><{$key}>" value="<{$key}>"><{$value}>
                                            <br>
                                        <{/foreach}>
                                        <label for="<{$field.fieldname}>"></label>
                                        <input type="radio" name="<{$field.fieldname}>" id="<{$field.fieldname}>"
                                               value="-1" checked><{$smarty.const._XHELP_TEXT_SELECT_ALL}>
                                    <{elseif $field.controltype == $smarty.const.XHELP_CONTROL_DATETIME}>
                                        <label for="<{$field.fieldname}>"></label>
                                        <input type="text" name="<{$field.fieldname}>" id="<{$field.fieldname}>"
                                               value="" maxlength="<{$field.maxlength}>" size="<{$field.fieldlength}>">
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
                                <option value="<{$smarty.const._XHELP_TEXT_RESULT1}>"><{$smarty.const._XHELP_TEXT_RESULT1}></option>
                                <option value="<{$smarty.const._XHELP_TEXT_RESULT2}>"><{$smarty.const._XHELP_TEXT_RESULT2}></option>
                                <option value="<{$smarty.const._XHELP_TEXT_RESULT3}>"><{$smarty.const._XHELP_TEXT_RESULT3}></option>
                                <option value="<{$smarty.const._XHELP_TEXT_RESULT4}>"
                                        selected="selected"><{$smarty.const._XHELP_TEXT_RESULT4}></option>
                            </select>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_SAVE_SEARCH}>
                    </td>
                    <td class="even">
                        <label>
                            <input type="checkbox" name="save" value="1" class="formButton">
                        </label>
                        &nbsp;&nbsp;<{$smarty.const._XHELP_TEXT_SEARCH_NAME}>
                        <label>
                            <input type="text" name="searchName" value="" class="formButton">
                        </label>
                    </td>
                </tr>
                <tr class="foot">
                    <td colspan="2">
                        <input type="submit" name="search" value="<{$smarty.const._XHELP_BUTTON_SEARCH}>">
                        <input type="reset" name="reset" value="<{$smarty.const._XHELP_BUTTON_RESET}>">
                    </td>
                </tr>
            </table>
        </form>
    </div>
<{else}>
    <div id="xhelpSearchResults">
        <form name="tickets" method="post" action="<{$xhelp_batch_form}>">
            <{include file='db:xhelp_staff_ticket_table.tpl'}>
            <br>
            <div id="xhelpBatchActions">
                <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
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
<{/if}>
