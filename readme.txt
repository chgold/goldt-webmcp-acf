=== Goldnat for ACF ===
Contributors: chgold
Tags: acf, advanced custom fields, mcp, ai, agents
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: Proprietary
License URI: https://goldnat.ai/terms

Lets AI agents read Advanced Custom Fields data — field groups, field definitions and values on posts, users, terms and options pages.

== Description ==

Adds read-only `acf.*` tools to the Goldnat WebMCP Bridge: listFieldGroups, getFieldGroup, listFields, getFieldValue, getFieldsForPost, getFieldObject, searchByFieldValue, getUserFields, getTermFields and getOptionsPageFields (the last one only when ACF PRO is active).

Every tool runs with the WordPress permissions of the connected user (read_post / edit_user / edit_term / ACF admin capability).

Write tools (set and clear values, bulk and copy, field-group management) are in Goldnat for ACF — Pro.

Requires the free Goldnat WebMCP Bridge plugin and Advanced Custom Fields.

== Changelog ==

= 1.2.0 =
* Breaking: the five write tools moved to Goldnat for ACF — Pro (same tool names). This add-on is now read only.

= 1.1.0 =
* New read tools: getUserFields, getTermFields, getOptionsPageFields.

= 1.0.1 =
* Security: per-object permission ceiling on all reads.

= 1.0.0 =
* Initial release.
