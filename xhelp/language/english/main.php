<?php
//$Id: main.php,v 1.126 2006/02/06 22:14:00 eric_juden Exp $

define('_XHELP_CATEGORY1','Assign Ownership');
define('_XHELP_CATEGORY2','Delete Responses');
define('_XHELP_CATEGORY3','Delete Tickets');
define('_XHELP_CATEGORY4','Log Users\' Tickets');
define('_XHELP_CATEGORY5','Modify Responses');
define('_XHELP_CATEGORY6','Modify Ticket Information');

define('_XHELP_SEC_TEXT_TICKET_ADD','Add Tickets');
define('_XHELP_SEC_TEXT_TICKET_EDIT','Modify Tickets');
define('_XHELP_SEC_TEXT_TICKET_DELETE','Delete Tickets');
define('_XHELP_SEC_TEXT_TICKET_OWNERSHIP','Change Ticket Ownership');
define('_XHELP_SEC_TEXT_TICKET_STATUS','Change Ticket Status');
define('_XHELP_SEC_TEXT_TICKET_PRIORITY','Change Ticket Priority');
define('_XHELP_SEC_TEXT_TICKET_LOGUSER','Log Ticket for User');
define('_XHELP_SEC_TEXT_RESPONSE_ADD','Add Response');
define('_XHELP_SEC_TEXT_RESPONSE_EDIT','Modify Response');
define('_XHELP_SEC_TEXT_TICKET_MERGE','Merge Tickets');
define('_XHELP_SEC_TEXT_FILE_DELETE','Delete File Attachments');
define('_XHELP_SEC_TEXT_FAQ_ADD','Add FAQs');
define('_XHELP_SEC_TEXT_TICKET_TAKE_OWNERSHIP','Take ticket ownership');

define('_XHELP_JSC_TEXT_DELETE','Are you sure you want to delete this ticket?');

define('_XHELP_MESSAGE_ADD_DEPT','Department added successfully');
define('_XHELP_MESSAGE_ADD_DEPT_ERROR','Error: department was not added');
define('_XHELP_MESSAGE_UPDATE_DEPT','Department successfully updated');
define('_XHELP_MESSAGE_UPDATE_DEPT_ERROR','Error: department was not updated');
define('_XHELP_MESSAGE_DEPT_DELETE','Department was successfully deleted');
define('_XHELP_MESSAGE_DEPT_DELETE_ERROR','Error: department was not deleted');
define('_XHELP_MESSAGE_ADDSTAFF_ERROR','Error: staff member was not added');
define('_XHELP_MESSAGE_ADDSTAFF','Staff member was successfully added');
define('_XHELP_MESSAGE_STAFF_DELETE','Staff member was successfully deleted');
define('_XHELP_MESSAGE_STAFF_DELETE_ERROR','Staff member was not deleted');
define('_XHELP_MESSAGE_EDITSTAFF','Staff member profile was successfully updated');
define('_XHELP_MESSAGE_EDITSTAFF_ERROR','Error: staff member was not updated');
define('_XHELP_MESSAGE_EDITSTAFF_NOCLEAR_ERROR','Error: old departments not removed');
define('_XHELP_MESSAGE_DEPT_EXISTS','Department already exists');
define('_XHELP_MESSAGE_ADDTICKET','Ticket was successfully logged');
define('_XHELP_MESSAGE_ADDTICKET_ERROR','Error: ticket was not logged');
define('_XHELP_MESSAGE_LOGMESSAGE_ERROR','Error: action was not logged to database');
define('_XHELP_MESSAGE_UPDATE_PRIORITY','Ticket priority updated successfully');
define('_XHELP_MESSAGE_UPDATE_PRIORITY_ERROR','Error: ticket priority was not updated');
define('_XHELP_MESSAGE_UPDATE_STATUS','Ticket status updated successfully');
define('_XHELP_MESSAGE_UPDATE_STATUS_ERROR','Error: ticket status was not updated');
define('_XHELP_MESSAGE_UPDATE_DEPARTMENT','Ticket department updated successfully');
define('_XHELP_MESSAGE_UPDATE_DEPARTMENT_ERROR','Error: Ticket department was not updated');
define('_XHELP_MESSAGE_CLAIM_OWNER','You have claimed the ownership of the ticket');
define('_XHELP_MESSAGE_CLAIM_OWNER_ERROR','Error: ticket ownership was not claimed');
define('_XHELP_MESSAGE_ASSIGN_OWNER','You have successfully assigned the ownership');
define('_XHELP_MESSAGE_ASSIGN_OWNER_ERROR','Error: ticket ownership was not assigned');
define('_XHELP_MESSAGE_UPDATE_OWNER','You have successfully updated the ownership of the ticket.');
define('_XHELP_MESSAGE_ADDFILE','File uploaded successfully');
define('_XHELP_MESSAGE_ADDFILE_ERROR','Error: file was not uploaded');
define('_XHELP_MESSAGE_ADDRESPONSE','Response added successfully');
define('_XHELP_MESSAGE_ADDRESPONSE_ERROR','Error: response was not added');
define('_XHELP_MESSAGE_UPDATE_CALLS_CLOSED_ERROR','Error: callsClosed field not updated');
define('_XHELP_MESSAGE_ALREADY_OWNER','%s is already the owner of this ticket');
define('_XHELP_MESSAGE_ALREADY_STATUS','Ticket is already set to this status');
define('_XHELP_MESSAGE_DELETE_TICKET','Ticket deleted successfully');
define('_XHELP_MESSAGE_DELETE_TICKET_ERROR','Error: ticket was not deleted');
define('_XHELP_MESSAGE_ADD_SIGNATURE','Signature added successfully');
define('_XHELP_MESSAGE_ADD_SIGNATURE_ERROR','Error: signature not updated');
define('_XHELP_MESSAGE_RESPONSE_TPL','Pre-Defined responses updated successfully');
define('_XHELP_MESSAGE_RESPONSE_TPL_ERROR','Error: responses not updated');
define('_XHELP_MESSAGE_DELETE_RESPONSE_TPL','Pre-Defined response deleted successfully');
define('_XHELP_MESSAGE_DELETE_RESPONSE_TPL_ERROR','Error: Pre-Defined response not deleted');
define('_XHELP_MESSAGE_ADD_STAFFREVIEW','Review successfully added');
define('_XHELP_MESSAGE_ADD_STAFFREVIEW_ERROR','Error: review was not added');
define('_XHELP_MESSAGE_UPDATE_STAFF_ERROR','Error: staff member info not updated');
define('_XHELP_MESSAGE_UPDATE_SIG_ERROR','Error: signature not updated');
define('_XHELP_MESSAGE_UPDATE_SIG','Signature updated');
define('_XHELP_MESSAGE_EDITTICKET','Ticket updated');
define('_XHELP_MESSAGE_EDITTICKET_ERROR','Error: ticket not updated');
define('_XHELP_MESSAGE_USER_MOREINFO','Ticket updated successfully.');
define('_XHELP_MESSAGE_USER_MOREINFO_ERROR','Error: information was not added');
define('_XHELP_MESSAGE_USER_NO_INFO','Error: you did not submit any new information');
define('_XHELP_MESSAGE_EDITRESPONSE','Response successfully updated');
define('_XHELP_MESSAGE_EDITRESPONSE_ERROR','Error: response not updated');
define('_XHELP_MESSAGE_NOTIFY_UPDATE','Notifications successfully updated');
define('_XHELP_MESSAGE_NOTIFY_UPDATE_ERROR','Notifications were not updated');
define('_XHELP_MESSAGE_NO_NOTIFICATIONS','User had no notifications');
define('_XHELP_MESSAGE_NO_DEPTS','Error: no departments set up. Contact administrator.');
define('_XHELP_MESSAGE_NO_STAFF','Error: no staff members set up. Contact administrator.');
define('_XHELP_MESSAGE_TICKET_REOPEN','Ticket re-opened successfully.');
define('_XHELP_MESSAGE_TICKET_REOPEN_ERROR','Error: ticket was not re-opened.');
define('_XHELP_MESSAGE_TICKET_CLOSE','Ticket closed successfully.');
define('_XHELP_MESSAGE_TICKET_CLOSE_ERROR','Error: ticket was not closed.');
define('_XHELP_MESSAGE_NOT_USER','Error: you cannot make changes to this ticket.');
define('_XHELP_MESSAGE_NO_TICKETS','Error: No Tickets selected.');
define('_XHELP_MESSAGE_NOOWNER','No owner');
define('_XHELP_MESSAGE_UNKNOWN','Unknown');
define('_XHELP_MESSAGE_WRONG_MIMETYPE','Error: filetype is not allowed. Please re-submit.');
define('_XHELP_MESSAGE_NO_UID','Error: no uid specified');
define('_XHELP_MESSAGE_NO_PRIORITY','Error: no priority specified');
define('_XHELP_MESSAGE_FILE_ERROR','Error: Unable to store uploaded file for the following reasons:<br />%s');
define('_XHELP_MESSAGE_UPDATE_EMAIL_ERROR','Error: email was not updated');
define('_XHELP_MESSAGE_TICKET_DELETE_CNFRM','Are you sure you want to delete these tickets?');
define('_XHELP_MESSAGE_DELETE_TICKETS','Tickets deleted successfully');
define('_XHELP_MESSAGE_DELETE_TICKETS_ERROR','Error: tickets were not deleted');
define('_XHELP_MESSAGE_VALIDATE_ERROR','Your ticket has errors, please correct and submit again.');
define('_XHELP_MESSAGE_UNAME_TAKEN',' is already in use.');
define('_XHELP_MESSAGE_INVALID',' is invalid.');
define('_XHELP_MESSAGE_REQUIRED',' is required.');
define('_XHELP_MESSAGE_LONG',' is too long.');
define('_XHELP_MESSAGE_SHORT',' is too short.');
define('_XHELP_MESSAGE_NOT_ENTERED',' was not entered.');
define('_XHELP_MESSAGE_NOT_NUMERIC',' is not numeric.');
define('_XHELP_MESSAGE_RESERVED',' is reserved.');
define('_XHELP_MESSAGE_NO_SPACES',' should not have spaces');
define('_XHELP_MESSAGE_NOT_SAME',' is not the same.');
define('_XHELP_MESSAGE_NOT_SUPPLIED',' is not supplied.');
define('_XHELP_MESSAGE_CREATE_USER_ERROR','User not created');
define('_XHELP_MESSAGE_NO_REGISTER','Please login to your account to submit a ticket.');
define('_XHELP_MESSAGE_NEW_USER_ERR','Error: your user account was not created.');
define('_XHELP_MESSAGE_EMAIL_USED','Error: email has already been registered.');
define('_XHELP_MESSAGE_DELETE_FILE_ERR','Error: file was not deleted.');
define('_XHELP_MESSAGE_DELETE_SEARCH_ERR','Error: search was not deleted.');

define('_XHELP_MESSAGE_UPLOAD_ALLOWED_ERR','Error: file uploading is disabled for the module.');
define('_XHELP_MESSAGE_UPLOAD_ERR','File %s from %s was not stored because %s.');

define('_XHELP_MESSAGE_NO_ADD_TICKET','You do not have permission to log tickets.');
define('_XHELP_MESSAGE_NO_DELETE_TICKET','You do not have permission to delete tickets.');
define('_XHELP_MESSAGE_NO_EDIT_TICKET','You do not have permission to edit tickets.');
define('_XHELP_MESSAGE_NO_CHANGE_OWNER','You do not have permission to change the ownership.');
define('_XHELP_MESSAGE_NO_CHANGE_PRIORITY','You do not have permission to change the priority.');
define('_XHELP_MESSAGE_NO_CHANGE_STATUS','You do not have permission to change the status.');
define('_XHELP_MESSAGE_NO_ADD_RESPONSE','You do not have permission to add responses.');
define('_XHELP_MESSAGE_NO_EDIT_RESPONSE','You do not have permission to edit responses.');
define('_XHELP_MESSAGE_NO_MERGE','You do not have permission to merge tickets.');
define('_XHELP_MESSAGE_NO_TICKET2','Error: you did not specify a ticket to merge with.');
define('_XHELP_MESSAGE_ADDED_EMAIL','Email added successfully.');
define('_XHELP_MESSAGE_ADDED_EMAIL_ERROR','Error: email was not added.');
define('_XHELP_MESSAGE_NO_EMAIL','Error: you did not specify an email to add.');
define('_XHELP_MESSAGE_ADD_EMAIL','Email notification was updated.');
define('_XHELP_MESSAGE_ADD_EMAIL_ERROR','Error: email was not updated.');
define('_XHELP_MESSAGE_NO_MERGE_TICKET','You do not have permission to suppress an email.');
define('_XHELP_MESSAGE_NO_FILE_DELETE','You do not have permission to delete files.');
define('_XHELP_MESSAGE_NO_CUSTFLD_ADDED','Error: custom field values were not saved.');

define('_XHELP_ERROR_INV_TICKET','Error: Invalid ticket specified.  Please check the ticket and try again!');
define('_XHELP_ERROR_INV_RESPONSE','Error: Invalid response specified. Please check the response and try again!');
define('_XHELP_ERROR_NODEPTPERM','You cannot submit a response on this ticket. Reason: Not a staff member of this department.');
define('_XHELP_ERROR_INV_STAFF','Error: User is not a staff member.');
define('_XHELP_ERROR_INV_TEMPLATE','Error: Fill in both the template name and text before submitting');
define('_XHELP_ERROR_INV_USER','Error: you do not have permission to view this ticket.');

define('_XHELP_TITLE_ADDTICKET','Log Ticket');
define('_XHELP_TITLE_ADDRESPONSE','Add Response');
define('_XHELP_TITLE_EDITTICKET','Edit Ticket Info');
define('_XHELP_TITLE_EDITRESPONSE','Edit Response');
define('_XHELP_TITLE_SEARCH','Search');

define('_XHELP_TEXT_SIZE','Size:');
define('_XHELP_TEXT_REALNAME','Real Name');
define('_XHELP_TEXT_ID','ID:');
define('_XHELP_TEXT_NAME','Username:');
define('_XHELP_TEXT_USER','User:');
define('_XHELP_TEXT_USERID','User ID:');
define('_XHELP_TEXT_LOOKUP','Lookup');
define('_XHELP_TEXT_LOOKUP_USER','Lookup User');
define('_XHELP_TEXT_EMAIL','Email:');
define('_XHELP_TEXT_ASSIGNTO','Assign To:');
define('_XHELP_TEXT_PRIORITY','Priority:');
define('_XHELP_TEXT_STATUS','Status:');
define('_XHELP_TEXT_SUBJECT','Subject:');
define('_XHELP_TEXT_DEPARTMENT','Department:');
define('_XHELP_TEXT_OWNER','Owner:');
define('_XHELP_TEXT_CLOSEDBY','Closed By:');
define('_XHELP_TEXT_NOTAPPLY','N/A');
define('_XHELP_TEXT_TIMESPENT','Time Spent:');
define('_XHELP_TEXT_DESCRIPTION','Description:');
define('_XHELP_TEXT_ADDFILE','Add File:');
define('_XHELP_TEXT_FILE','File:');
define('_XHELP_TEXT_RESPONSE','Response');
define('_XHELP_TEXT_RESPONSES','Responses');
define('_XHELP_TEXT_CLAIMOWNER','Claim Ownership:');
define('_XHELP_TEXT_CLAIM_OWNER','Claim Ownership');
define('_XHELP_TEXT_TICKETDETAILS','Ticket #%u Details');
define('_XHELP_TEXT_MINUTES','minutes');
define('_XHELP_TEXT_SEARCH','Search:');
define('_XHELP_TEXT_SEARCHBY','By:');
define('_XHELP_SEARCH_DESC','Description');
define('_XHELP_SEARCH_SUBJECT','Subject');
define('_XHELP_TEXT_NUMRESULTS','Number of Results Per Page:');
define('_XHELP_TEXT_RESULT1','5');
define('_XHELP_TEXT_RESULT2','10');
define('_XHELP_TEXT_RESULT3','25');
define('_XHELP_TEXT_RESULT4','50');
define('_XHELP_TEXT_SEARCH_RESULTS','Search Results');
define('_XHELP_TEXT_PREDEFINED_RESPONSES','Pre-Defined Responses:');
define('_XHELP_TEXT_PREDEFINED0','-- Create Response --');
define('_XHELP_TEXT_NO_USERS','No users found');
define('_XHELP_TEXT_SEARCH_AGAIN','Search Again');
define('_XHELP_TEXT_LOGGED_BY','Logged By:');
define('_XHELP_TEXT_LOG_TIME','Log Time:');
define('_XHELP_TEXT_OWNERSHIP_DETAILS','Ownership Details');
define('_XHELP_TEXT_ACTIVITY_LOG','Activity Log');
define('_XHELP_TEXT_HELPDESK_TICKET','Helpdesk Ticket:');
define('_XHELP_TEXT_YES','Yes');
define('_XHELP_TEXT_NO','No');
define('_XHELP_TEXT_ALL_TICKETS','All Tickets');
define('_XHELP_TEXT_HIGH_PRIORITY','Highest Priority Unassigned Tickets');
define('_XHELP_TEXT_NEW_TICKETS','New Tickets');
define('_XHELP_TEXT_MY_TICKETS','Open Tickets Assigned to Me');
define('_XHELP_TEXT_SUBMITTED_TICKETS','My Submitted Tickets');
define('_XHELP_TEXT_ANNOUNCEMENTS','Announcements');
define('_XHELP_TEXT_MY_PERFORMANCE','My Performance');
define('_XHELP_TEXT_RESPONSE_TIME','Average Response Time:');
define('_XHELP_TEXT_RATING','Rating:');
define('_XHELP_TEXT_NUMREVIEWS','Number of Reviews:');
define('_XHELP_TEXT_NUM_TICKETS_CLOSED','Number of Tickets Closed:');
define('_XHELP_TEXT_TEMPLATE_NAME','Template Name:');
define('_XHELP_TEXT_MESSAGE','Message:');
define('_XHELP_TEXT_ACTIONS','Actions:');
define('_XHELP_TEXT_ACTIONS2','Actions');
define('_XHELP_TEXT_MY_NOTIFICATIONS','My Notifications');
define('_XHELP_TEXT_SELECT_ALL','Select All');
define('_XHELP_TEXT_USER_IP','User IP:');
define('_XHELP_TEXT_OWNERSHIP','Ownership');
define('_XHELP_TEXT_ASSIGN_OWNER','Assign Ownership');
define('_XHELP_TEXT_TICKET','Ticket');
define('_XHELP_TEXT_USER_RATING','User Rating:');
define('_XHELP_TEXT_EDIT_RESPONSE','Edit Response');
define('_XHELP_TEXT_FILE_ADDED','File Added:');
define('_XHELP_TEXT_ACTION','Action:');
define('_XHELP_TEXT_LAST_TICKETS','Last Submitted Tickets from:');
define('_XHELP_TEXT_RATE_STAFF','Rate Staff Response');
define('_XHELP_TEXT_COMMENTS','Comments:');
define('_XHELP_TEXT_MY_OPEN_TICKETS','My Open Tickets');
define('_XHELP_TEXT_RATE_RESPONSE','Rate Response?');
define('_XHELP_TEXT_RESPONSE_RATING','Response Rating:');
define('_XHELP_TEXT_REOPEN_TICKET','Re-open Ticket?');
define('_XHELP_TEXT_MORE_INFO','More Info Required?');
define('_XHELP_TEXT_REOPEN_REASON','Reason for re-opening (optional)');
define('_XHELP_TEXT_MORE_INFO2','Need to add more information to the ticket? Fill it in here!');
define('_XHELP_TEXT_NO_DEPT','No Department');
define('_XHELP_TEXT_NOT_EMAIL','Email Address:');
define('_XHELP_TEXT_LAST_REVIEWS','Latest Staff Reviews:');
define('_XHELP_TEXT_SORT_TICKETS','Sort tickets by this column');
define('_XHELP_TEXT_ELAPSED','Elapsed:');
define('_XHELP_TEXT_FILTERTICKETS','Filter Tickets:');
define('_XHELP_TEXT_LIMIT','Records per page');
define('_XHELP_TEXT_SUBMITTEDBY','Submitted By:');
define('_XHELP_TEXT_NO_INCLUDE','ANY');
define('_XHELP_TEXT_PRIVATE_RESPONSE','Private Response:');
define('_XHELP_TEXT_PRIVATE','Private');
define('_XHELP_TEXT_CLOSE_TICKET','Close Ticket?');
define('_XHELP_TEXT_ADD_SIGNATURE','Add signature to responses?');
define('_XHELP_TEXT_LASTUPDATE','Last Update:');
define('_XHELP_TEXT_BATCH_ACTIONS','Batch Actions:');
define('_XHELP_TEXT_BATCH_DEPARTMENT','Change Department');
define('_XHELP_TEXT_BATCH_PRIORITY','Change Priority');
define('_XHELP_TEXT_BATCH_STATUS','Change Status');
define('_XHELP_TEXT_BATCH_DELETE','Delete Tickets');
define('_XHELP_TEXT_BATCH_RESPONSE','Respond');
define('_XHELP_TEXT_BATCH_OWNERSHIP','Take/Assign Ownership');
define('_XHELP_TEXT_UPDATE_COMP','Update Complete!');
define('_XHELP_TEXT_TOPICS_ADDED','Topics Added');
define('_XHELP_TEXT_DEPTS_ADDED','Departments Added');
define('_XHELP_TEXT_CLOSE_WINDOW','Close Window');
define('_XHELP_TEXT_USER_LOOKUP','User Lookup');
define('_XHELP_TEXT_EVENT','Event');
define('_XHELP_TEXT_AVAIL_FILETYPES','Available Filetypes');
define('_XHELP_USER_REGISTER','User Registration');

define('_XHELP_TEXT_SETDEPT','Choose a department:');
define('_XHELP_TEXT_SETPRIORITY','Set the ticket priority:');
define('_XHELP_TEXT_SETOWNER','Choose an owner:');
define('_XHELP_TEXT_SETSTATUS','Set the ticket status:');
define('_XHELP_TEXT_MERGE_TICKET','Merge Tickets');
define('_XHELP_TEXT_MERGE_TITLE','Enter the ticket ID you want to merge with.');
define('_XHELP_TEXT_EMAIL_NOTIFICATION','Email Notification:');
define('_XHELP_TEXT_EMAIL_NOTIFICATION_TITLE','Add an email address to be notified of ticket updates.');
define('_XHELP_TEXT_RECEIVE_NOTIFICATIONS','Receive Notifications:');
define('_XHELP_TEXT_EMAIL_SUPPRESS','Emails are suppressed. Click to send email notifications.');
define('_XHELP_TEXT_EMAIL_NOT_SUPPRESS','Emails are being sent. Click to suppress.');
define('_XHELP_TEXT_TICKET_NOTIFICATIONS','Ticket Notifications');
define('_XHELP_TEXT_STATE','State:');
define('_XHELP_TEXT_BY_STATUS','By Status:');
define('_XHELP_TEXT_BY_STATE','By State:');
define('_XHELP_TEXT_SEARCH_OR','-- OR --');
define('_XHELP_TEXT_VIEW1','Basic View');
define('_XHELP_TEXT_VIEW2','Advanced View');
define('_XHELP_TEXT_SAVE_SEARCH','Save Search?');
define('_XHELP_TEXT_SEARCH_NAME','Search Name:');
define('_XHELP_TEXT_SAVED_SEARCHES','Previously Saved Searches');
define('_XHELP_TEXT_SWITCH_TO','Switch To ');
define('_XHELP_TEXT_ADDITIONAL_INFO','Additional Information');

//added 03-09-2010
define('_XHELP_TEXT_USE','Use');
define('_XHELP_TEXT_DATE','Date Range:');
define('_XHELP_TEXT_DATE_MIN','From:');
define('_XHELP_TEXT_DATE_MAX','To:');

define('_XHELP_ROLE_NAME1','Ticket Manager');
define('_XHELP_ROLE_NAME2','Support');
define('_XHELP_ROLE_NAME3','Browser');
define('_XHELP_ROLE_DSC1','Can do anything and everything');
define('_XHELP_ROLE_DSC2','Log tickets and responses, change status and priority, and log tickets for a user');
define('_XHELP_ROLE_DSC3','Can make no changes');
define('_XHELP_ROLE_VAL1', 2047);
define('_XHELP_ROLE_VAL2', 241);
define('_XHELP_ROLE_VAL3', 0);



// Ticket.php - Actions
define('_XHELP_TEXT_SELECTED','With Selected:');
define('_XHELP_TEXT_ADD_RESPONSE','Add Response');
define('_XHELP_TEXT_EDIT_TICKET','Edit Ticket');
define('_XHELP_TEXT_DELETE_TICKET','Delete Ticket');
define('_XHELP_TEXT_PRINT_TICKET','Print Ticket');
define('_XHELP_TEXT_UPDATE_PRIORITY','Update Priority');
define('_XHELP_TEXT_UPDATE_STATUS','Update Status');

define('_XHELP_PIC_ALT_USER_AVATAR','User Avatar');

// Index.php - Auto Refresh Page vars
define('_XHELP_TEXT_AUTO_REFRESH0','No Auto Refresh');
define('_XHELP_TEXT_AUTO_REFRESH1','Auto Refresh Every 1 minute');
define('_XHELP_TEXT_AUTO_REFRESH2','Auto Refresh Every 5 minutes');
define('_XHELP_TEXT_AUTO_REFRESH3','Auto Refresh Every 10 minutes');
define('_XHELP_TEXT_AUTO_REFRESH4','Auto Refresh Every 30 minutes');
define('_XHELP_AUTO_REFRESH0', 0);          // Change these to
define('_XHELP_AUTO_REFRESH1', 60);         // adjust the values
define('_XHELP_AUTO_REFRESH2', 300);        // in the select box
define('_XHELP_AUTO_REFRESH3', 600);
define('_XHELP_AUTO_REFRESH4', 1800);

define('_XHELP_MENU_MAIN','Summary');
define('_XHELP_MENU_LOG_TICKET','Log Ticket');
define('_XHELP_MENU_MY_PROFILE','My Profile');
define('_XHELP_MENU_ALL_TICKETS','View All Tickets');
define('_XHELP_MENU_SEARCH','Search');

define('_XHELP_SEARCH_EMAIL','Email');
define('_XHELP_SEARCH_USERNAME','Username');
define('_XHELP_SEARCH_UID','User ID');

define('_XHELP_BUTTON_ADDRESPONSE','Add Response');
define('_XHELP_BUTTON_ADDTICKET','Log Ticket');
define('_XHELP_BUTTON_EDITTICKET','Edit Ticket');
define('_XHELP_BUTTON_RESET','Reset');
define('_XHELP_BUTTON_EDITRESPONSE','Update Response');
define('_XHELP_BUTTON_SEARCH','Search');
define('_XHELP_BUTTON_LOG_USER','Log For User');
define('_XHELP_BUTTON_FIND_USER','Find User');
define('_XHELP_BUTTON_SUBMIT','Submit');
define('_XHELP_BUTTON_DELETE','Delete');
define('_XHELP_BUTTON_UPDATE','Update');
define('_XHELP_BUTTON_UPDATE_PRIORITY','Update Priority');
define('_XHELP_BUTTON_UPDATE_STATUS','Update Status');
define('_XHELP_BUTTON_ADD_INFO','Add Info');
define('_XHELP_BUTTON_SET','Set');
define('_XHELP_BUTTON_ADD_EMAIL','Add Email');
define('_XHELP_BUTTON_RUN','Run');

define('_XHELP_PRIORITY1', 1);
define('_XHELP_PRIORITY2', 2);
define('_XHELP_PRIORITY3', 3);
define('_XHELP_PRIORITY4', 4);
define('_XHELP_PRIORITY5', 5);

define('_XHELP_TEXT_PRIORITY1','High');
define('_XHELP_TEXT_PRIORITY2','Medium-High');
define('_XHELP_TEXT_PRIORITY3','Medium');
define('_XHELP_TEXT_PRIORITY4','Medium-Low');
define('_XHELP_TEXT_PRIORITY5','Low');

define('_XHELP_STATUS0','Open');
define('_XHELP_STATUS1','Hold');
define('_XHELP_STATUS2','Closed');

define('_XHELP_STATE1','Unresolved');
define('_XHELP_STATE2','Resolved');
define('_XHELP_NUM_STATE1', 1);
define('_XHELP_NUM_STATE2', 2);

define('_XHELP_RATING0','No rating');
define('_XHELP_RATING1','Poor');
define('_XHELP_RATING2','Below Average');
define('_XHELP_RATING3','Average');
define('_XHELP_RATING4','Above Average');
define('_XHELP_RATING5','Excellent');

// Log Messages
define('_XHELP_LOG_ADDTICKET','Ticket logged');
define('_XHELP_LOG_ADDTICKET_FORUSER','Ticket logged for %s by %s');
define('_XHELP_LOG_EDITTICKET','Ticket information edited');
define('_XHELP_LOG_UPDATE_PRIORITY','Priority updated from pri:%u to pri:%u');
define('_XHELP_LOG_UPDATE_STATUS','Status updated from %s to %s');
define('_XHELP_LOG_CLAIM_OWNERSHIP','Claimed ownership');
define('_XHELP_LOG_ASSIGN_OWNERSHIP','Assigned ownership to %s');
define('_XHELP_LOG_ADDRESPONSE','Response added');
define('_XHELP_LOG_USER_MOREINFO','Added more information');
define('_XHELP_LOG_EDIT_RESPONSE','Response # %s edited');
define('_XHELP_LOG_REOPEN_TICKET','Ticket re-opened');
define('_XHELP_LOG_CLOSE_TICKET','Ticket closed');
define('_XHELP_LOG_ADDRATING','Rated Response %u');
define('_XHELP_LOG_SETDEPT','Assigned to %s department');
define('_XHELP_LOG_MERGETICKETS','Merged ticket %s to %s');
define('_XHELP_LOG_DELETEFILE','File %s deleted');
define('_XHELP_LOG_NEWFAQ','FAQ Article \'%s\' added');

// Error checking for no records in DB
define('_XHELP_NO_TICKETS_ERROR','No tickets found');
define('_XHELP_NO_RESPONSES_ERROR','No responses found');
define('_XHELP_NO_MAILBOX_ERROR','Invalid Mailbox Specified');
define('_XHELP_NO_FILES_ERROR','No files found');

define('_XHELP_SIG_SPACER','<br /><br />-------------------------------<br />');
define('_XHELP_COMMMENTS','Comments?');
define("_XHELP_ANNOUNCE_READMORE","Read More...");
define("_XHELP_ANNOUNCE_ONECOMMENT","1 comment");
define("_XHELP_ANNOUNCE_NUMCOMMENTS","%s comments");
define("_XHELP_TICKET_MD5SIGNATURE","Support Key:");


define('_XHELP_NO_OWNER','No Owner');
define('_XHELP_RESPONSE_EDIT','Response modified by %s on %s');

define('_XHELP_TIME_SECS','seconds');
define('_XHELP_TIME_MINS','minutes');
define('_XHELP_TIME_HOURS','hours');
define('_XHELP_TIME_DAYS','days');
define('_XHELP_TIME_WEEKS','weeks');
define('_XHELP_TIME_YEARS','years');

define('_XHELP_TIME_SEC','second');
define('_XHELP_TIME_MIN','minute');
define('_XHELP_TIME_HOUR','hour');
define('_XHELP_TIME_DAY','day');
define('_XHELP_TIME_WEEK','week');
define('_XHELP_TIME_YEAR','year');

define('_XHELP_MAILEVENT_CLASS0', 0);     // Connection message
define('_XHELP_MAILEVENT_CLASS1', 1);     // Parse message
define('_XHELP_MAILEVENT_CLASS2', 2);     // Storage message
define('_XHELP_MAILEVENT_CLASS3', 3);     // General message

define('_XHELP_MAILEVENT_DESC0','Could not connect to server.');
define('_XHELP_MAILEVENT_DESC1','Could not parse message.');
define('_XHELP_MAILEVENT_DESC2','Could not store message.');
define('_XHELP_MAILEVENT_DESC3','');
define('_XHELP_MBOX_ERR_LOGIN','Connection failed to mail server: invalid login/password');
define('_XHELP_MBOX_INV_BOXTYPE','Specified mailbox type is not supported');

define('_XHELP_MAIL_CLASS0','Connection');
define('_XHELP_MAIL_CLASS1','Parsing');
define('_XHELP_MAIL_CLASS2','Storage');
define('_XHELP_MAIL_CLASS3','General');

define('_XHELP_GROUP_PERM_DEPT','xhelp_dept');
define('_XHELP_MISMATCH_EMAIL','%s has been notified that their message was not stored. Support key matched, but message should have been sent from %s instead.');
define('_XHELP_MESSAGE_MERGE','Merge successfully completed.');
define('_XHELP_MESSAGE_MERGE_ERROR','Error: merge was not completed.');
define('_XHELP_RESPONSE_NO_TICKET','No ticket found for ticket response');
define('_XHELP_MESSAGE_NO_ANON','Message from %s blocked, anonymous user ticket submission disabled');
define('_XHELP_MESSAGE_EMAIL_DEPT_MBOX','Message from %s blocked, sender is a department mailbox');

define('_XHELP_SIZE_BYTES','Bytes');
define('_XHELP_SIZE_KB','KB');
define('_XHELP_SIZE_MB','MB');
define('_XHELP_SIZE_GB','GB');
define('_XHELP_SIZE_TB','TB');

define('_XHELP_TEXT_USER_NOT_ACTIVATED','User has not finished activation process.');

define('_XHELP_TEXT_ADMIN_DISABLED','<em>[Disabled by Administrator]</em>');

define('_XHELP_TEXT_CURRENT_NOTIFICATION','Current Notification Method');
define('_XHELP_NOTIFY_METHOD1','Private Message');
define('_XHELP_NOTIFY_METHOD2','Email');

define('_XHELP_TEXT_TICKET_LISTS','Ticket Lists');
define('_XHELP_TEXT_LIST_NAME','List Name');
define('_XHELP_TEXT_CREATE_NEW_LIST','Create New List');
define('_XHELP_TEXT_NO_RECORDS','No Records Found');
define('_XHELP_TEXT_EDIT','Edit');
define('_XHELP_TEXT_DELETE','Delete');
define('_XHELP_TEXT_CREATE_SAVED_SEARCH','Create Saved Search');
define('_XHELP_MSG_ADD_TICKETLIST_ERR','Error: ticket list was not created.');
define('_XHELP_MSG_DEL_TICKETLIST_ERR','Error: ticket list was not deleted.');
define('_XHELP_MSG_NO_ID','Error: you did not specify an id.');
define('_XHELP_TEXT_VIEW_MORE_TICKETS','View More Tickets');
define('_XHELP_MSG_NO_EDIT_SEARCH','Error: you are not allowed to modify this search.');
define('_XHELP_MSG_NO_DEL_SEARCH','Error: you are not allowed to delete this search.');

define('_XHELP_TEXT_PROBLEM','Problem');
define('_XHELP_TEXT_SOLUTION','Solution');
define('_XHELP_TEXT_PREVIEW','Preview');
define('_XHELP_TEXT_SUBMIT','Submit');
define('_XHELP_TEXT_ADD_FAQ','Add FAQ');
define('_XHELP_TEXT_CATEGORIES','Categories');
define('_XHELP_TEXT_FAQ','FAQ');
define('_XHELP_MESSAGE_NO_ADD_FAQ','Error: you do not have permission to add an Faq');
define('_XHELP_MESSAGE_NO_FAQ','Error: no Faq / Knowledgebase application configured');
define('_XHELP_MESSAGE_ERR_ADD_FAQ','Error: Faq was not added.');
define('_XHELP_MESSAGE_ADD_FAQ','Faq was added successfully.');
define('_XHELP_TEXT_ADD_STAFF','Add Staff');
define('_XHELP_TEXT_USER_SAID','%s said:');
define('_XHELP_TEXT_ACC_TICKETS','Accepted Tickets');
define('_XHELP_TEXT_UNACC_TICKETS','Unaccepted Tickets');

define('_XHELP_TEXT_REPORTS','Reports');
define('_XHELP_TEXT_REPORT','Report');
define('_XHELP_TEXT_REPORT_NAME','Report Name');
define('_XHELP_MSG_NO_REPORT','Error: no report was specified.');
define('_XHELP_MSG_NO_REPORT_LOAD','Error: the report you specified could not be loaded.');

define('_XHELP_TEXT_VIEW_REPORT','View Report');

define('_XHELP_TEXT_TOTAL','Total');
define('_XHELP_TEXT_NO_JPGRAPH','Error: JPGraph is not installed on your server. Please look at readme.txt for xHelp.')

?>