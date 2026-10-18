**POSSystem: Hostinger Deployment Guide**

Prepared on 12 October 2026 for Hostinger Web or Cloud hosting with hPanel.

This guide covers deploying the existing POSSystem application and database. The route and Blade cache errors were fixed and both cache commands passed locally. Server deployment and browser testing are still required. This document does not make changes to your Hostinger account.

For inventory Excel exports returning HTTP 404, see the [verified route investigation and targeted recovery steps](docs/inventory-export-production-404.md). The missing production routes must be restored in the active application and its route cache rebuilt.

For Report Management's generic Excel export failure, see the [report library-loading fix and deployment checks](docs/report-export-production.md). These report routes resolve on production; three report pages were missing their Excel library includes.

Replace `example.com`, `u12345678`, database credentials, and paths with your actual values. Unless a step says **on your Mac**, run terminal commands **over SSH on Hostinger**. Stop when a command fails and resolve that error before continuing.

1. **Prepare the Hostinger website.**

   Open **Websites → Dashboard** for your domain. Connect the domain, enable SSL, and make sure HTTPS works before testing login.

   Open **PHP Configuration**. The current application was checked locally with PHP 8.2. Select PHP 8.2 if your account offers it; a newer version requires compatibility testing with the existing dependencies. Check that these extensions are available: `pdo_mysql`, `mbstring`, `curl`, `fileinfo`, `gd`, `dom`, `xml`, `simplexml`, `xmlreader`, `xmlwriter`, `zip`, `openssl`, and `sodium`. Composer will check the full dependency requirements later.

   Open **SSH Access**, enable it, and copy the SSH connection command shown by Hostinger into your Mac Terminal. SSH is available on eligible plans starting with Premium Web hosting. See Hostinger's [PHP configuration instructions](https://www.hostinger.com/support/4667515-how-to-manage-php-extensions-and-options-in-hostinger/) and [SSH instructions](https://www.hostinger.com/support/1583645-how-to-enable-ssh-access-in-hostinger/).

   This project uses Laravel 9, whose security support ended on 6 February 2024. Plan a supported-framework upgrade before using it for public production traffic. PHP 8.2 security support ends on 31 December 2026. See the [Laravel support policy](https://laravel.com/docs/9.x/releases#support-policy) and [PHP support schedule](https://www.php.net/supported-versions.php).

2. **Prepare the application and backups on your Mac.**

   Back up the project, database, and uploaded files. Export the working database as SQL with structure, data, and the `migrations` table. Pause local data entry while taking the final export if this is a live business database.

   Include the changes already made in these files:

   - `routes/admin.php`
   - `resources/admin/views/components/r2form/example.blade.php`
   - `resources/website/views/components/r2form/example.blade.php`

   Before launch, remove the two cache-clearing route definitions at the bottom of `routes/admin.php`: `clear-cache` and `clear-cache-s`. These URLs currently clear application caches without requiring login. Use SSH to manage caches instead. This guide has not removed them for you.

   From the project folder on your Mac, build production assets:

   ```bash
   npm ci
   npm run production
   ```

   Keep the generated public assets and `public/mix-manifest.json`. Build assets on your Mac and upload the output; this avoids needing Node.js on the hosting account. The build expects Composer dependencies, including TinyMCE, to exist in your local `vendor` directory.

3. **Upload the files in the correct layout.**

   Hostinger Web and Cloud hosting use `public_html` as the website root, which cannot be changed through hPanel. The layout below keeps application files outside the public directory. See [Hostinger's document-root instructions](https://www.hostinger.com/support/1583494-what-is-the-path-to-your-website-s-root-home-directory-and-how-to-change-it-in-hostinger/).

   ```text
   /home/u12345678/domains/example.com/
   ├── POSSystem/
   │   ├── app/
   │   ├── bootstrap/
   │   ├── config/
   │   ├── database/
   │   ├── lang/
   │   ├── resources/
   │   ├── routes/
   │   ├── storage/
   │   ├── artisan
   │   ├── composer.json
   │   ├── composer.lock
   │   └── .env
   └── public_html/
       ├── .htaccess
       ├── index.php
       ├── mix-manifest.json
       ├── admin-public/
       ├── website/
       ├── file_manager/
       └── uploads/
   ```

   Upload the application into `POSSystem`. Copy **all contents of the project's `public` folder** into `public_html`, including hidden files such as `.htaccess`. The tree above shows the main entries; include the remaining public files too. Back up any existing website before replacing its files.

   Include `public/file_manager`, `public/uploads`, and any required files from `storage/app`. If the Passport API is in use, securely preserve its existing OAuth keys in `storage` along with the matching database records.

   Exclude `node_modules`, `.git`, the Mac's `vendor` directory, `public/hot`, and generated caches, sessions, maintenance files, and logs under `bootstrap/cache`, `storage/framework`, and `storage/logs`. Keep their directories and `.gitignore` files. Transfer the production `.env` privately in step 5.

   Keep project ZIP files, SQL backups, and `.env` outside `public_html`. Uploading an old archive will not include the latest fixes or asset build.

4. **Adjust the server's application paths.**

   In the uploaded `public_html/index.php`, replace the existing maintenance-path block with:

   ```php
   if (file_exists($maintenance = __DIR__.'/../POSSystem/storage/framework/maintenance.php')) {
       require $maintenance;
   }
   ```

   Replace the autoloader reference with:

   ```php
   require __DIR__.'/../POSSystem/vendor/autoload.php';
   ```

   Replace the application bootstrap reference with:

   ```php
   $app = require_once __DIR__.'/../POSSystem/bootstrap/app.php';
   ```

   Leave the remaining `index.php` code intact.

   In the **server copy** of `POSSystem/bootstrap/app.php`, insert the following immediately before `return $app;`:

   ```php
   $app->instance('path.public', dirname(__DIR__, 2).'/public_html');
   ```

   This binding is appropriate for this project's installed Laravel version. It makes `public_path()` resolve to `public_html` for both web requests and Artisan. Uploads, file-manager files, storage links, and the Mix manifest depend on it. Preserve these server-specific changes during later uploads.

5. **Configure the production environment.**

   Privately copy the existing `.env` into `POSSystem/.env`, then edit its settings for Hostinger. The repository currently has no `.env.example` file.

   ```dotenv
   APP_NAME="POS System"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://example.com

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=u12345678_pos
   DB_USERNAME=u12345678_posuser
   DB_PASSWORD="YOUR_DATABASE_PASSWORD"

   CACHE_DRIVER=file
   SESSION_DRIVER=file
   SESSION_SECURE_COOKIE=true
   QUEUE_CONNECTION=sync
   FILESYSTEM_DISK=local
   LOG_LEVEL=error
   ```

   Use Hostinger's actual database host if it differs from `localhost`. Enter the complete database and user names, including Hostinger's prefixes. Update SMTP and other service settings for the features you use.

   **Preserve the existing `APP_KEY` when transferring this application and its data.** The example above intentionally omits the secret key. Generate a key only for a completely new installation with no existing key; do not rotate it during routine deployments.

   Remove local path overrides such as `VIEW_COMPILED_PATH=/Users/...`. Remove or correct any `SESSION_DOMAIN` referring to localhost or an old domain. With secure cookies enabled, access the application over HTTPS.

6. **Create and import the Hostinger database.**

   In the website dashboard, open the database management page, create a database and user, and import your SQL export through phpMyAdmin. Use the same credentials in `.env`. See [Hostinger's database instructions](https://www.hostinger.com/support/1583542-how-to-create-a-new-mysql-database-in-hostinger/).

   Confirm the import includes users, permissions, settings, business data, and the `migrations` table.

   **Do not run `migrate:fresh`, `migrate:refresh`, or `db:seed` on the imported business database.** The current `UserSeeder` truncates the users table, and the default seeder loads sample records. A new empty database needs a separately verified migration and initial-admin setup.

7. **Prepare writable directories before installing dependencies.**

   Connect through SSH and enter the application directory:

   ```bash
   cd /home/u12345678/domains/example.com/POSSystem
   ```

   Create the runtime directories and allow the hosting account owner to write to them:

   ```bash
   mkdir -p storage/framework/cache/data \
     storage/framework/sessions \
     storage/framework/views \
     storage/logs \
     bootstrap/cache \
     ../public_html/uploads \
     ../public_html/file_manager

   chmod -R u+rwX storage bootstrap/cache \
     ../public_html/uploads ../public_html/file_manager

   chmod 600 .env
   ```

   The website's PHP process must be able to read `.env` and write to the runtime and upload directories. If Hostinger reports a permission problem, check ownership and access with its support team. Do not set the entire application to `777`.

8. **Install the locked PHP dependencies.**

   From `POSSystem`, check the CLI tools:

   ```bash
   php -v
   composer2 --version
   ```

   Confirm CLI PHP matches the website's selected PHP version. If they differ, use the matching PHP executable provided by Hostinger for Composer and Artisan; changing hPanel's website version alone may not change CLI PHP.

   Install dependencies and check platform compatibility:

   ```bash
   composer2 install --no-dev --prefer-dist --optimize-autoloader
   composer2 check-platform-reqs --no-dev
   ```

   Resolve errors before proceeding. Keep `composer.lock`; use `install` for deployment, and reserve `update` for tested dependency changes. Do not bypass missing PHP extensions with `--ignore-platform-reqs`.

   Hostinger documents its [Composer 2 command](https://www.hostinger.com/support/5792078-how-to-use-composer-at-hostinger/). Composer's [platform check](https://getcomposer.org/doc/03-cli.md#check-platform-reqs) verifies the real PHP and extension requirements of installed packages.

9. **Check the database and build caches on Hostinger.**

   From `POSSystem`, run these commands individually and check each result:

   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan migrate:status
   ```

   If migrations are pending, review them, test them against a copy of the imported database, and take a current backup before applying them:

   ```bash
   php artisan migrate --force
   ```

   If the application uses files in `storage/app/public`, create the public storage link:

   ```bash
   php artisan storage:link
   ```

   Hostinger may require enabling the `symlink` PHP function first; see its [symlink instructions](https://www.hostinger.com/support/1583694-how-to-create-a-symlink-at-hostinger/). This link does not replace copying your existing `uploads` and `file_manager` directories.

   After the environment and database checks succeed, build the caches:

   ```bash
   php artisan config:cache &&
   php artisan route:cache &&
   php artisan view:cache
   ```

   Expect success messages for configuration, routes, and Blade templates. The `&&` operators stop the sequence if any command fails. Generate these caches on Hostinger after setting `.env`; do not upload cached configuration from your Mac. See [Laravel's deployment guidance](https://laravel.com/docs/9.x/deployment#optimization).

10. **Verify the live application.**

    Open `https://example.com/admin/auth` and check:

    - Login, logout, and the dashboard.
    - Product and customer selection.
    - Creating and viewing an order.
    - Reports, date filtering, exports, and invoice printing.
    - Uploading and displaying an image.
    - Email features, if configured.

    The project currently has no route for `/`; a 404 at the bare domain does not by itself mean deployment failed. Use the admin login URL above.

    Change any sample administrator passwords before launch. Keep full-page caching disabled for authenticated POS pages such as `/admin/*`. Laravel's compiled caches and Hostinger's page/CDN caches serve different purposes; see [Hostinger's cache guidance](https://www.hostinger.com/support/hostinger-cdn-how-to-clear-the-cache/).

    | Problem | What to check |
    |---|---|
    | HTTP 500 | Run `tail -n 100 storage/logs/laravel.log` from `POSSystem`. If Laravel cannot log, check hPanel's PHP error logs and file permissions. |
    | Database connection error | Verify database host, full database/user names, password, and user access. Rebuild configuration cache after changing `.env`. |
    | CSS or JavaScript missing | Check assets, `public_html/mix-manifest.json`, the `path.public` binding, and absence of a `hot` file. |
    | 404 at the admin login URL | Check `public_html/.htaccess`, the `index.php` paths, and deployed route files. |
    | Login error 419 | Check HTTPS, session directory permissions, cookie domain, and old browser cookies. |
    | Upload fails or images disappear | Check the public-path binding, upload-directory permissions, copied files, and PHP upload limits. |
    | Duplicate route name | Confirm the fixed `routes/admin.php` was uploaded; clear and rebuild route cache. |
    | Missing `r2input` component | Confirm both fixed example Blade files were uploaded; clear and rebuild view cache. |
    | Serialized closure was modified or is unsafe to unserialize | Follow step 13 to remove stale generated route/configuration caches and rebuild them using the server's application key. |
    | Permission denied creating a `/Users/.../storage/logs` directory | Follow step 14; a Mac filesystem path was carried into the deployment, commonly by copied configuration cache. |
    | Old page or assets remain | Rebuild the relevant Laravel cache, purge Hostinger/CDN cache, and refresh browser cache. |

    Keep `APP_DEBUG=false` on the public website. Use logs for diagnostics, and remove credentials or customer data before sharing log excerpts.

11. **Enable scheduled features only if you use them.**

    With `QUEUE_CONNECTION=sync`, queued Laravel jobs execute during the request and do not need a separate queue worker.

    This repository also schedules website job-application emails and cleanup of completed application attachments. If those features are intended for this deployment, configure a custom cron job in hPanel to run every minute:

    ```bash
    cd /home/u12345678/domains/example.com/POSSystem && /usr/bin/php artisan schedule:run >> storage/logs/scheduler.log 2>&1
    ```

    Use the actual matching PHP executable on your account if it differs from `/usr/bin/php`. Confirm SMTP settings and pending application records before enabling it, because scheduled jobs send emails and delete completed application files. See [Hostinger's cron instructions](https://www.hostinger.com/support/1583465-how-to-set-up-a-cron-job-at-hostinger/).

12. **Use the same order for future updates.**

    Back up the live database and uploads, then run `php artisan down` before replacing an existing deployment in place. Upload the tested application and built public assets. Preserve the production `.env`, uploaded files, storage data, and server-specific path changes from step 4.

    Clear configuration, route, and view caches; install locked dependencies; review and apply any pending migrations; then rebuild the three caches. Run `php artisan up` after all deployment commands succeed and repeat the browser checks.

    After an `.env` change, rebuild configuration cache. After a route change, rebuild route cache. After a Blade change, rebuild view cache. Purge Hostinger/CDN caches when updated content or assets are still being served from them.

    If deployment fails, retain the logs and keep maintenance mode active while fixing it or restoring the previous release. Database changes may require a separate recovery plan; restoring application files alone does not undo a migration.

13. **Recover from a serialized-closure signature error.**

    The message `Your serialized closure might have been modified or it's unsafe to be unserialized.` means Laravel rejected a serialized closure's signature. In this deployment, a likely cause is a route cache generated with a different `APP_KEY`, such as a cache uploaded from the Mac or retained after changing the server key. Laravel's [signature verification code](https://github.com/laravel/serializable-closure/blob/1.x/src/Serializers/Signed.php) throws this error when verification fails.

    In Hostinger File Manager, open `POSSystem/bootstrap/cache` and delete only these generated files if present:

    ```text
    routes-v7.php
    config.php
    ```

    Keep the directory and the other files. Do not delete `config/app.php`, `.env`, application source files, or database data. The names above are the default cache filenames used by this project; custom `APP_ROUTES_CACHE` or `APP_CONFIG_CACHE` environment overrides require clearing their configured locations instead.

    Confirm `POSSystem/.env` contains the intended existing `APP_KEY`. Do not run `key:generate` or disable signature verification to fix this error. If the key was accidentally replaced while transferring an existing application, restore the intended original key from your private backup before rebuilding caches.

    Reload `https://example.com/admin/auth`. It can run without route or configuration caching. Use File Manager for this recovery rather than the `/admin/clear-cache-s` URL, since the failing cached route may prevent that URL from executing.

    Once the login page works, rebuild caches through SSH from the server's `POSSystem` folder:

    ```bash
    php artisan config:cache &&
    php artisan route:cache &&
    php artisan view:cache
    ```

    Keep the same `APP_KEY` for CLI and web requests. Do not upload local generated caches again. If the same error returns after rebuilding, check for a different server-level key override between CLI and web PHP, and collect the new exception with its stack trace before making further changes.

14. **Recover when Hostinger tries to write to a Mac path.**

    If the error names `/Users/longdy/Documents/Projects/Laravel/POSSystem/storage/logs`, Hostinger is using a local development path. Inspection of the local `Archive.zip` on 12 October 2026 found `bootstrap/cache/config.php` containing that Mac project path, plus `bootstrap/cache/routes-v7.php`. Deploying that archive without removing these generated files can cause both the log-directory error and the serialized-closure error.

    Use Hostinger File Manager to open the active application's `bootstrap/cache` directory and delete `config.php` and `routes-v7.php` if present. Use the application root that is actually loaded by the website's `index.php`; it contains `artisan`, `bootstrap`, and `storage`. Delete the generated configuration cache before running Artisan, because stale logging paths may interfere with error reporting from commands too.

    Keep `.env` and the original `APP_KEY`. Check the server's environment settings for path overrides such as `APP_BASE_PATH` or `VIEW_COMPILED_PATH` containing `/Users/...`; remove those local overrides if present. Do not create the Mac directory on Hostinger or grant broader permissions to it.

    From the active application root over SSH, ensure the server's runtime directories exist and are writable by its PHP user:

    ```bash
    mkdir -p storage/logs storage/framework/cache/data \
      storage/framework/sessions storage/framework/views bootstrap/cache
    chmod -R u+rwX storage bootstrap/cache
    php artisan view:clear
    ```

    Reload the admin login page while configuration and route caching are still disabled. Once it works, rebuild the caches on Hostinger with the commands in step 9. Laravel [configuration caching](https://laravel.com/docs/9.x/configuration#configuration-caching) stores resolved settings, so deployment must use server-generated configuration rather than a copy from the Mac.

    Exclude `bootstrap/cache/*.php` and generated runtime files when preparing the next upload. Reuploading the same unfiltered archive would restore the stale caches.
