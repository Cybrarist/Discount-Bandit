![FreePalestine](./extra/palestine.png)

# Discount Bandit
Discount Bandit is a price tracker across multiple shopping websites simultaneously, and get notified when an item reaches the price you desire, so you don't have to keep checking shop stores for price changes.

# Documentation
Feel free to access the up-to-date documentation from [Here](https://discount-bandit.cybrarist.com)

## Deployment
Discount Bandit uses a web interface, so you need to install some dependencies first:
- [php](https://www.php.net)
- [composer](https://getcomposer.org)
- apache or nginx (https://httpd.apache.org , https://www.nginx.com)
- [mysql](https://www.mysql.com)
- sqlite (Default Option)

or you can install something like [MAMP](https://www.mamp.info/en/windows/) instead of installing each one alone. 


first, you need to install the packages with composer
```bash
composer install 
```

then run the following and follow the prompts
```text
php artisan discount:install
```
To make it easier for everyone, whether you are self hosting or just want to use the software, i have found that 
https://ntfy.sh is the best solution. you can install their app from google play / appstore
and can setup the notification very easily.

You can also setup telegram notifications.

# Updating
If you updating the system, please run the following command 

```text
php artisan discount:update
```
# CronJob
The previous command will give the output if whether you want to run app in the terminal or as cron for windows, mac and linux.

# Updates
If you want to update the code, after installing the new version run the following
```text
php artisan discount:update
```
## Connect
If you are coming outside github or don't like to use it, feel free to join [Discord](https://discord.gg/VBMHvH8tuR).


## Docker
Please check the docker repos to pull the image you prefer [Docker](https://hub.docker.com/r/cybrarist/discount-bandit)

# docker-compose

If you run docker-compose before creating a .env file, it will have created a '.env'.
To fix it, just delete the .env folder and make sure you create your own .env file before running:
```
rm -rf .env
cp .env.example .env
```

Don't forget to also populate the .env file with your own APP_KEY (a random 32 characters string).

## Sponsors
![Jetbrains](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)
