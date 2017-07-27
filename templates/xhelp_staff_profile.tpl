<{include file='db:xhelp_staff_header.tpl'}>

<div id="performance">
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
        <tr>
            <th colspan="2">
                <{$smarty.const._XHELP_TEXT_MY_PERFORMANCE}>
            </th>
        </tr>
        <tr>
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_RESPONSE_TIME}>
            </td>
            <td class="even">
                <{$xhelp_responseTime}>
            </td>
        </tr>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_RATING}>
            </td>
            <td class="even">
                <{if $xhelp_rating eq 1}>
                    <{$xhelp_rating1}>
                <{elseif $xhelp_rating eq 2}>
                    <{$xhelp_rating2}>
                <{elseif $xhelp_rating eq 3}>
                    <{$xhelp_rating3}>
                <{elseif $xhelp_rating eq 4}>
                    <{$xhelp_rating4}>
                <{elseif $xhelp_rating eq 5}>
                    <{$xhelp_rating5}>
                <{else}>
                    <{$xhelp_rating0}>
                <{/if}>
            </td>
        </tr>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_NUMREVIEWS}>
            </td>
            <td class="even">
                <{$xhelp_numReviews}>
            </td>
        </tr>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_NUM_TICKETS_CLOSED}>
            </td>
            <td class="even">
                <{$xhelp_callsClosed}>
            </td>
        </tr>
    </table>
</div>

<br>
<div id="ticketLists">
    <form method="post" action="<{$xhelp_baseURL}>/profile.php?op=addTicketList">
        <table width="100%" border="1">
            <tr>
                <th colspan="2"><{$smarty.const._XHELP_TEXT_TICKET_LISTS}></th>
            </tr>
            <{if $xhelp_hasTicketLists}>
                <tr class="head">
                    <td><{$smarty.const._XHELP_TEXT_LIST_NAME}></td>
                    <td align="right"><{$smarty.const._XHELP_TEXT_ACTIONS2}></td>
                </tr>
                <{foreach from=$xhelp_ticketLists item=ticketList}>
                    <tr class="even">
                        <td><{$ticketList.name}></td>
                        <td align="right">
                            <{if $ticketList.hasWeightUp}>
                                <a href="<{$xhelp_baseURL}>/profile.php?op=changeListWeight&amp;id=<{$ticketList.id}>&amp;up=0"><img
                                            src="<{$xhelp_imagePath}>/asc.png" alt="" title=""></a>
                            <{else}>
                                <img src="<{$xhelp_imagePath}>/arrow_blank.png" alt="" title="">
                            <{/if}>
                            <{if $ticketList.hasWeightDown}>
                                <a href="<{$xhelp_baseURL}>/profile.php?op=changeListWeight&amp;id=<{$ticketList.id}>&amp;up=1"><img
                                            src="<{$xhelp_imagePath}>/desc.png" alt="" title=""></a>
                            <{else}>
                                <img src="<{$xhelp_imagePath}>/arrow_blank.png" alt="" title="">
                            <{/if}>
                            <{if $ticketList.hasEdit}>
                                <a href="<{$xhelp_baseURL}>/search.php?op=edit&amp;id=<{$ticketList.searchid}>"><img
                                            src="<{$xhelp_imagePath}>/button_edit.png"
                                            alt="<{$smarty.const._XHELP_TEXT_EDIT}>"
                                            title="<{$smarty.const._XHELP_TEXT_EDIT}>"></a>
                            <{else}>
                                <img src="<{$xhelp_imagePath}>/button_blank.png" alt="" title="">
                            <{/if}>
                            <a href="<{$xhelp_baseURL}>/profile.php?op=deleteTicketList&amp;id=<{$ticketList.id}>"><img
                                        src="<{$xhelp_imagePath}>/button_delete.png"
                                        alt="<{$smarty.const._XHELP_TEXT_DELETE}>"
                                        title="<{$smarty.const._XHELP_TEXT_DELETE}>"></a>
                        </td>
                    </tr>
                <{/foreach}>
            <{else}>
                <tr class="even">
                    <td colspan="2"><{$smarty.const._XHELP_TEXT_NO_RECORDS}></td>
                </tr>
            <{/if}>
            <tr class="foot">
                <td colspan="2">
                    <{if $xhelp_hasUnusedSearches}>
                        <input type="submit" name="addTicketList" id="addTicketList"
                               value="<{$smarty.const._XHELP_TEXT_CREATE_NEW_LIST}>">
                        <select name="savedSearch">
                            <{foreach from=$xhelp_unusedSearches item=savedSearch}>
                                <option value="<{$savedSearch.id}>"><{$savedSearch.name}></option>
                            <{/foreach}>
                        </select>
                    <{/if}>
                    <a href="<{$xhelp_baseURL}>/search.php?return=profile"><{$smarty.const._XHELP_TEXT_CREATE_SAVED_SEARCH}></a>
                </td>
            </tr>
        </table>
    </form>
</div>

<br>
<div id="replies">
    <table width="100%" border="1">
        <tr>
            <td>
                <table width="100%" border="0" cellpadding="0" cellspacing="1" class="formButton">
                    <tr>
                        <th colspan="2">
                            <{$smarty.const._XHELP_TEXT_PREDEFINED_RESPONSES}>
                        </th>
                    </tr>
                    <tr>
                        <td class="head" width="20%">
                            <{$smarty.const._XHELP_TEXT_RESPONSES}>
                        </td>
                        <td class="even">
                            <form method="post" action="profile.php?op=responseTpl">
                                <{securityToken}><{*//mb*}>
                                <select name="replies"
                                        onchange="window.location='profile.php?responseTplID='+this.options[this.selectedIndex].value;">
                                    <option value="0"><{$smarty.const._XHELP_TEXT_PREDEFINED0}></option>
                                    <{if $xhelp_hasResponseTpl}>
                                        <{foreach from=$xhelp_responseTpl item=response}>
                                            <option value="<{$response.id}>"
                                                    <{if $xhelp_responseTplID eq $response.id}>selected="selected"<{/if}>><{$response.name}></option>
                                        <{/foreach}>
                                    <{/if}>
                                </select>
                                <{if $xhelp_displayTpl_id neq 0}>
                                    <input type="hidden" name="tplID" value="<{$xhelp_displayTpl_id}>">
                                    <input type="submit" name="delete_responseTpl"
                                           value="<{$smarty.const._XHELP_BUTTON_DELETE}>">
                                <{/if}>
                            </form>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <form method="post" action="profile.php?op=responseTpl">
                    <table width="100%" border="0" cellpadding="0" cellspacing="1" class="formButton">
                        <tr>
                            <td class="head" width="20%">
                                <{$smarty.const._XHELP_TEXT_TEMPLATE_NAME}>
                            </td>
                            <td class="even">
                                <input type="hidden" name="responseid" value="<{$xhelp_displayTpl_id}>">
                                <input type="text" name="name" value="<{$xhelp_displayTpl_name}>" class="formButton">
                            </td>
                        </tr>
                        <tr>
                            <td class="head">
                                <{$smarty.const._XHELP_TEXT_MESSAGE}>
                            </td>
                            <td class="even">
                                <textarea name="replyText" rows="10" cols="50"
                                          class="formButton"><{$xhelp_displayTpl_response}></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td class="head">
                                <{$smarty.const._XHELP_TEXT_ADD_SIGNATURE}>
                            </td>
                            <td class="even">
                                <input name="attachSig" value="1" type="radio" class="formButton"
                                       <{if $xhelp_has_sig neq 0}>checked="checked"<{/if}>><{$smarty.const._XHELP_TEXT_YES}>
                                <input name="attachSig" value="0" type="radio" class="formButton"
                                       <{if $xhelp_has_sig eq 0}>checked="checked"<{/if}>><{$smarty.const._XHELP_TEXT_NO}>
                            </td>
                        </tr>
                        <tr>
                            <td class="head">
                                <{$smarty.const._XHELP_TEXT_ACTIONS}>
                            </td>
                            <td class="even">
                                <input type="submit" name="updateResponse"
                                       value="<{$smarty.const._XHELP_BUTTON_UPDATE}>" class="formButton">
                                <input type="reset" value="<{$smarty.const._XHELP_BUTTON_RESET}>" class="formButton">
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
    </table>
</div>

<br>
<div id="staffNotify">
    <form name="notification_select" method="post" action="profile.php?op=updateNotification">
        <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
            <tr>
                <th colspan="2">
                    <{$smarty.const._XHELP_TEXT_MY_NOTIFICATIONS}>
                </th>
            </tr>
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_NOT_EMAIL}>
                </td>
                <td class="even">
                    <input type="text" name="email" value="<{$xhelp_staff_email}>" size="50" maxlength="255"
                           class="formButton">
                </td>
            </tr>
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_CURRENT_NOTIFICATION}>
                </td>
                <td class="even">
                    <{$xhelp_notify_method}>
                </td>
            </tr>
            <tr class="head">
                <td colspan="2">
                    &nbsp;<input name="allbox" id="allbox" onclick="xoopsCheckAll('notification_select','allbox');"
                                 type="checkbox" value="<{$smarty.const._XHELP_TEXT_SELECT_ALL}>">
                    <{$smarty.const._XHELP_TEXT_EVENT}>
                </td>
            </tr>
            <{foreach from=$xhelp_deptNotifications item=dept}>
                <tr class="<{cycle values="odd,even"}>">
                    <td colspan="2">
                        <input type="checkbox" name="notifications[]" value="<{$dept.bitValue}>"
                               <{if $dept.isChecked eq true}>checked="checked"<{/if}>
                                <{if $dept.staff_setting eq 0}>disabled="disabled"<{/if}> class="formButton">
                        <{$dept.caption}>
                        <{if $dept.staff_setting eq 0}>
                            &nbsp;<{$smarty.const._XHELP_TEXT_ADMIN_DISABLED}>
                        <{/if}>
                    </td>
                </tr>
            <{/foreach}>
            <tr class="foot">
                <td colspan="2">
                    <input type="submit" value="<{$smarty.const._XHELP_BUTTON_UPDATE}>" name="updateNotification">
                </td>
            </tr>
        </table>
    </form>
</div>

<{if $xhelp_hasReviews}>
    <br>
    <div id="staffReview">
        <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
            <tr>
                <th colspan="2"><{$smarty.const._XHELP_TEXT_LAST_REVIEWS}></th>
            </tr>
            <{foreach from=$xhelp_reviews item=review}>
                <tr>
                    <td class="head" width="20%"><a
                                href="<{$xoops_url}>/userinfo.php?uid=<{$review.submittedByUID}>"><{$review.submittedBy}></a><br>
                        <{$smarty.const._XHELP_TEXT_RATING}> <{$review.rating}>/5<br>
                        <{$review.ratingdsc}>
                    </td>
                    <td class="<{cycle values="odd, even"}>"><{$review.comments}></td>
                </tr>
            <{/foreach}>
        </table>
    </div>
<{/if}>
