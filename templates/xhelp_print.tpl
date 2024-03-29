<!DOCTYPE html>
<html xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=<{$xoops_charset}>">
    <meta http-equiv="content-language" content="<{$xoops_langcode}>">
    <meta name="robots" content="<{$xoops_meta_robots}>">
    <meta name="keywords" content="<{$xoops_meta_keywords}>">
    <meta name="description" content="<{$xoops_meta_description}>">
    <meta name="rating" content="<{$xoops_meta_rating}>">
    <meta name="author" content="<{$xoops_meta_author}>">
    <meta name="copyright" content="<{$xoops_meta_copyright}>">
    <meta name="generator" content="XOOPS">
    <title>Print Ticket Information</title>
    <link rel="stylesheet" type="text/css" media="all" href="<{$xoops_url}>/modules/xhelp/assets/css/print.css">
</head>

<body>
<script type="text/javascript" for=window event=onload language="javascript">
    window.print();
</script>

<h2><{$smarty.const._XHELP_TEXT_HELPDESK_TICKET}> <{$xhelp_ticket_subject}></h2>

<table width="100%" border="0">
    <tr>
        <td>
            <table border="0">
                <tr>
                    <th><{$xhelp_ticket_details}></th>
                </tr>
                <tr>
                    <td><{$smarty.const._XHELP_TEXT_SUBJECT}> <{$xhelp_ticket_subject}></td>
                </tr>
                <tr>
                    <td><{$smarty.const._XHELP_TEXT_DESCRIPTION}> <{$xhelp_ticket_description}></td>
                </tr>
                <tr>
                    <td>
                        <{$smarty.const._XHELP_TEXT_STATUS}>
                        <{$xhelp_ticket_status}>
                    </td>
                </tr>
                <tr>
                    <td>
                        <{$smarty.const._XHELP_TEXT_PRIORITY}>
                        <img src="<{$xoops_url}>/modules/xhelp/assets/images/priority<{$xhelp_ticket_priority}>print.png">
                    </td>
                </tr>
                <tr>
                    <td><{$smarty.const._XHELP_TEXT_LOGGED_BY}> <{$xhelp_username}></td>
                </tr>
                <tr>
                    <td><{$smarty.const._XHELP_TEXT_LOG_TIME}> <{$xhelp_ticket_posted}></td>
                </tr>
            </table>
        </td>
        <td valign="top">
            <table border="0">
                <tr>
                    <th><{$smarty.const._XHELP_TEXT_OWNERSHIP_DETAILS}></th>
                </tr>
                <tr>
                    <td>
                        <{$smarty.const._XHELP_TEXT_OWNER}>
                        <{if $xhelp_ticket_ownership|default:'' neq ''}>
                            <{$xhelp_ticket_ownership}>
                        <{else}>
                            <{$smarty.const._XHELP_NO_OWNER}>
                        <{/if}>
                    </td>
                </tr>
                <tr>
                    <td><{$smarty.const._XHELP_TEXT_DEPARTMENT}> <{$xhelp_ticket_department}></td>
                </tr>
                <tr>
                    <td><{$smarty.const._XHELP_TEXT_TIMESPENT}> <{$xhelp_ticket_totalTimeSpent}> <{$smarty.const._XHELP_TEXT_MINUTES}></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table border="0">
                <tr>
                    <th><{$smarty.const._XHELP_TEXT_ADDITIONAL_INFO}></th>
                </tr>
                <tr>
                    <td>
                        <{foreach from=$xhelp_custFields item=field}>
                            <{if $field.value|default:'' != ''}>
                                <{$field.name}>: <{$field.value}>
                                <br>
                            <{/if}>
                        <{/foreach}>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<br>
<table width="100%" border="0">
    <tr>
        <th><{$smarty.const._XHELP_TEXT_RESPONSES}></th>
    </tr>
    <{if $xhelp_hasResponses|default:false eq true}>
        <{foreach from=$xhelp_aResponses item=response}>
            <tr>
                <td><{$response.uname}><{if $response.private eq true}> (<{$smarty.const._XHELP_TEXT_PRIVATE}>)<{/if}>
                    - <{$response.updateTime}> - <{$response.message}></td>
            </tr>
        <{/foreach}>
    <{else}>
        <tr>
            <td>
                <{$smarty.const._XHELP_NO_RESPONSES_ERROR}>
            </td>
        </tr>
    <{/if}>
</table>


<br>
<table width="100%" border="0">
    <tr>
        <th><{$smarty.const._XHELP_TEXT_ACTIVITY_LOG}></th>
    </tr>
    <{foreach from=$xhelp_print_logMessages item=message}>
        <tr>
            <td><{$message.lastUpdated}> - <{$message.uname}> - <{$message.action}></td>
        </tr>
    <{/foreach}>
</table>
</body>

</html>
