# Mailster Google Analytics

Contributors: everpress, mailster, xaverb  
Tags: google analytics, newsletter, email, analytics, mailster  
Requires at least: 6.0  
Tested up to: 7.1  
Stable tag: 1.5.0  
License: GPLv2 or later  
Requires PHP: 7.2.5

Add Google Analytics UTM parameters to Mailster newsletter links and track the web version in GA4.

## Description

**Mailster Google Analytics** adds [Google Analytics](https://support.google.com/analytics/answer/10917952) campaign parameters to links in the [Mailster Newsletter Plugin for WordPress](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Google+Analytics), and can record a page view of each newsletter’s public web version.

Use it when you send Mailster campaigns and want those clicks to show up in Google Analytics as email traffic, with a source, medium, and campaign name, instead of as direct visits.

> This plugin requires [Mailster](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Google+Analytics). It does not include a Google Analytics account or connect to Google with an API key. When you save a Measurement ID, the newsletter web version loads Google’s tag. UTM parameters are added to link URLs. The plugin does not upload your subscriber list to Google.

### UTM parameters on newsletter links

- **Campaign parameters** — Adds `utm_source`, `utm_medium`, `utm_term`, `utm_content`, and `utm_campaign` to `http` and `https` links. Values are URL-encoded.
- **Defaults** — On first activation, if these options are not saved yet, source is `newsletter`, medium is `email`, term is `%%LINK%%`, content is empty, and campaign is `%%CAMP_TITLE%%`.
- **Merge tags** — You can use `%%CAMP_ID%%`, `%%CAMP_TITLE%%`, `%%CAMP_TYPE%%`, `%%CAMP_LINK%%`, `%%SUBSCRIBER_ID%%`, `%%SUBSCRIBER_EMAIL%%`, `%%SUBSCRIBER_HASH%%`, and `%%LINK%%`. The settings screen has copy buttons for these except `%%SUBSCRIBER_ID%%`, which the plugin still replaces when you type it in.
- **Mailster click tracking** — When the campaign tracks clicks, Mailster’s redirect adds the parameters after the click. When click tracking is off, the parameters are written into the link in the email.
- **External domains** — When this option is off, only links on this site’s domain are tagged. On first activation the option allows external domains.
- **Per campaign** — Turn on campaign-based values to show a Google Analytics box in the campaign editor. Finished and active campaigns keep those fields locked, as does an autoresponder while you view its statistics.

### Newsletter web version

On **Newsletter → Settings → Google Analytics**:

- **G-Tag ID** — GA4 Measurement ID, such as `G-XXXXXXXXXX`. Loads `gtag.js` on the public newsletter page.
- **Web Property ID (legacy)** — Older `UA-XXXXXXX-X` ID. Loads the legacy `ga.js` snippet. Use a GA4 Measurement ID for current Google Analytics reporting.
- **SetDomainName** — Optional cookie domain. Legacy tracking passes it to `_setDomainName`. GA4 passes it as `cookie_domain`.

That snippet runs in the newsletter web version only. It does not load Google’s tag on every page of the site.

### How to set it up

1. Install and activate Mailster, then activate **Mailster Google Analytics**.
2. In Google Analytics, copy the GA4 Measurement ID for the property that should receive the hits.
3. Open **Newsletter → Settings → Google Analytics**, paste the Measurement ID, review the default UTM values, and save.
4. Enable campaign-based values if a campaign needs its own source, medium, term, content, or name.
5. Send a test campaign and open a tracked link. When the destination site uses that Google Analytics property, the visit can be reported under **Acquisition → Traffic acquisition**. See [Google’s URL builder guide](https://support.google.com/analytics/answer/10917952).

Setup notes: [Track your clicks with Google Analytics](https://kb.mailster.co/track-your-clicks-with-google-analytics/?utm_campaign=wporg&utm_source=Google+Analytics&utm_medium=readme).

## Installation

1. Upload the entire `mailster-google-analytics` folder to the `/wp-content/plugins/` directory, or install it from the WordPress plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Make sure [Mailster](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Google+Analytics) is installed and active.
4. In Google Analytics, copy the GA4 Measurement ID (`G-…`) for the property you want to use. This plugin does not create that property for you.
5. Go to **Newsletter → Settings → Google Analytics**, paste the Measurement ID, set the UTM defaults, and save.
6. Optionally allow a different set of values on each campaign, and choose whether links to other domains are tagged.

## Frequently Asked Questions

### Does this plugin work without Mailster?

No. It is an addon for the [Mailster Newsletter Plugin](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Google+Analytics). The plugin header requires Mailster. There is no separate minimum Mailster version in this addon.

### Do I need a Google Analytics account?

You need a Google Analytics property if you want the hits to show up in Google Analytics. In the plugin, paste a GA4 Measurement ID. There is no API key, OAuth connection, or Google account login inside WordPress. This plugin is not a Google product.

### What is the difference between G-Tag ID and Web Property ID?

**G-Tag ID** is the GA4 Measurement ID (`G-XXXXXXXXXX`). It prints the current `gtag.js` snippet on the newsletter web version. **Web Property ID** is the legacy Universal Analytics ID (`UA-XXXXXXX-X`) and prints the older `ga.js` snippet. Use the GA4 ID for current reporting.

### When are the UTM parameters added?

If the campaign has Mailster click tracking turned on, the email link stays a Mailster tracking link and the UTM parameters are added when Mailster redirects after the click. If click tracking is off, the parameters are placed on the link in the email HTML. Parameters already present on a link are kept and override the matching default.

### Can each campaign have its own UTM values?

Yes. On **Newsletter → Settings → Google Analytics**, enable campaign-based values. The campaign editor then shows a Google Analytics box for source, medium, term, content, and name. Finished and active campaigns lock those fields, and so does an autoresponder while its statistics are open.

### Are links to other websites tagged?

Only when **External domains** is enabled. On first activation that option is on, so links to other hosts get the same UTM parameters. Turn it off to tag only links on this site’s domain.

### Is subscriber data sent to Google?

The plugin does not export your subscriber list. Two things can still reach Google or the destination site:

- If you save a Measurement ID or a legacy property ID, the newsletter web version loads Google’s script and sends a page view to that property.
- UTM values are part of the link URL. If you put `%%SUBSCRIBER_EMAIL%%` or `%%SUBSCRIBER_HASH%%` in a UTM field, that value is included in the URL. The default campaign name is the campaign title, not the subscriber’s email.

### Does this replace Mailster’s click reports?

No. Mailster still records opens and clicks in Mailster. This addon adds Google Analytics campaign parameters, and optionally a tag on the newsletter web version. It does not send form-signup events.

## Changelog

### 1.5.0

- fixed: utm arguments are now urlencoded
- fixed: do not append if tracking is enabled (will be added on click)
- fixed: warnings on PHP 8.2

### 1.4.0

- added: support for GA4

### 1.3.1

- lowered priority of the filter so adding the utm\_\* args happend at the end.

### 1.3

- improved handling of adding utm\_\* query args
- now works if tracking is disabled.

### 1.2

- added: option to allow tracking external domain

### 1.1

- updated file structure

### 1.0

- initial release
