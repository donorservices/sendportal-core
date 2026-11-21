# Sendportal Core

## 3.1.1 - 2026-11-20 (donorservices fork)
- Fix: SMTP send failures were swallowed and the message marked as sent. The SMTP adapter now throws, so the message stays unsent and the queued job fails (and can be retried), like the API-based adapters
- Fix: SMTP messages stored `-1` as their message ID; they now store the ID the server reports (or the generated Message-ID)
- Fix: sending a campaign test email or a draft message no longer returns a 500 when the email service rejects it; the reason is shown instead
- Fix: "Failed to dispatch test email." was flashed under a numeric key and never shown
- Fix: webhook endpoints returned 500 for a body that is not a JSON object; they now return 400 (SendGrid also skips array entries that are not objects)
- Fix: API campaign create/update required `tags` when `send_to_all` was JSON `true` (only `1` worked)

## 3.1.0 - 2026-11-20 (donorservices fork)
- Support Laravel 12 and 13 (`illuminate/support ^11.0|^12.0|^13.0`); tested on PHP 8.4 with Symfony 8.1
- Tests migrated to PHPUnit 12 (`#[Test]` attributes, stubs instead of mocks without expectations)
- Fix: subscriber page returned 500 for unsubscribed subscribers, and reports showed "UTC" instead of dates (`Helper::displayDate()` used Carbon 3's `tz(null)` getter)
- Fix: webview, unsubscribe and subscribe pages return 404 (was 500) for unknown message hashes
- Fix: PHP 8.2+ dynamic property deprecation in API `CampaignStoreRequest`; the draft-status check now uses the `{campaign}` route parameter only
- Fix: PHP 8.4 implicit-nullable deprecation in `Helper::displayDate()`; missing `Arr` import in `BaseTenantRepository`
- API: `GET /api/v1/subscribers` honours `?per_page=` (default 25, max 100) like the tags endpoint, so clients can page large lists within the 60 req/min limit
- Fix: `GET /campaigns/{id}` returned 500 (`View [common.template] not found`); it now redirects to the campaign's edit, status or report page by status
- Fix: the web UI let you edit and update campaigns that were no longer drafts (the API already refused); it now redirects with an error
- Fix: spam complaints now set the message's `complained_at` (previously only `unsubscribed_at`, so complaints were never recorded)
- Fix: Postal adapter rewritten for `postal/postal` 2.x (it referenced the removed `Postal\SendMessage` class, so every Postal send failed)
- Fix: subscriber export filename used the month (`m`) where it meant minutes
- Fix: API template and tag updates checked name uniqueness against a non-existent `workspaceId` route parameter, so duplicate names were allowed
- Templates must contain `{{content}}` (web and API); without it campaign content was silently dropped
- PHPUnit configuration migrated to the 12.x schema

## 3.0.0 - 2024-04-18
- Upgrade dependencies to Laravel V11 ([#194](https://github.com/mettle/sendportal-core/pull/194))

## 2.0.3 - 2021-07-05
- Self-host CSS and JS assets ([#144](https://github.com/mettle/sendportal-core/pull/144))
- Handle merge content and subject when subscriber first name or last name are null ([#143](https://github.com/mettle/sendportal-core/pull/143))
- Add german translation ([#140](https://github.com/mettle/sendportal-core/pull/140))
- PHP 8: Bug fix for str_ireplace - empty values ([#133](https://github.com/mettle/sendportal-core/pull/133))
- Update GitHub workflow for PHP CS Fixer v3 ([#130](https://github.com/mettle/sendportal-core/pull/130))
- Update dependencies

## 2.0.2 - 2021-04-30
- Add matrix strategy to cover all supported php versions ([#127](https://github.com/mettle/sendportal-core/pull/127))
- Tag Pagination ([#126](https://github.com/mettle/sendportal-core/pull/126))
- Keep query string for subscribers pagination ([#122](https://github.com/mettle/sendportal-core/pull/122))
- Support PHP 8 ([#120](https://github.com/mettle/sendportal-core/pull/120))
- Update dependencies

## 2.0.1 - 2021-03-08
- Message subject tags ([#114](https://github.com/mettle/sendportal-core/pull/114))
- Add SMTP driver ([#112](https://github.com/mettle/sendportal-core/pull/112))
- Split campaigns index ([#111](https://github.com/mettle/sendportal-core/pull/111))
- Delete failed messages on subscriber deletion ([#110](https://github.com/mettle/sendportal-core/pull/110))
- Fix rename segments to tags migration ([#115](https://github.com/mettle/sendportal-core/pull/115))
- Fix table prefixing in raw query ([#107](https://github.com/mettle/sendportal-core/pull/107))

## 2.0.0 - 2021-02-08
- Allow Core to be installed as a package in an existing project ([#85](https://github.com/mettle/sendportal-core/pull/85))
- Bump Laravel to v8 ([#103](https://github.com/mettle/sendportal-core/pull/103))

## 1.0.10 - 2020-10-15
- Resolve invalid message issue ([#87](https://github.com/mettle/sendportal-core/pull/87))

## 1.0.9 - 2020-10-09
- Set Subscriber unsubscribed_at via API ([#83](https://github.com/mettle/sendportal-core/pull/83))
- Adjust quota service ([#82](https://github.com/mettle/sendportal-core/pull/82))
- Ensure template names are unique ([#81](https://github.com/mettle/sendportal-core/pull/81))
- Gitignore php_cs.cache ([#80](https://github.com/mettle/sendportal-core/pull/80))
- Validate imported subscribers ([#79](https://github.com/mettle/sendportal-core/pull/79))
- Added ability to delete draft messages ([#78](https://github.com/mettle/sendportal-core/pull/78))
- Fix campaign count formatting ([#77](https://github.com/mettle/sendportal-core/pull/77))
- Make password hidden in setup command ([#76](https://github.com/mettle/sendportal-core/pull/76))
- Filter subscribers by segment ([#75](https://github.com/mettle/sendportal-core/pull/75))
- Move ping route to controller ([#73](https://github.com/mettle/sendportal-core/pull/73))
- Default campaigns to send to all ([#71](https://github.com/mettle/sendportal-core/pull/71))
- Validate segments when creating a campaign ([#70](https://github.com/mettle/sendportal-core/pull/70))
- Add GUI setup process ([#68](https://github.com/mettle/sendportal-core/pull/68))
- Clean up template deletion ([#67](https://github.com/mettle/sendportal-core/pull/67))
- Add ability to cancel campaigns ([#66](https://github.com/mettle/sendportal-core/pull/66))
- Add backslash to language files ([#65](https://github.com/mettle/sendportal-core/pull/65))

## 1.0.7 - 2020-08-14

- Add ability to delete a subscriber ([#60](https://github.com/mettle/sendportal-core/pull/60))
- Fix behaviour that removed subscribers on segment update ([#61](https://github.com/mettle/sendportal-core/pull/61))
- Add ability to test an e-mail service ([#62](https://github.com/mettle/sendportal-core/pull/62))

## 1.0.6 - 2020-08-07

- Add GH action for php-cs ([#35](https://github.com/mettle/sendportal-core/pull/35))
- Add Campaign Endpoints to API ([#40](https://github.com/mettle/sendportal-core/pull/40))
- Uniform mail adapters send method ([#51](https://github.com/mettle/sendportal-core/pull/51))
- Add Sqlite support and defaults the test suite to use the sqlite database ([#52](https://github.com/mettle/sendportal-core/pull/52))
- Fix count unique opens per period in Postgres ([#53](https://github.com/mettle/sendportal-core/pull/53))
- Bump elliptic from 6.5.2 to 6.5.3 ([#55](https://github.com/mettle/sendportal-core/pull/55))
- add from name to outgoing emails ([#56](https://github.com/mettle/sendportal-core/pull/56))
- add mailjet support ([#57](https://github.com/mettle/sendportal-core/pull/57))

## 1.0.5 - 2020-08-03

- bump lodash version ([#44](https://github.com/mettle/sendportal-core/pull/44))
- fix primary key when chunking on segments ([#54](https://github.com/mettle/sendportal-core/pull/54))

## 1.0.4 - 2020-07-21

- fix SES adapter ([#42](https://github.com/mettle/sendportal-core/pull/42))

## 1.0.3 - 2020-07-11

- added CHANGELOG.md ([#39](https://github.com/mettle/sendportal-core/pull/39))
- added API for templates ([#38](https://github.com/mettle/sendportal-core/pull/38))
- add validation for deleting templates that are in use ([#37](https://github.com/mettle/sendportal-core/pull/37))
- add Template API endpoints ([#38](https://github.com/mettle/sendportal-core/pull/38))
- added ability to delete segments ([#36](https://github.com/mettle/sendportal-core/pull/36))
- handle null campaign content ([#34](https://github.com/mettle/sendportal-core/pull/34))

## 1.0.2 - 2020-06-22

- changed setup command success outputs to use info instead of line [#33](https://github.com/mettle/sendportal-core/pull/33))
- increased content length in the campaign and template tables ([#32](https://github.com/mettle/sendportal-core/pull/32))
- replaced relative URLs with route helpers when linking to the dashboard ([#29](https://github.com/mettle/sendportal-core/pull/29))
- renamed and cleaned up model factories ([#31](https://github.com/mettle/sendportal-core/pull/31))

## 1.0.1 - 2020-06-11

- fixed dashboard subscriber growth chart in PostgreSQL environments ([#15](https://github.com/mettle/sendportal-core/pull/15))
- apply correct namespace to layouts that are extended in subscriber/unsubscribe views ([#16](https://github.com/mettle/sendportal-core/pull/16))
- remove deprecated helper methods ([#17](https://github.com/mettle/sendportal-core/pull/17))
- fix database constraint error when dispatching campaigns in environments that use the SES provider ([#18](https://github.com/mettle/sendportal-core/pull/18))
- fix subscriber email validation rule that was failing in PostgreSQL environments ([#19](https://github.com/mettle/sendportal-core/pull/19))
- fix behaviour surrounding the dispatch of scheduled campaigns ([#20](https://github.com/mettle/sendportal-core/pull/20))
- fix registration toggle ([#21](https://github.com/mettle/sendportal-core/pull/21))
- fix bold text rendering in subscriber/unsubscribe views ([#27](https://github.com/mettle/sendportal-core/pull/27))

## 1.0.0 - 2020-06-09

- initial release
