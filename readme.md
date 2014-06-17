# TwoAuth - WordPress Plugin
Simple Two-Factor authentication for WordPress.

During a session at [WordCamp 2014][5], [Christoph Daum][6] had the idea to make a simple Two-factor authentication via email. WordPress plugin hero [Sergej Müller][2] ([@wpseo][3]) turned it into a plugin called [2-Step-Verification][1]. With TwoAuth, i write my own solution based on its plugin.

## How It Works
The plugin extends the WordPress login page. By clicking on the "Get TwoAuthToken" button, the plugin sends an unique token to your WordPress account email address.

You have five minutes, to enter these five digits into the new "Two Auth Token" field. When the time has passed, you have to generate a new token.

The token will only generated, when the username and the password are valid.

## Problems: Have You Tried Turning It Off And On Again
Should you have any trouble with the plugin, you can just delete it from the server and the login procedure still works as usual.

## Todo
* Translation
* Maybe an admin page

Plugin Page: [TwoAuth - WordPress Plugin][4]

  [1]: https://github.com/sergejmueller/2-Step-Verification
  [2]: http://wpcoder.de/
  [3]: https://twitter.com/wpseo
  [4]: http://elf02.de/2014/06/17/twoauth-wordpress-plugin/
  [5]: http://2014.hamburg.wordcamp.org/
  [6]: http://christoph-daum.de/
