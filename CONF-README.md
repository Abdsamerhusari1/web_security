Task "server and PHP configuration and protection from attacks"
* Protection from attacks: Giving an attacker specific information about version
  numbers will greatly simplify the process of attacking the server. In Apache, the
  directive ServerTokens is used to control what information is given to clients.
  Action: change the conf "ServerTokens Full" to "ServerTokens Prod"
  How: go to C:\xampp\apache\conf\extra\httpd-default.conf and change the line "ServerTokens Full" to "ServerTokens Prod"