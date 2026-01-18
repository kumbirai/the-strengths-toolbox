# Performance Testing Guide

## Core Web Vitals Targets

- **LCP (Largest Contentful Paint):** < 2.5 seconds
- **FID (First Input Delay):** < 100 milliseconds
- **CLS (Cumulative Layout Shift):** < 0.1

## Testing Tools

### 1. Google PageSpeed Insights
- URL: https://pagespeed.web.dev/
- Enter your website URL
- Review Core Web Vitals scores

### 2. Chrome DevTools
- Open DevTools (F12)
- Go to Lighthouse tab
- Run Performance audit
- Review Core Web Vitals

### 3. Command Line Tool
```bash
php artisan performance:test-web-vitals
```

## Optimization Strategies

### LCP Optimization
1. Optimize hero images
2. Use WebP format
3. Set proper image dimensions
4. Minimize render-blocking resources
5. Use CDN for static assets

### FID Optimization
1. Minimize JavaScript execution
2. Code splitting
3. Defer non-critical scripts
4. Optimize event handlers

### CLS Optimization
1. Set image dimensions
2. Reserve space for ads/embeds
3. Use font-display: swap
4. Avoid inserting content above existing content
