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
all you have to do is to plug and play,
The available ENV is the following:

```text
DB_HOST=discount-bandit-mysql
APP_PORT=80
APP_URL=http://localhost:80
APP_ENV=production
APP_DEBUG=false

DB_DATABASE=discount-bandit
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=password
MYSQL_ROOT_PASSWORD=password

NTFY_LINK="NTFY_LINK"
NTFY_USER : ""
NTFY_PASSWORD : ""
NTFY_TOKEN : ""
DEFAULT_USER=Test
DEFAULT_EMAIL=docker@test.com
DEAFULT_PASSWORD=password

```

## Connect
if you are coming outside github or don't like to use it, feel free to join my discord.
https://discord.gg/VBMHvH8tuR

## Sponsors
![Jetbrains](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)
