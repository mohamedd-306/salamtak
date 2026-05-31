# Video Background Feature - Setup Guide

## Overview

The Salamtak website now features a beautiful video background on the home pages (user dashboard and admin dashboard). This creates an immersive, modern experience while maintaining excellent performance.

## Features Implemented

### 1. Video Background System
- Full-screen video background on dashboard pages
- Automatic looping and muted playback
- Smooth fade-in transition when video loads
- Gradient overlay for better text readability
- Fallback animated gradient if video is unavailable

### 2. Glass Morphism Effects
- All cards have a frosted glass appearance
- Enhanced backdrop blur for depth
- Subtle transparency for modern look
- Hover effects maintain glass aesthetic

### 3. Performance Optimizations
- Video automatically disabled on mobile devices
- Lazy loading with smooth transitions
- Fallback to animated gradient on error
- Optimized file size recommendations

### 4. Responsive Design
- Desktop: Full video background
- Mobile: Animated gradient (saves battery & data)
- Tablet: Adaptive based on screen size

## How to Add Your Video

### Quick Method:
1. Download a video from [Pexels](https://www.pexels.com/videos/) or [Pixabay](https://pixabay.com/videos/)
2. Rename it to `background.mp4`
3. Place it in `salamtak_web/assets/videos/`
4. Refresh your dashboard - done!

### Recommended Video Specifications:
- **Format**: MP4 (H.264 codec)
- **Resolution**: 1920x1080 (Full HD)
- **Duration**: 10-30 seconds
- **File Size**: Under 10MB
- **Frame Rate**: 24-30 fps
- **Content**: Subtle, non-distracting motion

### Suggested Video Themes:
- City infrastructure and roads
- Urban development scenes
- Abstract motion graphics
- Technology and innovation
- Community activities
- Subtle nature scenes

## Free Video Resources

### Stock Video Websites:
1. **Pexels Videos** - https://www.pexels.com/videos/
   - Search: "city infrastructure", "urban", "technology"
   
2. **Pixabay Videos** - https://pixabay.com/videos/
   - Search: "abstract background", "city", "road"
   
3. **Coverr** - https://coverr.co/
   - Pre-optimized for web backgrounds
   
4. **Videvo** - https://www.videvo.net/
   - Free HD stock footage

### Example Videos to Try:
```
City Infrastructure:
https://www.pexels.com/video/aerial-view-of-city-854100/

Urban Development:
https://www.pexels.com/video/time-lapse-video-of-city-854745/

Abstract Background:
https://www.pexels.com/video/abstract-digital-grid-3129957/

Technology:
https://www.pexels.com/video/digital-projection-of-abstract-geometrical-lines-3129671/
```

## Video Optimization

### Using FFmpeg (Command Line):

**Optimize MP4:**
```bash
ffmpeg -i input.mov -c:v libx264 -preset slow -crf 22 -c:a aac -b:a 128k -vf scale=1920:1080 -movflags +faststart background.mp4
```

**Create WebM (optional):**
```bash
ffmpeg -i input.mov -c:v libvpx-vp9 -b:v 2M -c:a libopus -vf scale=1920:1080 background.webm
```

### Online Conversion Tools:
- [CloudConvert](https://cloudconvert.com/) - Supports all formats
- [Online-Convert](https://www.online-convert.com/) - Easy to use
- [Compress Video](https://www.compressvideo.io/) - Reduce file size

## Technical Details

### Files Modified:
1. `salamtak_web/assets/css/style.css` - Video background styles
2. `salamtak_web/user/dashboard.php` - User dashboard with video
3. `salamtak_web/admin/dashboard.php` - Admin dashboard with video

### CSS Classes Added:
- `.video-background` - Container for video element
- `.dashboard-page` - Page wrapper for video pages
- `.has-video-bg` - Enables glass morphism effects
- `.no-video` - Fallback animated gradient

### JavaScript Features:
- Automatic video loading detection
- Error handling with fallback
- Mobile device detection
- Smooth fade-in transition

## Fallback Behavior

If no video is provided or if the video fails to load:
1. Animated gradient background displays automatically
2. Smooth color transitions create visual interest
3. No broken images or error messages
4. Seamless user experience maintained

## Browser Compatibility

### Supported Browsers:
- ✅ Chrome 60+
- ✅ Firefox 55+
- ✅ Safari 11+
- ✅ Edge 79+
- ✅ Opera 47+

### Video Format Support:
- MP4 (H.264): All modern browsers
- WebM (VP9): Chrome, Firefox, Opera

## Performance Tips

1. **Optimize File Size**: Keep video under 10MB
2. **Use Appropriate Resolution**: 1080p is sufficient
3. **Enable Fast Start**: Use `-movflags +faststart` in FFmpeg
4. **Test on Slow Connections**: Ensure acceptable load time
5. **Consider CDN**: For production, host video on CDN

## Troubleshooting

### Video Not Playing?
1. Check file name is exactly `background.mp4`
2. Verify file is in `salamtak_web/assets/videos/`
3. Check browser console for errors
4. Try a different video file
5. Clear browser cache

### Video Too Large?
1. Reduce resolution to 1280x720
2. Lower bitrate using FFmpeg
3. Shorten video duration
4. Use online compression tools

### Performance Issues?
1. Reduce video file size
2. Lower resolution
3. Disable on mobile (already implemented)
4. Use animated gradient instead

## Current Status

⚠️ **No video file detected**

The system is currently using the animated gradient fallback. To enable video background:
1. Add `background.mp4` to `salamtak_web/assets/videos/`
2. Refresh the dashboard page
3. Video will load automatically!

## Future Enhancements

Potential improvements for future versions:
- Multiple video options
- User preference to enable/disable video
- Seasonal video themes
- Admin panel to upload videos
- Video playlist rotation
- Custom overlay colors

---

**Need Help?** Check the files in `salamtak_web/assets/videos/` for more detailed instructions.
