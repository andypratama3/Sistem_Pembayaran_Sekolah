Screenshots & Demo assets

This folder contains SVG placeholder screenshots and an animated SVG demo for `template-editor`.

Conversion notes (recommendations):

1) Convert SVG to PNG (ImageMagick):

```bash
# For hero size (1200x700)
convert public/screens/auth-register.svg -resize 1200x700 public/screens/auth-register.png

# Or using rsvg-convert (recommended for better SVG support):
rsvg-convert -w 1200 -h 700 public/screens/auth-register.svg -o public/screens/auth-register.png
```

2) Create GIF from PNG frames:

```bash
# Generate PNG frames (frame1.png frame2.png frame3.png)
convert -delay 100 -loop 0 public/screens/frame1.png public/screens/frame2.png public/screens/frame3.png public/screens/template-editor-demo.gif
```

3) Alternatively, use `ffmpeg` to create a MP4 and convert to GIF for better quality:

```bash
ffmpeg -framerate 10 -i public/screens/frame%02d.png -c:v libx264 -pix_fmt yuv420p public/screens/template-editor-demo.mp4
ffmpeg -i public/screens/template-editor-demo.mp4 -vf "fps=10,scale=800:-1:flags=lanczos" -loop 0 public/screens/template-editor-demo.gif
```

If you want I can run conversions here, but ImageMagick / rsvg-convert may not be installed in this environment. Let me know if you want me to attempt conversion.
