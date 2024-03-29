<div id="staffMenu">
    <table border="0" width="100%">
        <tr align="center">
            <td>
                <a href="index.php"><img src="<{$xhelp_imagePath}>main.png"
                                         alt="<{$smarty.const._XHELP_MENU_MAIN}>"></a><a
                        href="index.php"><{$smarty.const._XHELP_MENU_MAIN}></a>
            </td>
            <td>
                <a href="addTicket.php"><img src="<{$xhelp_imagePath}>addTicket.png"
                                             alt="<{$smarty.const._XHELP_MENU_LOG_TICKET}>"></a><a
                        href="addTicket.php"><{$smarty.const._XHELP_MENU_LOG_TICKET}></a>
            </td>
            <td>
                <a href="profile.php"><img src="<{$xhelp_imagePath}>profile.png"
                                           alt="<{$smarty.const._XHELP_MENU_MY_PROFILE}>"></a><a
                        href="profile.php"><{$smarty.const._XHELP_MENU_MY_PROFILE}></a>
            </td>
            <td>
                <a href="index.php?viewAllTickets=1"><img src="<{$xhelp_imagePath}>ticket.png"
                                                          alt="<{$smarty.const._XHELP_MENU_ALL_TICKETS}>"></a> <a
                        href="index.php?op=staffViewAll"><{$smarty.const._XHELP_MENU_ALL_TICKETS}></a>
            </td>
            <td>
                <a href="search.php"><img src="<{$xhelp_imagePath}>search2.png"
                                          alt="<{$smarty.const._XHELP_MENU_SEARCH}>"></a> <a
                        href="search.php"><{$smarty.const._XHELP_MENU_SEARCH}></a>
            </td>
            <{if $xhelp_savedSearches|default:false neq false}>
                <td>
                    <form name="savedSearches" method="post" action="search.php">
                        <label>
                            <select name="savedSearch">
                                <{foreach from=$xhelp_savedSearches item=search}>
                                    <option value="<{$search.id}>"><{$search.name}></option>
                                <{/foreach}>
                            </select>
                        </label>
                        <input type="submit" name="runSavedSearch" id="runSavedSearch"
                               value="<{$smarty.const._XHELP_BUTTON_RUN}>">
                    </form>
                </td>
            <{/if}>
            <td>
                <form method="post" action="ticket.php">
                    <label>
                        <input type="text" name="id" size="6">
                    </label>
                    <input type="submit" name="getTicket" value="<{$smarty.const._GO}>">
                </form>
            </td>
        </tr>
    </table>
</div>
