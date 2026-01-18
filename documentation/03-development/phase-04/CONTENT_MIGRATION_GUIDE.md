# Content Migration Guide

## Overview
This guide provides step-by-step instructions for migrating all content from existing sources into the CMS.

## Quick Start

### Option 1: Automated Migration (Recommended)
```bash
# Migrate all content pages
php artisan db:seed --class=ContentMigrationSeeder

# Seed additional content (categories, tags, testimonials)
php artisan db:seed --class=ProductionContentSeeder

# Verify migration
php artisan content:verify
```

### Option 2: Manual Migration
Use the admin panel to create pages manually, or use the migration command:
```bash
php artisan content:migrate --source=manual
```

## Content Structure

### Pages Created by ContentMigrationSeeder

#### Strengths-Based Development (5 pages)
1. The Power of Strengths (`/the-power-of-strengths`)
2. Teams (`/strengths-based-development/teams`)
3. Managers / Leaders (`/strengths-based-development/managers-leaders`)
4. Salespeople (`/strengths-based-development/salespeople`)
5. Individuals (`/strengths-based-development/individuals`)

#### Sales Training (6 pages)
1. Strengths-Based Training (`/sales-training/strengths-based-training`)
2. Relationship Selling (`/sales-training/relationship-selling`)
3. Selling On The Phone (`/sales-training/selling-on-the-phone`)
4. Sales Fundamentals Workshop (`/sales-training/sales-fundamentals-workshop`)
5. Top 10 Sales Secrets (`/sales-training/top-10-sales-secrets`)
6. In-Person Sales (`/sales-training/in-person-sales`)

#### Facilitation/Workshops (8 pages)
1. Customer Service Workshop (`/facilitation/customer-service-workshop`)
2. Emotional Intelligence Workshop (`/facilitation/emotional-intelligence-workshop`)
3. Goal Setting (`/facilitation/goal-setting`)
4. High-Performance Teams (`/facilitation/high-performance-teams`)
5. Interpersonal Skills (`/facilitation/interpersonal-skills`)
6. Personal Finances (`/facilitation/personal-finances`)
7. Presentation Skills (`/facilitation/presentation-skills`)
8. Supervising Others (`/facilitation/supervising-others`)

#### Standalone Pages (2 pages)
1. Keynote Talks (`/keynote-talks`)
2. Books (`/books`)

**Total: 21 content pages**

## Migration Process

### Step 1: Run Content Migration Seeder
```bash
php artisan db:seed --class=ContentMigrationSeeder
```

This creates all 21 content pages with:
- Proper slugs and URLs
- SEO metadata (title, description, keywords)
- Content structure
- Published status

### Step 2: Update Content
The seeder creates pages with template content. Update each page via:
- Admin panel: `/admin/pages`
- Or directly in database
- Or via API if available

### Step 4: Verify Migration
```bash
php artisan content:verify
```

This checks:
- All required pages exist
- Brand name replacement (no TSA references)
- SEO metadata present
- Content quality

### Step 4: Brand Name Replacement
If migrating from TSA Business School:
```bash
# Search for any remaining TSA references
php artisan content:verify

# Manual replacement via admin panel or database
```

## Content Update Methods

### Method 1: Admin Panel
1. Log in to admin panel
2. Navigate to Pages
3. Edit each page
4. Update content, images, SEO
5. Save and publish

### Method 2: Database Direct
```sql
-- Update page content
UPDATE pages 
SET content = '<new content>' 
WHERE slug = 'the-power-of-strengths';
```

### Method 3: JSON Import
Create a JSON file with content:
```json
{
  "pages": [
    {
      "slug": "the-power-of-strengths",
      "content": "<h2>Updated Content</h2><p>...</p>",
      "meta_title": "Updated Title",
      "meta_description": "Updated description"
    }
  ]
}
```

Then import:
```bash
php artisan content:migrate --source=json --file=content-updates.json
```

## Content Checklist

### For Each Page
- [ ] Content is complete and accurate
- [ ] No placeholder text remains
- [ ] Images are uploaded and referenced
- [ ] SEO metadata is complete
- [ ] Brand name is correct (The Strengths Toolbox)
- [ ] Links are working
- [ ] Page is published
- [ ] Content formatting is correct

### Brand Name Verification
- [ ] No "TSA Business School" references
- [ ] No "TSA Business" references
- [ ] Contact information updated
- [ ] All references use "The Strengths Toolbox"

## Image Migration

### Step 1: Extract Images
Download all images from source websites and organize by page.

### Step 2: Optimize Images
```bash
php artisan images:optimize --format=webp --quality=85
```

### Step 3: Upload to Media Library
1. Go to admin panel → Media
2. Upload images
3. Organize in folders
4. Update page content with image references

### Step 5: Update Content
Replace image placeholders in page content with actual image references:
```html
<img src="{{ asset('storage/media/images/hero-image.webp') }}" alt="Description">
```

## Blog Posts Migration

### Automated Migration (Recommended)
```bash
php artisan db:seed --class=BlogPostMigrationSeeder
```

This creates 5 sample blog posts with:
- Complete content structure
- Categories and tags assigned
- SEO metadata
- Published status

**Note:** Update the seeder with actual blog posts from your existing website.

### Extraction Methods

#### Method 1: Automated Extraction (First Try)
```bash
php artisan blog:extract --url=https://www.thestrengthstoolbox.com --output=blog-posts.json
```

This attempts to automatically extract blog posts. Review the output and clean as needed.

#### Method 2: Manual Extraction (Most Reliable)
See detailed guide: `BLOG_POST_EXTRACTION_GUIDE.md`

1. Visit https://www.thestrengthstoolbox.com/blog
2. For each post, collect: title, content, date, categories, tags
3. Update `BlogPostMigrationSeeder.php` with actual data
4. Or create JSON file and import

#### Method 3: Admin Panel
1. Log in to admin panel
2. Create each blog post manually
3. Assign categories and tags
4. Upload featured images
5. Publish posts

### Bulk Import via JSON
Create JSON file with blog posts:
```json
{
  "blog_posts": [
    {
      "title": "Post Title",
      "slug": "post-slug",
      "excerpt": "Short excerpt",
      "content": "<p>Full content...</p>",
      "author_id": 1,
      "is_published": true,
      "published_at": "2025-01-01",
      "category_slugs": ["team-building"],
      "tag_slugs": ["strengths", "team performance"]
    }
  ]
}
```

Then import:
```bash
php artisan content:migrate --source=json --file=blog-posts.json
```

## Testimonials Migration

### Using Seeder
```bash
php artisan db:seed --class=ProductionContentSeeder
```

### Manual Addition
1. Go to admin panel → Testimonials
2. Add each testimonial
3. Mark featured testimonials
4. Set display order
5. Publish

## Verification Commands

### Verify Content Migration
```bash
php artisan content:verify
```

Checks:
- All required pages exist
- Brand name replacement
- SEO metadata
- Content quality

### Check Specific Page
```bash
php artisan tinker
>>> $page = App\Models\Page::where('slug', 'the-power-of-strengths')->first();
>>> $page->title;
>>> $page->is_published;
```

## Troubleshooting

### Pages Not Showing
- Check `is_published` status
- Verify `published_at` date
- Check route configuration
- Clear cache: `php artisan cache:clear`

### Content Not Updating
- Clear view cache: `php artisan view:clear`
- Clear application cache: `php artisan cache:clear`
- Check database directly

### Brand Name Issues
- Run verification: `php artisan content:verify`
- Search database: `SELECT * FROM pages WHERE content LIKE '%TSA%';`
- Update manually via admin panel

## Next Steps After Migration

1. **Review All Pages**
   - Visit each page on frontend
   - Check content accuracy
   - Verify formatting

2. **Update Images**
   - Upload actual images
   - Replace placeholders
   - Optimize all images

3. **SEO Optimization**
   - Review meta tags
   - Add schema markup
   - Optimize content for keywords

4. **Link Verification**
   - Test all internal links
   - Verify external links
   - Check navigation

5. **Final Verification**
   ```bash
   php artisan content:verify
   php artisan test:routes
   ```

---

**Last Updated:** 2025-01-27
