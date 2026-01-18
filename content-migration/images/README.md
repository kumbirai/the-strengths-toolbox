# Image Migration Directory

This directory contains images extracted from source websites and prepared for migration.

## Directory Structure

```
content-migration/images/
├── original/          # Original images from source websites
│   ├── tsa/
│   └── existing-site/
├── optimized/         # Optimized images (WebP format)
│   ├── tsa/
│   └── existing-site/
├── image-mapping.json # Mapping of original to new filenames
└── media-library-mapping.json # Mapping to media library URLs (generated after upload)
```

## Image Organization

Images should be organized by source and page/section:
- `tsa/homepage/hero/` - TSA homepage hero images
- `tsa/strengths-programme/` - Strengths Programme page images
- `existing-site/blog/` - Blog post images
- etc.

## Image Optimization

1. Extract images to `original/` directory
2. Run optimization script to create WebP versions in `optimized/`
3. Upload optimized images using: `php artisan images:upload-migrated`
4. Update image references using: `php artisan content:update-image-references`

## Image Mapping

The `image-mapping.json` file should contain:
- Original image path
- New filename
- Alt text
- Page/section information

## Next Steps

After images are uploaded:
1. Run `php artisan images:update-alt-text` to add alt text
2. Run `php artisan content:update-image-references` to update content URLs
3. Verify images display correctly on frontend
