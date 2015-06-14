Module Purpose
-----------------------------------------------------------------------
xhelp is designed as a user-friendly helpdesk application for the XOOPS
portal system. 

Installation Requirements
-----------------------------------------------------------------------
XOOPS 2.0.6+

Enabling Email Support Requires adds these requirements:
POP3 Email account(s)
Ability to create a cron job or scheduled task

Installation Instructions
-----------------------------------------------------------------------
Unzip archive to 'modules' directory. 
Install application using XOOPS module administration panel. 
Adjust module preferences as necessary.
Add ticket departments (categories).
Setup XOOPS user accounts that represent helpdesk staff members.
Follow steps in "Block Styles" section of this document
All Set!!!

Upgrade Instructions
-----------------------------------------------------------------------
Unzip archive to 'modules' directory.
Update module through XOOPS module administration panel.
Click on 'Check Tables' from the xhelp Main Menu, or the pop-up menu.
Adjust module preferences as necessary.
Follow steps in "Block Styles" section of this document.
Templates in 0.75 have changed significantly. If you are running a custom
template set, you will need to remove them and Generate the new 
templates.
All Set!!!

Block Styles
-----------------------------------------------------------------------
xHelp 0.7 adds the ability to flag a ticket as overdue. To see this 
flag in the xhelp blocks (My Tickets, Recently Viewed Tickets) you will
need to add the following style to your xoops theme's stylesheet:

#xhelp_dept_recent li.overdue {background-color:red;}
#xhelp_bOpenTickets li.overdue {background-color:red;}

In addition we recommend adding these styles to your theme's stylesheet

#xhelp_dept_recent li {list-style:none;}
#xhelp_bOpenTickets li {list-style:none;}

#xhelp_dept_recent ul, #xhelp_dept_recent li {margin:0; padding:0;}
#xhelp_bOpenTickets ul, #xhelp_bOpenTickets li {margin:0; padding:0;}

#xhelp_dept_recent li {margin:0;padding-left:18px; background:url('../../modules/xhelp/images/ticket-small.png') no-repeat 0 50%; line-height:16px;padding-bottom:2px;}
#xhelp_bOpenTickets li {margin:0;padding-left:18px; background:url('../../modules/xhelp/images/ticket-small.png') no-repeat 0 50%; line-height:16px;padding-bottom:2px;}



Email Ticket Submission
-----------------------------------------------------------------------
To configure email ticket submission some additional steps are 
necessary. First you need to create a POP3 email account for each
department that will receive email. Next, go to "Manage Departments" in
the xhelp Admin Panel. Next, edit the department you wish to hold the newly
created tickets. Next, Add a new mailbox to monitor:

Mailbox Type - currently the only option is POP3.
Server - DNS name of mail server (get from your hosting provider)
Port - Mail Service Port Number.  For POP3 this is usually 110.
Username - Username to access mailbox (get from your hosting provider)
Password - Password to access mailbox (also get from your hosting provider)
Default Ticket Priority - Adjust default priority for incoming tickets.
Reply-To Email Address - the email address for this account. Used for 
    handling replies (responses) to tickets.

Repeat this process for each mailbox you wish to monitor.

Once all mailboxes have been added, you need to setup a scheduled task
or a cron job to check these mailboxes on a regular basis.

For *nix machines the following crontab line should do the trick:

*/2 * * * * /usr/bin/wget -q <XOOPS_URL>/modules/xhelp/checkemail.php

The above line will check all the configured mailboxes, every other minute.

For windows servers you can try using Task Scheduler or runat with
this variant:
C:\php\php.exe "<XOOPS_ROOT_PATH>\modules\xhelp\checkemail.php"

If PHP was installed into a different directory from the default you
will need to adjust the path to php.exe accordingly.

In addition, there is a version of wget for windows:

ftp://ftp.sunsite.dk/projects/wget/windows/wget-1.9.1b-complete.zip

Unfortunately we cannot support you in the configuration of this 
scheduled task. Please contact your hosting provider with any questions.

License
-----------------------------------------------------------------------
GPL see LICENSE.txt

Updates, Bugs or Feedback
-----------------------------------------------------------------------
For up-to-date versions of this application or to give feedback for the
application go to the xhelp development site:

http://dev.xoops.org/modules/xfmod/project/?xhelp

Translations
-----------------------------------------------------------------------
xHelp not available in your language? Want to help by translating xhelp
into your native tongue? Please come to the translators forum on the 
xhelp development site (url listed above) for more details.

Credits and Thanks
-----------------------------------------------------------------------
See the about page in xhelp Admin area