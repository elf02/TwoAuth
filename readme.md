# TwoAuth - WordPress Plugin
2-Step-Verification for WordPress.

During a session at [WordCamp 2014][5], [Christoph Daum][6] had the idea to make a simple 2-Step-Verification via email. WordPress plugin hero [Sergej Müller][2] ([@wpseo][3]) turned it into a plugin called [2-Step-Verification][1]. With TwoAuth, i write my own solution based on its plugin.

## New in version 1.0.2
* App password implementation
* Optional separate email for token request

## New in version 1.0.1
* Security improvement

## How It Works
The plugin extends the WordPress login page. By clicking on the "Get TwoAuth Token" button, the plugin sends an unique token to your WordPress account email or an separate email address.

You have five minutes, to enter these five digits into the new "Two Auth Token" field. When the time has passed, you have to generate a new token.

## Additional
This is an experimental plugin. Please give me feedback: [email][7], [@_elf02][8].

## Problems: Have You Tried Turning It Off And On Again?
Should you have any trouble with the plugin, you can just delete it from the server and the login procedure still works as usual.

Plugin Page: [TwoAuth - WordPress Plugin][4]

  [1]: https://github.com/sergejmueller/2-Step-Verification
  [2]: http://wpcoder.de/
  [3]: https://twitter.com/wpseo
  [4]: http://elf02.de/2014/06/17/twoauth-wordpress-plugin/
  [5]: http://2014.hamburg.wordcamp.org/
  [6]: http://christoph-daum.de/
  [7]: mailto:chris@elf02.de
  [8]: https://twitter.com/_elf02
