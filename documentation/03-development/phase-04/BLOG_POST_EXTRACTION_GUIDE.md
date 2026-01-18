# Blog Post Extraction and Migration Guide

## Overview
This guide explains how to extract blog posts from the existing website (https://www.thestrengthstoolbox.com) and migrate them to the new Laravel application.

---

## Method 1: Automated Extraction (Recommended First Step)

### Step 1: Run Extraction Command
```bash
php artisan blog:extract --url=https://www.thestrengthstoolbox.com --output=blog-posts.json
```

This command will:
- Attempt to discover blog post URLs
- Extract title, content, dates, and metadata
- Save to JSON file for review

**Note:** Automatic extraction may not work perfectly. Review and clean the extracted data.

### Step 2: Review Extracted Data
Open `blog-posts.json` and review:
- Content completeness
- Date accuracy
- Category/tag assignment
- Image references

### Step 3: Clean and Update
- Fix any extraction errors
- Add missing categories/tags
- Update image references
- Verify content formatting

### Step 4: Import
```bash
php artisan content:migrate --source=json --file=blog-posts.json
```

---

## Method 2: Manual Extraction (Most Reliable)

### Step 1: Access Existing Website
Visit: https://www.thestrengthstoolbox.com/blog

### Step 2: For Each Blog Post, Collect:

#### Required Information:
- **Title:** Exact title of the blog post
- **Content:** Full HTML or plain text content
- **Publication Date:** When the post was published
- **Excerpt:** Short summary (first paragraph or meta description)
- **Categories:** Which categories the post belongs to
- **Tags:** Relevant tags/keywords
- **Featured Image:** URL or path to featured image
- **Author:** Author name (usually Eberhard Niklaus)

#### Optional Information:
- **SEO Title:** Custom meta title (if different from post title)
- **SEO Description:** Custom meta description
- **SEO Keywords:** Relevant keywords for SEO

### Step 3: Format Data

Create a JSON file or update the seeder directly.

#### Option A: JSON Format
Create `blog-posts.json`:
```json
{
  "blog_posts": [
    {
      "title": "Blog Post Title",
      "slug": "blog-post-slug",
      "excerpt": "Short excerpt of the blog post...",
      "content": "<p>Full HTML content of the blog post...</p>",
      "author_id": 1,
      "is_published": true,
      "published_at": "2024-01-15",
      "meta_title": "SEO Title - The Strengths Toolbox",
      "meta_description": "SEO description for search engines",
      "meta_keywords": "keyword1, keyword2, keyword3",
      "category_slugs": ["team-building", "leadership"],
      "tag_slugs": ["strengths", "team performance"]
    }
  ]
}
```

#### Option B: Update Seeder Directly
Edit `database/seeders/BlogPostMigrationSeeder.php` and add to the `$posts` array in `seedSampleBlogPosts()` method.

---

## Method 3: Update BlogPostMigrationSeeder

### Step 1: Open Seeder File
```bash
code database/seeders/BlogPostMigrationSeeder.php
```

### Step 2: Locate the `seedSampleBlogPosts()` Method

### Step 3: Replace Sample Posts with Actual Posts

For each blog post from the existing website, add an entry like this:

```php
[
    'title' => 'Actual Blog Post Title',
    'slug' => 'actual-blog-post-slug',
    'excerpt' => 'Actual excerpt from the blog post...',
    'content' => $this->getBlogPostContent('actual-post'), // Or inline HTML
    'author_id' => $author->id,
    'is_published' => true,
    'published_at' => Carbon::parse('2024-01-15'), // Actual publication date
    'meta_title' => 'SEO Title - The Strengths Toolbox',
    'meta_description' => 'SEO description for search engines',
    'meta_keywords' => 'relevant, keywords, here',
    'category_slugs' => ['team-building'], // Match existing categories
    'tag_slugs' => ['strengths', 'team performance'], // Match existing tags
],
```

### Step 4: Add Content Method (if using separate content)

If you want to keep content separate, add a method:

```php
protected function getBlogPostContent(string $type): string
{
    $content = [
        'actual-post' => <<<'HTML'
<div class="prose prose-lg max-w-none">
    <h2>Blog Post Heading</h2>
    <p>Full blog post content here...</p>
    <!-- Add all HTML content from existing website -->
</div>
HTML,
        // Add more posts...
    ];

    return $content[$type] ?? '<p>Content coming soon...</p>';
}
```

### Step 5: Run Seeder
```bash
php artisan db:seed --class=BlogPostMigrationSeeder
```

---

## Method 4: Admin Panel Import

### Step 1: Access Admin Panel
Log in to: `/admin`

### Step 2: Navigate to Blog Posts
Go to: Blog → Posts

### Step 3: Create Each Post
1. Click "Create New Post"
2. Enter title, content, excerpt
3. Assign categories and tags
4. Upload featured image
5. Set publication date
6. Add SEO metadata
7. Publish

**Note:** This method is time-consuming for many posts but gives full control.

---

## Content Formatting Guidelines

### HTML Content
- Preserve original HTML structure when possible
- Clean up any broken HTML
- Ensure proper heading hierarchy (h2, h3, etc.)
- Convert images to use Laravel asset paths
- Update internal links to new URL structure

### Images
1. Download images from existing website
2. Upload to Laravel media library or `public/images/blog/`
3. Update image references in content:
   ```html
   <!-- Old -->
   <img src="https://thestrengthstoolbox.com/images/post-image.jpg">
   
   <!-- New -->
   <img src="{{ asset('storage/media/blog/post-image.jpg') }}" alt="Description">
   ```

### Links
- Update internal links to use Laravel routes
- Keep external links as-is
- Verify all links work after migration

---

## Category and Tag Mapping

### Existing Categories
The seeder creates these categories:
- Team Building (`team-building`)
- Leadership (`leadership`)
- Sales Training (`sales-training`)
- Strengths-Based Development (`strengths-based-development`)

### Existing Tags
The seeder creates these tags:
- strengths
- team performance
- employee engagement
- leadership development
- sales strategies
- business growth
- organizational development
- coaching
- training
- management

### Mapping Process
1. Review categories/tags from existing website
2. Map to existing categories/tags (or create new ones)
3. Assign to each blog post in the seeder

---

## Verification Checklist

After migration, verify:

- [ ] All blog posts imported
- [ ] Titles are correct
- [ ] Content is complete
- [ ] Publication dates are accurate
- [ ] Categories assigned correctly
- [ ] Tags assigned correctly
- [ ] Featured images uploaded and referenced
- [ ] SEO metadata present
- [ ] All posts are published
- [ ] URLs/slugs work correctly
- [ ] Images display correctly
- [ ] Links work correctly
- [ ] No broken HTML
- [ ] Brand name correct (The Strengths Toolbox)

---

## Troubleshooting

### Issue: Content Not Displaying
- Check if HTML is properly formatted
- Verify content field in database
- Clear view cache: `php artisan view:clear`

### Issue: Images Not Showing
- Verify image paths are correct
- Check if images are uploaded
- Ensure storage is linked: `php artisan storage:link`

### Issue: Categories/Tags Not Assigning
- Verify category/tag slugs match
- Check if categories/tags exist in database
- Run ProductionContentSeeder first

### Issue: Dates Incorrect
- Verify date format (Y-m-d)
- Check timezone settings
- Update published_at field directly if needed

---

## Quick Reference

```bash
# Extract blog posts (attempts automatic extraction)
php artisan blog:extract --url=https://www.thestrengthstoolbox.com

# Import from JSON
php artisan content:migrate --source=json --file=blog-posts.json

# Run seeder
php artisan db:seed --class=BlogPostMigrationSeeder

# Verify posts created
php artisan tinker
>>> App\Models\BlogPost::count()
>>> App\Models\BlogPost::pluck('title', 'slug')
```

---

**Last Updated:** 2025-01-27
