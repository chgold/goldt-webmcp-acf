# Changelog

## 1.2.0 — 2026-11-09

- **Breaking: write tools moved to Goldnat for ACF — Pro** (`goldt-webmcp-acf-pro`, slug `acf-ai-connect-pro`). Removed from this free add-on: `acf.updateFieldValue`, `acf.deleteFieldValue`, `acf.createFieldGroup`, `acf.addField`, `acf.updateFieldGroup`. Install Goldnat for ACF — Pro 1.0.0+ to get them back under the same names (`acf.deleteFieldValue` now requires `confirm: true`).
- Free is now read only: 10 tools (listFieldGroups, getFieldGroup, listFields, getFieldValue, getFieldsForPost, getFieldObject, searchByFieldValue, getUserFields, getTermFields, getOptionsPageFields — the last only with ACF PRO).

## 1.1.0 — 2026-11-09

- New (Free, read): `acf.getUserFields` — all ACF field values for a user; per-user `edit_user` ceiling (SEC-00).
- New (Free, read): `acf.getTermFields` — all ACF field values for a taxonomy term (optional `taxonomy` guard); per-term `edit_term` ceiling (SEC-00).
- New (Free, read): `acf.getOptionsPageFields` — all ACF field values on an options page by `page_slug`; ceiling = the options page's own `capability`. Requires ACF PRO (capability `acf_pro_options_pages`, Capability-Aware Manifest v1): hidden from the manifest when ACF PRO is absent, and a direct call returns `424 missing_dependency`.

## 1.0.1 — 2026-11-09

- Security: permission ceiling (SEC-00) — getFieldValue / getFieldsForPost / getFieldObject now require `read_post` on the requested post, and searchByFieldValue drops every post the user cannot read (previously other authors' private/draft posts and their titles were returned).
- Security: permission ceiling (SEC-00) — listFieldGroups / getFieldGroup / listFields require ACF's admin capability (`acf_get_setting( 'capability' )`); updateFieldValue / deleteFieldValue require per-post `edit_post` instead of global `edit_others_posts`.

## 1.0.0

- Initial release.
