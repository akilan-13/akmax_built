@extends('layouts/blankLayout')

@section('title', 'Employee Profile')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss',
    ])

    {{-- Map UI for employee home/current location selection --}}
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js',
    ])

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>
@endsection

@section('content')
@php
    /* --------------------------------------------------------------------------
     | Safely normalize JSON-backed staff fields for the employee portal.
     | -------------------------------------------------------------------------- */
    $decodeArray = static function ($value): array {
        if (is_array($value)) {
            return array_values($value);
        }

        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode((string) $value, true);
        return is_array($decoded) ? array_values($decoded) : [];
    };

    $languageIds = $decodeArray($staff->languages ?? null);
    $hobbyIds = $decodeArray($staff->hobby ?? null);
    $courseTagIds = $decodeArray($staff->course_tag ?? null);

    /*
     * Community/Caste are master-data fields.
     * The employeeProfile controller must pass communityList to this view.
     * Keep the Blade defensive so a missing master collection never breaks
     * rendering and the employee's saved ID is preserved.
     */
    $communityOptionsData = collect($communityList ?? [])->map(static function ($item) {
        return [
            'id' => $item->sno ?? null,
            'name' => trim(
                (($item->community_code ?? '') !== '' ? $item->community_code . ' - ' : '')
                . ($item->community_name ?? '')
            ),
        ];
    })->filter(static fn ($item) => !empty($item['id']))->values()->toArray();

    $savedCommunityId = (string) ($staff->community_id ?? '');
    $communityIds = array_map(
        static fn ($item) => (string) ($item['id'] ?? ''),
        $communityOptionsData
    );
    $savedCommunityExists = $savedCommunityId !== ''
        && in_array($savedCommunityId, $communityIds, true);

    $courseTagText = implode(
        ', ',
        array_map(
            static fn ($value) => (string) $value,
            $courseTagIds
        )
    );

    $socialDetails = [];
    if (!empty($staff->social_media_details)) {
        $decodedSocial = json_decode((string) $staff->social_media_details, true);
        $socialDetails = is_array($decodedSocial) ? $decodedSocial : [];
    }

    $children = [];
    if ($family && !empty($family->children_details)) {
        $decodedChildren = json_decode((string) $family->children_details, true);
        $children = is_array($decodedChildren) ? array_values($decodedChildren) : [];
    }

    $siblings = [];
    if ($family && !empty($family->siblings_detail)) {
        $decodedSiblings = json_decode((string) $family->siblings_detail, true);
        $siblings = is_array($decodedSiblings) ? array_values($decodedSiblings) : [];
    }

    $contactNames = $decodeArray($staff->contact_person_name ?? null);
    $contactRelations = $decodeArray($staff->contact_person_relation ?? null);
    $contactNumbers = $decodeArray($staff->contact_person_no ?? null);

    /* Keep complex data outside @json(...) to avoid Blade parser issues. */
    $oldContactsData = [
        'names' => $contactNames,
        'relations' => $contactRelations,
        'numbers' => $contactNumbers,
    ];

    $oldEducationData = collect($education ?? [])->map(static function ($item) {
        return [
            'id' => $item->sno ?? null,
            'qualification_type' => $item->qualification_type ?? null,
            'major' => $item->major ?? null,
            'university_name' => $item->university_name ?? null,
            'year' => $item->year ?? null,
        ];
    })->values()->toArray();

    $documentOptionsData = collect($documentTypeList ?? [])->map(static function ($item) {
        return [
            'id' => $item->sno ?? null,
            'name' => $item->document_name ?? ($item->name ?? ''),
        ];
    })->filter(static fn ($item) => !empty($item['id']))->values()->toArray();

    $qualificationOptionsData = collect($qualificationList ?? [])->map(static function ($item) {
        return [
            'id' => $item->sno ?? null,
            'name' => $item->education ?? ($item->qualification_name ?? ($item->name ?? '')),
        ];
    })->filter(static fn ($item) => !empty($item['id']) && trim((string) ($item['name'] ?? '')) !== '')->values()->toArray();

    // Keep Employee Profile aligned with Add Staff even when the profile controller
    // does not provide qualificationList to the Blade.
    if (empty($qualificationOptionsData)) {
        $qualificationOptionsData = [
            ['id' => '1', 'name' => 'UG'],
            ['id' => '2', 'name' => 'PG'],
            ['id' => '3', 'name' => 'Doctorate'],
            ['id' => '4', 'name' => 'HSC'],
            ['id' => '5', 'name' => 'SSLC'],
            ['id' => '6', 'name' => 'Below SSLC'],
            ['id' => 'Others', 'name' => 'Others'],
        ];
    }

    $relationshipOptionsData = collect($relationshipList ?? [])->map(static function ($item) {
        return [
            'id' => $item->sno ?? null,
            'name' => $item->relationship_name ?? ($item->name ?? ''),
        ];
    })->filter(static fn ($item) => !empty($item['id']))->values()->toArray();

    $completionPercent = (float) data_get($completion, 'overall.percentage', $staff->completion_percentage ?? 0);
    $completionRemaining = (int) data_get($completion, 'overall.remaining', 0);
    $isComplete = (bool) data_get($completion, 'overall.is_complete', false);
    $stepKeys = ['personal', 'family', 'contact', 'social_media', 'education'];

    $profileImagePath = null;
    $profileImageUrl = asset('assets/egc_images/auth/user_3.png');
    if (!empty($staff->staff_image)) {
        if ((int) ($staff->company_type ?? 1) === 1) {
            $profileImagePath = public_path('staff_images/Management/' . $staff->staff_image);
            $profileImageUrl = asset('staff_images/Management/' . $staff->staff_image);
        } else {
            $profileImagePath = public_path('staff_images/Buisness/' . $staff->company_id . '/' . $staff->entity_id . '/' . $staff->staff_image);
            $profileImageUrl = asset('staff_images/Buisness/' . $staff->company_id . '/' . $staff->entity_id . '/' . $staff->staff_image);
        }

        if (!$profileImagePath || !is_file($profileImagePath)) {
            $profileImageUrl = asset('assets/egc_images/auth/user_3.png');
        }
    }
@endphp

<style>
    :root {
        --eg-maroon: #b52b25;
        --eg-maroon-dark: #8f1d1d;
        --eg-gold: #f5a623;
        --eg-gold-soft: #fff4dc;
        --eg-red-soft: #fff3f1;
        --eg-bg: #f4f6f9;
        --eg-card: #ffffff;
        --eg-text: #172033;
        --eg-muted: #6b7280;
        --eg-border: #e5e7eb;
        --eg-success: #16a34a;
        --eg-danger: #dc2626;
        --eg-shadow: 0 12px 35px rgba(25, 31, 44, .08);
        --eg-radius: 18px;
    }

    .employee-profile-page {
        min-height: 100dvh;
        background:
            radial-gradient(circle at 90% -10%, rgba(245,166,35,.08), transparent 34%),
            radial-gradient(circle at -10% 10%, rgba(181,43,37,.06), transparent 30%),
            var(--eg-bg);
        padding: 20px 12px 36px;
        color: var(--eg-text);
    }

    .employee-shell {
        width: min(1220px, 100%);
        margin: 0 auto;
    }

    .portal-header {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        background: linear-gradient(135deg, var(--eg-maroon-dark), var(--eg-maroon));
        box-shadow: var(--eg-shadow);
        padding: 22px;
        color: #fff;
        margin-bottom: 16px;
    }

    .portal-header::after {
        content: '';
        position: absolute;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        right: -100px;
        top: -125px;
        border: 42px solid rgba(255,255,255,.09);
        pointer-events: none;
    }

    .brand-mark {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #fff;
        color: var(--eg-maroon);
        display: grid;
        place-items: center;
        font-weight: 900;
        font-size: 20px;
        letter-spacing: -1px;
        flex: 0 0 auto;
        box-shadow: 0 6px 18px rgba(0,0,0,.12);
    }

    .portal-kicker {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1.35px;
        text-transform: uppercase;
        opacity: .82;
    }

    .portal-title {
        font-size: clamp(22px, 4vw, 30px);
        line-height: 1.12;
        font-weight: 800;
        margin: 2px 0 4px;
    }

    .portal-subtitle {
        margin: 0;
        color: rgba(255,255,255,.86);
        font-size: 13px;
        line-height: 1.55;
        max-width: 780px;
    }

    .privacy-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid rgba(255,255,255,.2);
        background: rgba(255,255,255,.11);
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        margin-top: 12px;
        backdrop-filter: blur(8px);
    }

    .summary-card {
        border: 1px solid rgba(25,31,44,.07);
        border-radius: var(--eg-radius);
        background: rgba(255,255,255,.93);
        box-shadow: var(--eg-shadow);
        padding: 18px;
        margin-bottom: 18px;
        backdrop-filter: blur(6px);
    }

    .summary-grid {
        display: grid;
        grid-template-columns: 76px minmax(0, 1fr) auto;
        gap: 16px;
        align-items: center;
    }

    .summary-avatar {
        width: 76px;
        height: 76px;
        border-radius: 20px;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 6px 18px rgba(25,31,44,.12);
        background: #f3f4f6;
    }

    .summary-name {
        font-weight: 800;
        font-size: 20px;
        margin: 0;
        line-height: 1.2;
    }

    .summary-meta {
        margin-top: 5px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        color: var(--eg-muted);
        font-size: 12px;
    }

    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 8px;
        background: #f7f8fa;
        border: 1px solid #eceef1;
    }

    .summary-right {
        min-width: 175px;
    }

    .completion-ring {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: conic-gradient(var(--eg-maroon) calc(var(--completion) * 1%), #edf0f3 0);
        position: relative;
        margin-left: auto;
    }

    .completion-ring::after {
        content: '';
        position: absolute;
        width: 58px;
        height: 58px;
        border-radius: 50%;
        background: #fff;
    }

    .completion-ring strong {
        position: relative;
        z-index: 2;
        font-size: 15px;
        font-weight: 800;
    }

    .completion-progress {
        height: 8px;
        background: #eceff2;
        border-radius: 999px;
        overflow: hidden;
    }

    .completion-progress .progress-bar {
        background: linear-gradient(90deg, var(--eg-maroon-dark), var(--eg-maroon));
        border-radius: inherit;
        transition: width .45s ease;
    }

    .completion-note {
        font-size: 12px;
        color: var(--eg-muted);
        margin-top: 7px;
    }

    .portal-alert {
        display: none;
        border-radius: 13px;
        border: 1px solid transparent;
        margin-bottom: 14px;
        padding: 11px 13px;
        font-size: 13px;
        align-items: flex-start;
        gap: 9px;
    }

    .portal-alert.show { display: flex; }
    .portal-alert.success { background: #ecfdf3; border-color: #b7ebc8; color: #166534; }
    .portal-alert.error { background: #fff4f2; border-color: #f6c7c1; color: #991b1b; }
    .portal-alert.warning { background: #fff9e9; border-color: #f7dca0; color: #92400e; }
    .portal-alert.info { background: #eef7ff; border-color: #c8e0f7; color: #1d4ed8; }

    .portal-layout {
        display: grid;
        grid-template-columns: 295px minmax(0, 1fr);
        gap: 16px;
        align-items: start;
    }

    .step-panel {
        position: sticky;
        top: 14px;
        border: 1px solid rgba(25,31,44,.07);
        border-radius: var(--eg-radius);
        background: #fff;
        box-shadow: var(--eg-shadow);
        padding: 14px;
    }

    .step-panel-title {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 1px;
        color: var(--eg-muted);
        padding: 5px 8px 10px;
    }

    .step-list {
        display: grid;
        gap: 7px;
    }

    .profile-step {
        width: 100%;
        border: 1px solid transparent;
        border-radius: 13px;
        background: #fff;
        padding: 12px 11px;
        cursor: pointer;
        transition: .18s ease;
        text-align: left;
    }

    .profile-step:hover {
        background: #fafafa;
        border-color: #ededed;
        transform: translateY(-1px);
    }

    .profile-step.active {
        background: var(--eg-red-soft);
        border-color: rgba(181,43,37,.25);
        box-shadow: inset 3px 0 0 var(--eg-maroon);
    }

    .step-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: #f6f7f8;
        color: #475569;
        font-size: 20px;
        flex: 0 0 auto;
    }

    .profile-step.active .step-icon {
        background: #ffe5df;
        color: var(--eg-maroon);
    }

    .profile-step.done .step-icon {
        background: #eaf9ef;
        color: var(--eg-success);
    }

    .step-name {
        font-size: 13px;
        font-weight: 800;
        color: var(--eg-text);
    }

    .step-meta {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-top: 6px;
    }

    .step-mini-progress {
        height: 5px;
        background: #e9edf1;
        border-radius: 999px;
        overflow: hidden;
        flex: 1;
    }

    .step-mini-progress .progress-bar {
        background: var(--eg-gold);
        border-radius: inherit;
        transition: width .35s ease;
    }

    .step-percent {
        min-width: 34px;
        color: var(--eg-muted);
        font-size: 10px;
        font-weight: 700;
        text-align: right;
    }

    .form-panel {
        min-width: 0;
    }

    .profile-section { display: none; }
    .profile-section.active { display: block; }

    .section-card {
        border: 1px solid rgba(25,31,44,.07);
        border-radius: var(--eg-radius);
        background: #fff;
        box-shadow: var(--eg-shadow);
        overflow: visible;
    }

    .section-head {
        padding: 18px 20px;
        border-bottom: 1px solid #edf0f2;
        background: linear-gradient(180deg, #fff, #fcfcfd);
        border-top-left-radius: inherit;
        border-top-right-radius: inherit;
    }

    .section-badge {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        color: #fff;
        background: linear-gradient(135deg, var(--eg-maroon-dark), var(--eg-maroon));
        box-shadow: 0 7px 18px rgba(181,43,37,.22);
        font-size: 20px;
        flex: 0 0 auto;
    }

    .section-title {
        font-size: 19px;
        font-weight: 800;
        margin: 0 0 3px;
    }

    .section-description {
        margin: 0;
        color: var(--eg-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .section-body {
        padding: 18px;
    }

    .field-card {
        border: 1px solid #e8ebef;
        border-radius: 14px;
        padding: 14px;
        background: #fff;
        height: 100%;
    }

    .field-card.required {
        border-left: 3px solid var(--eg-maroon);
    }

    .form-label {
        font-size: 12px;
        margin-bottom: 7px;
    }

    .form-control,
    .form-select,
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        min-height: 42px;
        border-radius: 10px !important;
        border-color: #dfe4e8 !important;
        font-size: 13px;
    }

    .form-control:focus,
    .form-select:focus,
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: rgba(181,43,37,.55) !important;
        box-shadow: 0 0 0 .18rem rgba(181,43,37,.08) !important;
    }

    .select2-container { width: 100% !important; }
    .select2-container--default .select2-selection--multiple { padding: 4px 6px !important; }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        border: 0;
        background: #fbe8e4;
        color: var(--eg-maroon-dark);
        border-radius: 7px;
        font-size: 11px;
        padding: 3px 7px;
        margin-top: 3px;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field { border-radius: 8px; }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background: var(--eg-maroon); }

    .choice-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .choice-pill {
        position: relative;
    }

    .choice-pill input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .choice-pill label {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 36px;
        padding: 7px 11px;
        border: 1px solid #e0e5e9;
        background: #fff;
        border-radius: 10px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 700;
        transition: .15s ease;
    }

    .choice-pill input:checked + label {
        border-color: rgba(181,43,37,.34);
        background: var(--eg-red-soft);
        color: var(--eg-maroon-dark);
        box-shadow: inset 0 0 0 1px rgba(181,43,37,.08);
    }

    .sub-card {
        border: 1px dashed #dfe3e7;
        border-radius: 13px;
        padding: 13px;
        background: #fbfcfd;
    }

    .repeatable-row {
        position: relative;
        border: 1px solid #e8ebef;
        border-radius: 14px;
        padding: 14px;
        margin-bottom: 10px;
        background: #fff;
    }

    .repeatable-row:last-child { margin-bottom: 0; }

    .repeat-title {
        font-size: 12px;
        font-weight: 800;
        color: #374151;
    }

    .remove-btn {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: inline-grid;
        place-items: center;
        padding: 0;
    }

    .existing-doc {
        border: 1px solid #e6eaee;
        border-radius: 13px;
        padding: 13px;
        background: #fff;
        height: 100%;
    }

    .existing-doc.deleted {
        background: #fff6f4;
        border-color: #f0b7b0;
    }

    .doc-name {
        font-size: 12px;
        font-weight: 800;
        word-break: break-word;
    }

    .file-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 8px;
        background: #f7f8fa;
        border: 1px solid #eceff2;
        padding: 6px 8px;
        font-size: 10px;
        max-width: 100%;
        margin-top: 6px;
    }

    .file-chip span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 240px;
    }

    .upload-row {
        border: 1px solid #e6eaee;
        border-radius: 14px;
        padding: 13px;
        background: linear-gradient(180deg, #fff, #fcfcfd);
        margin-bottom: 10px;
    }

    .upload-status {
        margin-top: 7px;
        font-size: 11px;
        color: var(--eg-muted);
        min-height: 16px;
    }

    .upload-status.success { color: #166534; }
    .upload-status.error { color: #991b1b; }

    .locked-note {
        display: flex;
        gap: 10px;
        border: 1px dashed #cfd5db;
        border-radius: 14px;
        padding: 13px 14px;
        background: #f9fafb;
        color: var(--eg-muted);
        font-size: 12px;
        line-height: 1.5;
        margin-top: 14px;
    }

    .action-bar {
        position: sticky;
        bottom: 10px;
        z-index: 30;
        margin-top: 14px;
        border: 1px solid rgba(25,31,44,.10);
        border-radius: 16px;
        background: rgba(255,255,255,.94);
        backdrop-filter: blur(14px);
        box-shadow: 0 10px 30px rgba(25,31,44,.12);
        padding: 10px;
    }

    .action-bar .btn {
        min-height: 42px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
    }

    .btn-eg-primary {
        background: linear-gradient(135deg, var(--eg-maroon-dark), var(--eg-maroon));
        border: 0;
        color: #fff;
    }

    .btn-eg-primary:hover,
    .btn-eg-primary:focus {
        color: #fff;
        filter: brightness(.97);
    }

    .btn-eg-gold {
        background: var(--eg-gold);
        border-color: var(--eg-gold);
        color: #211400;
        font-weight: 800;
    }

    .btn-eg-gold:hover { color: #211400; filter: brightness(.97); }

    .btn-outline-eg {
        border-color: #d6dbe0;
        color: #374151;
        background: #fff;
    }

    .btn-outline-eg:hover {
        border-color: var(--eg-maroon);
        color: var(--eg-maroon);
        background: #fff;
    }

    .footer-note {
        text-align: center;
        color: #8b93a1;
        font-size: 11px;
        margin-top: 15px;
        line-height: 1.55;
    }

    .is-invalid + .select2-container .select2-selection,
    .select2-container.is-invalid .select2-selection {
        border-color: #dc3545 !important;
    }

    .field-error {
        display: none;
        color: #b91c1c;
        font-size: 10px;
        margin-top: 5px;
    }

    .field-error.show { display: block; }


    .location-map-shell {
        position: relative;
        overflow: hidden;
        border: 1px solid #e2e7eb;
        border-radius: 14px;
        background: #eef2f5;
    }

    .employee-location-map {
        width: 100%;
        height: 330px;
        min-height: 260px;
        z-index: 1;
    }

    .location-map-overlay {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 400;
        padding: 7px 10px;
        border: 1px solid rgba(255,255,255,.75);
        border-radius: 999px;
        background: rgba(255,255,255,.92);
        box-shadow: 0 5px 16px rgba(25,31,44,.11);
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        pointer-events: none;
    }

    .location-selected-panel {
        border: 1px dashed #d8dde2;
        border-radius: 12px;
        padding: 10px 11px;
        background: #fafbfc;
    }

    .location-status-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        background: #fff1ee;
        color: var(--eg-maroon);
        font-size: 18px;
    }

    .location-search-btn {
        min-width: 88px;
    }

    .leaflet-container {
        font-family: inherit;
    }

    .leaflet-control-attribution {
        font-size: 8px !important;
    }

    @media (max-width: 767.98px) {
        .employee-location-map {
            height: 300px;
            min-height: 240px;
        }

        .location-map-overlay {
            top: 8px;
            left: 8px;
            font-size: 9px;
            padding: 6px 8px;
        }
    }

    @media (max-width: 991.98px) {
        .portal-layout { grid-template-columns: 1fr; }
        .step-panel { position: static; padding: 10px; }
        .step-list {
            display: flex;
            overflow-x: auto;
            gap: 8px;
            scrollbar-width: thin;
            padding-bottom: 2px;
        }
        .profile-step {
            min-width: 220px;
            max-width: 250px;
        }
    }

    @media (max-width: 767.98px) {
        .employee-profile-page { padding: 8px 6px calc(26px + env(safe-area-inset-bottom)); }
        .portal-header { border-radius: 18px; padding: 17px 15px; }
        .summary-card { padding: 13px; border-radius: 15px; }
        .summary-grid { grid-template-columns: 58px minmax(0, 1fr); gap: 11px; }
        .summary-avatar { width: 58px; height: 58px; border-radius: 16px; }
        .summary-name { font-size: 17px; }
        .summary-right { grid-column: 1 / -1; min-width: 0; }
        .completion-ring { margin: 0; width: 58px; height: 58px; }
        .completion-ring::after { width: 44px; height: 44px; }
        .completion-ring strong { font-size: 12px; }
        .section-head { padding: 14px; }
        .section-body { padding: 12px; }
        .field-card { padding: 11px; border-radius: 12px; }
        .action-bar { bottom: 6px; padding: 8px; }
        .action-bar .btn { flex: 1 1 0; }
        .profile-step { min-width: 205px; }
        .step-panel-title { padding: 4px 6px 8px; }
    }

    @media (max-width: 420px) {
        .summary-meta { display: grid; grid-template-columns: 1fr 1fr; }
        .profile-step { min-width: 188px; }
        .section-title { font-size: 17px; }
        .action-bar .btn { padding-left: 10px; padding-right: 10px; }
    }

    /* ================================================================
       Production mobile-first responsive hardening
       ================================================================ */
    html, body { max-width: 100%; overflow-x: hidden !important; }
    .employee-profile-page,
    .employee-shell,
    .portal-layout,
    .form-panel,
    .section-card,
    .section-body { min-width: 0; max-width: 100%; width: 100%; }
    .employee-profile-page { overflow-x: clip; }
    .portal-header, .summary-card, .section-card, .field-card, .sub-card,
    .repeatable-row, .existing-doc, .upload-row, .location-card {
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .form-control, .form-select, .select2-container,
    .select2-container .select2-selection--single,
    .select2-container .select2-selection--multiple { max-width: 100%; }
    .select2-container--default .select2-selection--multiple { height: auto; min-height: 42px; }
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        display: flex; flex-wrap: wrap; gap: 2px; margin: 0; padding: 0 2px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        max-width: calc(100% - 8px); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .location-map-shell, .employee-location-map { width: 100%; max-width: 100%; }
    .leaflet-container { touch-action: pan-x pan-y; }

    @media (max-width: 991.98px) {
        .employee-profile-page { padding-left: 10px; padding-right: 10px; }
        .portal-layout { display: block; }
        .step-panel { position: static; top: auto; margin-bottom: 12px; padding: 10px; }
        .step-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            overflow: visible;
            padding-bottom: 0;
        }
        .profile-step { min-width: 0; max-width: none; width: 100%; }
    }

    @media (max-width: 767.98px) {
        .employee-profile-page { padding: 6px 5px calc(94px + env(safe-area-inset-bottom)); }
        .portal-header { padding: 14px 13px; border-radius: 16px; margin-bottom: 10px; }
        .portal-header > div { gap: 10px !important; }
        .brand-mark { width: 42px; height: 42px; min-width: 42px; border-radius: 12px; font-size: 17px; }
        .portal-kicker { font-size: 8px; letter-spacing: .9px; }
        .portal-title { font-size: 20px; line-height: 1.16; }
        .portal-subtitle { font-size: 11px; line-height: 1.45; }
        .privacy-pill { width: 100%; justify-content: center; text-align: center; font-size: 10px; line-height: 1.3; }

        .summary-card { padding: 11px; margin-bottom: 10px; border-radius: 14px; }
        .summary-grid { grid-template-columns: 52px minmax(0, 1fr); gap: 9px; }
        .summary-avatar { width: 52px; height: 52px; border-radius: 14px; }
        .summary-name { font-size: 16px; max-width: 100%; }
        .summary-meta { gap: 5px; margin-top: 4px; }
        .meta-chip { min-width: 0; max-width: 100%; padding: 4px 6px; font-size: 9px; }
        .summary-right { grid-column: 1 / -1; min-width: 0; width: 100%; }
        .completion-ring { width: 52px; height: 52px; }
        .completion-ring::after { width: 38px; height: 38px; }
        .completion-ring strong { font-size: 10px; }

        .step-panel { border-radius: 14px; padding: 8px; margin-bottom: 10px; }
        .step-panel-title { padding: 3px 4px 7px; font-size: 9px; }
        .step-list { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; }
        .profile-step { padding: 8px; border-radius: 11px; }
        .profile-step > .d-flex { gap: 7px !important; }
        .step-icon { width: 32px; height: 32px; min-width: 32px; border-radius: 9px; font-size: 15px; }
        .step-name { font-size: 10px; }
        .step-meta { margin-top: 4px; gap: 4px; }
        .step-mini-progress { height: 4px; }
        .step-percent { min-width: 27px; font-size: 8px; }

        .section-card { border-radius: 14px; }
        .section-head { padding: 12px; }
        .section-head > .d-flex { align-items: flex-start !important; gap: 9px !important; }
        .section-badge { width: 36px; height: 36px; min-width: 36px; border-radius: 10px; font-size: 17px; }
        .section-title { font-size: 16px; line-height: 1.25; }
        .section-description { font-size: 10px; line-height: 1.45; }
        .section-body { padding: 10px; }
        .row.g-3, .row.g-2 { --bs-gutter-x: .6rem; --bs-gutter-y: .6rem; }
        .field-card, .sub-card, .repeatable-row, .existing-doc, .upload-row { padding: 9px; border-radius: 11px; }
        .form-label { font-size: 10px; margin-bottom: 5px; }
        .form-control, .form-select,
        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple { min-height: 40px; font-size: 12px; }
        textarea.form-control { min-height: 78px; }

        .choice-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; }
        .choice-pill, .choice-pill label { width: 100%; }
        .choice-pill label { justify-content: center; min-height: 36px; padding: 7px 8px; font-size: 10px; }

        .location-card .d-flex.flex-wrap { width: 100%; }
        .location-card .d-flex.flex-wrap > .btn { flex: 1 1 0; min-width: 0; }
        .employee-location-map { height: 260px !important; min-height: 220px; }
        .location-map-overlay { max-width: calc(100% - 16px); font-size: 8px; }
        .location-search-btn { width: 100%; min-width: 0; min-height: 40px; }
        .location-selected-panel { padding: 8px; }

        .action-bar { position: sticky; bottom: max(5px, env(safe-area-inset-bottom)); margin-top: 10px; padding: 7px; border-radius: 13px; }
        .action-bar > .d-flex { gap: 6px !important; }
        .action-bar .btn { min-height: 40px; padding: 8px 9px; font-size: 10px; white-space: nowrap; }
        #saveStatusText { display: none !important; }
        .footer-note { font-size: 9px; padding: 0 4px; }
    }

    @media (max-width: 420px) {
        .step-list { grid-template-columns: 1fr 1fr; }
        .profile-step { padding: 7px; }
        .step-icon { width: 30px; height: 30px; min-width: 30px; font-size: 14px; }
        .step-name { font-size: 9px; }
        .section-body { padding: 8px; }
        .field-card { padding: 8px; }
        .employee-location-map { height: 235px !important; }
        .action-bar .btn { padding-left: 7px; padding-right: 7px; }
    }

    @media (max-width: 360px) {
        .step-list { grid-template-columns: 1fr; }
    }

</style>

<div class="employee-profile-page">
    <div class="employee-shell">
        <header class="portal-header">
            <div class="d-flex align-items-start gap-3 position-relative" style="z-index:2">
                <div class="brand-mark" aria-hidden="true">EG</div>
                <div class="flex-grow-1">
                    <div class="portal-kicker">ELYSIUM GROUPS · EMPLOYEE PORTAL</div>
                    <h1 class="portal-title">Complete your employee profile</h1>
                    <p class="portal-subtitle">
                        Review your personal information, family details, emergency contacts, social profiles, education and documents.
                        Your secure link is designed to work comfortably on mobile, tablet and desktop.
                    </p>
                    <span class="privacy-pill">
                        <i class="mdi mdi-shield-check-outline"></i>
                        HR-controlled employment & salary information is protected
                    </span>
                </div>
            </div>
        </header>

        <section class="summary-card" aria-label="Employee summary">
            <div class="summary-grid">
                <img src="{{ $profileImageUrl }}" alt="Employee profile" class="summary-avatar">
                <div class="min-w-0">
                    <h2 class="summary-name text-truncate">{{ $staff->staff_name ?? 'Employee' }}</h2>
                    <div class="summary-meta">
                        @if(!empty($staff->employee_id ?? null))
                            <span class="meta-chip"><i class="mdi mdi-badge-account-outline"></i>{{ $staff->employee_id }}</span>
                        @endif
                        @if(!empty($staff->email_id ?? null))
                            <span class="meta-chip"><i class="mdi mdi-email-outline"></i>{{ $staff->email_id }}</span>
                        @endif
                        @if(!empty($staff->mobile_no ?? null))
                            <span class="meta-chip"><i class="mdi mdi-phone-outline"></i>{{ $staff->mobile_no }}</span>
                        @endif
                    </div>
                </div>
                <div class="summary-right">
                    <div class="d-flex align-items-center gap-3">
                        <div class="completion-ring" style="--completion: {{ max(0, min(100, $completionPercent)) }}">
                            <strong id="completionRingText">{{ number_format($completionPercent, 0) }}%</strong>
                        </div>
                        <div class="flex-grow-1 d-none d-md-block">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-bold">Profile completion</span>
                                <span id="completionLabel">{{ number_format($completionPercent, 0) }}%</span>
                            </div>
                            <div class="completion-progress">
                                <div id="completionBar" class="progress-bar" role="progressbar" style="width: {{ max(0, min(100, $completionPercent)) }}%"></div>
                            </div>
                            <div class="completion-note" id="completionNote">
                                {{ $isComplete ? 'Everything required is complete.' : $completionRemaining . ' item(s) remaining.' }}
                            </div>
                        </div>
                    </div>
                    <div class="d-md-none mt-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-bold">Completion</span>
                            <span id="completionLabelMobile">{{ number_format($completionPercent, 0) }}%</span>
                        </div>
                        <div class="completion-progress"><div id="completionBarMobile" class="progress-bar" style="width: {{ max(0, min(100, $completionPercent)) }}%"></div></div>
                    </div>
                </div>
            </div>
        </section>

        <div id="portalAlert" class="portal-alert" role="status" aria-live="polite"></div>

        <div class="portal-layout">
            <aside class="step-panel" aria-label="Profile steps">
                <div class="step-panel-title">Profile sections</div>
                <div class="step-list" id="stepList">
                    @php
                        $stepList = [
                            1 => ['Personal Details', 'mdi-account-outline'],
                            2 => ['Family Details', 'mdi-account-group-outline'],
                            3 => ['Contact Details', 'mdi-phone-outline'],
                            4 => ['Social Media', 'mdi-share-variant-outline'],
                            5 => ['Education & Documents', 'mdi-school-outline'],
                        ];
                    @endphp
                    @foreach($stepList as $number => [$name, $icon])
                        @php $stepPct = (float) data_get($completion, 'steps.' . $stepKeys[$number - 1] . '.percentage', 0); @endphp
                        <button type="button" class="profile-step {{ $number === 1 ? 'active' : '' }} {{ $stepPct >= 100 ? 'done' : '' }}" data-go-step="{{ $number }}" id="stepCard{{ $number }}">
                            <span class="d-flex align-items-center gap-3">
                                <span class="step-icon"><i class="mdi {{ $icon }}"></i></span>
                                <span class="flex-grow-1 min-w-0">
                                    <span class="d-flex align-items-center justify-content-between gap-2">
                                        <span class="step-name text-truncate">{{ $name }}</span>
                                        <i class="mdi mdi-chevron-right text-muted"></i>
                                    </span>
                                    <span class="step-meta">
                                        <span class="step-mini-progress"><span class="progress-bar" data-step-progress="{{ $number }}" style="width: {{ max(0, min(100, $stepPct)) }}%"></span></span>
                                        <span class="step-percent" data-step-label="{{ $number }}">{{ number_format($stepPct, 0) }}%</span>
                                    </span>
                                </span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </aside>

            <main class="form-panel">
                <form id="employeeProfileForm"
                      method="POST"
                      action="{{ route('employee.profile.update', ['token' => $token]) }}"
                      enctype="multipart/form-data"
                      novalidate>
                    @csrf
                    <input type="hidden" name="stage" id="currentStage" value="1">
                    <input type="hidden" name="edit_id" value="{{ $staff->sno }}">

                    {{-- IMPORTANT: JSON-backed multi-select fields expected by employeeProfileUpdate(). --}}
                    <input type="hidden" name="languages" id="languagesPayload" value="{{ e(json_encode($languageIds)) }}">
                    <input type="hidden" name="hobby" id="hobbyPayload" value="{{ e(json_encode($hobbyIds)) }}">
                    {{-- Always present the social_media array so clearing all social links works. --}}
                    <input type="hidden" name="social_media[__empty]" value="">

                    {{-- STEP 1 --}}
                    <section class="profile-section active" data-section="1">
                        <div class="section-card">
                            <div class="section-head">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="section-badge"><i class="mdi mdi-account-outline"></i></div>
                                    <div>
                                        <h3 class="section-title">Personal Details</h3>
                                        <p class="section-description">Please keep your contact and identification information accurate.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="section-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="field-card">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ $profileImageUrl }}" alt="Profile" class="rounded-circle" style="width:58px;height:58px;object-fit:cover;border:3px solid #fff;box-shadow:0 5px 14px rgba(25,31,44,.11)">
                                                <div>
                                                    <div class="fw-bold" style="font-size:13px">Profile photo</div>
                                                    <div class="text-muted" style="font-size:11px">Your profile photo is managed by HR and cannot be changed from the shared employee link.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card required">
                                            <label class="form-label fw-bold">Staff Name <span class="text-danger">*</span></label>
                                            <input class="form-control" name="staff_name" value="{{ $staff->staff_name ?? '' }}" maxlength="255" required autocomplete="name">
                                            <div class="field-error" data-error-for="staff_name"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card required">
                                            <label class="form-label fw-bold">Mobile Number <span class="text-danger">*</span></label>
                                            <input class="form-control" name="mobile_no" value="{{ $staff->mobile_no ?? '' }}" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" required autocomplete="tel">
                                            <div class="field-error" data-error-for="mobile_no"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card">
                                            <label class="form-label fw-bold">Alternative Number <span class="text-muted fw-normal">Optional</span></label>
                                            <input class="form-control" name="alternative_no" value="{{ $staff->alternative_no ?? '' }}" inputmode="numeric" maxlength="15" autocomplete="tel">
                                            <div class="field-error" data-error-for="alternative_no"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card required">
                                            <label class="form-label fw-bold">Email ID <span class="text-danger">*</span></label>
                                            <input class="form-control" type="email" name="email_id" value="{{ $staff->email_id ?? '' }}" maxlength="255" required autocomplete="email">
                                            <div class="field-error" data-error-for="email_id"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card required">
                                            <label class="form-label fw-bold">Gender <span class="text-danger">*</span></label>
                                            <div class="choice-row">
                                                @foreach([1 => 'Male', 2 => 'Female', 3 => 'Others'] as $value => $label)
                                                    <span class="choice-pill">
                                                        <input id="gender_{{ $value }}" type="radio" name="gender" value="{{ $value }}" {{ (string) ($staff->gender ?? '') === (string) $value ? 'checked' : '' }} required>
                                                        <label for="gender_{{ $value }}"><i class="mdi mdi-gender-{{ $value === 1 ? 'male' : ($value === 2 ? 'female' : 'non-binary') }}"></i>{{ $label }}</label>
                                                    </span>
                                                @endforeach
                                            </div>
                                            <div class="field-error" data-error-for="gender"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card required">
                                            <label class="form-label fw-bold">Date of Birth <span class="text-danger">*</span></label>
                                            <input class="form-control" type="date" name="dob" value="{{ $staff->dob ?? '' }}" required autocomplete="bday">
                                            <div class="field-error" data-error-for="dob"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card">
                                            <label class="form-label fw-bold">Birth Place <span class="text-muted fw-normal">Optional</span></label>
                                            <input class="form-control" name="birth_place" value="{{ $staff->birth_place ?? '' }}" maxlength="255" autocomplete="address-level2">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="field-card">
                                            <label class="form-label fw-bold">Height (cm)</label>
                                            <input class="form-control" type="number" step="0.1" min="0" max="300" name="height" value="{{ $staff->height ?? '' }}" inputmode="decimal">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="field-card">
                                            <label class="form-label fw-bold">Weight (kg)</label>
                                            <input class="form-control" type="number" step="0.1" min="0" max="500" name="weight" value="{{ $staff->weight ?? '' }}" inputmode="decimal">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card required">
                                            <label class="form-label fw-bold">Mother Tongue <span class="text-danger">*</span></label>
                                            <select class="form-select select3" name="mother_tongue" data-placeholder="Select mother tongue" required>
                                                <option value=""></option>
                                                @foreach(($languageList ?? []) as $language)
                                                    <option value="{{ $language->sno }}" {{ (string) ($language->sno ?? '') === (string) ($staff->mother_tongue ?? '') ? 'selected' : '' }}>{{ $language->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="field-error" data-error-for="mother_tongue"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card required">
                                            <label class="form-label fw-bold">Languages Known <span class="text-danger">*</span></label>
                                            <select class="form-select select3" id="languagesUi" multiple data-placeholder="Choose languages">
                                                @foreach(($languageList ?? []) as $language)
                                                    <option value="{{ $language->sno }}" {{ in_array((string) $language->sno, array_map('strval', $languageIds), true) ? 'selected' : '' }}>{{ $language->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="field-error" data-error-for="languages"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card">
                                            <label class="form-label fw-bold">Hobbies <span class="text-muted fw-normal">Optional</span></label>
                                            <select class="form-select select3" id="hobbyUi" multiple data-placeholder="Choose hobbies">
                                                @foreach(($hobbyList ?? []) as $hobby)
                                                    <option value="{{ $hobby->sno }}" {{ in_array((string) $hobby->sno, array_map('strval', $hobbyIds), true) ? 'selected' : '' }}>{{ $hobby->hobby_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card required">
                                            <label class="form-label fw-bold">Blood Group <span class="text-danger">*</span></label>
                                            <select class="form-select select3" name="blood_group" data-placeholder="Select blood group" required>
                                                <option value=""></option>
                                                @foreach(($bloodGroupList ?? []) as $blood)
                                                    <option value="{{ $blood->sno }}" {{ (string) $blood->sno === (string) ($staff->blood_group ?? '') ? 'selected' : '' }}>{{ $blood->blood_group }}</option>
                                                @endforeach
                                            </select>
                                            <div class="field-error" data-error-for="blood_group"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card required">
                                            <label class="form-label fw-bold">Nationality <span class="text-danger">*</span></label>
                                            <select class="form-select select3" name="nationality_id" data-placeholder="Select nationality" required>
                                                <option value=""></option>
                                                @foreach(($nationalityList ?? []) as $item)
                                                    <option value="{{ $item->sno }}" {{ (string) $item->sno === (string) ($staff->nationality_id ?? '') ? 'selected' : '' }}>{{ $item->nationality_name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="field-error" data-error-for="nationality_id"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card required">
                                            <label class="form-label fw-bold">Religion <span class="text-danger">*</span></label>
                                            <select class="form-select select3" name="religion_id" data-placeholder="Select religion" required>
                                                <option value=""></option>
                                                @foreach(($religionList ?? []) as $item)
                                                    <option value="{{ $item->sno }}" {{ (string) $item->sno === (string) ($staff->religion_id ?? '') ? 'selected' : '' }}>{{ $item->religion_name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="field-error" data-error-for="religion_id"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card">
                                            <label class="form-label fw-bold">
                                                Community
                                                <span class="text-danger">*</span>
                                            </label>

                                            <select
                                                class="form-select select3"
                                                name="community_id"
                                                id="communityIdUi"
                                                data-placeholder="Select community"
                                                required
                                            >
                                                <option value=""></option>

                                                @foreach($communityOptionsData as $community)
                                                    <option
                                                        value="{{ $community['id'] }}"
                                                        {{ (string) ($staff->community_id ?? '') === (string) $community['id'] ? 'selected' : '' }}
                                                    >
                                                        {{ $community['name'] }}
                                                    </option>
                                                @endforeach

                                                @if($savedCommunityId !== '' && !$savedCommunityExists)
                                                    <option value="{{ $savedCommunityId }}" selected>
                                                        Saved community #{{ $savedCommunityId }}
                                                    </option>
                                                @endif
                                            </select>

                                            <div
                                                class="field-error"
                                                data-error-for="community_id"
                                            ></div>

                                            <div class="form-text">
                                                Select your community from the HR master list.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card">
                                            <label class="form-label fw-bold">
                                                Caste
                                                <span class="text-muted fw-normal">Optional</span>
                                            </label>

                                            <select
                                                class="form-select select3"
                                                name="caste_id"
                                                id="casteIdUi"
                                                data-saved-value="{{ $staff->caste_id ?? '' }}"
                                                data-placeholder="Select caste"
                                            >
                                                <option value=""></option>

                                                @if(!empty($staff->caste_id))
                                                    <option value="{{ $staff->caste_id }}" selected>
                                                        Saved caste #{{ $staff->caste_id }}
                                                    </option>
                                                @endif
                                            </select>

                                            <div
                                                class="form-text"
                                                id="casteHelpText"
                                            >
                                                Caste options are filtered by religion and community.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="field-card">
                                            <label class="form-label fw-bold">Identification Mark <span class="text-muted fw-normal">Optional</span></label>
                                            <textarea class="form-control" name="identification_mark" rows="2" maxlength="1000">{{ $staff->identification_mark ?? '' }}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="field-card">
                                            <div class="d-flex align-items-center justify-content-between gap-3">
                                                <div>
                                                    <div class="fw-bold" style="font-size:13px">Vehicle Information</div>
                                                    <div class="text-muted" style="font-size:11px">Turn this on only if you currently have a vehicle / driving licence to record.</div>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" name="vehicle_check" value="1" id="vehicleCheck" {{ (int) ($staff->vehicle_check ?? 0) === 1 ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                            <div id="vehicleFields" class="row g-2 mt-2 {{ (int) ($staff->vehicle_check ?? 0) === 1 ? '' : 'd-none' }}">
                                                <div class="col-md-4"><input class="form-control" name="driving_license_no" value="{{ $staff->driving_license_no ?? '' }}" placeholder="Driving Licence No"></div>
                                                <div class="col-md-4"><input class="form-control" name="vehicle_register_no" value="{{ $staff->vehicle_register_no ?? '' }}" placeholder="Vehicle Registration No"></div>
                                                <div class="col-md-4"><input class="form-control" type="date" name="license_expiry" value="{{ $staff->license_expiry ?? '' }}"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- STEP 2 --}}
                    <section class="profile-section" data-section="2">
                        <div class="section-card">
                            <div class="section-head">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="section-badge"><i class="mdi mdi-account-group-outline"></i></div>
                                    <div>
                                        <h3 class="section-title">Family Details</h3>
                                        <p class="section-description">Add family details relevant to your current situation. Optional areas stay hidden until you need them.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="section-body">
                                <div class="row g-3">
                                    @foreach([
                                        ['father_name','Father Name',$family->father_name ?? '',true],
                                        ['father_occup','Father Occupation',$family->father_occup ?? '',true],
                                        ['mother_name','Mother Name',$family->mother_name ?? '',true],
                                        ['mother_occup','Mother Occupation',$family->mother_occup ?? '',true]
                                    ] as [$field,$label,$value,$required])
                                        <div class="col-md-6">
                                            <div class="field-card {{ $required ? 'required' : '' }}">
                                                <label class="form-label fw-bold">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
                                                <input class="form-control" name="{{ $field }}" value="{{ $value }}" maxlength="255" {{ $required ? 'required' : '' }}>
                                                <div class="field-error" data-error-for="{{ $field }}"></div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="col-md-6">
                                        <div class="field-card required">
                                            <label class="form-label fw-bold">Marital Status <span class="text-danger">*</span></label>
                                            <select class="form-select select3" id="maritalStatus" name="marital_status" data-placeholder="Select marital status" required>
                                                <option value=""></option>
                                                <option value="1" {{ (string) ($family->marital_status ?? $staff->martial_status ?? '') === '1' ? 'selected' : '' }}>Married</option>
                                                <option value="2" {{ (string) ($family->marital_status ?? $staff->martial_status ?? '') === '2' ? 'selected' : '' }}>Unmarried</option>
                                            </select>
                                            {{-- Backend staff column is `martial_status`; family column is `marital_status`. --}}
                                            <input type="hidden" name="martial_status" id="martialStatusPayload" value="{{ $staff->martial_status ?? '' }}">
                                            <div class="field-error" data-error-for="marital_status"></div>
                                        </div>
                                    </div>

                                    <div id="spouseFields" class="col-12 {{ (string) ($family->marital_status ?? $staff->martial_status ?? '') === '1' ? '' : 'd-none' }}">
                                        <div class="sub-card">
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <i class="mdi mdi-ring text-danger fs-5"></i>
                                                <div>
                                                    <div class="fw-bold" style="font-size:13px">Spouse Information</div>
                                                    <div class="text-muted" style="font-size:11px">Complete the fields that apply to you.</div>
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-4"><label class="form-label fw-semibold">Anniversary Date</label><input class="form-control" type="date" name="anniversary_date" value="{{ $family->anniversary_date ?? '' }}"></div>
                                                <div class="col-md-4"><label class="form-label fw-semibold">Spouse Name</label><input class="form-control" name="spouse_name" value="{{ $family->spouse_name ?? '' }}"></div>
                                                <div class="col-md-4"><label class="form-label fw-semibold">Spouse Mobile</label><input class="form-control" name="spouse_mobile" value="{{ $family->spouse_mobile ?? '' }}" maxlength="15" inputmode="numeric"></div>
                                                <div class="col-md-4"><label class="form-label fw-semibold">Spouse DOB</label><input class="form-control" type="date" name="spouse_dob" value="{{ $family->spouse_dob ?? '' }}"></div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Spouse Working?</label>
                                                    <div class="choice-row">
                                                        @foreach(['Yes','No'] as $workValue)
                                                            <span class="choice-pill">
                                                                <input id="spouseWorking_{{ strtolower($workValue) }}" type="radio" name="spouse_working" value="{{ $workValue }}" {{ (($family->spouse_working ?? 'No') === $workValue) ? 'checked' : '' }}>
                                                                <label for="spouseWorking_{{ strtolower($workValue) }}">{{ $workValue }}</label>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="col-md-4 spouse-working-fields {{ ($family->spouse_working ?? '') === 'Yes' ? '' : 'd-none' }}"><label class="form-label fw-semibold">Spouse Designation</label><input class="form-control" name="spouse_designation" value="{{ $family->spouse_designation ?? '' }}"></div>
                                                <div class="col-md-4 spouse-working-fields {{ ($family->spouse_working ?? '') === 'Yes' ? '' : 'd-none' }}"><label class="form-label fw-semibold">Spouse Company</label><input class="form-control" name="spouse_company_name" value="{{ $family->spouse_company_name ?? '' }}"></div>
                                                <div class="col-md-4 spouse-working-fields {{ ($family->spouse_working ?? '') === 'Yes' ? '' : 'd-none' }}"><label class="form-label fw-semibold">Spouse Salary</label><input class="form-control" type="number" step="0.01" min="0" name="spouse_salary" value="{{ $family->spouse_salary ?? '' }}" inputmode="decimal"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card">
                                            <label class="form-label fw-bold">Do you have children?</label>
                                            <div class="choice-row">
                                                @foreach(['Yes','No'] as $value)
                                                    <span class="choice-pill">
                                                        <input id="children_{{ strtolower($value) }}" type="radio" name="has_children" value="{{ $value }}" {{ (($family->has_children ?? 'No') === $value) ? 'checked' : '' }}>
                                                        <label for="children_{{ strtolower($value) }}">{{ $value }}</label>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div id="childrenCountWrap" class="col-md-6 {{ ($family->has_children ?? 'No') === 'Yes' ? '' : 'd-none' }}">
                                        <div class="field-card">
                                            <label class="form-label fw-bold">Children Count</label>
                                            <input class="form-control" id="childrenCount" name="children_count" type="number" min="0" max="9" value="{{ $family->children_count ?? count($children) }}" inputmode="numeric">
                                        </div>
                                    </div>

                                    <div id="childrenDetails" class="col-12 {{ ($family->has_children ?? 'No') === 'Yes' ? '' : 'd-none' }}"></div>

                                    <div class="col-md-6">
                                        <div class="field-card">
                                            <label class="form-label fw-bold">Do you have siblings?</label>
                                            <div class="choice-row">
                                                @foreach(['Yes','No'] as $value)
                                                    <span class="choice-pill">
                                                        <input id="siblings_{{ strtolower($value) }}" type="radio" name="has_siblings" value="{{ $value }}" {{ (($family->has_siblings ?? 'No') === $value) ? 'checked' : '' }}>
                                                        <label for="siblings_{{ strtolower($value) }}">{{ $value }}</label>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div id="siblingsCountWrap" class="col-md-6 {{ ($family->has_siblings ?? 'No') === 'Yes' ? '' : 'd-none' }}">
                                        <div class="field-card">
                                            <label class="form-label fw-bold">Siblings Count</label>
                                            <input class="form-control" id="siblingsCount" name="siblings_count" type="number" min="0" max="9" value="{{ $family->sibling_count ?? count($siblings) }}" inputmode="numeric">
                                        </div>
                                    </div>

                                    <div id="siblingDetails" class="col-12 {{ ($family->has_siblings ?? 'No') === 'Yes' ? '' : 'd-none' }}"></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- STEP 3 --}}
                    <section class="profile-section" data-section="3">
                        <div class="section-card">
                            <div class="section-head">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="section-badge"><i class="mdi mdi-phone-outline"></i></div>
                                    <div>
                                        <h3 class="section-title">Contact Details</h3>
                                        <p class="section-description">Keep your address and emergency contacts up to date so HR can reach the right person when needed.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="section-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="field-card required">
                                            <label class="form-label fw-bold">Permanent Address <span class="text-danger">*</span></label>
                                            <textarea
                                                class="form-control"
                                                rows="4"
                                                name="address"
                                                id="employeeAddress"
                                                maxlength="2000"
                                                required
                                                autocomplete="street-address"
                                            >{{ $staff->address ?? '' }}</textarea>

                                            {{-- Backward compatibility with the validator used by employeeProfileUpdate(). --}}
                                            <input
                                                type="hidden"
                                                name="permanent_address"
                                                id="permanentAddressPayload"
                                                value="{{ $staff->address ?? '' }}"
                                            >

                                            <div
                                                class="field-error"
                                                data-error-for="address"
                                            ></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="field-card">
                                            <label class="form-label fw-bold">Residential Address <span class="text-muted fw-normal">Optional</span></label>
                                            <textarea class="form-control" rows="4" name="residential_address" maxlength="2000">{{ $staff->residential_address ?? '' }}</textarea>
                                            <div class="form-text" style="font-size:10px">Use this when your current residence differs from the permanent address.</div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="field-card location-card">
                                            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                                                <div>
                                                    <div class="fw-bold" style="font-size:13px">
                                                        Home / Current Location
                                                        <span class="text-muted fw-normal">Optional</span>
                                                    </div>
                                                    <div class="text-muted" style="font-size:11px">
                                                        Use your current GPS position or tap anywhere on the map to choose a custom home location.
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-wrap gap-2">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-eg-primary"
                                                        id="useMyLocation"
                                                    >
                                                        <i class="mdi mdi-crosshairs-gps me-1"></i>
                                                        Use My Location
                                                    </button>

                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-eg"
                                                        id="clearLocation"
                                                    >
                                                        <i class="mdi mdi-map-marker-off-outline me-1"></i>
                                                        Clear
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="location-map-shell">
                                                <div
                                                    id="employeeLocationMap"
                                                    class="employee-location-map"
                                                    aria-label="Choose employee home location on map"
                                                ></div>

                                                <div class="location-map-overlay">
                                                    <span>
                                                        <i class="mdi mdi-map-marker-radius-outline me-1"></i>
                                                        Tap the map to set location
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="location-selected-panel mt-3">
                                                <div class="d-flex align-items-start gap-2">
                                                    <div class="location-status-icon">
                                                        <i class="mdi mdi-map-marker-check-outline"></i>
                                                    </div>

                                                    <div class="flex-grow-1 min-w-0">
                                                        <div class="fw-bold" style="font-size:12px">
                                                            Selected location
                                                        </div>

                                                        <div
                                                            id="locationSummary"
                                                            class="text-muted"
                                                            style="font-size:11px"
                                                        >
                                                            No location selected yet.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Values actually accepted by employeeProfileUpdate(). --}}
                                            <input
                                                type="hidden"
                                                name="location_url"
                                                id="locationUrl"
                                                value="{{ $staff->location_url ?? '' }}"
                                            >

                                            <input
                                                type="hidden"
                                                name="latitude"
                                                id="locationLatitude"
                                                value="{{ $staff->latitude ?? '' }}"
                                            >

                                            <input
                                                type="hidden"
                                                name="longitude"
                                                id="locationLongitude"
                                                value="{{ $staff->longitude ?? '' }}"
                                            >

                                            <div class="row g-2 mt-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold mb-1">
                                                        Latitude
                                                    </label>

                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        id="locationLatitudeDisplay"
                                                        value="{{ $staff->latitude ?? '' }}"
                                                        placeholder="Latitude"
                                                        readonly
                                                    >
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold mb-1">
                                                        Longitude
                                                    </label>

                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        id="locationLongitudeDisplay"
                                                        value="{{ $staff->longitude ?? '' }}"
                                                        placeholder="Longitude"
                                                        readonly
                                                    >
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column flex-sm-row gap-2 mt-3">
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="locationSearch"
                                                    placeholder="Search a place or area to move the map"
                                                    autocomplete="off"
                                                >

                                                <button
                                                    type="button"
                                                    class="btn btn-outline-eg location-search-btn"
                                                    id="searchLocationBtn"
                                                >
                                                    <i class="mdi mdi-magnify"></i>
                                                    <span class="d-none d-sm-inline">Search</span>
                                                </button>
                                            </div>

                                            <div
                                                id="locationSearchStatus"
                                                class="form-text mt-2"
                                            ></div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-2">
                                            <div>
                                                <h4 class="mb-1" style="font-size:15px">Emergency Contacts</h4>
                                                <div class="text-muted" style="font-size:11px">Add at least one complete contact. You can add more when required.</div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-eg-primary" id="addContact"><i class="mdi mdi-plus me-1"></i>Add Contact</button>
                                        </div>
                                        <div id="contactsWrapper"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- STEP 4 --}}
                    <section class="profile-section" data-section="4">
                        <div class="section-card">
                            <div class="section-head">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="section-badge"><i class="mdi mdi-share-variant-outline"></i></div>
                                    <div>
                                        <h3 class="section-title">Social Media</h3>
                                        <p class="section-description">Select only the platforms you want to share and enter a valid profile link.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="section-body">
                                <div class="row g-3">
                                    @forelse(($social_media_list ?? []) as $social)
                                        @php
                                            $socialId = (string) ($social->sno ?? '');
                                            $saved = $socialDetails[$socialId] ?? ($socialDetails[$social->sno] ?? '');
                                        @endphp
                                        <div class="col-md-6">
                                            <div class="field-card">
                                                <div class="d-flex align-items-center justify-content-between gap-3">
                                                    <label class="d-flex align-items-center gap-2 mb-0" style="cursor:pointer;font-size:13px;font-weight:800">
                                                        <input class="form-check-input social-toggle mt-0" type="checkbox" data-social-id="{{ $socialId }}" {{ !empty($saved) ? 'checked' : '' }}>
                                                        <span>{{ $social->social_media_name }}</span>
                                                    </label>
                                                    <i class="mdi mdi-link-variant text-muted"></i>
                                                </div>
                                                <div class="social-field mt-2 {{ !empty($saved) ? '' : 'd-none' }}">
                                                    <input class="form-control social-url" data-social-input="{{ $socialId }}" value="{{ $saved }}" placeholder="https://..." inputmode="url">
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="locked-note mb-0"><i class="mdi mdi-information-outline fs-5"></i><div>No social media platforms are configured. You can safely continue.</div></div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- STEP 5 --}}
                    <section class="profile-section" data-section="5">
                        <div class="section-card">
                            <div class="section-head">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="section-badge"><i class="mdi mdi-school-outline"></i></div>
                                    <div>
                                        <h3 class="section-title">Education & Documents</h3>
                                        <p class="section-description">Add qualifications and upload documents when required. Existing files are only changed when you explicitly choose an action.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="section-body">
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-2">
                                    <div>
                                        <h4 class="mb-1" style="font-size:15px">Education</h4>
                                        <div class="text-muted" style="font-size:11px">Add your relevant qualifications. Do not duplicate the same qualification.</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-eg-primary" id="addEducation"><i class="mdi mdi-plus me-1"></i>Add Education</button>
                                </div>
                                <div id="educationWrapper"></div>

                                <hr class="my-4">

                                <div class="field-card">
                                    <label class="form-label fw-bold">Any additional course completed? <span class="text-danger">*</span></label>
                                    <div class="choice-row">
                                        @foreach(['Yes','No'] as $value)
                                            <span class="choice-pill">
                                                <input id="course_{{ strtolower($value) }}" type="radio" name="is_Course" value="{{ $value }}" {{ (($staff->is_Course ?? 'No') === $value) ? 'checked' : '' }} required>
                                                <label for="course_{{ strtolower($value) }}">{{ $value }}</label>
                                            </span>
                                        @endforeach
                                    </div>
                                    <div
                                        id="courseField"
                                        class="mt-3 {{ ($staff->is_Course ?? 'No') === 'Yes' ? '' : 'd-none' }}"
                                    >
                                        <label class="form-label fw-semibold">
                                            Course Name
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            id="courseTagInput"
                                            value="{{ $courseTagText }}"
                                            placeholder="Enter course name(s), separated by commas"
                                            maxlength="1000"
                                            autocomplete="off"
                                        >

                                        <div class="form-text">
                                            Example: Python, Digital Marketing, Excel
                                        </div>

                                        {{-- Generated just before AJAX submit because the API expects course_tag[] --}}
                                        <div id="courseTagPayloads"></div>
                                    </div>

                                    <div
                                        class="field-error"
                                        data-error-for="course_tag"
                                    ></div>
                                </div>

                                <hr class="my-4">

                                <div class="mb-2">
                                    <h4 class="mb-1" style="font-size:15px">Existing Documents</h4>
                                    <div class="text-muted" style="font-size:11px">Delete changes are marked here and applied only after you save the profile.</div>
                                </div>
                                <div class="row g-3">
                                    @forelse(($attachments ?? []) as $attachment)
                                        @php
                                            $files = json_decode($attachment->attachment_name ?? '[]', true);
                                            $files = is_array($files) ? $files : [];
                                        @endphp
                                        <div class="col-md-6">
                                            <div class="existing-doc" data-attachment-id="{{ $attachment->sno }}" data-document-id="{{ $attachment->document_id }}">
                                                <input type="hidden" name="existing_attachment_action[{{ $attachment->sno }}]" value="keep" class="attachment-action">
                                                <div class="d-flex align-items-start justify-content-between gap-2">
                                                    <div class="min-w-0">
                                                        <div class="doc-name"><i class="mdi mdi-file-document-outline me-1"></i>{{ $attachment->document_name ?? ('Document #' . $attachment->document_id) }}</div>
                                                        @foreach($files as $file)
                                                            <div class="file-chip" title="{{ basename($file) }}"><i class="mdi mdi-paperclip"></i><span>{{ basename($file) }}</span></div>
                                                        @endforeach
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-existing-document remove-btn" aria-label="Mark document for deletion" title="Mark for deletion"><i class="mdi mdi-delete-outline"></i></button>
                                                </div>
                                                <div class="existing-doc-state mt-2 text-muted" style="font-size:10px">Keeping this document</div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="locked-note mb-0"><i class="mdi mdi-file-alert-outline fs-5"></i><div>No existing documents were found. Use the upload section below when you need to add a required document.</div></div>
                                        </div>
                                    @endforelse
                                </div>

                                <hr class="my-4">

                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-2">
                                    <div>
                                        <h4 class="mb-1" style="font-size:15px">Upload / Replace Documents</h4>
                                        <div class="text-muted" style="font-size:11px">Files are uploaded securely to temporary storage first, then moved into the employee document folder when you save.</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-eg" id="addDocument"><i class="mdi mdi-plus me-1"></i>Add Document</button>
                                </div>
                                <div id="documentWrapper"></div>

                                <div class="locked-note">
                                    <i class="mdi mdi-shield-lock-outline fs-5"></i>
                                    <div>
                                        <div class="fw-bold text-dark mb-1">Documents stay under your employee record</div>
                                        <div>Only files you choose to upload or delete are changed. Company, job role, salary, login and other HR-managed information cannot be edited from this link.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="action-bar">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <button type="button" class="btn btn-outline-eg" id="previousBtn"><i class="mdi mdi-arrow-left me-1"></i><span class="d-none d-sm-inline">Previous</span></button>
                            <div class="small text-muted fw-semibold d-none d-md-block" id="saveStatusText">Changes are saved only when you press Save.</div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-eg" id="saveBtn"><i class="mdi mdi-content-save-outline me-1"></i>Save<span id="saveLoader" class="spinner-border spinner-border-sm d-none ms-1" aria-hidden="true"></span></button>
                                <button type="button" class="btn btn-eg-gold" id="nextBtn">Save & Continue <i class="mdi mdi-arrow-right ms-1"></i></button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="footer-note">
                    This page is intended for the employee profile shared through the secure email link. For changes to company, work type, job role, salary or HR workflow information, please contact your HR executive.
                </div>
            </main>
        </div>
    </div>
</div>

<script>
(() => {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('employeeProfileForm');
        if (!form) return;

        const sections = [...document.querySelectorAll('.profile-section')];
        const stepCards = [...document.querySelectorAll('.profile-step')];
        const alertBox = document.getElementById('portalAlert');
        const currentStage = document.getElementById('currentStage');
        const previousBtn = document.getElementById('previousBtn');
        const saveBtn = document.getElementById('saveBtn');
        const nextBtn = document.getElementById('nextBtn');
        const saveLoader = document.getElementById('saveLoader');
        const saveStatusText = document.getElementById('saveStatusText');
        const languagesUi = document.getElementById('languagesUi');
        const hobbyUi = document.getElementById('hobbyUi');
        const languagesPayload = document.getElementById('languagesPayload');
        const hobbyPayload = document.getElementById('hobbyPayload');
        const martialStatusPayload = document.getElementById('martialStatusPayload');
        const maritalStatus = document.getElementById('maritalStatus');
        const vehicleCheck = document.getElementById('vehicleCheck');
        const vehicleFields = document.getElementById('vehicleFields');
        const childrenCount = document.getElementById('childrenCount');
        const siblingsCount = document.getElementById('siblingsCount');
        const childrenDetails = document.getElementById('childrenDetails');
        const siblingDetails = document.getElementById('siblingDetails');
        const childrenCountWrap = document.getElementById('childrenCountWrap');
        const siblingsCountWrap = document.getElementById('siblingsCountWrap');
        const spouseFields = document.getElementById('spouseFields');
        const contactsWrapper = document.getElementById('contactsWrapper');
        const educationWrapper = document.getElementById('educationWrapper');
        const documentWrapper = document.getElementById('documentWrapper');
        const addContactBtn = document.getElementById('addContact');
        const addEducationBtn = document.getElementById('addEducation');
        const addDocumentBtn = document.getElementById('addDocument');
        const useMyLocationBtn = document.getElementById('useMyLocation');
        const clearLocationBtn = document.getElementById('clearLocation');
        const employeeLocationMapEl = document.getElementById('employeeLocationMap');
        const locationLatitude = document.getElementById('locationLatitude');
        const locationLongitude = document.getElementById('locationLongitude');
        const locationUrl = document.getElementById('locationUrl');
        const locationLatitudeDisplay = document.getElementById('locationLatitudeDisplay');
        const locationLongitudeDisplay = document.getElementById('locationLongitudeDisplay');
        const locationSummary = document.getElementById('locationSummary');
        const locationSearch = document.getElementById('locationSearch');
        const searchLocationBtn = document.getElementById('searchLocationBtn');
        const locationSearchStatus = document.getElementById('locationSearchStatus');

        const communityIdUi = document.getElementById('communityIdUi');
        const casteIdUi = document.getElementById('casteIdUi');
        const casteHelpText = document.getElementById('casteHelpText');

        const stepKeys = @json($stepKeys);
        const oldChildren = @json($children);
        const oldSiblings = @json($siblings);
        const oldContacts = @json($oldContactsData);
        const oldEducation = @json($oldEducationData);
        const qualificationOptions = @json($qualificationOptionsData);
        const relationshipOptions = @json($relationshipOptionsData);
        const documentOptions = @json($documentOptionsData);

        const uploadUrl = @json(route('upload-temp-documentstaff'));
        const deleteTempUrl = @json(route('delete-temp-documentstaff'));
        const csrfToken = @json(csrf_token());
        const DEFAULT_STEP = 1;
        let currentStep = DEFAULT_STEP;
        let saving = false;
        let dirty = false;
        let alertTimer = null;
        let rowUid = 0;

        let locationMap = null;
        let locationMarker = null;
        let locationReverseRequest = null;
        let casteRequest = null;

        const isArray = value => Array.isArray(value);
        const toArray = value => isArray(value) ? value : [];
        const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
        const esc = value => String(value ?? '').replace(/[&<>'"]/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
        }[char]));
        const asString = value => String(value ?? '');

        const parseJsonInput = (value, fallback = []) => {
            try {
                const parsed = JSON.parse(value || '');
                return isArray(parsed) ? parsed : fallback;
            } catch (e) {
                return fallback;
            }
        };

        const notify = (type, message, autoHide = true) => {
            if (!alertBox) return;
            clearTimeout(alertTimer);
            alertBox.className = `portal-alert ${type} show`;
            alertBox.innerHTML = `<i class="mdi ${type === 'success' ? 'mdi-check-circle-outline' : type === 'warning' ? 'mdi-alert-outline' : type === 'info' ? 'mdi-information-outline' : 'mdi-alert-circle-outline'} fs-5"></i><div>${esc(message)}</div>`;
            if (autoHide && type === 'success') {
                alertTimer = setTimeout(() => alertBox.classList.remove('show'), 3800);
            }
        };

        const clearFieldErrors = scope => {
            const root = scope || document;
            root.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            root.querySelectorAll('.field-error.show').forEach(el => {
                el.classList.remove('show');
                el.textContent = '';
            });
            root.querySelectorAll('.select2-container.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        };

        const fieldKeyFromName = name => name ? name.replace(/\[\]$/,'') : '';

        const showFieldError = (key, message, scope = document) => {
            const cleanKey = String(key || '').replace(/\.\d+$/, '');
            const candidates = [
                `[name="${CSS.escape(cleanKey)}"]`,
                `[name="${CSS.escape(cleanKey)}[]"]`,
                `[data-error-for="${CSS.escape(cleanKey)}"]`
            ];

            let target = null;
            for (const selector of candidates) {
                target = scope.querySelector(selector) || target;
                if (target) break;
            }

            if (!target && cleanKey === 'languages') {
                target = document.getElementById('languagesUi');
            }

            if (target) {
                target.classList.add('is-invalid');
                if (target.classList.contains('select3') && window.jQuery) {
                    $(target).next('.select2').addClass('is-invalid');
                }
            }

            const errorNode = scope.querySelector(`[data-error-for="${CSS.escape(cleanKey)}"]`)
                || document.querySelector(`[data-error-for="${CSS.escape(cleanKey)}"]`);
            if (errorNode) {
                errorNode.textContent = message;
                errorNode.classList.add('show');
            }
        };

        const showValidationErrors = errors => {
            clearFieldErrors();
            const entries = Object.entries(errors || {});
            entries.forEach(([key, value]) => {
                const message = isArray(value) ? String(value[0] ?? 'Invalid value.') : String(value ?? 'Invalid value.');
                showFieldError(key, message);
            });
            const first = document.querySelector('.is-invalid, .field-error.show');
            if (first) {
                const section = first.closest('.profile-section');
                if (section) showStep(Number(section.dataset.section));
                setTimeout(() => first.scrollIntoView({ behavior: 'smooth', block: 'center' }), 80);
            }
        };

        const initSelect3 = scope => {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2) return;
            const $root = $(scope || document);
            $root.find('.select3').each(function () {
                const $select = $(this);
                if ($select.hasClass('select2-hidden-accessible')) return;
                const isMultiple = $select.prop('multiple');
                const placeholder = $select.data('placeholder') || 'Select';
                $select.select2({
                    width: '100%',
                    placeholder,
                    allowClear: !isMultiple,
                    closeOnSelect: !isMultiple,
                    dropdownAutoWidth: false,
                    dropdownParent: $('body')
                });
            });
        };

        const destroySelect2 = select => {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2 || !select) return;
            const $select = $(select);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
        };

        const syncSelectPayloads = () => {
            if (languagesUi && languagesPayload) {
                const values = window.jQuery
                    ? $(languagesUi).val() || []
                    : [...languagesUi.selectedOptions].map(option => option.value);

                languagesPayload.value = JSON.stringify(values);
            }

            if (hobbyUi && hobbyPayload) {
                const values = window.jQuery
                    ? $(hobbyUi).val() || []
                    : [...hobbyUi.selectedOptions].map(option => option.value);

                hobbyPayload.value = JSON.stringify(values);
            }

            if (maritalStatus && martialStatusPayload) {
                martialStatusPayload.value =
                    maritalStatus.value || '';
            }
        };

        const syncCoursePayload = () => {
            const payloadWrapper =
                document.getElementById('courseTagPayloads');

            const courseInput =
                document.getElementById('courseTagInput');

            if (!payloadWrapper) return;

            payloadWrapper.innerHTML = '';

            const isCourseYes =
                document.querySelector('input[name="is_Course"]:checked')?.value === 'Yes';

            if (!isCourseYes) {
                return;
            }

            const values = String(courseInput?.value || '')
                .split(',')
                .map(value => value.trim())
                .filter(Boolean);

            const uniqueValues = [
                ...new Set(values)
            ];

            uniqueValues.forEach(value => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'course_tag[]';
                input.value = value;
                payloadWrapper.appendChild(input);
            });
        };

        const markDirty = () => {
            dirty = true;
            if (saveStatusText) saveStatusText.textContent = 'You have unsaved changes.';
        };

        const markClean = () => {
            dirty = false;
            if (saveStatusText) saveStatusText.textContent = 'Your changes are saved.';
        };

        const setSaving = state => {
            saving = Boolean(state);
            [saveBtn, nextBtn, previousBtn].forEach(btn => { if (btn) btn.disabled = saving; });
            if (saveLoader) saveLoader.classList.toggle('d-none', !saving);
            if (nextBtn) {
                nextBtn.innerHTML = saving
                    ? '<span class="spinner-border spinner-border-sm me-1"></span>Saving…'
                    : (currentStep === 5 ? 'Save Profile <i class="mdi mdi-check ms-1"></i>' : 'Save & Continue <i class="mdi mdi-arrow-right ms-1"></i>');
            }
        };

        const applyCompletion = data => {
            const overall = data?.overall || {};
            const percent = clamp(Number(overall.percentage || 0), 0, 100);
            const label = document.getElementById('completionLabel');
            const mobileLabel = document.getElementById('completionLabelMobile');
            const bar = document.getElementById('completionBar');
            const mobileBar = document.getElementById('completionBarMobile');
            const ring = document.querySelector('.completion-ring');
            const ringText = document.getElementById('completionRingText');
            const note = document.getElementById('completionNote');
            [label, mobileLabel].forEach(node => { if (node) node.textContent = `${Math.round(percent)}%`; });
            [bar, mobileBar].forEach(node => { if (node) node.style.width = `${percent}%`; });
            if (ring) ring.style.setProperty('--completion', percent);
            if (ringText) ringText.textContent = `${Math.round(percent)}%`;
            if (note) note.textContent = overall.is_complete ? 'Everything required is complete.' : `${Number(overall.remaining || 0)} item(s) remaining.`;

            stepKeys.forEach((key, index) => {
                const step = data?.steps?.[key] || {};
                const stepPct = clamp(Number(step.percentage || 0), 0, 100);
                const progress = document.querySelector(`[data-step-progress="${index + 1}"]`);
                const labelNode = document.querySelector(`[data-step-label="${index + 1}"]`);
                const card = document.getElementById(`stepCard${index + 1}`);
                if (progress) progress.style.width = `${stepPct}%`;
                if (labelNode) labelNode.textContent = `${Math.round(stepPct)}%`;
                if (card) card.classList.toggle('done', stepPct >= 100);
            });
        };


        /*
         * Live completion preview
         * ----------------------
         * The server-side StaffProfileCompletionService remains the source
         * of truth after Save. This function updates the UI immediately
         * while the employee is filling the form, so the progress indicator
         * never waits for a save request.
         */
        let liveCompletionTimer = null;

        const isFieldVisible = field => {
            if (!field || field.disabled) return false;

            const style = window.getComputedStyle(field);
            if (style.display === 'none' || style.visibility === 'hidden') return false;

            return !field.closest('.d-none');
        };

        const fieldHasValue = field => {
            if (!field) return false;

            if (field.type === 'checkbox') {
                return field.checked;
            }

            if (field.type === 'radio') {
                return !!document.querySelector(
                    `[name="${CSS.escape(field.name || '')}"]:checked`
                );
            }

            if (field.tagName === 'SELECT' && field.multiple) {
                return Array.from(field.selectedOptions || []).some(option => String(option.value || '').trim() !== '');
            }

            return String(field.value || '').trim() !== '';
        };

        const calculateLiveStep = section => {
            if (!section) {
                return { completed: 0, total: 0, percentage: 100 };
            }

            const fields = [];
            section.querySelectorAll('input, select, textarea').forEach(field => {
                /*
                 * Hidden payloads are implementation details, not completion
                 * fields. Also ignore submit/reset/button controls.
                 */
                if (
                    field.type === 'hidden' ||
                    ['button', 'submit', 'reset', 'file'].includes(String(field.type || '').toLowerCase())
                ) {
                    return;
                }

                if (!isFieldVisible(field)) return;

                fields.push(field);
            });

            /*
             * Use required fields first because they are explicitly part of
             * the employee-editable UI contract. For sections that contain
             * repeatable controls, dynamically applied `required` attributes
             * are already handled by applyQualificationRules().
             */
            let candidates = fields.filter(field => field.required);

            /*
             * Contact stage: one complete emergency contact is required by
             * the validation flow, even if the row controls themselves are
             * not marked required.
             */
            if (Number(section.dataset.section) === 3) {
                const contactRows = [...section.querySelectorAll('.contact-row')];
                const hasCompleteContact = contactRows.some(row => {
                    const name = row.querySelector('[name="contact_person_name[]"]');
                    const relation = row.querySelector('[name="contact_person_relation[]"]');
                    const mobile = row.querySelector('[name="contact_person_no[]"]');

                    return (
                        String(name?.value || '').trim() &&
                        String(relation?.value || '').trim() &&
                        String(mobile?.value || '').trim()
                    );
                });

                candidates = [
                    ...candidates.filter(field => !field.closest('.contact-row')),
                    { __virtual: true, complete: hasCompleteContact }
                ];
            }

            /*
             * Personal stage: Languages Known is required by the validation
             * flow even though Select2's HTML required state can vary.
             */
            if (Number(section.dataset.section) === 1 && languagesUi) {
                const alreadyTracked = candidates.some(field => field === languagesUi);
                const languageComplete = fieldHasValue(languagesUi);

                if (!alreadyTracked) {
                    candidates.push({
                        __virtual: true,
                        complete: languageComplete
                    });
                }
            }

            /*
             * Education stage: one education record is required. Additional
             * Course is also required and Course text applies only when Yes.
             */
            if (Number(section.dataset.section) === 5) {
                const educationRows = [...section.querySelectorAll('.education-row')];

                if (educationRows.length === 0) {
                    candidates.push({
                        __virtual: true,
                        complete: false
                    });
                }

                const courseChoice = section.querySelector(
                    'input[name="is_Course"]:checked'
                );

                if (courseChoice) {
                    const courseInput = document.getElementById('courseTagInput');

                    /*
                     * `is_Course` is already represented through the radio group
                     * if one of the radios is required. Add the conditional
                     * Course value only when Yes is selected.
                     */
                    if (courseChoice.value === 'Yes' && courseInput) {
                        candidates.push({
                            __virtual: true,
                            complete: String(courseInput.value || '').trim() !== ''
                        });
                    }
                } else {
                    candidates.push({
                        __virtual: true,
                        complete: false
                    });
                }
            }

            /*
             * A section with no effective fields is considered complete.
             * This matches the Social Media policy where an empty selection
             * is valid and the server treats the section as complete.
             */
            if (!candidates.length) {
                return { completed: 1, total: 1, percentage: 100 };
            }

            let completed = 0;

            candidates.forEach(field => {
                if (field.__virtual) {
                    if (field.complete) completed++;
                    return;
                }

                /*
                 * A visible required radio group counts once, not once per
                 * radio button.
                 */
                if (field.type === 'radio') {
                    const sameGroup = candidates.filter(
                        candidate =>
                            !candidate.__virtual &&
                            candidate.type === 'radio' &&
                            candidate.name === field.name
                    );

                    if (sameGroup[0] !== field) return;

                    if (fieldHasValue(field)) completed++;
                    return;
                }

                if (fieldHasValue(field)) {
                    completed++;
                }
            });

            /*
             * Remove duplicate radio group members from total.
             */
            const radioGroups = new Set(
                candidates
                    .filter(field => !field.__virtual && field.type === 'radio')
                    .map(field => field.name)
            );

            const radioDuplicates = candidates.filter(
                field => !field.__virtual && field.type === 'radio'
            ).length - radioGroups.size;

            const total = Math.max(1, candidates.length - Math.max(0, radioDuplicates));

            return {
                completed,
                total,
                percentage: Math.round((completed / total) * 100)
            };
        };

        const updateLiveCompletion = () => {
            const sections = [
                ...document.querySelectorAll('.profile-section')
            ];

            if (!sections.length) return;

            const liveSteps = {};
            let totalCompleted = 0;
            let totalFields = 0;

            sections.forEach((section, index) => {
                const result = calculateLiveStep(section);
                liveSteps[index + 1] = result;

                totalCompleted += result.completed;
                totalFields += result.total;

                const progress = document.querySelector(
                    `[data-step-progress="${index + 1}"]`
                );
                const label = document.querySelector(
                    `[data-step-label="${index + 1}"]`
                );
                const card = document.getElementById(`stepCard${index + 1}`);

                if (progress) {
                    progress.style.width = `${result.percentage}%`;
                }

                if (label) {
                    label.textContent = `${result.percentage}%`;
                }

                if (card) {
                    card.classList.toggle('done', result.percentage >= 100);
                }
            });

            const percent = totalFields > 0
                ? Math.round((totalCompleted / totalFields) * 100)
                : 100;

            const label = document.getElementById('completionLabel');
            const mobileLabel = document.getElementById('completionLabelMobile');
            const bar = document.getElementById('completionBar');
            const mobileBar = document.getElementById('completionBarMobile');
            const ring = document.querySelector('.completion-ring');
            const ringText = document.getElementById('completionRingText');
            const note = document.getElementById('completionNote');

            [label, mobileLabel].forEach(node => {
                if (node) node.textContent = `${percent}%`;
            });

            [bar, mobileBar].forEach(node => {
                if (node) node.style.width = `${percent}%`;
            });

            if (ring) ring.style.setProperty('--completion', percent);
            if (ringText) ringText.textContent = `${percent}%`;

            if (note) {
                note.textContent = percent >= 100
                    ? 'Everything visible and required is complete.'
                    : `${Math.max(0, totalFields - totalCompleted)} item(s) remaining.`;
            }

            return liveSteps;
        };

        const scheduleLiveCompletion = () => {
            clearTimeout(liveCompletionTimer);
            liveCompletionTimer = setTimeout(() => {
                try {
                    applyQualificationRules();
                    updateLiveCompletion();
                } catch (error) {
                    console.warn('Live completion update failed:', error);
                }
            }, 40);
        };

        const currentInvalid = section => {
            clearFieldErrors(section);
            const invalid = [...section.querySelectorAll('input, textarea, select')].find(el => {
                if (el.disabled) return false;
                if (el.type === 'hidden') return false;
                if (el.name === 'languages') return false;
                return !el.checkValidity();
            });

            if (invalid) {
                invalid.classList.add('is-invalid');
                const key = fieldKeyFromName(invalid.name);
                const message = invalid.validity.valueMissing
                    ? 'This field is required.'
                    : invalid.validity.typeMismatch
                        ? 'Please enter a valid value.'
                        : invalid.validity.patternMismatch
                            ? 'Please enter a valid value.'
                            : 'Please correct this field.';
                showFieldError(key, message, section);
                invalid.focus({ preventScroll: true });
                invalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            if (languagesUi) {
                const selectedLanguages = window.jQuery ? ($(languagesUi).val() || []) : [...languagesUi.selectedOptions].map(o => o.value);
                if (selectedLanguages.length === 0) {
                    showFieldError('languages', 'Please choose at least one language.', section);
                    showStep(1);
                    languagesUi.focus({ preventScroll: true });
                    return false;
                }
            }

            if (section.dataset.section === '3') {
                const rows =
                    [...section.querySelectorAll('.contact-row')];

                const hasComplete =
                    rows.some(row => {
                        const name =
                            row.querySelector(
                                '[name="contact_person_name[]"]'
                            )?.value.trim();

                        const number =
                            row.querySelector(
                                '[name="contact_person_no[]"]'
                            )?.value.trim();

                        const relation =
                            row.querySelector(
                                '[name="contact_person_relation[]"]'
                            )?.value;

                        return (
                            name &&
                            number &&
                            relation
                        );
                    });

                if (!hasComplete) {
                    notify(
                        'warning',
                        'Please add at least one complete emergency contact before continuing.'
                    );

                    const first =
                        rows[0]?.querySelector(
                            'input,select'
                        );

                    first?.focus({
                        preventScroll: true
                    });

                    first?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    return false;
                }
            }

            if (section.dataset.section === '5') {
                const educationRows =
                    [...section.querySelectorAll('.education-row')];

                const seen =
                    new Set();

                for (const row of educationRows) {
                    const qualification =
                        row.querySelector(
                            '[name="qualification_type[]"]'
                        );

                    const value =
                        qualification?.value || '';

                    if (!value) {
                        notify(
                            'warning',
                            'Please select a qualification for every education row.'
                        );

                        qualification?.focus();

                        return false;
                    }

                    if (seen.has(value)) {
                        notify(
                            'warning',
                            'Please do not add the same qualification more than once.'
                        );

                        qualification?.focus();

                        return false;
                    }

                    seen.add(value);

                    const skipMajor =
                        [
                            '4',
                            '5',
                            '6',
                            'Others'
                        ].includes(
                            asString(value)
                        );

                    const major =
                        row.querySelector(
                            '[name="major[]"]'
                        );

                    const university =
                        row.querySelector(
                            '[name="univ_name[]"]'
                        );

                    const year =
                        row.querySelector(
                            '[name="pass_year[]"]'
                        );

                    if (!skipMajor && !String(major?.value || '').trim()) {
                        notify(
                            'warning',
                            'Please select a major for UG, PG or Doctorate.'
                        );

                        major?.focus();

                        return false;
                    }

                    if (!skipMajor && !String(university?.value || '').trim()) {
                        notify(
                            'warning',
                            'Please enter the institute or university.'
                        );

                        university?.focus();

                        return false;
                    }

                    const yearValue =
                        String(
                            year?.value || ''
                        ).trim();

                    if (
                        !/^\d{4}$/.test(
                            yearValue
                        )
                    ) {
                        notify(
                            'warning',
                            'Please enter a valid four-digit passing year.'
                        );

                        year?.focus();

                        return false;
                    }
                }
                const documentRows = [...section.querySelectorAll('.document-row')];
                for (const row of documentRows) {
                    const documentType = row.querySelector('.document-type')?.value || '';
                    const uploaded = parseJsonInput(row.querySelector('.uploaded-files-payload')?.value, []);
                    if (!documentType && !uploaded.length) continue;
                    if (!documentType) {
                        notify('warning', 'Please select a document type for every upload row, or remove the empty row.');
                        row.querySelector('.document-type')?.focus();
                        return false;
                    }
                    if (!uploaded.length) {
                        notify('warning', 'Please choose at least one file for each selected document type, or remove that upload row.');
                        row.querySelector('.document-input')?.focus({ preventScroll: true });
                        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return false;
                    }
                }

                const courseYes =
                    section.querySelector(
                        'input[name="is_Course"]:checked'
                    )?.value === 'Yes';

                const courseInput =
                    document.getElementById(
                        'courseTagInput'
                    );

                if (
                    courseYes &&
                    courseInput &&
                    !String(
                        courseInput.value || ''
                    ).trim()
                ) {
                    notify(
                        'warning',
                        'Please enter at least one course, or choose No.'
                    );

                    courseInput.focus();

                    return false;
                }
            }

            syncSelectPayloads();
            return true;
        };

        const showStep = number => {
            let step = Number(number) || DEFAULT_STEP;
            step = clamp(step, 1, sections.length || 5);
            currentStep = step;
            sections.forEach(section => section.classList.toggle('active', Number(section.dataset.section) === step));
            stepCards.forEach(card => card.classList.toggle('active', Number(card.dataset.goStep) === step));
            if (currentStage) currentStage.value = String(step);
            if (previousBtn) previousBtn.disabled = step === 1 || saving;
            if (nextBtn && !saving) nextBtn.innerHTML = step === 5 ? 'Save Profile <i class="mdi mdi-check ms-1"></i>' : 'Save & Continue <i class="mdi mdi-arrow-right ms-1"></i>';
            const activeSection = sections.find(s => Number(s.dataset.section) === step);
            if (activeSection) initSelect3(activeSection);
            if (stepCards.find(card => Number(card.dataset.goStep) === step)) {
                document.getElementById(`stepCard${step}`)?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        const makeRelationshipOptions = selected => {
            let html = '<option value=""></option>';
            toArray(relationshipOptions).forEach(item => {
                const id = asString(item.id);
                html += `<option value="${esc(id)}" ${id === asString(selected) ? 'selected' : ''}>${esc(item.name)}</option>`;
            });
            return html;
        };

        const initEducationYearPicker = scope => {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.datepicker) return;

            const $root = $(scope || educationWrapper || document);
            $root.find('input[name="pass_year[]"]').each(function () {
                const $input = $(this);
                if ($input.data('datepicker')) return;

                $input.attr({
                    maxlength: '4',
                    inputmode: 'numeric',
                    autocomplete: 'off',
                    placeholder: 'YYYY',
                    readonly: 'readonly'
                });

                $input.datepicker({
                    format: 'yyyy',
                    viewMode: 'years',
                    minViewMode: 'years',
                    autoclose: true,
                    clearBtn: true,
                    todayBtn: false,
                    orientation: 'bottom auto'
                });
            });
        };

        const makeQualificationOptions = selected => {
            const configured = toArray(qualificationOptions);
            const fallback = [
                { id: '1', name: 'UG' },
                { id: '2', name: 'PG' },
                { id: '3', name: 'Doctorate' },
                { id: '4', name: 'HSC' },
                { id: '5', name: 'SSLC' },
                { id: '6', name: 'Below SSLC' },
                { id: 'Others', name: 'Others' }
            ];

            const seen = new Set();
            const options = [...configured, ...fallback].filter(item => {
                const id = asString(item?.id);
                if (!id || seen.has(id)) return false;
                seen.add(id);
                return true;
            });

            let html = '<option value=""></option>';
            options.forEach(item => {
                const id = asString(item.id);
                html += `<option value="${esc(id)}" ${id === asString(selected) ? 'selected' : ''}>${esc(item.name)}</option>`;
            });
            return html;
        };

        const makeDocumentOptions = () => {
            let html = '<option value=""></option>';
            toArray(documentOptions).forEach(item => {
                html += `<option value="${esc(item.id)}">${esc(item.name)}</option>`;
            });
            return html;
        };


        const loadMajorOptions = async (
            row,
            qualificationId,
            selectedMajor = ''
        ) => {
            if (!row || !qualificationId) {
                return;
            }

            const majorSelect =
                row.querySelector('.major-select');

            if (!majorSelect) {
                return;
            }

            const hiddenQualifications = [
                '4',
                '5',
                '6',
                'Others'
            ];

            const qualification =
                asString(qualificationId);

            if (hiddenQualifications.includes(qualification)) {
                return;
            }

            const routeUrl =
                @json(route('major_list_by_qualification'));

            destroySelect2(majorSelect);

            majorSelect.innerHTML = `
                <option value=""></option>
            `;

            majorSelect.disabled = true;

            try {
                const query =
                    new URLSearchParams({
                        qualification_id: qualification
                    });

                const response =
                    await fetch(
                        `${routeUrl}?${query.toString()}`,
                        {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        }
                    );

                const data =
                    await response.json();

                if (
                    !response.ok ||
                    Number(data?.status) !== 200
                ) {
                    throw new Error(
                        data?.message ||
                        'Unable to load major list.'
                    );
                }

                const options =
                    Array.isArray(data?.data)
                        ? data.data
                        : [];

                options.forEach(item => {
                    const option =
                        document.createElement('option');

                    option.value =
                        asString(item.sno);

                    option.textContent =
                        asString(item.major_name);

                    if (
                        asString(item.sno) ===
                        asString(selectedMajor)
                    ) {
                        option.selected = true;
                    }

                    majorSelect.appendChild(option);
                });

                const others =
                    document.createElement('option');

                others.value = 'Others';
                others.textContent = 'Others';

                if (
                    asString(selectedMajor) ===
                    'Others'
                ) {
                    others.selected = true;
                }

                majorSelect.appendChild(others);

            } catch (error) {
                console.warn(
                    'Major list load failed:',
                    error
                );

                if (
                    selectedMajor &&
                    ![
                        ...majorSelect.options
                    ].some(
                        option =>
                            asString(option.value) ===
                            asString(selectedMajor)
                    )
                ) {
                    const saved =
                        document.createElement('option');

                    saved.value =
                        asString(selectedMajor);

                    saved.textContent =
                        `Saved major #${selectedMajor}`;

                    saved.selected = true;

                    majorSelect.appendChild(saved);
                }

            } finally {
                majorSelect.disabled = false;
                initSelect3(row);
            }
        };

        const applyQualificationRules = () => {
            document
                .querySelectorAll('.education-row')
                .forEach(row => {
                    const qualification =
                        row.querySelector('.qualification');

                    const majorWrap =
                        row.querySelector('.major-wrap');

                    const universityWrap =
                        row.querySelector('.univ-wrap');

                    const majorSelect =
                        row.querySelector('.major-select');

                    const universityInput =
                        row.querySelector('.university-input');

                    const value =
                        asString(qualification?.value);

                    const skipMajorAndUniversity = [
                        '4',
                        '5',
                        '6',
                        'Others'
                    ].includes(value);

                    majorWrap?.classList.toggle(
                        'd-none',
                        skipMajorAndUniversity
                    );

                    universityWrap?.classList.toggle(
                        'd-none',
                        skipMajorAndUniversity
                    );

                    if (majorSelect) {
                        majorSelect.required =
                            !skipMajorAndUniversity;
                    }

                    if (universityInput) {
                        universityInput.required =
                            !skipMajorAndUniversity;

                        if (skipMajorAndUniversity) {
                            universityInput.value = '';
                        }
                    }
                });
        };

        const loadCasteOptions = async () => {
            if (!casteIdUi) {
                return;
            }

            const religionId =
                document.querySelector(
                    '[name="religion_id"]'
                )?.value || '';

            const communityId =
                communityIdUi?.value || '';

            const savedCaste =
                asString(casteIdUi.dataset.savedValue || casteIdUi.value || '');

            destroySelect2(casteIdUi);

            casteIdUi.innerHTML =
                '<option value=""></option>';

            if (!religionId || !communityId) {
                if (savedCaste) {
                    const saved =
                        document.createElement('option');

                    saved.value = savedCaste;
                    saved.textContent = `Saved caste #${savedCaste}`;
                    saved.selected = true;

                    casteIdUi.appendChild(saved);
                }

                initSelect3();
                if (casteHelpText) {
                    casteHelpText.textContent =
                        'Choose religion and community to load caste options.';
                }
                return;
            }

            if (casteHelpText) {
                casteHelpText.textContent =
                    'Loading caste options…';
            }

            if (casteRequest) {
                casteRequest.abort();
            }

            casteRequest =
                new AbortController();

            try {
                const routeUrl =
                    @json(route('caste_list_by_religion_community'));

                const query =
                    new URLSearchParams({
                        religion_id: religionId,
                        community_id: communityId
                    });

                const response =
                    await fetch(
                        `${routeUrl}?${query.toString()}`,
                        {
                            method: 'GET',
                            signal: casteRequest.signal,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        }
                    );

                const data =
                    await response.json();

                if (
                    !response.ok ||
                    Number(data?.status) !== 200
                ) {
                    throw new Error(
                        data?.message ||
                        'Unable to load caste list.'
                    );
                }

                const list =
                    Array.isArray(data?.data)
                        ? data.data
                        : [];

                list.forEach(item => {
                    const option =
                        document.createElement('option');

                    option.value =
                        asString(item.sno);

                    option.textContent =
                        asString(item.caste_name);

                    if (
                        asString(item.sno) ===
                        savedCaste
                    ) {
                        option.selected = true;
                    }

                    casteIdUi.appendChild(option);
                });

                if (
                    savedCaste &&
                    ![
                        ...casteIdUi.options
                    ].some(
                        option =>
                            asString(option.value) ===
                            savedCaste
                    )
                ) {
                    const saved =
                        document.createElement('option');

                    saved.value = savedCaste;
                    saved.textContent =
                        `Saved caste #${savedCaste}`;
                    saved.selected = true;

                    casteIdUi.appendChild(saved);
                }

                if (casteHelpText) {
                    casteHelpText.textContent =
                        list.length
                            ? 'Caste list updated from HR master data.'
                            : 'No caste options are configured for this combination.';
                }

            } catch (error) {
                if (error?.name === 'AbortError') {
                    return;
                }

                console.warn(
                    'Caste list load failed:',
                    error
                );

                if (savedCaste) {
                    const saved =
                        document.createElement('option');

                    saved.value = savedCaste;
                    saved.textContent =
                        `Saved caste #${savedCaste}`;
                    saved.selected = true;

                    casteIdUi.appendChild(saved);
                }

                if (casteHelpText) {
                    casteHelpText.textContent =
                        'Unable to load caste options right now. Your saved value is preserved.';
                }
            } finally {
                initSelect3();
            }
        };

        const setLocationValue = async (
            latitude,
            longitude,
            source = 'Selected on map'
        ) => {
            const lat =
                Number(latitude);

            const lng =
                Number(longitude);

            if (
                !Number.isFinite(lat) ||
                !Number.isFinite(lng) ||
                lat < -90 ||
                lat > 90 ||
                lng < -180 ||
                lng > 180
            ) {
                return;
            }

            const latText =
                lat.toFixed(7);

            const lngText =
                lng.toFixed(7);

            if (locationLatitude) {
                locationLatitude.value =
                    latText;
            }

            if (locationLongitude) {
                locationLongitude.value =
                    lngText;
            }

            if (locationUrl) {
                locationUrl.value =
                    `https://www.google.com/maps?q=${latText},${lngText}`;
            }

            if (locationLatitudeDisplay) {
                locationLatitudeDisplay.value =
                    latText;
            }

            if (locationLongitudeDisplay) {
                locationLongitudeDisplay.value =
                    lngText;
            }

            if (locationMap && locationMarker) {
                locationMarker.setLatLng([
                    lat,
                    lng
                ]);

                locationMap.setView(
                    [
                        lat,
                        lng
                    ],
                    Math.max(
                        locationMap.getZoom(),
                        16
                    )
                );
            }

            markDirty();

            if (locationSummary) {
                locationSummary.textContent =
                    `${source} · ${latText}, ${lngText}`;
            }

            await reverseGeocodeLocation(
                lat,
                lng
            );
        };

        const reverseGeocodeLocation = async (
            latitude,
            longitude
        ) => {
            if (!locationSummary) {
                return;
            }

            if (locationReverseRequest) {
                locationReverseRequest.abort();
            }

            locationReverseRequest =
                new AbortController();

            const controller =
                locationReverseRequest;

            const currentText =
                locationSummary.textContent;

            locationSummary.textContent =
                `${currentText} · Looking up address…`;

            try {
                const url =
                    'https://nominatim.openstreetmap.org/reverse?' +
                    new URLSearchParams({
                        format: 'jsonv2',
                        lat: latitude,
                        lon: longitude,
                        zoom: '18',
                        addressdetails: '1'
                    }).toString();

                const response =
                    await fetch(
                        url,
                        {
                            method: 'GET',
                            signal: controller.signal,
                            headers: {
                                'Accept': 'application/json'
                            }
                        }
                    );

                if (!response.ok) {
                    throw new Error(
                        'Reverse geocoding failed.'
                    );
                }

                const data =
                    await response.json();

                const displayName =
                    asString(data?.display_name);

                if (displayName) {
                    locationSummary.textContent =
                        displayName;
                }

            } catch (error) {
                if (error?.name === 'AbortError') {
                    return;
                }

                console.warn(
                    'Location reverse geocode failed:',
                    error
                );
            }
        };

        const initLocationMap = () => {
            if (
                !employeeLocationMapEl ||
                typeof L === 'undefined'
            ) {
                return;
            }

            const initialLat =
                Number(
                    locationLatitude?.value
                );

            const initialLng =
                Number(
                    locationLongitude?.value
                );

            const hasInitialLocation =
                Number.isFinite(initialLat) &&
                Number.isFinite(initialLng) &&
                initialLat >= -90 &&
                initialLat <= 90 &&
                initialLng >= -180 &&
                initialLng <= 180;

            const mapCenter =
                hasInitialLocation
                    ? [
                        initialLat,
                        initialLng
                    ]
                    : [
                        20.5937,
                        78.9629
                    ];

            locationMap =
                L.map(
                    employeeLocationMapEl,
                    {
                        zoomControl: true,
                        scrollWheelZoom: true,
                        dragging: true,
                        touchZoom: true
                    }
                ).setView(
                    mapCenter,
                    hasInitialLocation
                        ? 16
                        : 5
                );

            L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    maxZoom: 19,
                    attribution:
                        '&copy; OpenStreetMap contributors'
                }
            ).addTo(locationMap);

            locationMarker =
                L.marker(
                    mapCenter,
                    {
                        draggable: true,
                        autoPan: true
                    }
                ).addTo(locationMap);

            if (!hasInitialLocation) {
                locationMarker.setOpacity(0);
            }

            locationMarker.on(
                'dragend',
                async event => {
                    const position =
                        event.target.getLatLng();

                    await setLocationValue(
                        position.lat,
                        position.lng,
                        'Location adjusted'
                    );
                }
            );

            locationMap.on(
                'click',
                async event => {
                    locationMarker.setOpacity(1);

                    await setLocationValue(
                        event.latlng.lat,
                        event.latlng.lng,
                        'Custom location'
                    );
                }
            );

            if (hasInitialLocation) {
                setTimeout(() => {
                    reverseGeocodeLocation(
                        initialLat,
                        initialLng
                    );
                }, 150);
            }

            setTimeout(() => {
                locationMap.invalidateSize();
            }, 200);
        };

        const clearLocation = () => {
            if (locationLatitude) {
                locationLatitude.value = '';
            }

            if (locationLongitude) {
                locationLongitude.value = '';
            }

            if (locationUrl) {
                locationUrl.value = '';
            }

            if (locationLatitudeDisplay) {
                locationLatitudeDisplay.value = '';
            }

            if (locationLongitudeDisplay) {
                locationLongitudeDisplay.value = '';
            }

            if (locationSummary) {
                locationSummary.textContent =
                    'No location selected yet.';
            }

            if (locationMarker) {
                locationMarker.setOpacity(0);
            }

            if (locationMap) {
                locationMap.setView(
                    [
                        20.5937,
                        78.9629
                    ],
                    5
                );
            }

            if (locationSearchStatus) {
                locationSearchStatus.textContent =
                    '';
            }

            markDirty();
        };

        const useCurrentLocation = () => {
            if (!navigator.geolocation) {
                notify(
                    'warning',
                    'Location is not supported by this browser. You can choose a location manually on the map.'
                );

                return;
            }

            if (useMyLocationBtn) {
                useMyLocationBtn.disabled = true;

                useMyLocationBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1"></span>Locating…';
            }

            navigator.geolocation.getCurrentPosition(
                async position => {
                    try {
                        if (locationMarker) {
                            locationMarker.setOpacity(1);
                        }

                        if (locationMap) {
                            locationMap.setView(
                                [
                                    position.coords.latitude,
                                    position.coords.longitude
                                ],
                                17
                            );
                        }

                        await setLocationValue(
                            position.coords.latitude,
                            position.coords.longitude,
                            'Current device location'
                        );

                        notify(
                            'success',
                            'Your current location has been added.'
                        );

                    } finally {
                        if (useMyLocationBtn) {
                            useMyLocationBtn.disabled = false;

                            useMyLocationBtn.innerHTML =
                                '<i class="mdi mdi-crosshairs-gps me-1"></i>Use My Location';
                        }
                    }
                },
                error => {
                    console.warn(
                        'Geolocation error:',
                        error
                    );

                    notify(
                        'warning',
                        'We could not read your location. Allow location access or tap the map to choose it manually.'
                    );

                    if (useMyLocationBtn) {
                        useMyLocationBtn.disabled = false;

                        useMyLocationBtn.innerHTML =
                            '<i class="mdi mdi-crosshairs-gps me-1"></i>Use My Location';
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 300000
                }
            );
        };

        const searchLocation = async () => {
            const query =
                String(
                    locationSearch?.value || ''
                ).trim();

            if (
                !query ||
                !locationMap
            ) {
                return;
            }

            if (locationSearchStatus) {
                locationSearchStatus.textContent =
                    'Searching…';
            }

            if (searchLocationBtn) {
                searchLocationBtn.disabled = true;
            }

            try {
                const url =
                    'https://nominatim.openstreetmap.org/search?' +
                    new URLSearchParams({
                        q: query,
                        format: 'jsonv2',
                        limit: '1'
                    }).toString();

                const response =
                    await fetch(
                        url,
                        {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json'
                            }
                        }
                    );

                const data =
                    await response.json();

                const first =
                    Array.isArray(data)
                        ? data[0]
                        : null;

                if (!first) {
                    throw new Error(
                        'Location not found.'
                    );
                }

                const lat =
                    Number(first.lat);

                const lng =
                    Number(first.lon);

                locationMarker?.setOpacity(1);

                locationMap.setView(
                    [
                        lat,
                        lng
                    ],
                    16
                );

                await setLocationValue(
                    lat,
                    lng,
                    'Location search'
                );

                if (locationSearchStatus) {
                    locationSearchStatus.textContent =
                        first.display_name ||
                        'Location selected.';
                }

            } catch (error) {
                console.warn(
                    'Location search failed:',
                    error
                );

                if (locationSearchStatus) {
                    locationSearchStatus.textContent =
                        'Could not find that place. Try a nearby area, city or landmark.';
                }
            } finally {
                if (searchLocationBtn) {
                    searchLocationBtn.disabled = false;
                }
            }
        };

        const renderChildren = () => {
            const enabled = document.querySelector('input[name="has_children"]:checked')?.value === 'Yes';
            childrenCountWrap?.classList.toggle('d-none', !enabled);
            childrenDetails?.classList.toggle('d-none', !enabled);
            if (!childrenDetails) return;
            if (!enabled) {
                childrenDetails.innerHTML = '';
                return;
            }
            const count = clamp(parseInt(childrenCount?.value || '0', 10) || 0, 0, 9);
            if (childrenCount) childrenCount.value = String(count);
            let html = '';
            for (let i = 0; i < count; i++) {
                const child = oldChildren[i] || {};
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="repeatable-row h-100">
                            <div class="repeat-title mb-3"><i class="mdi mdi-human-child me-1 text-danger"></i>Child ${i + 1}</div>
                            <div class="row g-2">
                                <div class="col-12"><label class="form-label fw-semibold">Name</label><input class="form-control" name="child_name[]" value="${esc(child.child_name)}" maxlength="255"></div>
                                <div class="col-6"><label class="form-label fw-semibold">DOB</label><input class="form-control" type="date" name="child_dob[]" value="${esc(child.child_dob)}"></div>
                                <div class="col-3"><label class="form-label fw-semibold">Standard</label><input class="form-control" name="child_std[]" value="${esc(child.child_std)}" maxlength="100"></div>
                                <div class="col-3"><label class="form-label fw-semibold">Year</label><input class="form-control" name="child_year[]" value="${esc(child.child_year)}" maxlength="4" inputmode="numeric"></div>
                            </div>
                        </div>
                    </div>`;
            }
            childrenDetails.innerHTML = html;
        };

        const renderSiblings = () => {
            const enabled = document.querySelector('input[name="has_siblings"]:checked')?.value === 'Yes';
            siblingsCountWrap?.classList.toggle('d-none', !enabled);
            siblingDetails?.classList.toggle('d-none', !enabled);
            if (!siblingDetails) return;
            if (!enabled) {
                siblingDetails.innerHTML = '';
                return;
            }
            const count = clamp(parseInt(siblingsCount?.value || '0', 10) || 0, 0, 9);
            if (siblingsCount) siblingsCount.value = String(count);
            let html = '';
            for (let i = 0; i < count; i++) {
                const sibling = oldSiblings[i] || {};
                const selectedType = asString(sibling.sibling_type);
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="repeatable-row h-100">
                            <div class="repeat-title mb-3"><i class="mdi mdi-account-group-outline me-1 text-danger"></i>Sibling ${i + 1}</div>
                            <div class="row g-2">
                                <div class="col-12"><label class="form-label fw-semibold">Name</label><input class="form-control" name="sibling_name[]" value="${esc(sibling.sibling_name)}" maxlength="255"></div>
                                <div class="col-6"><label class="form-label fw-semibold">Type</label><select class="form-select select3" name="sibling_type[]" data-placeholder="Elder / Younger"><option value=""></option><option value="Elder" ${selectedType === 'Elder' ? 'selected' : ''}>Elder</option><option value="Younger" ${selectedType === 'Younger' ? 'selected' : ''}>Younger</option></select></div>
                                <div class="col-6"><label class="form-label fw-semibold">Education / Occupation</label><input class="form-control" name="sibling_std[]" value="${esc(sibling.sibling_std)}" maxlength="255"></div>
                                <div class="col-12"><label class="form-label fw-semibold">Annual Income</label><input class="form-control" name="sibling_income[]" value="${esc(sibling.sibling_income)}" maxlength="100" inputmode="decimal"></div>
                            </div>
                        </div>
                    </div>`;
            }
            siblingDetails.innerHTML = html;
            initSelect3(siblingDetails);
        };

        const renderContacts = () => {
            if (!contactsWrapper) return;
            const names = toArray(oldContacts.names);
            const relations = toArray(oldContacts.relations);
            const numbers = toArray(oldContacts.numbers);
            const count = Math.max(1, names.length, relations.length, numbers.length);
            let html = '';
            for (let i = 0; i < count; i++) {
                html += `
                    <div class="repeatable-row contact-row" data-contact-index="${i}">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <div class="repeat-title"><i class="mdi mdi-phone-in-talk-outline me-1 text-danger"></i>Emergency Contact ${i + 1}</div>
                            ${i > 0 ? '<button type="button" class="btn btn-outline-danger remove-contact remove-btn" aria-label="Remove emergency contact"><i class="mdi mdi-delete-outline"></i></button>' : ''}
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4"><label class="form-label fw-semibold">Contact Name</label><input class="form-control" name="contact_person_name[]" value="${esc(names[i])}" maxlength="255"></div>
                            <div class="col-md-4"><label class="form-label fw-semibold">Relation</label><select class="form-select select3" name="contact_person_relation[]" data-placeholder="Select relation">${makeRelationshipOptions(relations[i])}</select></div>
                            <div class="col-md-4"><label class="form-label fw-semibold">Mobile Number</label><input class="form-control" name="contact_person_no[]" value="${esc(numbers[i])}" maxlength="15" inputmode="numeric"></div>
                        </div>
                    </div>`;
            }
            contactsWrapper.innerHTML = html;
            initSelect3(contactsWrapper);
        };

        const updateEducationHeading = () => {
            [...document.querySelectorAll('.education-row')].forEach((row, index) => {
                const title = row.querySelector('.repeat-title');
                if (title) title.innerHTML = `<i class="mdi mdi-school-outline me-1 text-danger"></i>Qualification ${index + 1}`;
                const remove = row.querySelector('.remove-education');
                if (remove) remove.classList.toggle('d-none', index === 0);
            });
        };

        const renderEducation = () => {
            if (!educationWrapper) return;

            const rows = oldEducation.length
                ? oldEducation
                : [{}];

            educationWrapper.innerHTML = rows
                .map((edu, index) => `
                    <div
                        class="repeatable-row education-row"
                        data-education-uid="${++rowUid}"
                    >
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <div class="repeat-title">
                                <i class="mdi mdi-school-outline me-1 text-danger"></i>
                                Qualification ${index + 1}
                            </div>

                            <button
                                type="button"
                                class="btn btn-outline-danger remove-education remove-btn ${index === 0 ? 'd-none' : ''}"
                                aria-label="Remove qualification"
                            >
                                <i class="mdi mdi-delete-outline"></i>
                            </button>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    Qualification
                                    <span class="text-danger">*</span>
                                </label>

                                <select
                                    class="form-select select3 qualification"
                                    name="qualification_type[]"
                                    data-placeholder="Select qualification"
                                    required
                                >
                                    ${makeQualificationOptions(edu.qualification_type)}
                                </select>

                                <div
                                    class="field-error"
                                    data-error-for="qualification_type"
                                ></div>
                            </div>

                            <div class="col-md-3 major-wrap">
                                <label class="form-label fw-semibold">
                                    Major / Specialization
                                    <span class="text-danger">*</span>
                                </label>

                                <select
                                    class="form-select select3 major-select"
                                    name="major[]"
                                    data-placeholder="Select major"
                                >
                                    <option value=""></option>
                                </select>
                            </div>

                            <div class="col-md-3 univ-wrap">
                                <label class="form-label fw-semibold">
                                    Institute / University
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    class="form-control university-input"
                                    name="univ_name[]"
                                    value="${esc(edu.university_name)}"
                                    maxlength="255"
                                    placeholder="Enter Institute / University"
                                >
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    Passing Year
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    class="form-control education-year-input"
                                    name="pass_year[]"
                                    value="${esc(edu.year)}"
                                    maxlength="4"
                                    placeholder="YYYY"
                                    inputmode="numeric"
                                    pattern="[0-9]{4}"
                                >
                            </div>
                        </div>
                    </div>
                `)
                .join('');

            initSelect3(educationWrapper);
            initEducationYearPicker(educationWrapper);

            educationWrapper
                .querySelectorAll('.education-row')
                .forEach(row => {
                    const qualification = row.querySelector('.qualification');
                    const selectedMajor = oldEducation[
                        [...educationWrapper.querySelectorAll('.education-row')].indexOf(row)
                    ]?.major;

                    loadMajorOptions(
                        row,
                        qualification?.value || '',
                        selectedMajor
                    );
                });

            applyQualificationRules();
            updateEducationHeading();
        };

        const clearTempFile = async filename => {
            if (!filename) return true;
            try {
                const response = await fetch(deleteTempUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ filename })
                });
                return response.ok;
            } catch (e) {
                console.warn('Temp file delete failed', e);
                return false;
            }
        };

        const uploadSingleFile = async (file, statusNode, row) => {
            const maxBytes = 20 * 1024 * 1024;
            const allowed = [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain'
            ];
            if (file.size > maxBytes) {
                throw new Error(`${file.name} is larger than 20 MB.`);
            }
            if (file.type && !allowed.includes(file.type)) {
                throw new Error(`${file.name} is not a supported document type.`);
            }

            statusNode.textContent = `Uploading ${file.name}…`;
            statusNode.className = 'upload-status';
            const body = new FormData();
            body.append('file', file);
            const response = await fetch(uploadUrl, {
                method: 'POST',
                body,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            let data = null;
            try { data = await response.json(); } catch (e) { data = null; }
            if (!response.ok || !data?.status) {
                throw new Error(data?.message || 'File upload failed.');
            }
            statusNode.textContent = `${file.name} uploaded.`;
            statusNode.className = 'upload-status success';
            return data.filename;
        };

        const addDocumentRow = () => {
            if (!documentWrapper) return;
            const uid = ++rowUid;
            const row = document.createElement('div');
            row.className = 'upload-row document-row';
            row.dataset.uid = String(uid);
            row.innerHTML = `
                <input type="hidden" name="uploaded_files[]" value="[]" class="uploaded-files-payload">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <div class="repeat-title"><i class="mdi mdi-file-upload-outline me-1 text-danger"></i>New document</div>
                    <button type="button" class="btn btn-outline-danger remove-document remove-btn" aria-label="Remove upload row"><i class="mdi mdi-close"></i></button>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Document Type</label>
                        <select class="form-select select3 document-type" name="doc_type[]" data-placeholder="Select document type" required>${makeDocumentOptions()}</select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Files</label>
                        <input type="file" class="form-control document-input" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.txt">
                        <div class="upload-status">Maximum 20 MB per file.</div>
                    </div>
                </div>
                <div class="uploaded-file-list mt-2"></div>
            `;
            documentWrapper.appendChild(row);
            initSelect3(row);
            markDirty();
        };

        const bindDocumentUpload = row => {
            const input = row.querySelector('.document-input');
            const payload = row.querySelector('.uploaded-files-payload');
            const list = row.querySelector('.uploaded-file-list');
            const status = row.querySelector('.upload-status');
            if (!input || !payload || !list || !status || input.dataset.bound === '1') return;
            input.dataset.bound = '1';
            input.addEventListener('change', async event => {
                const files = [...(event.target.files || [])];
                if (!files.length) return;
                const existing = parseJsonInput(payload.value, []);
                event.target.value = '';
                for (const file of files) {
                    try {
                        const filename = await uploadSingleFile(file, status, row);
                        existing.push(filename);
                        payload.value = JSON.stringify([...new Set(existing)]);

                        const selectedDocumentId = row.querySelector('.document-type')?.value || '';
                        if (selectedDocumentId) {
                            const existingCard = [...document.querySelectorAll('.existing-doc')].find(card =>
                                String(card.dataset.documentId || '') === String(selectedDocumentId)
                            );
                            const existingAction = existingCard?.querySelector('.attachment-action');
                            if (existingAction && existingAction.value === 'keep') {
                                existingAction.value = 'replace';
                                existingCard.classList.add('border-warning');
                                const state = existingCard.querySelector('.existing-doc-state');
                                if (state) state.textContent = 'A new file is queued to replace this document when you save';
                            }
                        }

                        const chip = document.createElement('div');
                        chip.className = 'file-chip d-flex align-items-center justify-content-between gap-2';
                        chip.dataset.filename = filename;
                        chip.innerHTML = `<div class="d-flex align-items-center gap-1 min-w-0"><i class="mdi mdi-file-check-outline text-success"></i><span>${esc(file.name)}</span></div><button type="button" class="btn btn-link p-0 text-danger temp-file-remove" aria-label="Remove file"><i class="mdi mdi-close"></i></button>`;
                        list.appendChild(chip);
                        markDirty();
                    } catch (error) {
                        status.textContent = error?.message || 'File upload failed.';
                        status.className = 'upload-status error';
                        notify('error', status.textContent);
                    }
                }
            });
        };

        const buildSocialPayload = () => {
            const data = {};
            document.querySelectorAll('.social-toggle').forEach(toggle => {
                if (!toggle.checked) return;
                const id = toggle.dataset.socialId;
                const input = document.querySelector(`[data-social-input="${CSS.escape(id)}"]`);
                const value = input?.value.trim() || '';
                if (value) data[id] = value;
            });

            const existingEmptyMarker = form.querySelector('input[name="social_media[__empty]"]');
            if (existingEmptyMarker) existingEmptyMarker.value = '';
            Object.keys(data).forEach(key => {
                let input = form.querySelector(`input[name="social_media[${CSS.escape(key)}]"]`);
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `social_media[${key}]`;
                    input.dataset.generatedSocial = '1';
                    form.appendChild(input);
                }
                input.value = data[key];
            });
            form.querySelectorAll('input[data-generated-social="1"]').forEach(input => {
                const key = input.name.match(/social_media\[([^\]]+)\]/)?.[1];
                if (!key || !Object.prototype.hasOwnProperty.call(data, key)) input.remove();
            });
        };

        const checkPendingUploads = () => [...document.querySelectorAll('.document-row')].some(row => {
            const status = row.querySelector('.upload-status');
            return status && /Uploading/.test(status.textContent || '');
        });

        const save = async goNext => {
            if (saving) return;
            const active = sections.find(section => Number(section.dataset.section) === currentStep);
            if (!active || !currentInvalid(active)) return;
            if (checkPendingUploads()) {
                notify('warning', 'Please wait for document uploads to finish before saving.');
                return;
            }

            syncSelectPayloads();
            syncCoursePayload();
            buildSocialPayload();

            setSaving(true);
            clearFieldErrors(active);

            const formData = new FormData(form);
            formData.set('stage', String(currentStep));

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                let data = null;
                try { data = await response.json(); } catch (e) { data = null; }

                if (!response.ok || data?.status === false) {
                    if (response.status === 422 && data?.errors) {
                        showValidationErrors(data.errors);
                    }
                    throw new Error(data?.message || 'Unable to save your profile.');
                }

                applyCompletion(data?.completion || {});
                markClean();
                notify('success', data?.message || 'Profile saved successfully.');

                if (goNext) {
                    if (currentStep < 5) {
                        showStep(currentStep + 1);
                    } else if (nextBtn) {
                        nextBtn.innerHTML = '<i class="mdi mdi-check-circle-outline me-1"></i>Profile Saved';
                    }
                }
            } catch (error) {
                console.error('Employee profile save error:', error);
                if (!alertBox?.classList.contains('show')) notify('error', error?.message || 'Unable to save your profile.');
            } finally {
                setSaving(false);
            }
        };

        /* ---------- Events ---------- */
        stepCards.forEach(card => card.addEventListener('click', () => {
            if (saving) return;
            showStep(Number(card.dataset.goStep));
        }));


        communityIdUi?.addEventListener(
            'change',
            () => {
                markDirty();
                loadCasteOptions();
                scheduleLiveCompletion();
            }
        );

        document
            .querySelector('[name="religion_id"]')
            ?.addEventListener(
                'change',
                () => {
                    markDirty();
                    loadCasteOptions();
                    scheduleLiveCompletion();
                }
            );

        document.addEventListener(
            'change',
            event => {
                if (
                    event.target.classList.contains(
                        'qualification'
                    )
                ) {
                    const row =
                        event.target.closest(
                            '.education-row'
                        );

                    if (row) {
                        loadMajorOptions(
                            row,
                            event.target.value,
                            ''
                        );
                    }

                    applyQualificationRules();

                    return;
                }

                if (
                    event.target.name === 'is_Course'
                ) {
                    const yes =
                        event.target.value === 'Yes';

                    document
                        .getElementById(
                            'courseField'
                        )
                        ?.classList.toggle(
                            'd-none',
                            !yes
                        );

                    if (!yes) {
                        const input =
                            document.getElementById(
                                'courseTagInput'
                            );

                        if (input) {
                            input.value = '';
                        }
                    }

                    markDirty();
                }
            }
        );

        useMyLocationBtn?.addEventListener(
            'click',
            useCurrentLocation
        );

        clearLocationBtn?.addEventListener(
            'click',
            clearLocation
        );

        searchLocationBtn?.addEventListener(
            'click',
            searchLocation
        );

        locationSearch?.addEventListener(
            'keydown',
            event => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchLocation();
                }
            }
        );

        document
            .getElementById('courseTagInput')
            ?.addEventListener(
                'input',
                markDirty
            );

        document
            .getElementById('employeeAddress')
            ?.addEventListener(
                'input',
                event => {
                    const hidden =
                        document.getElementById(
                            'permanentAddressPayload'
                        );

                    if (hidden) {
                        hidden.value =
                            event.target.value;
                    }

                    markDirty();
                }
            );

        previousBtn?.addEventListener('click', () => {
            if (saving) return;
            showStep(Math.max(1, currentStep - 1));
        });

        saveBtn?.addEventListener('click', () => save(false));
        nextBtn?.addEventListener('click', () => save(true));

        form.addEventListener('input', event => {
            if (event.target.matches('input,textarea')) markDirty();
            scheduleLiveCompletion();
            if (event.target.name === 'mobile_no' || event.target.name === 'alternative_no' || event.target.name === 'spouse_mobile' || event.target.name === 'contact_person_no[]') {
                event.target.value = event.target.value.replace(/\D+/g, '');
            }
        });

        form.addEventListener('change', event => {
            markDirty();
            syncSelectPayloads();
            scheduleLiveCompletion();
            if (event.target === languagesUi || event.target === hobbyUi) clearFieldErrors();

            if (event.target.classList.contains('qualification')) {
                const row = event.target.closest('.education-row');
                if (row) {
                    loadMajorOptions(row, event.target.value, '');
                }
                applyQualificationRules();
            }

            if (event.target.classList.contains('social-toggle')) {
                const field = document.querySelector(`[data-social-input="${CSS.escape(event.target.dataset.socialId || '')}"]`)?.closest('.social-field');
                if (field) field.classList.toggle('d-none', !event.target.checked);
            }

            if (event.target.name === 'spouse_working') {
                const show = event.target.value === 'Yes';
                document.querySelectorAll('.spouse-working-fields').forEach(el => el.classList.toggle('d-none', !show));
            }

            if (event.target.name === 'marital_status') {
                if (maritalStatus && martialStatusPayload) martialStatusPayload.value = maritalStatus.value || '';
                spouseFields?.classList.toggle('d-none', event.target.value !== '1');
            }

            if (event.target.name === 'has_children') renderChildren();
            if (event.target.name === 'has_siblings') renderSiblings();
            if (event.target === vehicleCheck) vehicleFields?.classList.toggle('d-none', !vehicleCheck.checked);
            if (event.target.classList.contains('qualification')) applyQualificationRules();
        });

        document.addEventListener('click', async event => {
            const removeContact = event.target.closest('.remove-contact');
            if (removeContact) {
                removeContact.closest('.contact-row')?.remove();
                markDirty();
                return;
            }

            const removeEducation = event.target.closest('.remove-education');
            if (removeEducation) {
                removeEducation.closest('.education-row')?.remove();
                updateEducationHeading();
                applyQualificationRules();
                markDirty();
                return;
            }

            const removeDocument = event.target.closest('.remove-document');
            if (removeDocument) {
                const row = removeDocument.closest('.document-row');
                const filenames = parseJsonInput(row?.querySelector('.uploaded-files-payload')?.value, []);
                await Promise.all(filenames.map(clearTempFile));
                if (row) {
                    row.querySelectorAll('.select3').forEach(destroySelect2);
                    row.remove();
                }
                markDirty();
                return;
            }

            const tempRemove = event.target.closest('.temp-file-remove');
            if (tempRemove) {
                const chip = tempRemove.closest('.file-chip');
                const row = tempRemove.closest('.document-row');
                const filename = chip?.dataset.filename;
                const payload = row?.querySelector('.uploaded-files-payload');
                const filenames = parseJsonInput(payload?.value, []).filter(item => item !== filename);
                if (payload) payload.value = JSON.stringify(filenames);
                await clearTempFile(filename);
                chip?.remove();
                markDirty();
                return;
            }

            const existingDelete = event.target.closest('.remove-existing-document');
            if (existingDelete) {
                const card = existingDelete.closest('.existing-doc');
                const action = card?.querySelector('.attachment-action');
                const state = card?.querySelector('.existing-doc-state');
                if (!card || !action) return;
                if (action.value === 'delete') {
                    action.value = 'keep';
                    card.classList.remove('deleted');
                    existingDelete.innerHTML = '<i class="mdi mdi-delete-outline"></i>';
                    existingDelete.title = 'Mark for deletion';
                    if (state) state.textContent = 'Keeping this document';
                } else {
                    action.value = 'delete';
                    card.classList.add('deleted');
                    existingDelete.innerHTML = '<i class="mdi mdi-undo-variant"></i>';
                    existingDelete.title = 'Undo deletion';
                    if (state) state.textContent = 'Marked for deletion when you save';
                }
                markDirty();
                return;
            }
        });

        addContactBtn?.addEventListener('click', () => {
            const currentRows = [...document.querySelectorAll('.contact-row')];
            const row = document.createElement('div');
            row.className = 'repeatable-row contact-row';
            row.innerHTML = `
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <div class="repeat-title"><i class="mdi mdi-phone-in-talk-outline me-1 text-danger"></i>Emergency Contact ${currentRows.length + 1}</div>
                    <button type="button" class="btn btn-outline-danger remove-contact remove-btn" aria-label="Remove emergency contact"><i class="mdi mdi-delete-outline"></i></button>
                </div>
                <div class="row g-2">
                    <div class="col-md-4"><label class="form-label fw-semibold">Contact Name</label><input class="form-control" name="contact_person_name[]" maxlength="255"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Relation</label><select class="form-select select3" name="contact_person_relation[]" data-placeholder="Select relation">${makeRelationshipOptions('')}</select></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Mobile Number</label><input class="form-control" name="contact_person_no[]" maxlength="15" inputmode="numeric"></div>
                </div>`;
            contactsWrapper?.appendChild(row);
            initSelect3(row);
            markDirty();
        });

        addEducationBtn?.addEventListener('click', () => {
            const row = document.createElement('div');

            row.className = 'repeatable-row education-row';
            row.dataset.educationUid = String(++rowUid);

            row.innerHTML = `
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <div class="repeat-title">
                        <i class="mdi mdi-school-outline me-1 text-danger"></i>
                        Qualification
                    </div>

                    <button
                        type="button"
                        class="btn btn-outline-danger remove-education remove-btn"
                        aria-label="Remove qualification"
                    >
                        <i class="mdi mdi-delete-outline"></i>
                    </button>
                </div>

                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Qualification <span class="text-danger">*</span>
                        </label>
                        <select
                            class="form-select select3 qualification"
                            name="qualification_type[]"
                            data-placeholder="Select qualification"
                            required
                        >
                            ${makeQualificationOptions('')}
                        </select>
                    </div>

                    <div class="col-md-3 major-wrap">
                        <label class="form-label fw-semibold">
                            Major / Specialization
                        </label>
                        <select
                            class="form-select select3 major-select"
                            name="major[]"
                            data-placeholder="Select major"
                        >
                            <option value=""></option>
                        </select>
                    </div>

                    <div class="col-md-3 univ-wrap">
                        <label class="form-label fw-semibold">
                            Institute / University
                        </label>
                        <input
                            class="form-control university-input"
                            name="univ_name[]"
                            maxlength="255"
                            placeholder="Enter Institute / University"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Passing Year <span class="text-danger">*</span>
                        </label>
                        <input
                            class="form-control"
                            name="pass_year[]"
                            maxlength="4"
                            inputmode="numeric"
                            pattern="[0-9]{4}"
                            placeholder="YYYY"
                            required
                        >
                    </div>
                </div>
            `;

            educationWrapper?.appendChild(row);

            initSelect3(row);
            initEducationYearPicker(row);
            updateEducationHeading();
            applyQualificationRules();

            markDirty();
        });

        addDocumentBtn?.addEventListener('click', addDocumentRow);

        document.addEventListener('change', event => {
            if (event.target.classList.contains('document-input')) {
                const row = event.target.closest('.document-row');
                if (row) bindDocumentUpload(row);
            }
        });

        form.addEventListener('submit', event => event.preventDefault());

        window.addEventListener('beforeunload', event => {
            if (!dirty || saving) return;
            event.preventDefault();
            event.returnValue = '';
        });

        /* ---------- Initial state ---------- */
        renderChildren();
        renderSiblings();
        renderContacts();
        renderEducation();
        document.querySelectorAll('.document-row').forEach(bindDocumentUpload);

        initSelect3();
        initEducationYearPicker(educationWrapper);

        if (casteIdUi) {
            casteIdUi.dataset.savedValue =
                casteIdUi.value || '';
        }

        syncSelectPayloads();
        syncCoursePayload();
        showStep(1);
        scheduleLiveCompletion();

        initLocationMap();
        loadCasteOptions();

        // Leaflet needs a size recalculation after mobile rotation / browser chrome changes.
        let lastViewportWidth = window.innerWidth;
        window.addEventListener('resize', () => {
            if (Math.abs(window.innerWidth - lastViewportWidth) >= 20) {
                lastViewportWidth = window.innerWidth;
                if (locationMap) window.requestAnimationFrame(() => locationMap.invalidateSize(false));
            }
        }, { passive: true });
        window.visualViewport?.addEventListener('resize', () => {
            if (locationMap) window.requestAnimationFrame(() => locationMap.invalidateSize(false));
        }, { passive: true });

        /* Ensure social input visibility is aligned with saved values. */
        document.querySelectorAll('.social-toggle').forEach(toggle => {
            const input = document.querySelector(`[data-social-input="${CSS.escape(toggle.dataset.socialId || '')}"]`);
            input?.closest('.social-field')?.classList.toggle('d-none', !toggle.checked);
        });
    });
})();
</script>
@endsection
