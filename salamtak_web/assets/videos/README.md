# Video Background Setup

## Adding Your Video

To add a video background to the dashboard pages, place your video file(s) in this folder with the following names:

- `background.mp4` (required - MP4 format for broad compatibility)
- `background.webm` (optional - WebM format for better compression)

## Video Requirements

### Recommended Specifications:
- **Resolution**: 1920x1080 (Full HD) or higher
- **Duration**: 10-30 seconds (will loop automatically)
- **File Size**: Keep under 10MB for fast loading
- **Aspect Ratio**: 16:9 (landscape)
- **Frame Rate**: 24-30 fps
- **Codec**: H.264 for MP4, VP9 for WebM

### Content Suggestions:
- City infrastructure scenes
- Roads and urban development
- Community activities
- Abstract motion graphics
- Subtle animations (avoid distracting content)

## Free Video Resources

You can download free stock videos from:
- [Pexels Videos](https://www.pexels.com/videos/)
- [Pixabay Videos](https://pixabay.com/videos/)
- [Videvo](https://www.videvo.net/)
- [Coverr](https://coverr.co/)

### Search Keywords:
- "city infrastructure"
- "urban development"
- "road construction"
- "community"
- "abstract background"
- "technology"

## Converting Videos

If you need to convert your video to the required formats:

### Using FFmpeg (Command Line):

**Convert to MP4:**
```bash
ffmpeg -i input.mov -c:v libx264 -preset slow -crf 22 -c:a aac -b:a 128k -vf scale=1920:1080 background.mp4
```

**Convert to WebM:**
```bash
ffmpeg -i input.mov -c:v libvpx-vp9 -b:v 2M -c:a libopus -vf scale=1920:1080 background.webm
```

### Using Online Tools:
- [CloudConvert](https://cloudconvert.com/)
- [Online-Convert](https://www.online-convert.com/)

## Fallback

If no video is provided, the dashboard will display with the default gradient background. The video background is an enhancement and not required for the website to function.

## Performance Tips

1. **Optimize file size**: Compress videos to reduce loading time
2. **Use appropriate resolution**: 1080p is sufficient for most screens
3. **Consider mobile**: Video backgrounds may be disabled on mobile devices for performance
4. **Test loading**: Ensure video loads quickly on slower connections

## Current Status

⚠️ **No video file detected**

Please add `background.mp4` to this folder to enable the video background feature.
