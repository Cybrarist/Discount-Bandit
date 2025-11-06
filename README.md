![FreePalestine](./extra/palestine.png)

# Discount Bandit
Discount Bandit is a price tracker for your favorite products across multiple stores.
It has a notification system built in, so you can be notified when the price drops or matches you criteria specified.

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

then compy your `.env.example` file to `.env` and fill the environment variables needed from the following (link)[https://discount-bandit.cybrarist.com/installation/environments].

## Connect
If you are coming outside github or don't like to use it, feel free to join [Discord](https://discord.gg/VBMHvH8tuR).


## Docker
Please check the docker repos to pull the image you prefer [Docker](https://hub.docker.com/r/cybrarist/discount-bandit)
