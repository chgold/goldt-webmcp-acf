# Changelog

## 1.0.1 — 2026-11-09

- Security: permission ceiling (SEC-00) — getFieldValue / getFieldsForPost / getFieldObject now require `read_post` on the requested post, and searchByFieldValue drops every post the user cannot read (previously other authors' private/draft posts and their titles were returned).
- Security: permission ceiling (SEC-00) — listFieldGroups / getFieldGroup / listFields require ACF's admin capability (`acf_get_setting( 'capability' )`); updateFieldValue / deleteFieldValue require per-post `edit_post` instead of global `edit_others_posts`.

## 1.0.0

- Initial release.
