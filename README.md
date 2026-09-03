# Rhema Sounds – Backend Setup Guide

## Files Included

| File | Purpose |
|------|---------|
| `index.html` | Frontend streaming site (modified) |
| `upload.php` | Admin panel — upload & manage tracks, configure live streams |
| `api.php` | REST API (tracks, thumbnails, live stream config) |
| `stream.php` | Audio streaming with HTTP Range support (enables seeking) |
| `config.php` | Shared PHP helpers — **do not expose this URL directly** |

## Requirements

- **PHP 7.4+** with these common extensions: `gd`, `json`, `fileinfo`
- **Web server:** Apache or Nginx with PHP-FPM
- **Optional (for auto-duration):** `ffprobe` installed on the server

## Deployment Steps

1. **Upload all 5 PHP/HTML files to your web host** (public HTML directory).

2. **Create writable directories** (the app creates them automatically, but you may need to set permissions):
   ```bash
   chmod 755 uploads/ data/
   ```

3. **Change the admin password** before going live.
   Open `config.php` and change:
   ```php
   define('ADMIN_PASSWORD', 'rhema2024');
   ```
   to something strong and private.

4. **Visit `upload.php`** and log in to start uploading songs.

## How It Works

### Uploading a Track
1. Go to `yoursite.com/upload.php`
2. Log in with the admin password
3. Drag & drop an audio file (MP3, OGG, WAV, FLAC, AAC, M4A — max 50MB)
4. Fill in title, artist, album, genre
5. Pick a background color for the thumbnail (auto-generated)
6. Paste lyrics (optional)
7. Click **Upload Track** — a thumbnail is generated automatically

### Audio Thumbnails
Thumbnails are auto-generated on upload:
- Uses the **GD library** if available (produces a JPG with music note art + text)
- Falls back to **SVG** if GD is not available (works everywhere, scales perfectly)
- Stored in `uploads/thumbs/`

### Live Streams
1. Go to `upload.php` → **Live Streams** tab
2. Configure stream type:
   - **YouTube Live** → paste the YouTube embed URL with the video ID
   - **HLS Stream** → paste a `.m3u8` URL (works with Wowza, nginx-rtmp, Ant Media, etc.)
   - **Radio/Icecast** → paste your Icecast/Shoutcast stream URL
3. Toggle "Mark as Active" to show the LIVE badge
4. Save — changes appear on the site immediately

### Audio Streaming
All audio goes through `stream.php` which:
- Serves files with correct MIME types
- Supports **HTTP Range requests** (so users can seek/scrub without re-downloading)
- Sets proper cache headers

## Data Storage
- Track metadata: `data/tracks.json`
- Site config: `data/config.json`
- Audio files: `uploads/audio/`
- Thumbnails: `uploads/thumbs/`

A `.htaccess` in the `data/` directory denies direct HTTP access (created automatically).

## Security Notes
- The admin password is stored in plain text in `config.php` — keep this file private and never commit it to public repos
- `data/` directory is protected from direct browser access via `.htaccess`
- File type validation uses both extension and MIME type checks
- All user input is sanitized before storage

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Thumbnails not generating | Check if GD is enabled: `phpinfo()` or `php -m | grep gd` |
| Duration shows "0:00" | Install `ffprobe`: `apt install ffmpeg` or use your host's control panel |
| Audio won't play | Check file permissions on `uploads/audio/` (must be readable by web server) |
| Upload fails | Check `upload_max_filesize` and `post_max_size` in `php.ini` — set to at least 60MB |
| 404 on stream.php | Make sure all files are in the same directory |
