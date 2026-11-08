# Hostinger Cron Job Setup Guide: Automated Daily Backups

This guide walks you through configuring an automated **once-a-day** backup cron job for your POS system on **Hostinger hPanel**.

---

## 1. Overview

Your POS system includes an automated backup command:
```bash
php artisan backup:run --type=full
```

When this command runs, it automatically:
1. **Dumps the MySQL Database**: Backs up all tables, schemas, and records.
2. **Archives Uploaded Files**: Packages all images and documents from `public/uploads/`.
3. **Creates a Compressed ZIP**: Saves the archive in `storage/app/backups/`.
4. **Cloud Sync (Optional)**: Automatically uploads a copy to your configured cloud storage (Hostinger Object Storage, Cloudflare R2, AWS S3).
5. **Rotates Old Backups**: Keeps only your configured number of backups (e.g. 15) to preserve Hostinger disk space.

---

## 2. Step 1: Find Your Exact Website Path in Hostinger

1. Log into **[Hostinger hPanel](https://hpanel.hostinger.com/)**.
2. Select your website (`forestgreen-donkey-748531.hostingersite.com`).
3. Click **Files** &rarr; **File Manager** (or click *File manager* under *Essentials* on your dashboard).
4. Note your absolute home directory path. On Hostinger, it is structured like:
   ```text
   /home/uXXXXXXX/domains/forestgreen-donkey-748531.hostingersite.com/public_html
   ```
   *(Replace `uXXXXXXX` with your actual Hostinger username).*

---

## 3. Step 2: Configure the Cron Job in Hostinger hPanel

1. In the Hostinger left-hand sidebar, click **Advanced** &rarr; **Cron Jobs**.
2. Under **Type of Cron Job**, select **Custom**.
3. Set the schedule to run **Once a day** (e.g., at 2:00 AM):

   | Field | Setting | Description |
   | :--- | :--- | :--- |
   | **Minute** | `0` | Minute 0 |
   | **Hour** | `2` | 2:00 AM (server local time) |
   | **Day of Month** | `*` | Every day |
   | **Month** | `*` | Every month |
   | **Day of Week** | `*` | Every day of the week |

   *Cron expression:* `0 2 * * *`

4. In the **Command to run** box, enter the following command:

   ```bash
   cd /home/uXXXXXXX/domains/forestgreen-donkey-748531.hostingersite.com/public_html && php artisan backup:run --type=full >> /dev/null 2>&1
   ```

   > **Note:** Replace `/home/uXXXXXXX/domains/forestgreen-donkey-748531.hostingersite.com/public_html` with your actual path from Step 1. Alternatively, you can use:
   > ```bash
   > cd /home/$(whoami)/domains/forestgreen-donkey-748531.hostingersite.com/public_html && php artisan backup:run --type=full >> /dev/null 2>&1
   > ```

5. Click **Save** / **Create**.

---

## 4. Step 3: Configure Cloud & Retention Settings in POS Admin

1. Open your POS admin panel: **Setting** &rarr; **Backup & Restore**.
2. Click the **Cloud & Auto-Backup Settings** tab.
3. Configure the following:
   * **Auto-Upload to Cloud**: Check this box if you have configured S3/Cloudflare/Hostinger cloud storage.
   * **Maximum Local Backups to Keep**: Set to `15` (or your preferred number) so older backups are automatically pruned to prevent filling your Hostinger disk.
4. Click **Save Configuration**.

---

## 5. Step 4: Verification & Testing

### Option A: Test via SSH Terminal (Optional)
If you have SSH access enabled in Hostinger (**Advanced** &rarr; **SSH Access**):
```bash
cd /home/uXXXXXXX/domains/forestgreen-donkey-748531.hostingersite.com/public_html
php artisan backup:run --type=full
```

You should see:
```text
Starting automated POS system backup...
Backup completed successfully!
 - Filename: backup_full_2026-11-09_020000.zip
 - Type: full
 - Size: ...
 - Cloud status: ...
```

### Option B: Check the POS Dashboard
1. Go to **Setting** &rarr; **Backup & Restore** in your POS system.
2. In the **Server Backups** tab, you will see your new backup row with:
   * Name: `Automated Backup (...)`
   * Type: `All System Data (DB + Files)`
   * Size, Tables / Files count, and Timestamp.
3. The **Server Disk Usage & Storage Health** monitor at the top will automatically update to reflect the new storage usage.

---

## 6. Troubleshooting

| Symptom | Cause | Solution |
| :--- | :--- | :--- |
| **Cron doesn't execute** | Incorrect project path | Double-check path in File Manager; make sure it points to the folder containing `artisan`. |
| **PHP CLI memory error** | Large database or file uploads | Add memory limit flag: `php -d memory_limit=512M artisan backup:run --type=full` |
| **Permission denied** | Backup folder permissions | Ensure `storage/app/backups/` exists and has `0755` permissions in File Manager. |
| **Cloud upload fails** | S3 credentials or bucket name | Use the **Test Cloud Connection** button in **Setting &rarr; Backup & Restore** to verify your bucket and keys. |

