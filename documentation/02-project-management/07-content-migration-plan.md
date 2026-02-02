# Content Migration Plan

## 1. Overview

This document provides a detailed plan for migrating content from TSA Business School website and the existing The Strengths Toolbox website to the new Laravel-based website. It includes content inventory, migration workflow, brand name replacement process, and validation procedures.

## 2. Content Migration Objectives

### 2.1 Primary Objectives

- Migrate all content from TSA Business School website
- Preserve all existing content from The Strengths Toolbox website
- Replace all "TSA Business School" references with "The Strengths Toolbox"
- Maintain content accuracy and formatting
- Optimize images for web
- Verify all links work correctly
- Ensure SEO metadata is preserved or enhanced

### 2.2 Success Criteria

- 100% content migration completed
- 100% brand name replacement verified
- Zero content loss
- All links functional
- All images optimized and loading
- Content formatting preserved
- SEO metadata on all pages

## 3. Content Inventory

### 3.1 TSA Business School Content

#### Homepage Content
- **Source:** https://www.tsabusinessschool.co.za/
- **Sections:**
  1. Hero section (headline, subheadline, CTA)
  2. "What is the Power of Strengths?" section
  3. "Three Pillars of Success" section
  4. "Why Strong Teams Fail Without Strategy" section
  5. "Why The Strengths Toolbox?" section
  6. "Results You Can Expect" section
  7. "What You'll Experience" section
  8. "How it Works" section
  9. Free eBook sign-up form
  10. Client testimonials

#### Strengths Programme Page
- **Source:** https://www.tsabusinessschool.co.za/strengths-programme/
- **Sections:**
  1. Hero section
  2. "What Strengths Matter for Your Business?" section
  3. "The Power of Strengths: Four Proven Programs" section
  4. "Ready to Build Strong Teams and Strong Profits" section
  5. FAQ section

#### About Us Page
- **Source:** https://www.tsabusinessschool.co.za/about-us/
- **Sections:**
  1. "Our Story" section (Eberhard Niklaus biography)
  2. "Why Choose Us?" section
  3. "Track Record" section

#### Testimonials
- **Source:** TSA Business School website
- **Count:** All testimonials from TSA website
- **Format:** Name, company, testimonial text, rating (if available)

### 3.2 Existing The Strengths Toolbox Content

#### Main Navigation Pages

**Strengths-Based Development:**
- The Power Of Strengths
- Teams
- Managers / Leaders
- Salespeople
- Individuals

**Sales Training:**
- Strengths-Based Training
- Relationship Selling
- Selling On The Phone
- Sales Fundamentals Workshop
- Top 10 Sales Secrets
- In-Person Sales

**Facilitation:**
- Customer Service Workshop
- Emotional Intelligence Workshop
- Goal Setting and Getting Things Done
- High-Performance Teams Workshop
- Interpersonal Skills Workshop
- Managing Personal Finances Workshop
- Presentation Skills Workshop
- Supervising Others Workshop

**Standalone Pages:**
- Keynote Talks
- Testimonials
- Books
- Contact Me
- Booking Calendar
- Blog (all posts)
- Privacy Statement

#### Homepage Elements
- Search functionality
- Free eBook download form ("Free Sales Book")
- Contact information section
- Unique value propositions

#### Blog Content
- All blog posts
- Categories
- Tags
- Featured images

#### Media Assets
- All images from existing site
- Logos
- Headshots
- Workshop images
- Testimonial images (if any)

## 4. Content Migration Workflow

### 4.1 Phase 1: Content Extraction (Week 13, Days 1-2)

**Objective:** Extract all content from source websites

**Tasks:**
1. **TSA Business School Content Extraction**
   - Visit each source page
   - Copy text content
   - Note image locations
   - Document structure and formatting
   - Save to organized files (Word/Google Docs)

2. **Existing Website Content Extraction**
   - Access existing website
   - Extract all page content
   - Extract blog posts
   - Note image locations
   - Document navigation structure
   - Save to organized files

3. **Content Organization**
   - Create folder structure by page type
   - Name files clearly (e.g., "homepage-hero-section.md")
   - Create content inventory spreadsheet
   - Document content sources

**Deliverables:**
- Content files organized by page
- Content inventory spreadsheet
- Image location list
- Link reference list

### 4.2 Phase 2: Content Transformation (Week 13, Days 3-4)

**Objective:** Transform content with brand name replacement and updates

**Tasks:**
1. **Brand Name Replacement**
   - Use find/replace: "TSA Business School" → "The Strengths Toolbox"
   - Use find/replace: "TSA Business" → "The Strengths Toolbox" (if applicable)
   - Manual review for context-specific replacements
   - Check for variations (TSA, TSA Business, etc.)

2. **Contact Information Update**
   - Replace all contact information with The Strengths Toolbox details:
     - Name: Eberhard Niklaus
     - Mobile: +27 83 294 8033
     - Email: welcome@eberhardniklaus.co.za (or updated email)

3. **Content Merging**
   - Merge duplicate content (e.g., testimonials)
   - Identify unique content from each source
   - Resolve content conflicts
   - Prioritize more comprehensive content

4. **Content Editing**
   - Proofread all content
   - Fix formatting issues
   - Ensure consistency in tone and style
   - Verify accuracy of information

**Deliverables:**
- Transformed content files
- Brand name replacement verification list
- Content merge decisions document
- Edited and proofread content

### 4.3 Phase 3: Content Import (Week 14, Days 1-3)

**Objective:** Import content into Laravel application database

**Tasks:**
1. **Homepage Content Import**
   - Create homepage page record
   - Import hero section content
   - Import all 10 sections
   - Set up eBook form
   - Link testimonials

2. **Strengths Programme Page Import**
   - Create page record
   - Import all sections
   - Set up FAQ section
   - Configure CTAs

3. **About Us Page Import**
   - Create page record
   - Import biography content
   - Import "Why Choose Us" content
   - Import track record statistics

4. **Existing Content Pages Import**
   - Create page records for all existing pages
   - Import content for each page
   - Maintain page hierarchy (parent/child relationships)
   - Set up navigation structure

5. **Blog Posts Import**
   - Create blog post records
   - Import post content
   - Assign categories and tags
   - Set publication dates
   - Import featured images

6. **Testimonials Import**
   - Create testimonial records
   - Import from both sources
   - Remove duplicates
   - Mark featured testimonials
   - Set display order

**Deliverables:**
- All content imported to database
- Pages accessible via URLs
- Blog posts published
- Testimonials displayed

### 4.4 Phase 4: Image Migration (Week 14, Days 4-5)

**Objective:** Migrate and optimize all images

**Tasks:**
1. **Image Extraction**
   - Download all images from source sites
   - Organize by page/section
   - Document image purposes
   - Note image dimensions and file sizes

2. **Image Optimization**
   - Convert to WebP format (with JPEG fallback)
   - Compress images (reduce file size)
   - Resize oversized images
   - Optimize for web (appropriate dimensions)
   - Add descriptive file names

3. **Image Upload**
   - Upload to Laravel media library
   - Organize in folders
   - Update image references in content
   - Verify images display correctly

4. **Image Metadata**
   - Add alt text to all images
   - Add descriptive file names
   - Document image sources
   - Verify image licensing

**Deliverables:**
- All images optimized and uploaded
- Image references updated in content
- Alt text added to all images
- Images loading correctly

### 4.5 Phase 5: Content Validation (Week 15, Days 1-3)

**Objective:** Validate all migrated content

**Tasks:**
1. **Brand Name Replacement Verification**
   - Search entire database for "TSA Business School"
   - Search for "TSA Business"
   - Search for variations
   - Verify 100% replacement
   - Document any exceptions (with justification)

2. **Content Accuracy Verification**
   - Review all pages for accuracy
   - Verify contact information
   - Check statistics and numbers
   - Verify biographical information
   - Check service descriptions

3. **Link Verification**
   - Test all internal links
   - Test all external links
   - Fix broken links
   - Update outdated links
   - Verify navigation structure

4. **Formatting Verification**
   - Check content formatting
   - Verify headings hierarchy
   - Check lists and bullet points
   - Verify spacing and layout
   - Check special characters display

5. **Image Verification**
   - Verify all images display
   - Check image quality
   - Verify alt text present
   - Check image loading speed
   - Verify responsive images

**Deliverables:**
- Content validation report
- Brand name replacement verification (100%)
- Link verification report
- Image verification report
- List of issues and fixes

## 5. Brand Name Replacement Process

### 5.1 Replacement Rules

**Primary Replacement:**
- "TSA Business School" → "The Strengths Toolbox"

**Context-Specific Replacements:**
- "TSA Business School's" → "The Strengths Toolbox's"
- "TSA Business School website" → "The Strengths Toolbox website"
- "at TSA Business School" → "at The Strengths Toolbox"

**Exceptions:**
- Historical references in testimonials (if contextually appropriate)
- Third-party quotes (maintain original attribution)
- Legal documents (if applicable)

### 5.2 Automated Replacement

**Tools:**
- Find/Replace in text editors
- Database search and replace (with caution)
- Script-based replacement (for bulk operations)

**Process:**
1. Export content to text files
2. Use find/replace tool
3. Review replacements
4. Import back to database
5. Verify replacements

### 5.3 Manual Review

**Areas Requiring Manual Review:**
- Testimonials (context-specific)
- Historical references
- Third-party content
- Legal text
- Email templates
- Meta descriptions
- Alt text
- Image file names

### 5.4 Verification Process

**Step 1: Database Search**
```sql
-- Search for any remaining TSA references
SELECT * FROM pages WHERE content LIKE '%TSA Business School%';
SELECT * FROM blog_posts WHERE content LIKE '%TSA Business School%';
SELECT * FROM testimonials WHERE testimonial LIKE '%TSA Business School%';
```

**Step 2: Content Review**
- Review all pages manually
- Check admin panel content
- Review email templates
- Check form labels

**Step 3: Frontend Review**
- View all pages on frontend
- Check meta tags (view source)
- Check schema markup
- Review all text content

**Step 4: Final Verification**
- Automated script to find any remaining references
- Manual spot-check of random pages
- Stakeholder review
- Final sign-off

## 6. Content Validation Checklist

### 6.1 Content Completeness

- [ ] All TSA Business School content migrated
- [ ] All existing website content migrated
- [ ] No content sections missing
- [ ] All blog posts migrated
- [ ] All testimonials migrated
- [ ] All pages created in database

### 6.2 Brand Consistency

- [ ] 100% brand name replacement verified
- [ ] No "TSA Business School" references remain
- [ ] Contact information updated everywhere
- [ ] Logo updated (if applicable)
- [ ] Brand colors consistent
- [ ] Brand voice consistent

### 6.3 Content Accuracy

- [ ] All text accurate
- [ ] Contact information correct
- [ ] Statistics and numbers correct
- [ ] Biographical information accurate
- [ ] Service descriptions accurate
- [ ] No typos or grammatical errors

### 6.4 Link Validation

- [ ] All internal links work
- [ ] All external links work
- [ ] Navigation links correct
- [ ] Footer links correct
- [ ] CTA links correct
- [ ] No broken links

### 6.5 Image Validation

- [ ] All images display correctly
- [ ] Images optimized (WebP/compressed)
- [ ] Alt text on all images
- [ ] Images load quickly
- [ ] Responsive images work
- [ ] Image file names descriptive

### 6.6 Formatting Validation

- [ ] Content formatting preserved
- [ ] Headings hierarchy correct
- [ ] Lists display correctly
- [ ] Spacing consistent
- [ ] Special characters display
- [ ] Tables formatted correctly (if any)

### 6.7 SEO Validation

- [ ] Meta titles on all pages
- [ ] Meta descriptions on all pages
- [ ] Schema markup on all pages
- [ ] Alt text on images
- [ ] Heading hierarchy correct
- [ ] Internal linking structure good

## 7. Content Migration Tools

### 7.1 Content Extraction Tools

- **Browser:** Manual copy/paste
- **Web Scrapers:** If needed (with permission)
- **Screenshots:** For reference
- **Text Editors:** For content organization

### 7.2 Content Transformation Tools

- **Text Editors:** Find/replace, editing
- **Word Processors:** For complex formatting
- **Spreadsheets:** For content inventory
- **Scripts:** For bulk operations (if needed)

### 7.3 Content Import Tools

- **Laravel Admin Panel:** Manual entry
- **Database Import:** CSV/JSON import (if applicable)
- **Laravel Seeders:** For bulk import
- **API:** If content management API available

### 7.4 Image Tools

- **Image Editors:** Photoshop, GIMP, or online tools
- **Image Optimizers:** TinyPNG, ImageOptim, Squoosh
- **WebP Converters:** cwebp, online converters
- **Laravel Media Library:** For upload and management

## 8. Content Migration Schedule

### Week 13: Content Extraction and Transformation

**Days 1-2: Content Extraction**
- Extract TSA Business School content
- Extract existing website content
- Organize content files

**Days 3-4: Content Transformation**
- Brand name replacement
- Contact information update
- Content merging and editing

**Day 5: Content Review**
- Review transformed content
- Stakeholder review (if needed)
- Finalize content

### Week 14: Content Import and Image Migration

**Days 1-3: Content Import**
- Import homepage content
- Import Strengths Programme page
- Import About Us page
- Import existing content pages
- Import blog posts
- Import testimonials

**Days 4-5: Image Migration**
- Extract images
- Optimize images
- Upload images
- Update image references

### Week 15: Content Validation

**Days 1-2: Validation**
- Brand name replacement verification
- Content accuracy verification
- Link verification

**Day 3: Final Checks**
- Formatting verification
- Image verification
- Final content review
- Stakeholder approval

## 9. Content Migration Risks and Mitigation

### 9.1 Risks

**Risk 1: Content Extraction Delays**
- **Mitigation:** Start early, use automated tools where possible

**Risk 2: Brand Name Replacement Errors**
- **Mitigation:** Multiple verification passes, automated scripts

**Risk 3: Content Formatting Loss**
- **Mitigation:** Preserve formatting, manual review

**Risk 4: Image Quality Issues**
- **Mitigation:** Test optimization, maintain originals

**Risk 5: Link Breakage**
- **Mitigation:** Document all links, test systematically

### 9.2 Contingency Plans

- **Content Delays:** Extend migration phase
- **Quality Issues:** Content cleanup sprint
- **Missing Content:** Manual entry if needed

## 10. Content Migration Team

### 10.1 Roles

**Content Specialist:**
- Content extraction
- Content transformation
- Content validation

**Lead Developer:**
- Content import to database
- Image optimization and upload
- Technical validation

**Stakeholder:**
- Content approval
- Content decisions
- Final sign-off

### 10.2 Responsibilities

**Content Specialist:**
- Extract all content accurately
- Transform content with brand replacement
- Edit and proofread content
- Validate content accuracy

**Lead Developer:**
- Import content to database
- Optimize and upload images
- Verify technical aspects
- Fix technical issues

**Stakeholder:**
- Provide content access
- Approve transformed content
- Verify brand consistency
- Final content approval

## 11. Post-Migration Tasks

### 11.1 Content Updates

- Monitor for content errors
- Update content based on feedback
- Add new content as needed
- Maintain content freshness

### 11.2 SEO Optimization

- Review and optimize meta tags
- Enhance schema markup
- Improve internal linking
- Add new content for SEO

### 11.3 Performance Optimization

- Further image optimization if needed
- Content caching optimization
- Database query optimization

### 11.4 Post-Migration Image Setup (Local Storage)

All images are kept in local storage. After deployment or fresh seed:

1. **Storage link** (if not already created): `php artisan storage:link`
2. **Sales Courses images** (TSA originals downloaded locally):  
   `php artisan content:download-sales-courses-images`  
   Saves to `storage/app/public/sales-courses/` (served at `/storage/sales-courses/`).
3. **Blog featured images** (TSA blog posts):  
   `php artisan blog:download-tsa-images`  
   Uses `content-migration/images/image-mapping.json` (entries with `source_url` and `blog_post_slug`).  
   Saves to `storage/app/public/blog/` and assigns `featured_image` to matching posts.

---

**Document Version:** 1.0  
**Date Created:** 2025  
**Status:** Active  
**Next Review:** Before content migration phase begins
