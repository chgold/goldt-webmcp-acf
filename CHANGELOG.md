# Changelog

## 1.0.1 — 2026-11-09

- Security: permission ceiling (SEC-00) — getFieldValue / getFieldsForPost / getFieldObject now require `read_post` on the requested post, and searchByFieldValue drops every post the user cannot read (previously other authors' private/draft posts and their titles were returned).

## 1.0.0

- Initial release.
