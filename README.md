![FreePalestine](./extra/palestine.png)


# Discount Bandit
Discount bandit is a price tracker across multiple stores, where you can track the price across all of them.

you can also get notified when an item reaches the price you desire, so you don't have to check each store everyday.

# NOTES:
### the previous database will not work with the new one.

i wasn't planning to make the project as this big tbh, so i had to redesign the database and
make it more systematic, thus the previous database will not work, and you have to insert the
products manually sadly

### will i provide the code to migrate when the app is stable?
yes, so far i am planning to provide it as an automated step. but since this is a beta release, i want to test for errors and bugs first.


## Deployment
this is a website, so if you're beginner you need to install some tools first:
you will need the following:
- php (https://www.php.net/)
- composer (https://getcomposer.org/)
- apache or nginx (https://httpd.apache.org/ , https://www.nginx.com/)
- mysql (https://www.mysql.com/)

or you can install something like MAMP instead of installing each one alone. (https://www.mamp.info/en/windows/)


one of the issues of the last software is how many steps are needed to set it up.
 so now i have reduced it.

first, you need to install the packages with composer
```bash
composer install 
```

then run the following and follow the prompts
```text
php artisan discount:install
```
to make it easier for everyone, whether you are self hosting or just want to use the software, i have found that  
https://ntfy.sh is the best solution. you can install their app from google play / appstore
and can setup the notification very easily.

# Updating
if you updating the system, please run the following command 

```text
php artisan discount:update
```
# CronJob
the previous command will give the output if whether you want to run app in the terminal or as cron for windows, mac and linux.

# Updates
if you want to update the code, after installing the new version run the following
```text
php artisan discount:update
```
## Connect
if you are coming outside github or don't like to use it, feel free to join my discord.
https://discord.gg/VBMHvH8tuR

## Docker
there is no docker image for beta, since i might need the flexibility to push updates or fix bugs.


## Sponsors
![Jetbrains](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)
