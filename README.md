## Add Calendar Ticket

Develop a system to enrich the calendar meetings of a sales rep with information that could be useful to prepare for their meetings and then send the information in the morning by email.

### How to run

-   Install PHP & Laravel's packages with `composer install`
-   Create a MySQL database and add that DB name on the env file
-   Run this command to migrate the tables and seed some data `php artisan migrate:fresh --seed`

### How to test

In order to test the functionality of the application, I have created an additional command just for testing purposes.

This command will simulate as if the date was `2022-07-01 06:00:00` and it will fetch the events of that date for the users in our database.

`php artisan test:emails`

After running this command the email json files will be saved in this path: `/storage/app/emails/`.

### Available Commands

-   `php artisan schedule:emails`
-   `php artisan send:emails`
-   `php artisan test:emails`

### How to run Unit tests

In order to run some unit tests created for this project please run this command: `php artisan test`.
