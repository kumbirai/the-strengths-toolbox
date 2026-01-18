# Business Requirement Document: The Strengths Toolbox Website Rebuild

## 1. Executive Summary

### 1.1 Project Overview

Complete website rebuild and enhancement for https://www.thestrengthstoolbox.com/ to create a modern, production-grade PHP website. The rebuild will incorporate content from TSA Business School website while maintaining all existing content from the current The Strengths Toolbox website.

### 1.2 Business Objectives

- Modernize the website with updated content and design
- Consolidate best content from both TSA Business School and The Strengths Toolbox websites
- Maintain brand consistency by rebranding all TSA Business School references to The Strengths Toolbox
- Preserve all existing valuable content from the current website
- Improve user experience and conversion optimization

### 1.3 Technology Stack

- **Backend:** PHP (Production-Grade)
- **Framework:** To be determined (Laravel/Symfony recommended)
- **Database:** MySQL/PostgreSQL
- **Frontend:** Modern responsive design

---

## 2. Branding Requirements

### 2.1 Brand Name Standardization

**Critical Requirement:** All references to "TSA Business School" throughout the new website must be replaced with "The Strengths Toolbox".

**Scope of Replacement:**

- Page titles and headings
- Body content
- Navigation menus
- Footer content
- Meta tags and SEO elements
- Form labels and placeholders
- Call-to-action buttons
- Testimonials (where applicable)
- Any other text content

**Exceptions:**

- Historical references in testimonials may retain original company names if contextually appropriate
- Third-party content or quotes should maintain original attribution

---

## 3. Content Migration and Page Structure

### 3.1 Landing Page (Home Page)

**Content Source:** https://www.tsabusinessschool.co.za/ (main landing page)

**Required Content Sections:**

1. **Hero Section**
   - Headline: "Build Strong Teams. Unlock Strong Profits."
   - Subheadline: Welcome message introducing The Strengths Toolbox
   - Primary CTA: "Book Your Complimentary 30-Minute Breakthrough Call" or similar

2. **What is the Power of Strengths?**
   - Main value proposition section
   - Key benefits list (6 key benefits as per source)
   - Business growth system description

3. **Three Pillars of Success**
   - Turn talent into performance
   - Build teams that stick
   - Drive growth with purpose

4. **Why Strong Teams Fail Without Strategy**
   - Problem statement section
   - Link to Strengths Programme page

5. **Why The Strengths Toolbox?**
   - Company credibility section
   - 20+ years experience mention
   - Link to About Us page

6. **Results You Can Expect**
   - Stronger Teams
   - Higher Profits
   - Confident Leadership

7. **What You'll Experience**
   - Results description section
   - Link to Sales Courses

8. **How it Works**
   - 3-step process explanation
   - Step 1: Book a Consultation
   - Step 2: Power of Strengths Training
   - Step 3: Watch Your Profits Grow

9. **Free eBook Sign-Up Form**
   - Lead generation form
   - Fields: First Name, Last Name, Email

10. **Client Testimonials**
    - All testimonials from TSA Business School website
    - Maintain original attribution and content

**Content Transformation:**

- Replace "TSA Business School" with "The Strengths Toolbox" throughout
- Update contact information to match The Strengths Toolbox details
- Maintain all value propositions and benefits

---

### 3.2 Strengths Programme Page

**Content Source:** https://www.tsabusinessschool.co.za/strengths-programme/

**Page URL:** `/strengths-programme` or `/strengths-program`

**Required Content Sections:**

1. **Hero Section**
   - Headline: "Unlock growth through the Power of Strengths"
   - Subheadline: Introduction to The Strengths Toolbox services
   - Primary CTA: "Book a 30 Minute Free Consultation"

2. **What Strengths Matter for Your Business?**
   - Problem identification section
   - Four key problems:
     - Misaligned teams
     - High turnover
     - Missed sales targets
     - Burnout and disengagement

3. **The Power of Strengths: Four Proven Programs**
   - **For Individuals** - Discover Your Potential
   - **For Managers & Leaders** - Lead With Strength
   - **For Salespeople** - Sell With Confidence
   - **For Teams** - Build Collective Power
   - Each program section should include benefits and outcomes

4. **Ready to Build Strong Teams and Strong Profits**
   - Closing CTA section
   - Link to consultation booking

5. **Frequently Asked Questions (FAQ)**
   - What is the Strengths Programme and how can it benefit my business?
   - Who should participate in the Strengths Programme?
   - How does the Strengths-Based Team Development component work?
   - What results can businesses expect from participating?
   - How do I get started with the Strengths Programme?

**Content Transformation:**

- Replace all "TSA Business School" references with "The Strengths Toolbox"
- Update contact information
- Maintain all program descriptions and benefits

---

### 3.3 About Us Page

**Content Source:** https://www.tsabusinessschool.co.za/about-us/

**Page URL:** `/about-us` or `/about`

**Required Content Sections:**

1. **Our Story**
   - Founder introduction: Eberhard Niklaus
   - Background and experience
   - Career progression: Sales Representative → Franchisee → Sales Manager → Franchise Network Leader
   - 17+ years in franchise industry
   - Transition to coaching and training
   - Launch of The Strengths Toolbox (formerly TSA Business School)
   - Professional headshot image requirement

2. **Why Choose Us?**
   - Proven Success
   - Customized Solutions
   - Empower Your Team
   - A Holistic Approach

3. **Track Record**
   - 30 Years Experience
   - 1560+ Happy Clients
   - Visual representation (teamwork imagery)

**Content Transformation:**

- Replace "TSA Business School" with "The Strengths Toolbox"
- Update company history to reflect The Strengths Toolbox branding
- Maintain all biographical and experience information about Eberhard Niklaus
- Update statistics and achievements

---

### 3.4 Existing Content Preservation

**Content Source:** https://www.thestrengthstoolbox.com/ (current website)

**All existing pages and content must be preserved and migrated:**

#### 3.4.1 Main Navigation Pages

- **Strengths-Based Development** (parent page with sub-pages)
  - The Power Of Strengths
  - Teams
  - Managers / Leaders
  - Salespeople
  - Individuals

- **Sales Training** (parent page with sub-pages)
  - Strengths-Based Training
  - Relationship Selling
  - Selling On The Phone
  - Sales Fundamentals Workshop
  - Top 10 Sales Secrets
  - In-Person Sales

- **Facilitation** (parent page with sub-pages)
  - Customer Service Workshop
  - Emotional Intelligence Workshop
  - Goal Setting and Getting Things Done
  - High-Performance Teams Workshop
  - Interpersonal Skills Workshop
  - Managing Personal Finances Workshop
  - Presentation Skills Workshop
  - Supervising Others Workshop

- **Keynote Talks** (standalone page)
- **Testimonials** (standalone page)
- **Books** (standalone page)
- **Contact Me** (standalone page with form)
- **Booking Calendar** (standalone page with calendar integration)
- **Blog** (blog listing and individual posts)
- **Privacy Statement** (legal page)

#### 3.4.2 Homepage Elements to Preserve

- Search functionality
- Free eBook download form ("Free Sales Book")
- Contact information section
- Any unique value propositions not covered by TSA content

#### 3.4.3 Content Elements to Preserve

- All workshop descriptions and details
- All service descriptions
- All blog posts and articles
- All testimonials (merge with TSA testimonials)
- All contact forms and functionality
- Booking calendar integration
- Privacy policy content
- Any unique content not present in TSA website

---

## 4. Content Integration Strategy

### 4.1 Content Merging Rules

1. **Testimonials:** Merge testimonials from both websites, removing duplicates
2. **Services:** Preserve all unique services from both websites
3. **Contact Information:** Use The Strengths Toolbox contact details as primary
4. **Forms:** Preserve all existing form functionality and add new forms from TSA content
5. **Blog:** Preserve all existing blog content

### 4.2 Content Prioritization

- **Primary:** New landing page content from TSA Business School
- **Primary:** New Strengths Programme page content
- **Primary:** New About Us page content
- **Secondary:** All existing The Strengths Toolbox content
- **Tertiary:** Additional TSA content that doesn't conflict

### 4.3 Duplicate Content Handling

- If content exists on both sites, use the more comprehensive or recent version
- Preserve unique value propositions from both sources
- Ensure no contradictory information

---

## 5. Functional Requirements

### 5.1 Contact Information

**Primary Contact Details:**

- **Name:** Eberhard Niklaus
- **Mobile:** +27 83 294 8033
- **Email:** welcome@eberhardniklaus.co.za (or update if new email provided)

**Display Requirements:**

- Contact information must be visible in header/footer
- Contact page must include contact form
- All contact forms must be functional

### 5.2 Forms and Lead Generation

1. **Free eBook Download Form**
   - Fields: First Name, Last Name, Email
   - Integration with email marketing system
   - Thank you page/confirmation

2. **Contact Form**
   - Standard contact form fields
   - Email notification to business owner
   - Auto-responder to user

3. **Booking Calendar**
   - Integration with booking system (Calendly or similar)
   - 30-minute consultation booking
   - Calendar availability display

### 5.3 Navigation Requirements

- Clear, intuitive navigation structure
- Mobile-responsive menu
- Breadcrumb navigation for deep pages
- Footer navigation duplicate

### 5.4 Search Functionality

- Preserve existing search functionality
- Search across all pages and blog posts
- Search results page

---

## 6. Design and User Experience Requirements

### 6.1 Modern Design Standards

- Clean, professional design
- Mobile-first responsive design
- Fast page load times
- Accessible design (WCAG compliance)
- Modern typography and spacing

### 6.2 Call-to-Action (CTA) Requirements

- Primary CTA: "Book Your Complimentary 30-Minute Breakthrough Call"
- Secondary CTAs: "Learn More", "Contact Us", "Download Free eBook"
- CTAs must be prominently placed and visually distinct
- Multiple CTAs per page where appropriate

### 6.3 Image Requirements

- Professional headshot of Eberhard Niklaus for About Us page
- High-quality images for hero sections
- Optimized images for web (WebP format preferred)
- Alt text for all images (accessibility)

---

## 7. SEO Requirements

### 7.1 Page-Specific SEO

- Unique, optimized title tags for each page
- Unique meta descriptions for each page
- Proper heading hierarchy (H1, H2, H3)
- Keyword optimization (strengths-based development, CliftonStrengths, etc.)
- Schema markup implementation

### 7.2 URL Structure

- Clean, descriptive URLs
- SEO-friendly URLs (e.g., `/strengths-programme`, `/about-us`)
- 301 redirects for any changed URLs

### 7.3 Content Optimization

- Keyword-rich content without keyword stuffing
- Internal linking structure
- Image alt tags
- Meta tags for social sharing

---

## 8. Technical Requirements

### 8.1 Performance

- Page load time: < 3 seconds
- Mobile-friendly (Google Mobile-Friendly Test)
- Core Web Vitals compliance
- Image optimization

### 8.2 Security

- HTTPS/SSL certificate
- Secure form submissions
- Data protection compliance
- Regular security updates

### 8.3 Browser Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile browsers (iOS Safari, Chrome Mobile)
- Graceful degradation for older browsers

---

## 9. Content Migration Checklist

### 9.1 New Content Integration

- [ ] Landing page content from TSA Business School
- [ ] Strengths Programme page content
- [ ] About Us page content
- [ ] Brand name replacement (TSA → The Strengths Toolbox)

### 9.2 Existing Content Migration

- [ ] All Strengths-Based Development pages
- [ ] All Sales Training pages
- [ ] All Facilitation/Workshop pages
- [ ] Keynote Talks page
- [ ] Testimonials page
- [ ] Books page
- [ ] Contact page
- [ ] Booking Calendar page
- [ ] Blog posts and articles
- [ ] Privacy Statement

### 9.3 Content Quality Assurance

- [ ] All brand name replacements verified
- [ ] Contact information updated and consistent
- [ ] No broken links
- [ ] All forms functional
- [ ] Images optimized and properly attributed
- [ ] Content proofread and edited

---

## 10. Success Criteria

### 10.1 Content Migration Success

- All specified content successfully migrated
- No content loss from existing website
- Brand consistency achieved (100% TSA → The Strengths Toolbox replacement)
- All pages accessible and functional

### 10.2 User Experience Success

- Modern, professional appearance
- Fast page load times
- Mobile-responsive design
- Intuitive navigation
- Clear call-to-actions

### 10.3 Business Objectives Success

- Improved conversion rates
- Better user engagement
- Enhanced SEO performance
- Professional brand representation

---

## 11. Approval and Sign-off

### 11.1 Content Approval

- Landing page content structure
- Strengths Programme page content
- About Us page content
- Brand name replacement verification

### 11.2 Functional Approval

- All forms functional
- Booking calendar integrated
- Search functionality working
- Navigation structure approved

---

## 12. Timeline and Milestones

### Phase 1: Content Collection and Organization

- Gather all content from specified sources
- Organize content by page
- Identify content gaps or conflicts

### Phase 2: Content Transformation

- Replace brand names
- Update contact information
- Merge duplicate content
- Edit and proofread

### Phase 3: Content Migration

- Implement new landing page
- Create Strengths Programme page
- Create About Us page
- Migrate existing content

### Phase 4: Quality Assurance

- Brand consistency check
- Content accuracy verification
- Functional testing
- SEO validation

---

## Appendix A: Content Sources Reference

### A.1 Primary Content Sources

1. **Landing Page:** https://www.tsabusinessschool.co.za/
2. **Strengths Programme:** https://www.tsabusinessschool.co.za/strengths-programme/
3. **About Us:** https://www.tsabusinessschool.co.za/about-us/
4. **Existing Content:** https://www.thestrengthstoolbox.com/

### A.2 Content Documentation

- TSA Website Content: `documentation/tsa-website/TSA_Website_Content_Clean.md`
- Website Development Plan: `documentation/website-development-plan.md`

---

**Document Version:** 1.0  
**Date Created:** 2025  
**Status:** Draft for Review  
**Next Review:** Upon stakeholder approval
