# Deploying The Strengths Toolbox to cPanel (MWeb) — No SSH

This guide covers deploying the Laravel 12 application to cPanel 134.0.9 on MWeb using only the cPanel web interface (no SSH).

---

## 1. Requirements

- **PHP 8.2 or higher** (app uses `php: ^8.2` in `composer.json`)
- **MySQL** (or MariaDB) database
- **Composer** and **Node/npm** run **on your local machine** to produce a deployable package

Your MultiPHP Manager shows **PHP 8.4** for `cm-solution.co.za`, which is suitable. For any other domain (e.g. `demo.cm-solution.co.za`), set its PHP version to **PHP 8.2** or **8.4** in **MultiPHP Manager** (select domain → PHP Version → Apply).

---

## 2. Build the application locally

On your computer, in the project root:

```bash
# Install production dependencies (no dev packages)
composer install --no-dev --optimize-autoloader

# Build frontend assets (Vite)
npm ci
npm run build
```

This gives you:

- `vendor/` (production Composer packages)
- `public/build/` (compiled JS/CSS)

Do **not** upload `node_modules/`. Do upload `vendor/` and `public/build/`.

---

## 3. Create the deployment package

Upload everything **except**:

- `.env` (you will create this in cPanel)
- `node_modules/`
- `.git/`
- `storage/logs/*`, `storage/framework/cache/*`, `storage/framework/sessions/*`, `storage/framework/views/*` (optional to exclude; they can be recreated)
- Any local/dev-only files (e.g. `.env.local`, `.phpunit.result.cache`)

**Include** (all of these must be on the server):

- All app code: `app/`, `bootstrap/`, `config/`, `database/`, `public/`, **`resources/`** (including **`resources/views/`** and all subfolders, e.g. `resources/views/pages/`), `routes/`
- `vendor/` (from step 2)
- `public/build/` (from step 2)
- `composer.json`, `composer.lock`
- `.env.example` (as reference; do not use as-is in production)

If you see **"View [pages.home] not found"** (or any view name), the server cannot find the Blade templates: ensure the full **`resources/views/`** directory was uploaded to the **app root** (the folder that contains `app/`, `config/`, `vendor/`). With root deployment, that is the parent of `public_html/`.

Create a ZIP of the project (excluding the above) and upload via **cPanel → File Manager**, or use **cPanel → Git™ Version Control** if you use Git and can run Composer/npm via a deploy script or another method.

---

## 4. Where to put the files

Typical setups:

- **Option A – Subdomain or addon domain**  
  Example: `demo.cm-solution.co.za` → document root something like `public_html/demo` or `demo.cm-solution.co.za`. Put the Laravel project **inside** that folder so that the **document root** can point to the `public` subfolder (see step 5).

- **Option B – Main domain in subfolder**  
  Put the project in e.g. `public_html/strengths-toolbox`. Document root will point to `public_html/strengths-toolbox/public`.

Do **not** put the Laravel app directly in `public_html` unless the document root is set to `public_html/public` (see next step).

---

## 4a. Testing on GoDaddy (or cPanel) without a domain yet

If you have GoDaddy (or other cPanel) hosting but no domain pointed yet, you can still test the site using a **temporary URL**.

### Find your temporary URL

1. Log in to **cPanel**.
2. Check **General Information** (right or left sidebar) for a **“Temporary URL”** or **“Shared IP Address”** and your **cPanel username**.
3. Typical formats:
   - **GoDaddy:** `https://YOUR-SERVER.prod.iad2.secureserver.net/~cpanel_username`
   - **Other cPanel:** `https://server-hostname/~cpanel_username` or `http://server-ip/~cpanel_username`

If you don’t see it, open **Domains** → your domain (or the default one) and look for a temporary or staging URL. The server hostname often appears in the cPanel login URL or in **Metrics** → **Errors** (domain dropdown).

### Deploy so the temp URL serves the app

The temporary URL usually serves from your **`public_html`** directory and you often **cannot** change its document root. Two options:

- **Option 1 – Temp URL = main site (recommended for “no domain” testing)**  
  Use the same layout as **GoDaddy root deployment**: put **only the contents of Laravel’s `public/`** inside `public_html/` (e.g. `index.php`, `front.php`, `.htaccess`, `build/`, etc.), and put the rest of the Laravel app (e.g. `app/`, `config/`, `vendor/`, `.env`) in the **home directory** (one level above `public_html`). Then the temp URL (e.g. `https://server/~user/`) will serve the app.  
  In **`public_html/index.php`** and **`public_html/front.php`** (if you use it), ensure paths point to the parent directory (e.g. `__DIR__.'/../vendor/autoload.php'`, `__DIR__.'/../bootstrap/app.php'`).

- **Option 2 – Subfolder**  
  Put the full Laravel project in e.g. `public_html/strengths-toolbox` with document root set to `public_html/strengths-toolbox/public` **for that domain**. The temp URL often cannot be pointed at a subfolder; if your host allows **“Domains” → document root** for the temp URL, set it to `public_html/strengths-toolbox/public`. Otherwise use Option 1.

### Set APP_URL to the temporary URL

In **`.env`** (in the Laravel root, not inside `public` or `public_html`):

```ini
APP_URL=https://YOUR-SERVER.prod.iad2.secureserver.net/~cpanel_username
```

Use **https** if the temp URL is served over HTTPS (no trailing slash). This keeps sitemaps, schema, and links correct while testing.

### Run migrations and seed

Use the same steps as in **sections 10–11** (Terminal or one-time script), but open the script or site using the **temporary URL** (e.g. `https://server/~user/run-migrate.php`). Remove the script and `RUN_DEPLOY` after use.

### When your domain is ready

1. Point the domain to the host (A record or CNAME as instructed by GoDaddy/cPanel).
2. In **Domains**, set the **document root** for the domain to your app’s `public` directory (same as in section 5).
3. In **`.env`**, set `APP_URL` to your real domain, e.g. `APP_URL=https://yourdomain.com`.
4. Clear config cache if you use it: e.g. `php artisan config:clear` (or re-run any deploy script that clears cache).
5. Test the site on the real domain; you can stop using the temporary URL.

---

## 5. Set the document root to `public`

Laravel’s entry point is `public/front.php` (see step 5). The web server must use the `public` directory as the document root.

In cPanel:

1. Go to **Domains** (or **Addon Domains** / **Subdomains**).
2. Find the domain/subdomain you are using.
3. Set **Document Root** to the `public` folder of your app, e.g.:
   - `public_html/demo/public`, or
   - `public_html/strengths-toolbox/public`

If your host uses **“Domain pointing to a directory”** or similar, point it to that same `public` path.

**Front controller:** This project’s `public/.htaccess` sends all requests (e.g. `/`, `/blog`, `/contact`) to **`front.php`**, not `index.php`. Some hosts block or mishandle `index.php`; using `front.php` avoids that so every route works. Ensure **`public/front.php`** is uploaded with the rest of the app.

---

## 6. Create the database

1. **cPanel → MySQL® Databases** (or **MySQL® Database Wizard**).
2. Create a new database (e.g. `f3303405_strengths`).
3. Create a database user with a strong password.
4. Add the user to the database with **ALL PRIVILEGES**.

Note the **database name**, **username**, and **password** (and host, usually `localhost`) for `.env`.

---

## 7. Create `.env` in cPanel

In **File Manager**, go to the **project root** (the folder that contains `app/`, `config/`, `public/`, etc., not inside `public`). Create a new file named `.env` (enable “Show Hidden Files” if needed).

Copy the contents from `.env.example` and set at least:

```ini
APP_NAME="The Strengths Toolbox"
APP_ENV=production
APP_KEY=                    # See below
APP_DEBUG=false
APP_URL=https://your-domain.co.za

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

**APP_KEY:** Generate a key locally and paste it:

```bash
php artisan key:generate --show
```

Or use an online Laravel key generator and set `APP_KEY=base64:...` in `.env`.

If you use the **chatbot**, add (with your own values):

```ini
OPENAI_API_KEY=your_key
# Or for custom AI: CHATBOT_AI_PROVIDER=custom, CUSTOM_AI_* variables
```

Adjust **MAIL_*** if you want real email (e.g. SMTP); otherwise `MAIL_MAILER=log` is fine for testing. On **GoDaddy**, the port 25 relay often times out — use **smtpout.secureserver.net** on **port 587** with **MAIL_SCHEME=tls** and your Workspace/Email address and password (see GoDaddy deployment guide).

---

## 8. Writable directories

Laravel needs to write to:

- `storage/` (all subdirs: `app/`, `framework/cache`, `framework/sessions`, `framework/views`, `logs`)
- `bootstrap/cache/`

In **File Manager**, select:

- `storage`
- `bootstrap/cache`

Use **Permissions** (or “Change Permissions”) and set to **755** or **775**. If 755 does not allow the web server to write, use **775**. Ensure the same writable structure exists under `storage/` (e.g. `storage/framework/sessions`, `storage/logs`). Create any missing subdirectories.

---

## 9. Storage link (document root `storage` → `storage/app/public`)

Homepage section images, blog media, and uploads are served at `/storage/...`. The web server must see a `storage` link in the **document root** that points to `storage/app/public`.

**With standard deployment** (document root = `public/`): the link is `public/storage` → `../storage/app/public`.

**With root deployment** (document root = `public_html/`): the link must be **`public_html/storage`** → `../storage/app/public`. If you created the link before pointing the app at `public_html`, it may have been created in `public/storage` (which is not served). Run `storage:link` again after deployment so it is created in the active public path, or create it manually as below.

**If cPanel has a web Terminal:**

```bash
cd /home/f3303405/your_project_path
php artisan storage:link
```

(Run this **after** the app is deployed so that the link is created in the correct public directory, e.g. `public_html/`.)

**If you have no terminal:**

- In **File Manager**, go to your **document root** (e.g. `public/` or `public_html/`).
- Create a **symbolic link** named `storage` pointing to `../storage/app/public` (path relative to that directory).
- Example for root deployment: inside `public_html/`, create link `storage` → `../storage/app/public`.

After deployment, ensure **`storage`** exists in the document root and points to `storage/app/public` so `/storage/...` URLs (and images) work.

---

## 10. Run migrations (no SSH)

You need to run migrations once (and after future deployments that include new migrations).

**Option A – cPanel Terminal**  
If **Terminal** is available in cPanel:

```bash
cd /home/f3303405/your_project_path
php artisan migrate --force
```

**Option B – One-time PHP script (then remove)**  
If there is no terminal but you can run PHP via the browser:

1. Use the provided `public/run-migrate.php` from the project (it requires `RUN_DEPLOY=1` in `.env` and writes to `public/run-migrate-debug.log` if something fails).
2. In `.env`, add `RUN_DEPLOY=1` temporarily.
3. Visit `https://your-domain.co.za/run-migrate.php` once.
4. **Delete `run-migrate.php`** and **run-migrate-debug.log** (if present), and remove `RUN_DEPLOY` from `.env` after.

Example script (also available as `public/run-migrate.php` in the project; it requires `RUN_DEPLOY=1` in `.env` to run):

```php
<?php
// One-time script: run migrations. DELETE THIS FILE after use.
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
echo 'Migrations completed. Delete this file.';
```

**Option C – Run migrations locally against production DB**  
Temporarily set `.env` (or a separate `.env.production`) with the cPanel MySQL host, database, user, and password, then run `php artisan migrate --force` on your machine. Only do this over a secure connection and remove credentials after.

### Run seeders (optional, for demo/fresh content)

To populate pages, blog posts, testimonials, etc., run the database seeders once after migrations.

- **cPanel Terminal:** `php artisan db:seed --force`
- **No SSH:** Upload **`public/do-seed.php`**, ensure `RUN_DEPLOY=1` is in `.env`, visit **`https://your-domain.co.za/do-seed.php`** once, then delete the file and remove `RUN_DEPLOY`.

For a **completely fresh** database (drop all tables and re-run migrations + seed), use **cPanel Terminal** if available: `php artisan migrate:fresh --seed`. Do not expose `migrate:fresh` via a web script (it drops all data).

### If run-migrate.php returns 500

The script writes a **debug log** to `public/run-migrate-debug.log` (and now writes **Step 0** on the very first line, before any other logic).

1. **Upload the latest `public/run-migrate.php`** from the project.
2. Run the script again, then open **`https://your-domain.co.za/run-migrate-debug.log`** in the browser (or open it in cPanel File Manager).
3. The log shows how far the script got (Step 0, 1, 2…) and the actual PHP error. Fix the reported issue, then delete the log and the script when done.

**If run-migrate-debug.log is still not created:**

- **Try the WAF-safe script:** Many hosts (including with Imunify360) block URLs containing `run` or `migrate`. Upload **`public/do-migrations.php`** (same logic, different name) and open **`https://your-domain.co.za/do-migrations.php`** (with `RUN_DEPLOY=1` in `.env`). If **run-migrate-debug.log** is then created, the block was on the filename; use **do-migrations.php** for migrations and delete it and the log when done.
- **Test PHP write:** Upload **`public/ping.php`** from the project and open `https://your-domain.co.za/ping.php`. If you see “ok” and a file **`public/ping.log`** is created, PHP in `public/` can write files. If run-migrate.php still gives 500 with no log, the request may not be reaching `run-migrate.php` (see next points). Delete `ping.php` and `ping.log` after.
- **Bypass OPcache:** Rename the script (e.g. to `run-migrate-xt.php`), add `RUN_DEPLOY=1` to `.env`, and open the new URL once. Some hosts cache old PHP and serve a broken version.
- **Check .htaccess:** In `public/`, ensure there is no rule that sends *all* requests to `index.php`. Laravel’s default only rewrites when the requested file does not exist (`RewriteCond %{REQUEST_FILENAME} !-f`), so `run-migrate.php` should run as PHP. If your `.htaccess` is different, adjust it or move the script to a subfolder that isn’t rewritten.
- **Server error log:** In **cPanel → Errors** (or **Metrics → Errors**), open the error log for the domain and look for the time you hit `run-migrate.php`. The log often shows the real PHP fatal/parse error (e.g. “syntax error” or “allowed memory size”).

---

## 11. PHP PEAR packages

This application uses **Composer**, not PEAR. You do **not** need to install anything from the **PHP PEAR Packages** page in cPanel for this project. Composer dependencies are in `vendor/` and uploaded with the app.

---

## 12. Optional: Cron for Laravel scheduler

If you later add scheduled tasks in `routes/console.php`, add a cron job in **cPanel → Cron Jobs**:

- **Minute:** `*`
- **Hour:** `*`
- **Day:** `*`
- **Month:** `*`
- **Weekday:** `*`
- **Command:**  
  `php /home/f3303405/your_project_path/artisan schedule:run >> /dev/null 2>&1`

(Adjust path to your project root.)

---

## 13. Post-deployment checks

1. Visit `https://your-domain.co.za`. You should see the app, not a directory listing or 500 error.
2. If you see “500 Internal Server Error”, check:
   - **cPanel → Errors** or `storage/logs/laravel.log` for the real error.
   - `.env` exists in the project root (not in `public/`), and `APP_KEY` is set.
   - Document root is exactly the `public` directory.
   - PHP version is 8.2+ for that domain (MultiPHP Manager).
3. Test a page that reads from the database and a page that uses `/storage/...` (e.g. blog images) to confirm DB and storage link.

**If the homepage returns 500** and `storage/logs/` is empty, upload **`public/debug-app.php`** and open **`https://your-domain.co.za/debug-app.php`**. It runs the same bootstrap as `index.php` and prints any PHP exception (e.g. missing APP_KEY, permission, or class not found). Fix the reported error, then delete `debug-app.php`.

**If you see "View [pages.home] not found" (or "View [x] not found)":** Laravel cannot find the Blade template. The app root (the directory that contains `app/`, `vendor/`, `bootstrap/`) must contain **`resources/views/`** and its full tree (e.g. `resources/views/pages/home.blade.php`). With **root deployment** (public contents in `public_html/`), the app root is the **parent** of `public_html/` — upload `resources/` there so you have e.g. `…/resources/views/pages/home.blade.php`. Re-upload or unzip the project so that `resources/views/` is present; do not exclude it when packaging.

**If you see "Vite manifest not found" or "ViteManifestNotFoundException":** The app needs the Vite build output in the document root. (1) Run **`npm run build`** locally so that **`public/build/`** exists (with `manifest.json` and the built JS/CSS). (2) Upload **`public/build/`** to the server into your document root as **`build/`** (e.g. `public_html/build/` when using root deployment). (3) With **root deployment**, the app will automatically use `public_html` as the public path when it finds `public_html/build/manifest.json`; you can also set **`APP_PUBLIC_PATH`** in `.env` to the full path to your document root (e.g. `/home/username/public_html`) if needed.

**If images (e.g. homepage “Why Strong Teams Fail”, experience section, blog) do not show:** They are served from `/storage/...`. (1) Ensure the **storage symlink** is in the **document root** (e.g. **`public_html/storage`** → `../storage/app/public` for root deployment). Run **`php artisan storage:link`** on the server after deployment so the link is created in the correct folder, or create the symlink manually in File Manager. (2) Ensure **`storage/app/public`** contains the media files (run the seed or uploads); the symlink only exposes that directory at `/storage/`.

**If you get "File not found (404)" on routes like /about-us, /contact, /blog:** The server must send all non-file requests to the Laravel front controller. (1) Ensure **`.htaccess`** is in the **document root** (e.g. `public/` or `public_html/`) and contains **`RewriteBase /`** and **`RewriteRule ^ front.php [L]`** (or `index.php` if you use that). (2) If the site is in a subdirectory, set **`RewriteBase /subdir/`** and ensure the rule points to the front controller. (3) On some hosts, **AllowOverride** must be enabled so `.htaccess` is read; if 404s persist, ask the host to allow overrides or to point the domain's document root to the folder that contains `.htaccess` and `front.php`. (4) Re-upload **`public/.htaccess`** from the project (it includes `RewriteBase /`).

---

## 14. Summary checklist

| Step | Action |
|------|--------|
| 1 | Set domain PHP to 8.2+ in MultiPHP Manager |
| 2 | Run `composer install --no-dev` and `npm run build` locally |
| 3 | Upload project (with `vendor/`, `public/build/`) to the server |
| 4 | Point document root to the `public` folder |
| 5 | Create MySQL database and user; add user to database |
| 6 | Create `.env` with APP_KEY, APP_URL, DB_*, etc. |
| 7 | Set permissions on `storage/` and `bootstrap/cache/` (755 or 775) |
| 8 | Create `public/storage` symlink to `storage/app/public` |
| 9 | Run migrations (Terminal, one-time script, or locally) |
| 10 | Remove any one-time deploy script; test the site |

If you tell me whether you’re using the main domain or a subdomain and the exact folder path on the server, I can adapt the paths in this guide (e.g. for `demo.cm-solution.co.za` or `cm-solution.co.za`).
