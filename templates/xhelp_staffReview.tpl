<{include file='db:xhelp_user_header.tpl'}>

<div id="userRateStaff">
    <form method="post" action="<{$xhelp_baseURL}>/staffReview.php">
        <{securityToken}><{*//mb*}>
        <table width="100%" border="1" cellpadding="0" cellspacing="2" class="formButton">
            <tr>
                <th colspan="2">
                    <img src="<{$xhelp_imagePath}>response.png"
                         alt="<{$smarty.const._XHELP_TEXT_RATE_STAFF}>"> <{$smarty.const._XHELP_TEXT_RATE_STAFF}>
                </th>
            </tr>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_RATING}>
                </td>
                <td class="even">
                    <label>
                        <select name="rating" class="formButton">
                            <option value="1"><{$smarty.const._XHELP_RATING1}></option>
                            <option value="2"><{$smarty.const._XHELP_RATING2}></option>
                            <option value="3"><{$smarty.const._XHELP_RATING3}></option>
                            <option value="4"><{$smarty.const._XHELP_RATING4}></option>
                            <option value="5"><{$smarty.const._XHELP_RATING5}></option>
                        </select>
                    </label>
                </td>
            </tr>
            <tr>
                <td class="head">
                    <{$smarty.const._XHELP_TEXT_COMMENTS}>
                </td>
                <td class="even">
                    <label>
                        <textarea name="comments" rows="10" cols="50" class="formButton"></textarea>
                    </label>
                    <input type="hidden" name="staffid" value="<{$xhelp_staffid}>">
                    <input type="hidden" name="ticketid" value="<{$xhelp_ticketid}>">
                    <input type="hidden" name="responseid" value="<{$xhelp_responseid}>">
                </td>
            </tr>
            <tr>
                <td class="head">
                </td>
                <td class="even">
                    <input type="submit" name="submit" value="<{$smarty.const._XHELP_BUTTON_SUBMIT}>"
                           class="formButton">
                    <input type="reset" value="<{$smarty.const._XHELP_BUTTON_RESET}>" class="formButton">
                </td>
            </tr>
        </table>
    </form>
</div>
