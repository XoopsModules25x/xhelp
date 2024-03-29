<{include file='db:xhelp_user_header.tpl'}>

<{if $xhelp_noStaff|default:''}>
    <div id="readOnly" class="errorMsg"
         style="border:1px solid #D24D00; background:#FEFECC no-repeat 7px 50%;color:#333;padding-left:45px;">
        <{$smarty.const._XHELP_MESSAGE_NO_STAFF}>
    </div>
<{/if}>

<br>
<div id="userTickets">
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
        <tr>

            <th colspan="5">
                <img src="<{$xhelp_imagePath}>openTicket.png"
                     alt="<{$smarty.const._XHELP_TEXT_MY_OPEN_TICKETS}>"><{$smarty.const._XHELP_TEXT_MY_OPEN_TICKETS}>
            </th>

        </tr>
        <{if $xhelp_has_userTickets|default:0 neq 0}>
            <tr class="head">
                <td>
                    <{$smarty.const._XHELP_TEXT_ID}>
                </td>
                <td>
                    <{$smarty.const._XHELP_TEXT_SUBJECT}>
                </td>
                <td>
                    <{$smarty.const._XHELP_TEXT_STATUS}>
                </td>
                <td>
                    <{$smarty.const._XHELP_TEXT_PRIORITY}>
                </td>
                <td>
                    <{$smarty.const._XHELP_TEXT_LOG_TIME}>
                </td>
            </tr>
            <{foreach from=$xhelp_userTickets item=ticket}>
                <tr class="<{cycle values="odd,even"}>">
                    <td>
                        <{$ticket.id}>
                    </td>
                    <td>
                        <a href="<{$xhelp_baseURL}>/ticket.php?id=<{$ticket.id}>"><{$ticket.subject}></a>
                    </td>
                    <td>
                        <{$ticket.status}>
                    </td>
                    <td>
                        <img src="<{$xhelp_imagePath}>priority<{$ticket.priority}>.png"
                             alt="<{$smarty.const._XHELP_TEXT_PRIORITY}> <{$ticket.priority}>">
                    </td>
                    <td>
                        <{$ticket.posted}>
                    </td>
                </tr>
            <{/foreach}>
        <{else}>
            <tr class="odd">
                <td colspan="4">
                    <{$smarty.const._XHELP_NO_TICKETS_ERROR}>
                </td>
            </tr>
        <{/if}>
    </table>
</div>

<br>
<{if $xhelp_useAnnouncements|default:false eq true}>
    <div id="announcements">
        <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
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
