=== CardanoPress - Governance for Cardano ===
Contributors: pbwebdev, gaft
Donate link: https://www.paypal.com/donate/?hosted_button_id=T8MR6AMVWWGK8
Tags: cardano, blockchain, web3, ada, token-gating
Requires at least: 5.9
Tested up to: 6.6.99
Stable tag: 1.7.0
Requires PHP: 7.4
License: GPLv3
License URI: https://www.gnu.org/licenses/licenses.html
Requires Plugins: cardanopress

Allows users to be able to submit, participate and vote on governance proposals. Voting is done on-chain by submitting
transactions with metadata. Voting power is based on the amount of Non-Fungible Tokens or Fungible Tokens in a users
connected wallet. Requires parent plugin CardanoPress.


== Description ==

The CardanoPress Governance plugin allows projects to engage with their users by allowing them to cast votes on-chain.
For any decentralised autonomous organisation, this is a must. The Governance plugins will allow you to write and
publish proposals, allow you to set up the parameters around voting power and view historic proposals and their results.

This plugin requires the parent plugin [CardanoPress](https://wordpress.org/plugins/cardanopress/) and a free account with [Blockfrost](http://bit.ly/3W90KDd) to be able to talk to the Cardano blockchain.

The plugin is created by the team at [PB Web Development](https://pbwebdev.com).

You can find out more information about CardanoPress and our blockchain integrations at [CardanoPress.io](https://cardanopress.io).

= Example Use Cases =

For projects that have a fungible token, you can configure the plugin to take into account the balance of that token in
a users connected wallet. For each proposal that is listed on the website, you can define the voting power requirements
and snapshot time and date. This will allow users to move assets to their wallets in time for a voting power snapshot.

Users of the project can then case their vote but sending a transaction to their own wallet with a special unique
decimal value. This value is recorded on chain along with some corresponding metadata to help identify the proposal and
the voting decision by the wallet owner on that proposal.

The same scenario can be setup but with a non-fungible token and the amount of NFTs in a user's wallet be used as the
voting power.


== Screenshots ==
1. Governance configuration screen
2. FT & NFT policy ID configuration
3. Voting interface example


== Follow Us ==

Follow us on [Twitter](https://twitter.com/cardanopress)
View all of our repos on [GitHub](https://github.com/CardanoPress/)
View all of our documentation and resources on our [website](https://cardanopress.io)


== Installation ==

The Governance Plugin requires the parent plugin [CardanoPress](https://wordpress.org/plugins/cardanopress/). The
CardanoPress plugin manages the communication with the Cardano blockchain and wallet integrations. Please ensure you
install and configure the core CardanoPress plugin before installing the Governance plugin.

This plugin requires your own standalone WordPress installation and access to the web server to add a line of code to your htaccess file.

1. Installing the Governance Plugin

Find the plugin in the list at the backend and click to install it. Or, upload the ZIP file through the admin backend. Or, upload the unzipped tag-groups folder to the /wp-content/plugins/ directory.

2. Activate the plugin

Navigate to Plugins from the WordPress admin area and activate the CardanoPress - Governance plugin.

The plugin will create the base pages for all that you need.

3. Configure the plugin

Navigate to the configuration screen for the Governance plugin. Here you can define the global voting power rules for
your website/project. These can be overridden on a per proposal basis.

4. Create a Proposal

From the Admin dashboard, navigate to Proposals. Here you can create a new proposal. Define the details from the title,
overview of the proposal, voting options and the time and date period for voting on the proposal.

These proposals will be accessible by users from the frontend of the website once you have linked to the proposals
archive layout from your main menus.

For more detailed documentation and tutorials on how to use the plugin, please visit the [CardanoPress documentation website](https://cardanopress.io).


== Get Support ==

We have community support available on our website under the [CardanoPress forums](https://cardanopress.io/community/). We also have an online chat support via our [Discord server](https://discord.gg/CEX4aSfkXF). We encourage you to use the forums first though as it will help others that read through the forums for support.


== Frequently Asked Questions ==

= Can I Run This on My WordPress.com Website? =

No you can not. You need full access to your web server to be able to allow for the WASM file type to load. Without this access you will not be able to run the plugin.

= Can I Get Paid Support? =

Yes you can, we offer subscription to support for our plugins and consultation to help get your project started and to a professional level.

= Where Can I See Other Projects That Are Using CardanoPress? =

If you visit our main website, [CardanoPress.io](https://cardanopress.io), there will be a section dedicated to all the websites and projects that have built using CardanoPress.

= Can I customise the look and feel of the plugin? =

Yes, we've built the plugin and sub plugins with hooks and template layouts that can over overridden in a child theme. We've followed the same methods as WooCommerce where you simply need to copy the template files into your child theme to start overriding the layouts.

We've also taking into account page builders and created short codes for all the template parts of the theme. This will allow builders such as Divi, Elementor, WPBakery to be used with CardanoPress.


== Privacy ==

This plugin does not collect or process any personal user data unless you expressively opt-in.


== Changelog ==

You can follow our [GitHub release](https://github.com/CardanoPress/plugin-governance/releases) for full details on updates to the plugins.

= 1.7.0 =
- Made the discussion link url the only requirement
- Easily set voting power messages in the dashboard

= 1.6.0 =
- Add index.php files to all folders
- Minor code fixes
  - Return correct number value
  - Use correct hook type
  - A non-nullable

= 1.5.0 =
- Optional voting fee that sends to a predefined wallet address

= 1.4.0 =
- Disable fields if no powers
- Revamp voting flow actions
  - validate, verify then complete

= 1.3.0 =
- Correctly render provided templates in block themes

= 1.2.1 =
- Corrected version requirements
- Add new `requires` plugins header

= 1.2.0 =
Localize script messages
Translatable strings
Simplify templates
Minor code fixes

= 1.1.0 =
An updated framework with prefixed dependencies
Handle recommended and required plugins with TGMPA

= 1.0.0 =
First stable release (exact same version as 0.14.0)


== Upgrade Notice ==

Please ensure that you back up your website before upgrading or modifying any of the code.
