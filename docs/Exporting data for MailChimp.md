These are SQL queries I used to export data manually.

For most export, you can use the built-in bulk action (Auditionees > Bulk Actions > Export).

# Filtering for different-table-stored data

This works for the list of callbacks by auditionee.

I got a list of all callbacks by auditionee:

```sql
SELECT a.`ID`,
	GROUP_CONCAT(g.`p2p_from` SEPARATOR ', ')
FROM wp_posts a
LEFT JOIN wp_p2p g ON g.`p2p_to` = a.`ID`
WHERE a.post_type = "acac_auditionee"
GROUP BY a.ID
```

Aligned those IDs with the export IDs, pasted them into a separate sheet. Added them to the real sheet:

`=VLOOKUP(G2,Callbacks!A:B,2,TRUE)`

Where `G2` is the auditionee's ID column, `Callbacks!A:B` is the sheet with the exported data, in the format `Auditionee ID`, `Group IDs` (simple copy-paste from Sequel Pro).

Then I used find and replace manually from the list of group IDs I found on the website (look at the URLs for each group edit page).

Then I used a nice hacky way to get group count:

```excel
=IF(ISBLANK(E2), 0, COUNTA(SPLIT(E2, ",", FALSE, TRUE)))
```

`E2` is the callback groups column for the current auditionee.

---

# Filtering for locally-stored data

This works for anything stored in the auditionee itself (so not callbacks):

I filtered in sublime by regex: `i:\d*;s:\d*:"(\d*)";`

Then I replaced with the first capture followed by a comma: `$1, `

Then I stripped trailing commas: `, $` replace with ``.

Then I took the list of group IDs I manually found and did a manual find/replace in Sublime