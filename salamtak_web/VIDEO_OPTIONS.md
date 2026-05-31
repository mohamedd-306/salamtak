# Video Background Options

## Current Video
The website is currently using a free abstract blue gradient video from Mixkit.

## Change the Video

To use a different video, edit these files:
- `salamtak_web/user/dashboard.php` (line with video source)
- `salamtak_web/admin/dashboard.php` (line with video source)

Replace the URL in the `<source>` tag with one of the options below:

## Free CDN Videos (No Download Required)

### Option 1: Abstract Blue Gradient (Current)
```html
<source src="https://assets.mixkit.co/videos/preview/mixkit-abstract-blue-background-with-particles-5512-large.mp4" type="video/mp4">
```
- Size: ~2MB
- Theme: Abstract, modern
- Best for: Professional, tech-focused

### Option 2: City Lights at Night
```html
<source src="https://assets.mixkit.co/videos/preview/mixkit-city-lights-at-night-2825-large.mp4" type="video/mp4">
```
- Size: ~5MB
- Theme: Urban, infrastructure
- Best for: City services, urban development

### Option 3: Digital Technology
```html
<source src="https://assets.mixkit.co/videos/preview/mixkit-digital-animation-of-futuristic-devices-27744-large.mp4" type="video/mp4">
```
- Size: ~3MB
- Theme: Technology, innovation
- Best for: Modern, tech-savvy

### Option 4: Aerial City View
```html
<source src="https://assets.mixkit.co/videos/preview/mixkit-aerial-view-of-a-city-at-night-2869-large.mp4" type="video/mp4">
```
- Size: ~4MB
- Theme: Urban landscape
- Best for: Infrastructure, community

### Option 5: Purple Abstract
```html
<source src="https://assets.mixkit.co/videos/preview/mixkit-purple-and-blue-abstract-background-4002-large.mp4" type="video/mp4">
```
- Size: ~2MB
- Theme: Abstract, colorful
- Best for: Creative, modern

### Option 6: Network Connection
```html
<source src="https://assets.mixkit.co/videos/preview/mixkit-network-of-connections-4484-large.mp4" type="video/mp4">
```
- Size: ~3MB
- Theme: Connectivity, network
- Best for: Community, connection

### Option 7: Rotating Earth
```html
<source src="https://assets.mixkit.co/videos/preview/mixkit-rotating-earth-1192-large.mp4" type="video/mp4">
```
- Size: ~4MB
- Theme: Global, world
- Best for: International, global services

### Option 8: Light Particles
```html
<source src="https://assets.mixkit.co/videos/preview/mixkit-light-particles-floating-1130-large.mp4" type="video/mp4">
```
- Size: ~2MB
- Theme: Elegant, subtle
- Best for: Clean, professional

## How to Change

1. Open `salamtak_web/user/dashboard.php`
2. Find the line with `<source src="https://assets.mixkit.co/..."`
3. Replace the URL with your chosen option
4. Repeat for `salamtak_web/admin/dashboard.php`
5. Refresh your browser - done!

## Download Video Locally

If you want to host the video locally instead of using CDN:

### Method 1: Use the Download Script
```bash
php salamtak_web/download_video.php
```

### Method 2: Manual Download
1. Open the video URL in your browser
2. Right-click and "Save Video As..."
3. Save as `background.mp4` in `salamtak_web/assets/videos/`
4. The website will automatically use the local file

## Custom Video

To use your own video:
1. Place your video file in `salamtak_web/assets/videos/`
2. Name it `background.mp4`
3. The website will use it automatically (local files have priority)

## Performance Notes

- CDN videos load from Mixkit's servers (fast, reliable)
- Local videos load from your server (full control)
- Videos are automatically disabled on mobile devices
- Fallback gradient shows if video fails to load

## Testing

After changing the video:
1. Clear browser cache (Ctrl+Shift+R or Cmd+Shift+R)
2. Open browser console (F12) to check for errors
3. Video should start playing within 2-3 seconds

## Troubleshooting

**Video not playing?**
- Check browser console for errors
- Try a different video URL
- Clear browser cache
- Check internet connection

**Video loading slowly?**
- Choose a smaller video (2-3MB)
- Use local hosting instead of CDN
- Compress the video file

**Want to disable video?**
- Remove the `has-video-bg` class from `<body>` tag
- Animated gradient will show instead
