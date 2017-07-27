<{include file='db:xhelp_staff_header.tpl'}>

<form name="tickets" method="post" action="<{$xhelp_baseURL}>/index.php">
    <{if $xhelp_viewAllTickets}>    <{* view all tickets? *}>
        <table id="allTickets" width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
            <tr>
                <th colspan="8">
                    <img src="<{$xhelp_imagePath}>ticket.png"
                         alt="<{$smarty.const._XHELP_TEXT_ALL_TICKETS}>"><{$smarty.const._XHELP_TEXT_ALL_TICKETS}>
                </th>
            </tr>
            <{if $xhelp_has_tickets eq true}>
                <tr>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_ID}>
                    </td>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_PRIORITY}>
                    </td>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_SUBJECT}>
                    </td>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_STATUS}>
                    </td>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_DEPARTMENT}>
                    </td>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_LOGGED_BY}>
                    </td>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_LOG_TIME}>
                    </td>
                    <td class="head">
                        <{$smarty.const._XHELP_TEXT_OWNER}>
                    </td>
                </tr>
                <{foreach from=$xhelp_allTickets item=ticket}>
                    <tr class="<{cycle values="odd, even"}> pri<{$ticket.priority}><{if $ticket.overdue}> overdue<{/if}>">
                        <td nowrap="nowrap">
                            <input type="checkbox" name="tickets[]" value="<{$ticket.id}>"> <a
                                    href="ticket.php?id=<{$ticket.id}>"><{$ticket.id}></a>
                        </td>
                        <td class="priority">
                            <img src="assets/images/priority<{$ticket.priority}>.png"
                                 alt="<{$smarty.const._XHELP_TEXT_PRIORITY}> <{$ticket.priority}>">
                        </td>
                        <td class="subject">
                            <a href="ticket.php?id=<{$ticket.id}>"><{$ticket.subject}></a>
                        </td>
                        <td class="status">
                            <{$ticket.status}>
                        </td>
                        <td class="department">
                            <a href="<{$ticket.departmenturl}>"><{$ticket.department}></a>
                        </td>
                        <td class="user">
                            <a href="<{$ticket.userinfo}>"><{$ticket.uname}></a>
                        </td>
                        <td class="posted" nowrap="nowrap">
                            <{$ticket.posted}>
                        </td>
                        <td class="owner" nowrap="nowrap">
                            <{if $ticket.ownerinfo neq ''}>
                                <a href="<{$ticket.ownerinfo}>"><{$ticket.ownership}></a>
                            <{else}>
                                <{$ticket.ownership}>
                            <{/if}>
                        </td>
                    </tr>
                <{/foreach}>
            <{else}>
                <tr class="odd">
                    <td colspan="6">
                        <{$smarty.const._XHELP_NO_TICKETS_ERROR}>
                    </td>
                </tr>
            <{/if}>
        </table>
        <div id="xhelp_nav"><{$xhelp_pagenav}></div>
    <{else}>
        <{if $xhelp_hasTicketLists}>
            <{foreach from=$xhelp_ticketLists item=ticketList}>
                <table id="<{$ticketList.tableid}>" width="100%" border="1" cellpadding="0" cellspacing="2"
                       class="outer searchlist">
                    <tr>
                        <th colspan="8" class="listtitle">
                            <a href="<{$xhelp_baseURL}>/search.php?savedSearch=<{$ticketList.searchid}>"
                               style="float:right;"><{$smarty.const._XHELP_TEXT_VIEW_MORE_TICKETS}></a>
                            <{$ticketList.searchname}>
                        </th>
                    </tr>
                    <{if $ticketList.hasTickets}>
                        <tr>
                            <td class="head">
                                <{$smarty.const._XHELP_TEXT_ID}>
                            </td>
                            <td class="head">
                                <{$smarty.const._XHELP_TEXT_PRIORITY}>
                            </td>
                            <td class="head">
                                <{$smarty.const._XHELP_TEXT_SUBJECT}>
                            </td>
                            <td class="head">
                                <{$smarty.const._XHELP_TEXT_STATUS}>
                            </td>
                            <td class="head">
                                <{$smarty.const._XHELP_TEXT_DEPARTMENT}>
                            </td>
                            <td class="head">
                                <{$smarty.const._XHELP_TEXT_LOGGED_BY}>
                            </td>
                            <td class="head">
                                <{$smarty.const._XHELP_TEXT_LOG_TIME}>
                            </td>
                            <td class="head">
                                <{$smarty.const._XHELP_TEXT_OWNER}>
                            </td>
                        </tr>
                        <{foreach from=$ticketList.tickets item=ticket}>
                            <tr class="<{cycle values="odd, even"}> pri<{$ticket.priority}><{if $ticket.overdue}> overdue<{/if}>">
                                <td nowrap="nowrap">
                                    <input type="checkbox" name="tickets[]" value="<{$ticket.id}>"> <a
                                            href="ticket.php?id=<{$ticket.id}>"><{$ticket.id}></a>
                                </td>
                                <td class="priority">
                                    <img src="assets/images/priority<{$ticket.priority}>.png"
                                         alt="<{$smarty.const._XHELP_TEXT_PRIORITY}> <{$ticket.priority}>">
                                </td>
                                <td class="subject">
                                    <a href="ticket.php?id=<{$ticket.id}>"><{$ticket.subject}></a>
                                </td>
                                <td class="status">
                                    <{$ticket.status}>
                                </td>
                                <td class="department">
                                    <a href="<{$ticket.departmenturl}>"><{$ticket.department}></a>
                                </td>
                                <td class="user">
                                    <a href="<{$ticket.userinfo}>"><{$ticket.uname}></a>
                                </td>
                                <td class="posted" nowrap="nowrap">
                                    <{$ticket.posted}>
                                </td>
                                <td class="owner" nowrap="nowrap">
                                    <{if $ticket.ownerinfo neq ''}>
                                        <a href="<{$ticket.ownerinfo}>"><{$ticket.ownership}></a>
                                    <{else}>
                                        <{$ticket.ownership}>
                                    <{/if}>
                                </td>
                            </tr>
                        <{/foreach}>
                    <{else}>
                        <tr class="odd">
                            <td colspan="6">
                                <{$smarty.const._XHELP_NO_TICKETS_ERROR}>
                            </td>
                        </tr>
                    <{/if}>
                </table>
                <br>
            <{/foreach}>
        <{/if}>
    <{/if}>

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
                    <select name="op">
                        <option value="setdept"><{$smarty.const._XHELP_TEXT_BATCH_DEPARTMENT}></option>
                        <option value="setpriority"><{$smarty.const._XHELP_TEXT_BATCH_PRIORITY}></option>
                        <option value="setstatus"><{$smarty.const._XHELP_TEXT_BATCH_STATUS}></option>
                        <option value="delete"><{$smarty.const._XHELP_TEXT_BATCH_DELETE}></option>
                        <option value="addresponse"><{$smarty.const._XHELP_TEXT_BATCH_RESPONSE}></option>
                        <option value="setowner"><{$smarty.const._XHELP_TEXT_BATCH_OWNERSHIP}></option>
                    </select>
                    <input type="submit" value="<{$smarty.const._GO}>">
                </td>
            </tr>
        </table>
    </div>
</form>

<br>
<{if $xhelp_viewAllTickets eq false}>
    <div id="staffSideBar">
        <table class="formButton">

            <tr>
                <td>
                    <form name="formRefresh" method="get" action="<{$xhelp_current_file}>">
                        <select name="refresh"
                                onchange="window.location='<{$xhelp_baseURL}>/index.php?refresh='+this.options[this.selectedIndex].value;">
                            <option value="<{$smarty.const._XHELP_AUTO_REFRESH0}>"><{$smarty.const._XHELP_TEXT_AUTO_REFRESH0}></option>
                            <option value="<{$smarty.const._XHELP_AUTO_REFRESH1}>"
                                    <{if $xhelp_refresh eq $smarty.const._XHELP_AUTO_REFRESH1}>selected="selected"<{/if}>><{$smarty.const._XHELP_TEXT_AUTO_REFRESH1}></option>
                            <option value="<{$smarty.const._XHELP_AUTO_REFRESH2}>"
                                    <{if $xhelp_refresh eq $smarty.const._XHELP_AUTO_REFRESH2}>selected="selected"<{/if}>><{$smarty.const._XHELP_TEXT_AUTO_REFRESH2}></option>
                            <option value="<{$smarty.const._XHELP_AUTO_REFRESH3}>"
                                    <{if $xhelp_refresh eq $smarty.const._XHELP_AUTO_REFRESH3}>selected="selected"<{/if}>><{$smarty.const._XHELP_TEXT_AUTO_REFRESH3}></option>
                            <option value="<{$smarty.const._XHELP_AUTO_REFRESH4}>"
                                    <{if $xhelp_refresh eq $smarty.const._XHELP_AUTO_REFRESH4}>selected="selected"<{/if}>><{$smarty.const._XHELP_TEXT_AUTO_REFRESH4}></option>
                        </select>
                        <input type="submit" value="<{$smarty.const._XHELP_BUTTON_SUBMIT}>">
                    </form>
                </td>
            </tr>
        </table>
    </div>
<{/if}>

<{if $xhelp_viewAllTickets eq false}>
    <{if $xhelp_useAnnouncements eq true}>
        <br>
        <div id="announcements">
            <table width="100%" border="1" cellpadding="0" cellspacing="2" class="outer">
                <tr>
                    <th>
                        <{$smarty.const._XHELP_TEXT_ANNOUNCEMENTS}>
                    </th>
                </tr>
                <tr>
                    <td>
                        <{* start news item loop *}>
                        <{section name=i loop=$xhelp_announcements}>
                            <{include file="db:xhelp_announcement.tpl" story=$xhelp_announcements[i]}>
                            <br>
                        <{/section}>
                        <{* end news item loop *}>
                    </td>
                </tr>
            </table>
        </div>
    <{/if}>
<{/if}>
