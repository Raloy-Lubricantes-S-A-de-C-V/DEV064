<?php
/*
  $Id$

*/

//------------------------------------------------------------------------------------------

//ALLOWING WEB-BASED SETUP

  //Allow web-based the setup prgram to alter this file (values are "N", or "Y").
  //Defaults to "N" (not allowed) after the first successful web install.
  //** For security: change this to "N" **
  $WEB_CONFIG = "Y";

//----------------------------------------------------------------------------------------------

//BASE DIRECTORY

  //You need to add the full webservername and dir to WebCollab here. Example"
  // "http://www.your-url-here.com/backend/org/" (don't forget the tailing slash)
  define('BASE_URL', "http://www.zar-kruse.com/intranet/apps/webcollab/" );


//----------------------------------------------------------------------------------------------

//DATABASE OPTIONS

  define('DATABASE_NAME', "zarkruse_webcollab" );
  define('DATABASE_USER', "zarkruse_intrane" );
  define('DATABASE_PASSWORD', "Totich182308" );

  //Database type (valid options are "mysql_pdo" or "postgresql_pdo")
  define('DATABASE_TYPE', "mysql_pdo" );

  //Database host (usually "localhost")
  define('DATABASE_HOST', "localhost" );

  //Database port (can usually be left empty)
  define('DATABASE_PORT', "3306" );

  /*Note:
    1. DATABASE_PORT is not required to be set and will default to standard ports.
    2. For PostgreSQL on UNIX/Linux setting DATABASE_HOST to "" (empty) will enable use of local sockets.
  */


//----------------------------------------------------------------------------------------------

//FILE UPLOADS

  //upload to what directory ?
  define('FILE_BASE', "/var/www/html/webcollab/usrdocs/" );

  //max uploaded file size in bytes (2 Mb is the default)
  define('FILE_MAXSIZE', 2000000 );

  //number of file upload boxes to show
  define('NUM_FILE_UPLOADS', 3 );

  //downloaded files to be 'inline' or 'attachment'
  define('FILE_DOWNLOAD', 'inline' );

  /*Note:
    1. Make sure the file_base directory exists, and is writeable by the webserver, or you
       won't be able to upload any files.
    2. The filebase directory should be outside your webserver root directory to maintain file
       security.  This is important to prevent users navigating to the file directory with
       their web browsers, and viewing all the files.  (The default location given is NOT outside
       the webserver root, but it makes first-time setup easier).
    3. The FILE_BASE is the full path to the operating system root, not the webserver root directory.
    4. PHP and Apache settings will overide the maximum file size set here.

  */


//----------------------------------------------------------------------------------------------

//LANGUAGE

  /* available locales are
          'en'    (English)
          'bg'    (Bulgarian)
          'ca'    (Catalan)
          'cs'    (Czech)
          'da'    (Danish)
          'de'    (German)
          'eo'    (Esperanto)
          'es'    (Spanish)
          'fr'    (French)
          'gr'    (Greek)
          'hu'    (Hungarian)
          'it'    (Italian)
          'ja'    (Japanese)
          'ko'    (Korean)
          'no'    (Norwegian)
          'nl'    (Dutch)
          'pl'    (Polish)
          'pt'    (Portuguese)
          'pt-br' (Brazilian Portuguese)
          'ru'    (Russian)
          'se'    (Swedish)
          'sk'    (Slovakian)
          'sl'    (Slovenian)
          'sr-la' (Serbian (Latin))      'sr-cy' (Serbian (cyrillic))
          'tr'    (Turkish)
          'zh-tw' (Traditional Chinese)  'zh-hk' (Simplified Chinese)
  */

  define('LOCALE', "es" );


//----------------------------------------------------------------------------------------------

//TIME AND DATE

  //date format (Refer to PHP manual examples for date() )
  $FORMAT_DATE = 'Y-M-d';

  //date and time format (Refer to PHP manual examples for date() )
  $FORMAT_DATETIME = 'Y-M-d G:i O';

  //timezone offset from GMT/UTC (hours)
  define('TZ', -6 );


//----------------------------------------------------------------------------------------------

//EMAIL CONFIGURATION

  //enable email to send messages? (Values are "Y" or "N").
  //  default is "Y".
  define('USE_EMAIL', "Y" );

      //location of SMTP server (ip address or FQDN)
      define('SMTP_HOST', "tx15.fcomet.com" );

      //mail transport (SMTP for standard mailserver, or PHPMAIL for PHP mail() )
      define('MAIL_TRANSPORT', "SMTP" );

      //SMTP port (leave as 25 for ordinary mailservers)
      define('SMTP_PORT', 465 );

      //use smtp auth? ('Y' or 'N')
      define('SMTP_AUTH', "Y" );
        //if using SMTP_AUTH give username & password
        define('MAIL_USER', "no-reply@zar-kruse.com" );
        define('MAIL_PASSWORD', "Totich182308" );
        //use TLS encryption?
        define('TLS', 'Y' );


//----------------------------------------------------------------------------------------------
// Less important items below this line

//-- These items need to be edited directly from this file --

//STYLE AND APPEARANCE

  //Style sheets (CSS) Note: Setup always uses 'default.css' stylesheet for CSS_MAIN. (Place your CSS into /css directory)
  define('CSS_MAIN', 'default.css' );
  define('CSS_CALENDAR', 'calendar.css' );
  define('CSS_PRINT', 'print.css' );

  //custom image to replace the webcollab banner on splash page (base directory is [webcollab]/images)
  define('SITE_IMG', "webcollab.png" );

  //number of days that new or updated tasks should be highlighted as 'New' or 'Updated'
  define('NEW_TIME', 14 );

//CALENDAR CONTROLS

  //Start day of week on calendar (Sun = 0, Mon = 1, Tue = 2, Wed = 3, etc)
  define('START_DAY', 1 );

  //Use VEVENT for iCalendar instead of VTODO - works for Google Calendar and others (values are "N", or "Y")
  define('VEVENT', "N");

//RSS

  //enable autodiscovery of rss feeds by web browser
  define('RSS_AUTODISCOVERY', 'N' );

//LOGIN CONTROLS

  //session timeout in hours
  define('SESSION_TIMEOUT', 1 );

  //security token timeout for forms (in minutes)
  define('TOKEN_TIMEOUT', 60 );

  //Show passwords in user edit screens as plain text or hidden ('****') (values are "text", or "password")
  define('PASS_STYLE', "text" );

  //Stop GUEST users from changing their login details or posting in the forums (values are "N", or "Y")
  define('GUEST_LOCKED', "N" );

//LOGIN AUTHENTICATION

  //Use external webserver authorisation to login (values are "N", or "Y")
  define('WEB_AUTH', "N" );

  //Use Active Directory to authenticate (values are "N", or "Y")
  define('ACTIVE_DIRECTORY', 'N' );

  //address and port of Active Directory server
  $AD_HOST = "ldap://10.0.0.7/";
  define('AD_PORT', 389 );

//ERROR DEBUGGER

  //If an error occurs, who do you want the error to be mailed to ?
  define('EMAIL_ERROR', "" );

  //show full debugging messages on the screen when errors occur (values are "N", or "Y")
  define('DEBUG', "N" );
  
  //uncomment the next line to show all errors for development debugging
  //error_reporting(-1 );

  //Don't show full error message on the screen - just a 'sorry, try again' message (values are "N", or "Y")
  define('NO_ERROR', "N" );

//DATABASE

  //Use to set a prefix to the database table names (Note: Table names in /db directory will need be manually changed to match)
  define('PRE', "" );

//OUTPUT COMPRESSION

  //Use to enable zlib output compression of web pages (values are "N", or "Y")
  define('COMPRESS_OUTPUT', 'N' );

// LEGACY FILE UPLOADS

  //Character set hack for older files stored with pre-WebCollab 3.00 that have been upgraded (usually 'ISO-8859-1') 
  define('FILENAME_CHAR_SET', 'ISO-8859-1' );

//WEBCOLLAB VERSION

  //version info
  define('WEBCOLLAB_VERSION', "3.50" );

?>