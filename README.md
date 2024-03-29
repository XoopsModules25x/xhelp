![alt XOOPS CMS](https://xoops.org/images/logoXoopsPhp81.png)

## xHelp module for  [XOOPS CMS 2.5.10+](https://xoops.org)

[![XOOPS CMS Module](https://img.shields.io/badge/XOOPS%20CMS-Module-blue.svg)](https://xoops.org)
[![Software License](https://img.shields.io/badge/license-GPL-brightgreen.svg?style=flat)](https://www.gnu.org/licenses/gpl-2.0.html)

[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/XoopsModules25x/xhelp.svg?style=flat)](https://scrutinizer-ci.com/g/XoopsModules25x/xhelp/?branch=master)
[![Latest Pre-Release](https://img.shields.io/github/tag/XoopsModules25x/xhelp.svg?style=flat)](https://github.com/XoopsModules25x/xhelp/tags/)
[![Latest Version](https://img.shields.io/github/release/XoopsModules25x/xhelp.svg?style=flat)](https://github.com/XoopsModules25x/xhelp/releases/)

**xHelp** for [XOOPS CMS](https://xoops.org) is designed as a user-friendly HelpDesk application for the XOOPS portal system.

Current and upcoming "next generation" versions of XOOPS CMS are crafted on GitHub at: https://github.com/XOOPS

## Module Details

**PURPOSE:**

- For IT staff to keep a log of all problems that users are having with their hardware and software.
- Should have a knowledge base for past problems
- Should be able to separate tickets by department
- The tickets should have different priorities (high, medium, low)
- xHelp will use Xoops permissions for the users

**BREAKDOWN OF PAGES:**

1. Add ticket page Page will have fields for:
    - department
    - priority (1=urgent to 5=inquiry)
    - ticket status(open, closed, pending)
    - subject
    - description

2. Main page Will display lists of tickets sorted by posted time
    - emergency tickets (high priority)
    - new tickets (have not been looked at yet)
    - open tickets assigned to me
    - My submitted tickets Priorities will be different colors

Regular users will see the unresolved tickets they have submitted.

- Will also have link to submit new ticket.

At the bottom of the page, everyone will be able to see announcements from the admin (uses news module, and must be enabled in xhelp Preferences).

3. Ticket information page Will display all information about a specific ticket
    - ticket ID
    - Priority
    - Subject and description
    - username (will be a link to the userinfo page)
    - Logged time

Should be able to do all of these things (with proper permission):

- claim ownership on the ticket
- assign the task to another admin/staff person
- delete request
- edit the ticket
- edit a response
- merge multiple tickets together
- change status of a ticket

Will have a link to add a response Be able to update the status and priority of the ticket from this page

4. Add response page (admin/staff)
    - Allow IT staff to add comments to the ticket for updated status

5. Knowledge Base (admin/staff)
    - Admin and staff are allowed to add articles to the knowledge base to search for previous problems
    - Below search, it will have recent topics listed and the admin/staff will be able to click to look at the recent article

6. Search page Have a search page to search by:
    - ticket id
    - username
    - email
    - subject
    - priority
    - text
    - department
    - custom fields

**BLOCKS**

1. Overview block
    - Users: Will see a list of their open tickets that are being worked on
        - Click on the subject name and it will go to the ticket information page
    - Staff/Admin: Will display the department and the number of tickets awaiting response
        - Click on department to bring up all tickets awaiting response for that department
2. Staff Performance
    - Will display staff performance (average response time, rating)

3. Options
    - Will display a list of links to all the options the user/staff has

4. Actions
    - Will have a list of all actions possible by the staff member
