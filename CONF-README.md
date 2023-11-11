** Task "server and PHP configuration and protection from attacks" ** 
* Protection from attacks: Giving an attacker specific information about version
  numbers will greatly simplify the process of attacking the server. In Apache, the
  directive ServerTokens is used to control what information is given to clients.
  ServerTokens Prod Apache
  ServerTokens Major Apache/2
  ServerTokens Minor Apache/2.2
  ServerTokens Min Apache/2.2.14
  ServerTokens OS Apache/2.2.14 (Ubuntu)
  ServerTokens Full Apache/2.2.14 (Ubuntu) PHP/5.3.2-1ubuntu4.9
  Action: change the conf "ServerTokens Full" to "ServerTokens Prod"
  How: go to C:\xampp\apache\conf\extra\httpd-default.conf and change the line "ServerTokens Full" to "ServerTokens Prod"

* Protection from attack: PHP will by default send information about the fact that PHP is used and which version. It can be valuable information for an attacker. 
  Action: By default, an X-Powered-By header is added to the HTTP response, specifying the PHP version in use. This information can be suppressed using the expose php = Off directive. Some combinations are given below.
  How: go to following files and change the line "expose_php = On" to "expose_php = Off"
  "C:\xampp\php\php.ini" and "C:\xampp\php\php.ini-development" and "C:\xampp\php\windowsXamppPhp\php.ini-development" and "C:\xampp\php\php.ini-production" and "C:\xampp\php\windowsXamppPhp\php.ini-production"

* Protection from attack: once the application is in production, errors reporting should be turned off. Errors can give valuable information to an attacker, e.g., file paths, file names, uninitialized variables, and arguments to functions, which in the worst case could include passwords to databases used.
  Action:Error reporting is controlled in php.ini. The directive display errors specifies if errors should be displayed on the screen. This defaults to On but should be turned off in production stage.
  How: go to following files and change the line "display_errors = On" to "display_errors = Off"
  "C:\xampp\php\php.ini" and "C:\xampp\php\php.ini-development" and "C:\xampp\php\windowsXamppPhp\php.ini-development" and "C:\xampp\php\php.ini-production" and "C:\xampp\php\windowsXamppPhp\php.ini-production"
  Protection from attack: Instead, errors should be logged to a file.
  Action: This can be done by setting log errors = On and specifying the file to log to using the directive error log.
  How: go to following files and change the line "log_errors = Off" to "log_errors = On"
  "C:\xampp\php\php.ini" and "C:\xampp\php\php.ini-development" and "C:\xampp\php\windowsXamppPhp\php.ini-development" and "C:\xampp\php\php.ini-production" and "C:\xampp\php\windowsXamppPhp\php.ini-production"
  Additionally, go to C:\xampp\php and create a file called "logs" and in that file create a file called "php_errors_log.txt"
  Then go to following files and change the line "error_log = php_errors_log" to "error_log = C:\xampp\php\logs\php_errors_log.txt"
