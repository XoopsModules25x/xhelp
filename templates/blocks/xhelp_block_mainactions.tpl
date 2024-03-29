<{$block.startblock}>
<{foreach name=items from=$block.items item=menuitem}>
    <{$block.startitem}><a <{if $smarty.foreach.items.first && $block.menustyle neq 0}>class="menuTop"<{/if}>
                           href="<{$block.linkPath}><{$menuitem.link}>">
    <{if $block.showicon}><img src="<{$block.imagePath}><{$menuitem.image}>"
                               alt="<{$menuitem.text}>"><{/if}><{$menuitem.text}>
    </a><{$block.enditem}>
<{/foreach}>
<{$block.endblock}>
<{if $block.savedSearches neq false}>
    <div align="center">
        <form name="savedSearches" method="post" action="<{$block.linkPath}>search.php">
            <label>
                <select name="savedSearch">
                    <{foreach from=$block.savedSearches item=search}>
                        <option value="<{$search.id}>"><{$search.name}></option>
                    <{/foreach}>
                </select>
            </label>
            <input type="submit" name="runSavedSearch" id="runSavedSearch" value="<{$smarty.const._XHELP_BUTTON_RUN}>">
        </form>
    </div>
<{/if}>
<{if $block.whoami eq "staff"}>
    <form method="post" action="<{$block.linkPath}>ticket.php">
        <label>
            <input type="text" name="id" size="6">
        </label>
        <input type="submit" name="getTicket" value="<{$smarty.const._GO}>">
    </form>
<{/if}>
