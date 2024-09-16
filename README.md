
# A simple PHP + Sqlite based directory listing for TOR Sites.

## How to use

If u have hosted any php site with sqlite database, you should be good to go.<br>
The entry point is simply `index.php`. 

## Config

- `TITLE` : The `<title>` of your site.

- `DB_FILE` : Path to sqlite database file.[If just filename given, the DB will be created in root directory.]
   > [!NOTE] <br>
   >The DataBase file should be readable & writable for the web server user.

- `ADMIN_USERNAME` : Admin username for admin access.

- `ADMIN_PASSWORD` : Admin password for admin access.

- `ADMIN_SECRET_KEY` : Secret key for admin access.<br>
    > [!NOTE] <br>
    > - To access the admin panel use : https://oniondomain.onion/index.php?action=admin_login&key=ADMIN_SECRET_KEY<br>
    > - You **CANNOT** get to the admin login page without this secrety key. You will
    > need the `ADMIN_SECRET_KEY` key to access the admin panel and then `ADMIN_PASSWORD` to
    > login.<br>
    > - This is by design to prevent dumb brute force attacks againt the login page.

- `DEFAULT_CATEGORIES` : Default pre built categories to select in drop down.<br>
    > Additional Categories can be made on the fly while adding a site with UI

## Contributors

The porject is in very early stage and has a shit ton of scope to improve so ALL
Contributions are highly welcome in the form of PRs, issues or mails :)<br>
See [TODO](./TODO.md)



