<{include file='db:xhelp_user_header.tpl'}>

<div id="details">
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
        <tr>
            <th colspan="2">
                <{$xhelp_ticket_details}>
            </th>
        </tr>
        <tr class="head">
            <td width="20%">
            </td>
            <td>
                <{$xhelp_ticket_posted}>
            </td>
        </tr>
        <tr class="even">
            <td>
                <div class="comUserStat">
                    <{$smarty.const._XHELP_TEXT_PRIORITY}>
                    <img src="<{$xhelp_imagePath}>priority<{$xhelp_ticket_priority}>full.png"
                         alt="<{$xhelp_priorities_desc.$xhelp_ticket_priority}>">
                </div>
                <div class="comUserStat">
                    <{$smarty.const._XHELP_TEXT_STATUS}>
                    <{$xhelp_ticket_status}>
                </div>
                <div class="comUserStat">
                    <{$smarty.const._XHELP_TEXT_LOG_TIME}>
                    <{$xhelp_ticket_posted}>
                </div>
            </td>
            <td>
                <b><{$xhelp_ticket_subject}></b><br>
                <{$xhelp_ticket_description}>

                <{if $xhelp_hasCustFields}>
                    <div id="custFields">
                        <br><br>
                        <b><{$smarty.const._XHELP_TEXT_ADDITIONAL_INFO}></b><br>
                        <{foreach from=$xhelp_custFields item=field}>
                            <{if $field.value|default:'' != ''}>
                                <{if $smarty.const.XHELP_CONTROL_FILE == $field.controltype}>
                                    <b><{$field.name}></b>
                                    :
                                    <a href="<{$smarty.const.XHELP_BASE_URL}>/viewFile.php?id=<{$field.fileid}>"><{$field.filename}></a>
                                <{else}>
                                    <b><{$field.name}></b>
                                    : <{$field.value}>
                                <{/if}>
                                <br>
                            <{/if}>
                        <{/foreach}>
                    </div>
                <{/if}>

                <{if $xhelp_hasTicketFiles}>
                    <br>
                    <br>
                    <div id="Xhelp\Files">
                        <table border="0" class="outer">
                            <tr class="head">
                                <td>
                                    <{$smarty.const._XHELP_TEXT_FILE}>
                                </td>
                                <td>
                                    <{$smarty.const._XHELP_TEXT_SIZE}>
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
<div id="responses">
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
        <tr>
            <th colspan="2">
                <img src="<{$xhelp_imagePath}>response.png"
                     alt="<{$smarty.const._XHELP_TEXT_RESPONSES}>"><{$smarty.const._XHELP_TEXT_RESPONSES}>
            </th>
        </tr>
        <{if $xhelp_hasResponses eq true}>
            <{foreach from=$xhelp_aResponses item=response}>
                <{if $response.private eq false}>
                    <tr>
                        <td class="head" width="20%">
                            <{$response.uname}><br>
                            <{$response.updateTime}><br>
                            <br>
                            <{if $response.rating eq '' && $response.uid neq $xhelp_uid}>
                                <a href="staffReview.php?staff=<{$response.uid}>&amp;ticketid=<{$xhelp_ticketID}>&amp;responseid=<{$response.id}>"><{$smarty.const._XHELP_TEXT_RATE_RESPONSE}></a>
                            <{elseif $response.rating neq '' && $response.uid neq $xhelp_uid}>
                                <{$smarty.const._XHELP_TEXT_RESPONSE_RATING}> <{$response.rating}>
                            <{/if}>
                        </td>
                        <td class="<{cycle name="message" values="odd, even"}>">
                            <{$response.message}>
                            <{if $response.user_sig neq ''}>
                                <{$smarty.const._XHELP_SIG_SPACER}>
                                <{$response.user_sig}>
                            <{/if}>
                            <br><br>
                            <{if $response.hasFiles eq true}>
                                <table border="0" class="outer">
                                    <tr class="head">
                                        <td>
                                            <{$smarty.const._XHELP_TEXT_FILE}>
                                        </td>
                                        <td>
                                            <{$smarty.const._XHELP_TEXT_SIZE}>
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
                                            </tr>
                                        <{/if}>
                                    <{/foreach}>
                                </table>
                            <{/if}>
                        </td>
                    </tr>
                <{/if}>
            <{/foreach}>
        <{else}>
            <tr class="even">
                <td colspan="2">
                    <{$smarty.const._XHELP_NO_RESPONSES_ERROR}>
                </td>
            </tr>
        <{/if}>
    </table>
</div>

<br>
<{if $xhelp_allowResponse eq 1}>
    <div id="userResponse">
        <form method="post" enctype="multipart/form-data"
              action="ticket.php?id=<{$xhelp_ticketID}>&amp;op=userResponse">
            <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
                <tr>
                    <th colspan="2">
                        <{if $xhelp_reopenTicket eq true}>
                            <{$smarty.const._XHELP_TEXT_REOPEN_TICKET}>
                        <{else}>
                            <{$smarty.const._XHELP_TEXT_MORE_INFO}>
                        <{/if}>
                    </th>
                </tr>
                <tr>
                    <td width="20%" class="head">
                        <{if $xhelp_reopenTicket eq true}>
                            <{$smarty.const._XHELP_TEXT_REOPEN_REASON}>
                        <{else}>
                            <{$smarty.const._XHELP_TEXT_MORE_INFO2}>
                        <{/if}>
                    </td>
                    <td class="even">
                        <textarea name="userResponse" rows="10" cols="50" class="formButton"></textarea>
                    </td>
                </tr>
                <{if $xhelp_allowUpload eq 1}>
                    <tr>
                        <td width="20%" class="head">
                            <{$smarty.const._XHELP_TEXT_ADDFILE}>
                        </td>
                        <td class="even">
                            <input name="userfile" id="userfile" type="file" class="formButton">
                        </td>
                    </tr>
                <{/if}>
                <{if $xhelp_reopenTicket eq false}>
                    <tr>
                        <td class="head">
                            <{$smarty.const._XHELP_TEXT_CLOSE_TICKET}>
                        </td>
                        <td class="even">
                            <input type="checkbox" name="closeTicket" value="1">
                        </td>
                    </tr>
                <{/if}>

                <tr class="foot">
                    <td colspan="2">
                        <{if $xhelp_reopenTicket eq true}>
                            <input type="submit" name="newResponse" value="<{$smarty.const._XHELP_BUTTON_ADDRESPONSE}>"
                                   class="formButton">
                        <{else}>
                            <input type="submit" name="newResponse" value="<{$smarty.const._XHELP_BUTTON_SUBMIT}>"
                                   class="formButton">
                        <{/if}>
                    </td>
                </tr>
            </table>
        </form>
    </div>
<{/if}>
