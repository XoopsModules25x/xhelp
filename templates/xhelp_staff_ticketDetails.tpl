<{include file='db:xhelp_staff_header.tpl'}>

<div id="ticketDetails">
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
        <tr>
            <th colspan="2">
                <img src="<{$xhelp_imagePath}>ticketInfo.png"
                     alt="<{$xhelp_ticket_details}>"> <{$xhelp_ticket_details}>
            </th>
        </tr>
        <tr>
            <td width="20%" class="head">
                <a href="<{$xhelp_userinfo}>"><{$xhelp_username}></a>
                <{if $xhelp_userlevel eq 0}>
                    <img src="<{$xhelp_imagePath}>ball.png" alt="<{$smarty.const._XHELP_TEXT_USER_NOT_ACTIVATED}>"
                         title="<{$smarty.const._XHELP_TEXT_USER_NOT_ACTIVATED}>">
                <{/if}>
            </td>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_LOG_TIME}> <{$xhelp_ticket_posted}>
            </td>
        </tr>
        <tr class="even">
            <td>
                <img src="<{$xhelp_user_avatar}>" class="comUserImg"
                     alt="<{$smarty.const._XHELP_PIC_ALT_USER_AVATAR}>">
                <div class="comUserStat">
                    <b><{$smarty.const._XHELP_TEXT_PRIORITY}></b> <img
                            src="assets/images/priority<{$xhelp_ticket_priority}>full.png"
                            alt="<{$xhelp_priorities_desc.$xhelp_ticket_priority}>">
                </div>
                <div class="comUserStat">
                    <b><{$smarty.const._XHELP_TEXT_STATUS}> </b>
                    <{$xhelp_text_status}>
                </div>
                <div class="comUserStat">
                    <b><{$smarty.const._XHELP_TEXT_DEPARTMENT}></b>
                    <a href="<{$xhelp_departmenturl}>"><{$xhelp_ticket_department}></a>
                </div>
                <div class="comUserStat">
                    <b><{$smarty.const._XHELP_TEXT_USER_IP}></b>
                    <{$xhelp_ticket_userIP}>
                </div>
            </td>
            <td>
                <b><{$xhelp_ticket_subject}></b><br>
                <{$xhelp_ticket_description}>

                <{if $xhelp_hasCustFields|default:''}>
                    <div id="custFields">
                        <br><br>
                        <b><{$smarty.const._XHELP_TEXT_ADDITIONAL_INFO}></b><br>
                        <{foreach from=$xhelp_custFields item=field}>
                            <{if $field.value|default:'' != ''}>
                                <{if $smarty.const.XHELP_CONTROL_FILE == $field.controltype}>
                                    <b><{$field.name}></b>
                                    :
                                    <a href="<{$xhelp_baseURL}>/viewFile.php?id=<{$field.fileid}>"><{$field.filename}></a>
                                    <{if $xhelp_has_deleteFile|default:''}>
                                        <a href="<{$xhelp_baseURL}>/ticket.php?op=deleteFile&amp;id=<{$xhelp_ticketID}>&amp;fileid=<{$field.fileid}>&amp;field=<{$field.fieldname}>"><img
                                                    src="<{$xhelp_imagePath}>button_delete.png"
                                                    alt="<{$smarty.const._XHELP_BUTTON_DELETE}>"></a>
                                    <{/if}>
                                <{else}>
                                    <b><{$field.name}></b>
                                    : <{$field.value}>
                                <{/if}>
                                <br>
                            <{/if}>
                        <{/foreach}>
                    </div>
                <{/if}>

                <{if $xhelp_hasTicketFiles|default:''}>
                    <br>
                    <br>
                    <div id="Xhelp\Files">
                        <table border="0" class="outer">
                            <tr>
                                <td class="head">
                                    <{$smarty.const._XHELP_TEXT_FILE}>
                                </td>
                                <td class="head">
                                    <{$smarty.const._XHELP_TEXT_SIZE}>
                                </td>
                                <td class="head">
                                    <{$smarty.const._XHELP_TEXT_ACTIONS}>
                                </td>
                            </tr>
                            <{foreach from=$xhelp_aFiles item=aFile}>
                                <{if $aFile.responseid eq 0}>
                                    <tr class="even">
                                        <td>
                                            <a href="<{$aFile.path}>"><{$aFile.filename}></a>
                                        </td>
                                        <td>
                                            <{$aFile.size}>
                                        </td>
                                        <td>
                                            <{if $xhelp_has_deleteFile|default:''}>
                                                <a href="<{$xhelp_baseURL}>/ticket.php?op=deleteFile&amp;id=<{$xhelp_ticketID}>&amp;fileid=<{$aFile.id}>"><img
                                                            src="<{$xhelp_imagePath}>button_delete.png"
                                                            alt="<{$smarty.const._XHELP_BUTTON_DELETE}>"></a>
                                            <{/if}>
                                        </td>
                                    </tr>
                                <{/if}>
                            <{/foreach}>
                        </table>
                    </div>
                <{/if}>
            </td>
        </tr>
    </table>
</div>

<br>
<div id="ownershipDetails">
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
        <tr>
            <th colspan="2">
                <img src="<{$xhelp_imagePath}>ticketInfo.png"
                     alt="<{$smarty.const._XHELP_TEXT_OWNERSHIP_DETAILS}>"><{$smarty.const._XHELP_TEXT_OWNERSHIP_DETAILS}>
            </th>
        </tr>
        <tr>
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_OWNER}>
            </td>
            <td class="even">
                <{if $xhelp_ticket_ownership|default:'' neq ''}>
                    <a href="<{$xhelp_ownerinfo}>"><{$xhelp_ticket_ownership}></a>
                <{else}>
                    <{$smarty.const._XHELP_NO_OWNER}>
                <{/if}>
            </td>
        </tr>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_TIMESPENT}>
            </td>
            <td class="even">
                <{$xhelp_ticket_totalTimeSpent}> <{$smarty.const._XHELP_TEXT_MINUTES}>
            </td>
        </tr>
        <tr>
            <td class="head">
                <{$smarty.const._XHELP_TEXT_DEPARTMENT}>
            </td>
            <td class="even">
                <{$xhelp_ticket_department}>
            </td>
        </tr>
    </table>
</div>

<{if $xhelp_showActions|default:'' eq 1}>
    <br>
    <div id="actions">
        <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
            <tr>
                <th colspan="6"><img src="<{$xhelp_imagePath}>actions.png"
                                     alt="<{$smarty.const._XHELP_TEXT_ACTIONS2}>"><{$smarty.const._XHELP_TEXT_ACTIONS2}>
                </th>
            </tr>
            <{if $xhelp_has_changeOwner|default:''}>
                <tr>
                    <td class="head" width="20%">
                        <{$smarty.const._XHELP_TEXT_OWNERSHIP}>
                    </td>
                    <td class="even" colspan="3">
                        <form method="post"
                              action="<{$xhelp_baseURL}>/ticket.php?id=<{$xhelp_ticketID}>&amp;op=ownership">
                            <label>
                                <select name="uid" class="formButton">
                                    <{foreach from=$xhelp_aOwnership item=staff}>
                                        <option value="<{$staff.uid}>"
                                                <{if $xhelp_ticket_ownerUid eq $staff.uid}>selected="selected"<{/if}>><{$staff.uname}></option>
                                    <{/foreach}>
                                </select>
                            </label>
                            <input type="image" src="<{$xhelp_imagePath}>assignOwner.png"
                                   title="<{$smarty.const._XHELP_TEXT_ASSIGN_OWNER}>" name="assignOwner"
                                   style="border:0;background:transparent;">
                            <br><{$smarty.const._XHELP_TEXT_ASSIGN_OWNER}>
                        </form>
                    </td>
                    <td class="even" colspan="2" nowrap="nowrap">
                        <{if $xhelp_has_takeOwnership|default:''}>
                            <form method="post" action="<{$xhelp_baseURL}>/ticket.php?id=<{$xhelp_ticketID}>&amp;op=ownership">
                                <{securityToken}><{*//mb*}>
                                <input type="hidden" value="<{$xhelp_claimOwner}>" name="uid">
                                <input type="image" src="<{$xhelp_imagePath}>claimOwner.png"
                                       title="<{$smarty.const._XHELP_TEXT_CLAIM_OWNER}>" name="claimOwner"
                                       style="border:0;background:transparent;">
                                <br><{$smarty.const._XHELP_TEXT_CLAIM_OWNER}>
                            </form>
                        <{/if}>
                    </td>
                </tr>
                <tr>
                    <td class="head" width="20%">
                        <{$smarty.const._XHELP_TEXT_ASSIGNTO}>
                    </td>
                    <td class="even" colspan="5">
                        <form method="post" action="<{$xhelp_baseURL}>/index.php?op=setdept">
                            <{securityToken}><{*//mb*}>
                            <label>
                                <select name="department">
                                    <{html_options options=$xhelp_departments selected=$xhelp_departmentid}>
                                </select>
                            </label>
                            <input type="hidden" name="tickets[]" value="<{$xhelp_ticketID}>">
                            <input type="hidden" name="setdept" value="1">
                            <input type="image" src="<{$xhelp_imagePath}>assignOwner.png"
                                   title="<{$smarty.const._XHELP_TEXT_ASSIGNTO}>" name="assignDept"
                                   style="border:0;background:transparent;">
                        </form>
                    </td>
                </tr>
            <{/if}>
            <tr>
                <td class="head" width="20%">
                    <{$smarty.const._XHELP_TEXT_TICKET}>
                </td>
                <{if $xhelp_has_addResponse|default:''}>
                    <td class="even center">
                        <a href="<{$xhelp_baseURL}>/response.php?id=<{$xhelp_ticketID}>&amp;op=staffFrm"><img
                                    src="<{$xhelp_imagePath}>response.png"
                                    alt="<{$smarty.const._XHELP_TEXT_ADD_RESPONSE}>"></a>
                        <br><{$smarty.const._XHELP_TEXT_ADD_RESPONSE}>
                    </td>
                <{/if}>
                <{if $xhelp_has_editTicket|default:''}>
                    <td class="even center">
                        <a href="<{$xhelp_baseURL}>/ticket.php?id=<{$xhelp_ticketID}>&amp;op=edit"><img
                                    src="<{$xhelp_imagePath}>edit.png" alt="<{$smarty.const._XHELP_TEXT_EDIT_TICKET}>"></a>
                        <br><{$smarty.const._XHELP_TEXT_EDIT_TICKET}>
                    </td>
                <{/if}>
                <{if $xhelp_has_deleteTicket|default:''}>
                    <td class="even center">
                        <form method="post" action="<{$xhelp_baseURL}>/ticket.php?id=<{$xhelp_ticketID}>&amp;op=delete">
                            <{securityToken}><{*//mb*}>
                            <input type="hidden" value="<{$xhelp_ticketID}>" name="ticketid">
                            <input type="hidden" value="1" name="delete_ticket">
                            <input type="image" src="<{$xhelp_imagePath}>delete.png"
                                   title="<{$smarty.const._XHELP_TEXT_DELETE_TICKET}>" name="deleteTicket"
                                   onclick='return confirm("<{$smarty.const._XHELP_JSC_TEXT_DELETE}>");'
                                   style="border:0;background:transparent;">
                            <br><{$smarty.const._XHELP_TEXT_DELETE_TICKET}>
                        </form>
                    </td>
                <{/if}>
                <{if $xhelp_has_mergeTicket|default:''}>
                    <td class="even center">
                        <form method="post" action="<{$xhelp_baseURL}>/ticket.php?id=<{$xhelp_ticketID}>&amp;op=merge">
                            <input type="text" name="ticket2" size="8" title="<{$smarty.const._XHELP_TEXT_MERGE_TITLE}>"
                                   class="formButton">
                            <input type="image" src="<{$xhelp_imagePath}>merge.png"
                                   title="<{$smarty.const._XHELP_TEXT_MERGE_TICKET}>" name="mergeTicket"
                                   style="border:0;background:transparent;">
                            <br><{$smarty.const._XHELP_TEXT_MERGE_TICKET}>
                        </form>
                    </td>
                <{/if}>
                <td class="even center">
                    <a href="<{$xhelp_baseURL}>/ticket.php?id=<{$xhelp_ticketID}>&amp;op=print" target="_blank"><img
                                src="<{$xhelp_imagePath}>print.png"
                                alt="<{$smarty.const._XHELP_TEXT_PRINT_TICKET}>"></a>
                    <br><{$smarty.const._XHELP_TEXT_PRINT_TICKET}>
                </td>
            </tr>
            <{if $xhelp_has_changePriority|default:''}>
                <tr>
                    <td class="head" width="20%">
                        <{$smarty.const._XHELP_TEXT_UPDATE_PRIORITY}>
                    </td>
                    <td class="even" colspan="5">
                        <form method="post"
                              action="<{$xhelp_baseURL}>/ticket.php?id=<{$xhelp_ticketID}>&amp;op=updatePriority">
                            <{foreach from=$xhelp_priorities item=priority}>
                                <input type="radio" value="<{$priority}>" id="priority<{$priority}>" name="priority"
                                       <{if $xhelp_ticket_priority eq $priority}>checked<{/if}>>
                                <label for="priority<{$priority}>"><img
                                            src="<{$xhelp_imagePath}>priority<{$priority}>.png"
                                            alt="<{$xhelp_priorities_desc.$priority}>"></label>
                            <{/foreach}>
                            <input type="submit" name="updatePriority"
                                   value="<{$smarty.const._XHELP_BUTTON_UPDATE_PRIORITY}>" class="formButton">
                        </form>
                    </td>
                </tr>
            <{/if}>
            <{if $xhelp_has_changeStatus|default:'' || $xhelp_has_addResponse|default:''}>
                <tr>
                    <td class="head" width="20%">
                        <{if $xhelp_has_changeStatus}><{$smarty.const._XHELP_TEXT_UPDATE_STATUS}><{/if}><{if $xhelp_has_changeStatus && $xhelp_has_addResponse}> / <{/if}><{if $xhelp_has_addResponse}><{$smarty.const._XHELP_TEXT_ADD_RESPONSE}><{/if}>
                    </td>
                    <td class="even" colspan="5">
                        <form method="post"
                              action="<{$xhelp_baseURL}>/ticket.php?id=<{$xhelp_ticketID}>&amp;op=updateStatus">
                            <label>
                                <select name="status">
                                    <{foreach from=$xhelp_statuses item=status}>
                                        <option value="<{$status.id}>"
                                                <{if $xhelp_ticket_status eq $status.id}>selected="selected"<{/if}>><{$status.desc}></option>
                                    <{/foreach}>
                                </select>
                            </label><br>
                            <{if $xhelp_has_addResponse}>
                                <label for="response"></label>
                                <textarea name="response" id="response" rows="5" cols="60"
                                          class="formButton"></textarea>
                                <br>
                            <{/if}>
                            <input type="submit" name="updateStatus"
                                   value="<{if $xhelp_has_changeStatus}><{$smarty.const._XHELP_BUTTON_UPDATE_STATUS}><{/if}><{if $xhelp_has_changeStatus && $xhelp_has_addResponse}> / <{/if}><{if $xhelp_has_addResponse}><{$smarty.const._XHELP_BUTTON_ADDRESPONSE}><{/if}>"
                                   class="formButton">
                        </form>
                    </td>
                </tr>
                <{if $xhelp_has_faqAdd|default:''}>
                    <tr>
                        <td class="head" width="20%">
                            <{$smarty.const._XHELP_TEXT_FAQ}>
                        </td>
                        <td class="even" colspan="5">
                            <form method="post" action="<{$xhelp_baseURL}>/faq.php">
                                <{securityToken}><{*//mb*}>
                                <input type="hidden" name="ticketid" value="<{$xhelp_ticketID}>">
                                <input type="image" src="<{$xhelp_imagePath}>help.png"
                                       title="<{$smarty.const._XHELP_TEXT_ADD_FAQ}>" name="addFaq"
                                       style="border:0;background:transparent;">
                                <br><{$smarty.const._XHELP_TEXT_ADD_FAQ}>
                            </form>
                        </td>
                    </tr>
                <{/if}>
            <{/if}>
        </table>
    </div>
<{/if}>

<br>
<div id="responses">
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
        <tr>
            <th colspan="2">
                <img src="<{$xhelp_imagePath}>response.png"
                     alt="<{$smarty.const._XHELP_TEXT_RESPONSES}>"><{$smarty.const._XHELP_TEXT_RESPONSES}>
            </th>
        </tr>
        <{if $xhelp_hasResponses|default:false eq true}>
            <{foreach from=$xhelp_aResponses item=response}>
                <tr>
                    <td width="20%" class="head">
                        <img src="<{$response.user_avatar}>" class="comUserImg"
                             alt="<{$smarty.const._XHELP_PIC_ALT_USER_AVATAR}>">
                        <div class="comUserStat">
                            <{$smarty.const._XHELP_TEXT_USER}> <{$response.uname}> (<{$response.userIP}>)
                        </div>
                        <div class="comUserStat">
                            <{$smarty.const._XHELP_TEXT_LOG_TIME}> <{$response.updateTime}>
                        </div>
                        <div class="comUserStat">
                            <{$smarty.const._XHELP_TEXT_USER_RATING}> <{$response.staffRating}>
                        </div>
                        <{if $xhelp_has_editResponse|default:''}>
                            <br>
                            <a href="<{$xhelp_baseURL}>/response.php?op=staffEdit&amp;id=<{$xhelp_ticketID}>&amp;responseid=<{$response.id}>"><{$smarty.const._XHELP_TEXT_EDIT_RESPONSE}></a>
                        <{/if}>
                    </td>
                    <td class="<{cycle name="message" values="odd, even"}>">
                        <{if $response.private|default:false eq true}>
                            <b><{$smarty.const._XHELP_TEXT_PRIVATE}></b>
                            <br>
                            <br>
                        <{/if}>
                        <{$response.message}>
                        <{if $response.attachSig eq 1 && $response.user_sig neq ''}>
                            <{$smarty.const._XHELP_SIG_SPACER}>
                            <{$response.user_sig}>
                        <{/if}>
                        <br><br>
                        <{if $response.hasFiles|default:false eq true}>
                            <table border="0" class="outer">
                                <tr class="head">
                                    <td>
                                        <{$smarty.const._XHELP_TEXT_FILE}>
                                    </td>
                                    <td>
                                        <{$smarty.const._XHELP_TEXT_SIZE}>
                                    </td>
                                    <td>
                                        <{$smarty.const._XHELP_TEXT_ACTIONS}>
                                    </td>
                                </tr>
                                <{foreach from=$xhelp_aFiles item=aFile}>
                                    <{if $aFile.responseid eq $response.id && $aFile.responseid neq 0}>
                                        <tr class="even">
                                            <td>
                                                <a href="<{$aFile.path}>"><{$aFile.filename}></a>
                                            </td>
                                            <td>
                                                <{$aFile.size}>
                                            </td>
                                            <td>
                                                <a href="<{$xhelp_baseURL}>/ticket.php?op=deleteFile&amp;id=<{$xhelp_ticketID}>&amp;fileid=<{$aFile.id}>"><img
                                                            src="<{$xhelp_imagePath}>button_delete.png"
                                                            alt="<{$smarty.const._XHELP_BUTTON_DELETE}>"></a>
                                            </td>
                                        </tr>
                                    <{/if}>
                                <{/foreach}>
                            </table>
                        <{/if}>
                    </td>
                </tr>
            <{/foreach}>
        <{else}>
            <tr class="odd">
                <td colspan="2">
                    <{$smarty.const._XHELP_NO_RESPONSES_ERROR}>
                </td>
            </tr>
        <{/if}>

    </table>
</div>

<br>
<div id="logMessages">
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
        <tr>
            <th colspan="3">
                <img src="<{$xhelp_imagePath}>logMessages.png"
                     alt="<{$smarty.const._XHELP_TEXT_ACTIVITY_LOG}>"><{$smarty.const._XHELP_TEXT_ACTIVITY_LOG}>
            </th>
        </tr>
        <tr class="head">
            <td>
                <{$smarty.const._XHELP_TEXT_LOG_TIME}>
            </td>
            <td>
                <{$smarty.const._XHELP_TEXT_NAME}>
            </td>
            <td>
                <{$smarty.const._XHELP_TEXT_ACTION}>
            </td>
        </tr>
        <{foreach from=$xhelp_logMessages item=message}>
            <tr class="<{cycle values="odd, even"}>">
                <td>
                    <{$message.lastUpdated|default:''}>
                </td>
                <td>
                    <{$message.uname|default:''}>
                </td>
                <td>
                    <{$message.action|default:''}>
                </td>
            </tr>
        <{/foreach}>
    </table>
</div>

<{if $xhelp_has_lastSubmitted|default:''}>
    <br>
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
        <tr>
            <th colspan="3">
                <{$smarty.const._XHELP_TEXT_LAST_TICKETS}> <{$xhelp_username}>
            </th>
        </tr>
        <tr class="head">
            <td>
                <{$smarty.const._XHELP_TEXT_SUBJECT}>
            </td>
            <td>
                <{$smarty.const._XHELP_TEXT_STATUS}>
            </td>
            <td>
                <{$smarty.const._XHELP_TEXT_DEPARTMENT}>
            </td>
        </tr>
        <{foreach from=$xhelp_lastSubmitted item=ticket}>
            <tr class="<{cycle values="odd, even"}>">
                <td>
                    <a href="<{$ticket.url}>"><{$ticket.subject}></a>
                </td>
                <td>
                    <{$ticket.status}>
                </td>
                <td>
                    <a href="<{$ticket.dept_url}>"><{$ticket.department}></a>
                </td>
            </tr>
        <{/foreach}>
    </table>
<{/if}>

<br>
<div id="emailNotification">
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
        <tr>
            <th colspan="2">
                <{$smarty.const._XHELP_TEXT_TICKET_NOTIFICATIONS}>
            </th>
        </tr>
        <tr class="head">
            <td>
                <{$smarty.const._XHELP_TEXT_EMAIL}>
            </td>
            <td>
                <{$smarty.const._XHELP_TEXT_RECEIVE_NOTIFICATIONS}>
            </td>
        </tr>
        <{foreach from=$xhelp_notifiedUsers item=user}>
            <tr class="<{cycle values="odd, even"}>">
                <td>
                    <{$user.email}>
                </td>
                <td>
                    <{if $user.suppress eq 0}>
                        <a href="<{$user.suppressUrl}>"><img src="<{$xhelp_imagePath}>on.png"
                                                             alt="<{$smarty.const._XHELP_TEXT_EMAIL_NOT_SUPPRESS}>"></a>
                    <{else}>
                        <a href="<{$user.suppressUrl}>"><img src="<{$xhelp_imagePath}>off.png"
                                                             alt="<{$smarty.const._XHELP_TEXT_EMAIL_SUPPRESS}>"></a>
                    <{/if}>
                </td>
            </tr>
        <{/foreach}>
        <tr class="foot" valign="top">
            <td colspan="2">
                <br>
                <form method="post" action="<{$xhelp_baseURL}>/ticket.php?id=<{$xhelp_ticketID}>&amp;op=addEmail">
                    <{$smarty.const._XHELP_TEXT_EMAIL_NOTIFICATION}>
                    <input type="text" name="newEmail" size="35"
                           title="<{$smarty.const._XHELP_TEXT_EMAIL_NOTIFICATION_TITLE}>" class="formButton">
                    <input type="submit" name="updateEmails" value="<{$smarty.const._XHELP_BUTTON_ADD_EMAIL}>"
                           class="formButton">
                </form>
            </td>
        </tr>
    </table>
</div>
