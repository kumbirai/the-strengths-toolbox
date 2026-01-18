# Phase 4 Deferred Items

**Date:** 2025-01-27  
**Status:** Items identified but deferred to later phases

## Overview

This document tracks functionality and content requirements that were identified during Phase 4 gap analysis but should be deferred to later phases according to the project plan.

---

## 1. Homepage Enhancements (Deferred to Phase 5)

### 1.1 Search Functionality on Homepage

**Requirement:** Business Requirement Document Section 3.4.2 states that "Search functionality" should be preserved on the homepage.

**Current Status:**
- ✅ Search functionality exists on blog pages (`/blog`)
- ❌ No search functionality on homepage

**Decision:** **DEFERRED to Phase 5 (Content Migration)**

**Rationale:**
- Search functionality is mentioned in Phase 4 deliverables but not explicitly in Phase 4 tasks (P4.1.1-P4.1.11)
- Blog search is implemented (P4.5.6)
- Homepage search may require content indexing which is part of Phase 5
- Can be added during Phase 5 when content is fully migrated

**Action Required in Phase 5:**
- Add search form to homepage header or dedicated section
- Implement site-wide search (not just blog)
- Index all pages and content for search
- Create search results page

---

### 1.2 Contact Information Section on Homepage

**Requirement:** Business Requirement Document Section 3.4.2 states that "Contact information section" should be preserved on the homepage.

**Current Status:**
- ✅ Contact information exists in footer
- ⚠️ May need dedicated section on homepage

**Decision:** **DEFERRED to Phase 5 (Content Migration)**

**Rationale:**
- Contact information is already in footer (meets basic requirement)
- Dedicated homepage section may be part of content migration decisions
- Can be evaluated and added during Phase 5 content review

**Action Required in Phase 5:**
- Review if dedicated contact section is needed on homepage
- If required, add contact information section to homepage
- Ensure mobile number (+27 83 294 8033) and email (welcome@eberhardniklaus.co.za) are displayed

---

## 2. Content Population (Deferred to Phase 5)

### 2.1 Existing Content Pages - Content Migration

**Requirement:** Business Requirement Document Section 3.4.1 lists all existing content pages that must be preserved.

**Current Status:**
- ✅ Page structure created in Phase 4 (via `ExistingContentPagesSeeder`)
- ✅ Pages are accessible via CMS
- ❌ Content not yet populated (placeholder content only)

**Decision:** **DEFERRED to Phase 5 (Content Migration)**

**Rationale:**
- Phase 4 scope: Create page structure and templates (P4.4.1-P4.4.7) ✅
- Phase 5 scope: Import existing content pages (P5.3.4) - Content migration
- This is explicitly planned for Phase 5 per project plan

**Action Required in Phase 5:**
- Extract content from existing website (P5.1.4)
- Transform content (brand name replacement) (P5.2.1-P5.2.4)
- Import content into pages created in Phase 4 (P5.3.4)
- Verify all 19 pages have proper content

**Pages Created (Structure Only):**
- **Strengths-Based Development (5 pages):**
  - The Power Of Strengths (`/the-power-of-strengths`)
  - Teams (`/strengths-based-development/teams`)
  - Managers / Leaders (`/strengths-based-development/managers-leaders`)
  - Salespeople (`/strengths-based-development/salespeople`)
  - Individuals (`/strengths-based-development/individuals`)

- **Sales Training (6 pages):**
  - Strengths-Based Training (`/sales-training/strengths-based-training`)
  - Relationship Selling (`/sales-training/relationship-selling`)
  - Selling On The Phone (`/sales-training/selling-on-the-phone`)
  - Sales Fundamentals Workshop (`/sales-training/sales-fundamentals-workshop`)
  - Top 10 Sales Secrets (`/sales-training/top-10-sales-secrets`)
  - In-Person Sales (`/sales-training/in-person-sales`)

- **Facilitation/Workshop (8 pages):**
  - Customer Service Workshop (`/facilitation/customer-service-workshop`)
  - Emotional Intelligence Workshop (`/facilitation/emotional-intelligence-workshop`)
  - Goal Setting and Getting Things Done (`/facilitation/goal-setting`)
  - High-Performance Teams Workshop (`/facilitation/high-performance-teams`)
  - Interpersonal Skills Workshop (`/facilitation/interpersonal-skills`)
  - Managing Personal Finances Workshop (`/facilitation/personal-finances`)
  - Presentation Skills Workshop (`/facilitation/presentation-skills`)
  - Supervising Others Workshop (`/facilitation/supervising-others`)

---

## 3. Navigation Updates (Deferred to Phase 5)

### 3.1 Parent Navigation Items

**Requirement:** Navigation should include links to all main content sections per Business Requirement Document Section 3.4.1.

**Current Status:**
- ✅ Basic navigation exists (Home, Strengths Programme, Blog, About, Contact)
- ❌ Missing parent navigation items for:
  - Strengths-Based Development (with dropdown)
  - Sales Training (with dropdown)
  - Facilitation (with dropdown)

**Decision:** **DEFERRED to Phase 5 (Content Migration)**

**Rationale:**
- Navigation structure should be finalized after content is migrated
- Dropdown menus require all pages to be populated
- Can be implemented during Phase 5 when all pages are ready

**Action Required in Phase 5:**
- Add "Strengths-Based Development" to navigation with dropdown menu
- Add "Sales Training" to navigation with dropdown menu
- Add "Facilitation" to navigation with dropdown menu
- Ensure all sub-pages are linked in dropdowns
- Test navigation on mobile devices

---

## 4. Summary

### Phase 4 Completed ✅
- Page structure created for all 19 existing content pages
- Pages accessible via CMS
- Templates and components ready
- SEO structure in place

### Phase 5 Required Actions
1. **Content Migration:**
   - Extract content from existing website
   - Populate all 19 pages with actual content
   - Replace brand names (TSA → The Strengths Toolbox)

2. **Homepage Enhancements:**
   - Add search functionality (if required)
   - Add contact information section (if required)

3. **Navigation Updates:**
   - Add parent navigation items with dropdowns
   - Link all sub-pages in navigation

---

## 5. References

- **Business Requirement Document:** `documentation/00-business-requirement/business-requirement-document.md`
- **Project Plan:** `documentation/02-project-management/01-project-plan-and-roadmap.md`
- **Task Breakdown:** `documentation/02-project-management/02-task-breakdown-structure.md`
- **Content Migration Plan:** `documentation/02-project-management/07-content-migration-plan.md`
- **Phase 4 Implementation:** `documentation/03-development/phase-04/P4.4-existing-content-pages.md`

---

**Next Review:** During Phase 5 planning and execution
