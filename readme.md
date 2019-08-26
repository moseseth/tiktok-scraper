### TikTok Scraper™

The following project implements domcrawler and concurrent data retrival from tiktok website. Once promises are 
resolved, parses user and video related information and stores it to database for later consumption. 

Inorder to mitigate scraping process, all database related operations run in the background as jobs.

##### Installation:

```
cp .env.example .env // update DB_USERNAME & DB_PASSWORD & CREATE 'tiktok' database

composer install

php artisan migrate

php artisan serve

php artisan queue:work --tries=3
``` 

##### Run Test:
```$xslt
composer run-script test
```

##### Usage Example:
  * http://localhost:8000/api/users?id=@wilczewska,@realmadrid
  * http://localhost:8000/api/users/@wilczewska/videos?id=6727979845919214853,6722754487129246982

Glossary : `#PHP 7.2, #MySQL, #Queue, #Guzzle 6.0, #Lumen`
