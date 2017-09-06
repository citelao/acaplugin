# How to Run Auditions

Ben Stolovitz
(ben@stolovitz.com)

If any part of this guide is unclear or incorrect, please contact Ben Stolovitz for corrections.

## Introduction

This guide should give a step-by-step guide to running a cappella auditions *and* provide a brief technical overview of the systems at play.

The ACAC website consists of a standard Wordpress install plus two custom plugins:

1. ACAC Theme
2. ACAC Features

### ACAC Theme

The **ACAC Theme** is a basic Wordpress theme; it makes the pages you write look like ACAC pages. 

It has custom behavior on the home page: instead of displaying the page content (where you would normally write a page), it supports several arbitrary *sections*. If you try editing the page, you will see below the WYSIWYG editor a form to edit the sections in use. They support HTML, which I use to make the buttons look pretty.

Many of the pages are blank expect for some text and a **shortcode** (text surrounded by brackets---like `[acac_prefs]`). These are codes that **ACAC Features** uses to render auditionee-facing forms, like the pref cards and registration pages.

### ACAC Features

The **ACAC Features** necessary to run auditions are written entirely separately from the **ACAC Theme**. Most of the code handles internal administration pages used for callback selection and logistics.

It creates several new content types:

- Groups
- Auditionees
- Songs

It adds a `Manage Auditions` preference pane for all administrators.

It also registers two shortcodes for rendering auditionee-accessible forms:

- `acac_registration`
- `acac_prefs`

## The auditions process

Audtions are complex, but the webmaster's job is fairly straightforward.

### Step 1. Prepare for the onslaught

Get the system ready to handle auditionees registering.

You will receive emails throughout the day from people who messed up registration. You and anyone with `administrator` user role can edit auditionee data manually by clicking any name.

1. (Delete old auditionees?)
2. Add new a cappella groups
	1. Add each new group as a new (Wordpress) user.
	2. Set their role to `author`.
	3. Add a new `Group` for this a cappella group (in the `Groups` menu).
	4. Set this `Group`'s author to the newly-created author. This lets this user (and only this user and administrators like you) access this group-management page.
3. In the `Manage Auditions` menu on the Wordpress admin (`acac.wustl.edu/wp-admin`), configure the callback dates so that new auditionees are prompted to list conflicts for the right days.
4. In the same menu, set the registration subject and message that appear in their confirmation email. You can use shortcodes (like `[first_name]`) to include registration information in the response.
5. In the same menu, set the auditions stage to stage 1---allow registration, but don't let groups view callbacks.
5. Customize the registration form page as needed (it exists as a standard Wordpress page; the content should include the shortcode `[acac_registration]` to render the registration form).
6. Test your newly written registration confirmation email and registration process by doing a test registration. The site uses the notoriously unreliable `wp_mail` function to send email, but this can be made very reliable by using something like the `WP Mail SMTP` plugin. It should already be configured on the main website, but otherwise you will have to set it up to send from `acac@su.wustl.edu`, for example.
7. Share the registration URL with all the groups (`acac.wustl.edu/register`); make sure that they know to force new auditionees to register. There is a sample instructional email included in `docs/Instructions for groups.md`. There are some notes to you (`Dev note`s) that you can remove.
8. Reset passwords for all the groups that forgot them.

### Step 2. Schedule callbacks

After preliminary auditions finish, it is now time to help the groups schedule callbacks.

1. Hopefully all the groups have entered their callbacks on their `Group`'s page. If they haven't, make sure they do that.
2. In the `Manage Auditions` menu, set the auditions stage to stage 2---you want to close registration, generate pref cards, and allow groups to view callbacks.
3. Export the auditionees so ACAC can view conflict information and callback counts. There is no in-app way to do this, so I did this:
	1. Export all auditionees to CSV (using the `All Export` plugin).
	2. Edit the CSV (in Google Docs or Excel) to remove unneccessary columns (like pref card ID).
	3. Let people filter and sort the columns (so people can sort by callback number, etc).
	4. Share this Google Doc with ACAC. It is not your job to physically schedule callbacks or notify auditionees of their times. Thank God.
4. Export the auditionees to MailChimp. Again, no easy way.
	1. Export all auditionees to CSV (as before).
	2. To make it a little prettier, I edited all the list of called-back groups to be a comma-separated list (`Mosaic Whispers, Aristocats, and Stereotypes`) using a formula, but that is wholly unneccessary. You may need to change the name from IDs to actual group names (you can find the ID for a group in the URL on the edit page).
	3. Upload as a list to our MailChimp account. When uploading, ensure you load *callback count*, *called-back groups*, and *pref card ID* as variables. MailChimp has good documentation on this.  Make sure you create the proper MERGE TAGS in List>Settings>Merge Fields. **Note:** the pref card ID is just an *ID*, not a URL. The URL is traditionally `acac.wustl.edu/prefs?key=ID_HERE`---check out the old drafts.
	4. Draft a rejection email, a single-callback email, and a multiple-callbacks email. Old versions should be viewable on the site. Test these several times and ensure variable-replacement works (I did it wrong twice).
	5. Send the rejection email to all folks with `0` callbacks, the single-callback email to all folks with `1` callback, and the multiple-callbacks email to all folks with `>1` callback.

### Step 3. Handling preferences

Callback lists are published, emails are out, and people want to start filling out their pref cards. Nothing for you to do! Yay!

You will be getting emails throughout the day from people who messed up their forms. **You don't have to update their preferences manually!** If you find them in the `Auditionees` list, you can edit their data and *uncheck* the "Has filled out pref card" checkbox. If you save this change, they will be able to fill out a new pref card. 

### Step 4. Draft time

Undoubtedly my favorite time in a-cappella-world, it's time to close preferences and let the games begin.

1. At 4PM (or after whatever leeway you want), begin the *final stage* of auditions, in the `Manage Auditions` screen. This closes all forms and lets groups view each auditionee's preferences. You don't need to do anything.
2. In the royal draft hall, login to the ACAC website on whatever computer Rohan wants to use as the drafting machine.
3. One-by-one, go to each group's page and add the inductees to the group's "accepted" list. You can also go to an auditionee's page and change their "accepted" group manually.
4. Export the final lists to MailChimp. What fun!
	1. Same deal as before, export to CSV
	2. Make sure you grab the *accepted group* field this time.
	3. Draft the acceptance and rejection emails. Old versions should be on MailChimp. Test test test.
	4. Send the emails appropriately (schedule for 8AM the next day)!
5. YOU'RE DONE! Close auditions in the `Manage Auditions` page. Purge all memories of this from your mind.

## Technical stuff

Nothing I can think of atm.

There is a songboard and it works, I just haven't added the old songs.

## TODO

- [x] Hardcoded registration email (with hardcoded dates uhoh)
- [-] Make a deserializer PHP program for ACAC auditionees
- [ ] Debug the accepted group dropdown (why is it empty on Chloe's computer?)

## Links

https://github.com/citelao/acaplugin

https://github.com/citelao/acac-theme