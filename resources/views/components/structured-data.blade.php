@props(['type', 'data' => null])

@php
    $schemaService = app(\App\Services\SchemaService::class);
    
    $structuredData = match($type) {
        'organization' => $schemaService->getOrganizationSchema(),
        'website' => $schemaService->getWebSiteSchema(),
        'webpage' => $schemaService->getWebPageSchema($data),
        'article' => $schemaService->getArticleSchema($data),
        'breadcrumb' => $schemaService->getBreadcrumbSchema($data ?? []),
        default => [],
    };
@endphp

@if(!empty($structuredData))
    <script type="application/ld+json">
        {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif
