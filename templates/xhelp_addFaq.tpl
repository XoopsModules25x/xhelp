<{include file='db:xhelp_staff_header.tpl'}>

<form method="post" action="<{$xhelp_baseURL}>/faq.php?op=add">
    <{securityToken}><{*//mb*}>
    <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
        <tr>
            <th colspan="2">
                <{$smarty.const._XHELP_TEXT_ADD_FAQ}>
            </th>
        </tr>
        <tr>
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_SUBJECT}>
            </td>
            <td class="even">
                <input type="text" id="subject" name="subject" maxlength="100" size="55" value="<{$xhelp_faqSubject}>">
            </td>
        </tr>
        <tr>
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_PROBLEM}>
            </td>
            <td class="even">
                <textarea name="problem" id="problem" rows="5" cols="50"><{$xhelp_faqProblem}></textarea>
            </td>
        </tr>
        <tr>
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_SOLUTION}>
            </td>
            <td class="even">
                <textarea name="solution" id="solution" rows="5" cols="50"><{$xhelp_faqSolution}></textarea>
            </td>
        </tr>
        <tr>
            <td class="head" width="20%">
                <{$smarty.const._XHELP_TEXT_CATEGORIES}>
            </td>
            <td class="even">
                <{$xhelp_categories}>
            </td>
        </tr>
        <tr class="foot">
            <td colspan="2">
                <input type="hidden" name="ticketid" value="<{$xhelp_ticketID}>">
                <input type="submit" name="addFaq" id="addFaq" value="<{$smarty.const._XHELP_TEXT_SUBMIT}>"
                       class="formButton">
            </td>
        </tr>
    </table>
</form>
