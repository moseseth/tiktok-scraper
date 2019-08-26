#### TikTok Scraper

The following project implements domcrawler and concurrent data retrival from tiktok website. Once promises are 
resolved, parses user and video related information and stores it to database for later consumption. 

Inorder to mitigate scraping process, all database related operations run in the background as jobs.
```
copy .env.example .env | _update it to your local setup_

composer install

php artisan migrate

php artisan serve

php artisan queue:work --tries=3
``` 

##### Unit Test:
```$xslt
composer run-script test
```

##### Usage:
  * _api/users?id=@manchester,@liverpool,@arsenal_
  * _api/users/@liverpool/videos?id=3245673,8937482,323453_

Glossary : `#PHP 7.2, #MySQL, #Queue, #Guzzle 6.0, #Lumen`
