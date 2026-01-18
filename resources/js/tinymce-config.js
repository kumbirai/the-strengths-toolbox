/**
 * TinyMCE Configuration
 * 
 * This file configures TinyMCE for use in the admin panel.
 * Make sure to set TINYMCE_API_KEY in your .env file.
 */

export function initTinyMCE(selector = '#content', options = {}) {
    if (typeof tinymce === 'undefined') {
        console.error('TinyMCE is not loaded. Make sure to include the TinyMCE script.');
        return;
    }

    const defaultOptions = {
        selector: selector,
        height: 500,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help | code',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
        branding: false,
        promotion: false,
    };

    // Merge with custom options
    const config = { ...defaultOptions, ...options };

    tinymce.init(config);
}

export function destroyTinyMCE(selector = '#content') {
    if (typeof tinymce !== 'undefined') {
        tinymce.remove(selector);
    }
}
