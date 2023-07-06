
# Discount Bandit

I've noticed there are many price trackers, but they are either paid with missing data or outdated solutions, so I decided to build my own for amazon ( so far ) which I use the most. 

Right now I have tested it for the couple stores that are saved, but I will probably test it for other stores for the future, with an update mechanism to update the crawlers.

It's still a simple tracker, so errors can be found so don't expect it to compete  with the best websites out there, but I am planning to support it even more since I have a personal usage for it.


P.S: 

please note that the services have referral code, this is how you support me to continue developing the project.

no data is shared with anyone, I would appreciate to keep it, but you can remove it from database if you don't want to.


## Deployment

Copy .env.example to .env file
```bash
cp .env.example .env
```

Change the database credentials in  .env file
```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

add your email to send notification from it
```text
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=yournewemail@gmail
MAIL_PASSWORD="VeryComplicatedPassword"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="yournewemail@gmail"
MAIL_FROM_NAME="${APP_NAME}"
```

Run Composer install
```bash
composer install 
```


Link the storage to use the images
```bash
php artisan storage:link 
```




Migrate the data with the seed
```bash
php artisan migrate:fresh --seed --no-interaction
```

Generate an app key
```bash
php artisan key:generate
```

The server should be up and running with the following credentials
```text
email : test@test.com
password: password
```

you need to Change the email once you log in if you want to receive emails.

The app will check for prices for single products every 5 minutes, and for the group list every 15 minutes. 

pls refer to this for how to set up your cron setup depending on your need.
[https://laravel.com/docs/10.x/scheduling#running-the-scheduler]

then you need to add the following command in your cron to run every second.
```text
* * * * *  /path/to/php /path/to/app/artisan queue:work --stop-when-empty >> /dev/null 2>&1 
```

And that should be enough for the app to run.

After you're done and everything is running fine, go to your .env file and change the following:
```text
APP_ENV=production
```

I have added a htaccess file in case you need it, so don't forget to rename them to .htaccess if you want to use it

## Features

- Check Price or products across multiple Amazon Stores
- Notify Via Email if the price reach the desired amount
- Create Group of products with set of prices


## Missing
- Clothes and sizes are not working, but im working on a fix.

## Tech Stack

**Server:** Laravel , FilamentPHP. 



## Support

Feel free to open an issue, but please provide the product link along with the service caused the issue.

I might request the log file in case I couldn't detect the problem.
